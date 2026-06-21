<?php
// api/modules/ajouter_paiement_confection.php
// DANFANIMENT POS - Ajout d'un paiement supplémentaire sur une commande de confection

session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/confections.php');
    exit;
}

try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->beginTransaction();

    $id_commande = (int)($_POST['id_commande'] ?? 0);
    $montant = (float)($_POST['montant'] ?? 0);
    $mode_paiement = trim($_POST['mode_paiement'] ?? 'especes');
    $reference_transaction = trim($_POST['reference_transaction'] ?? '');
    $id_utilisateur = $_SESSION['id'];

    if ($id_commande <= 0 || $montant <= 0) {
        throw new Exception("Champs obligatoires manquants");
    }

    // Récupérer la commande
    $stmt = $bdd->prepare("
        SELECT id_commande, numero_commande, montant_avance, montant_total, solde_restant 
        FROM danfaniment_commandes_confection 
        WHERE id_commande = :id
    ");
    $stmt->execute([':id' => $id_commande]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        throw new Exception("Commande non trouvée");
    }

    // Vérifier que le montant ne dépasse pas le solde restant
    if ($montant > $commande['solde_restant']) {
        throw new Exception("Le montant du paiement ne peut pas dépasser le solde restant de " . number_format($commande['solde_restant'], 0, ',', ' ') . " FCFA");
    }

    // Calculer les nouveaux montants
    $nouveau_montant_avance = $commande['montant_avance'] + $montant;
    $nouveau_solde_restant = $commande['montant_total'] - $nouveau_montant_avance;

    // Mettre à jour la commande
    $stmt = $bdd->prepare("
        UPDATE danfaniment_commandes_confection 
        SET montant_avance = :nouveau_montant_avance,
            solde_restant = :nouveau_solde_restant,
            updated_at = NOW()
        WHERE id_commande = :id
    ");
    $stmt->execute([
        ':nouveau_montant_avance' => $nouveau_montant_avance,
        ':nouveau_solde_restant' => $nouveau_solde_restant,
        ':id' => $id_commande
    ]);

    // Enregistrer le paiement
    $type_paiement = ($nouveau_solde_restant <= 0) ? 'solde' : 'acompte_supplementaire';
    
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_paiements_confection 
        (id_commande, id_utilisateur, montant, type_paiement, mode_paiement, reference_transaction, created_at)
        VALUES 
        (:id_commande, :id_user, :montant, :type_paiement, :mode_paiement, :reference, NOW())
    ");
    $stmt->execute([
        ':id_commande' => $id_commande,
        ':id_user' => $id_utilisateur,
        ':montant' => $montant,
        ':type_paiement' => $type_paiement,
        ':mode_paiement' => $mode_paiement,
        ':reference' => $reference_transaction
    ]);

    // ✅ AJOUT IMPORTANT : Mettre à jour la caisse active
    // Récupérer la session de caisse active
    $stmt = $bdd->prepare("
        SELECT id_caisse, total_especes, total_carte, total_mobile_money, total_virement 
        FROM danfaniment_caisses 
        WHERE statut = 'ouverte' 
        ORDER BY id_caisse DESC LIMIT 1
    ");
    $stmt->execute();
    $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si aucune caisse ouverte trouvée, essayer avec l'utilisateur connecté
    if (!$caisse) {
        $stmt = $bdd->prepare("
            SELECT id_caisse, total_especes, total_carte, total_mobile_money, total_virement 
            FROM danfaniment_caisses 
            WHERE id_utilisateur = :id_user AND statut = 'ouverte' 
            ORDER BY id_caisse DESC LIMIT 1
        ");
        $stmt->execute([':id_user' => $_SESSION['id']]);
        $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($caisse) {
        // Récupérer les valeurs avant modification pour le journal
        $ancien_total_avance = 0;
        $stmt = $bdd->prepare("SELECT total_avance_confection FROM danfaniment_caisses WHERE id_caisse = :id_caisse");
        $stmt->execute([':id_caisse' => $caisse['id_caisse']]);
        $ancien_total_avance = (float)$stmt->fetchColumn();
        
        $nouveau_total_avance = $ancien_total_avance + $montant;
        
        // Mettre à jour la caisse : ajouter le paiement au total des confections
        $stmt = $bdd->prepare("
            UPDATE danfaniment_caisses 
            SET total_avance_confection = COALESCE(total_avance_confection, 0) + :montant,
                total_ventes_brut = COALESCE(total_ventes_brut, 0) + :montant,
                total_ventes_net = COALESCE(total_ventes_net, 0) + :montant
            WHERE id_caisse = :id_caisse
        ");
        $stmt->execute([
            ':montant' => $montant,
            ':id_caisse' => $caisse['id_caisse']
        ]);

        // Mettre à jour les totaux par mode de paiement
        if ($mode_paiement === 'especes') {
            $stmt = $bdd->prepare("UPDATE danfaniment_caisses SET total_especes = COALESCE(total_especes, 0) + :montant WHERE id_caisse = :id_caisse");
            $stmt->execute([
                ':montant' => $montant,
                ':id_caisse' => $caisse['id_caisse']
            ]);
        } elseif ($mode_paiement === 'carte') {
            $stmt = $bdd->prepare("UPDATE danfaniment_caisses SET total_carte = COALESCE(total_carte, 0) + :montant WHERE id_caisse = :id_caisse");
            $stmt->execute([
                ':montant' => $montant,
                ':id_caisse' => $caisse['id_caisse']
            ]);
        } elseif ($mode_paiement === 'mobile_money') {
            $stmt = $bdd->prepare("UPDATE danfaniment_caisses SET total_mobile_money = COALESCE(total_mobile_money, 0) + :montant WHERE id_caisse = :id_caisse");
            $stmt->execute([
                ':montant' => $montant,
                ':id_caisse' => $caisse['id_caisse']
            ]);
        } elseif ($mode_paiement === 'virement') {
            $stmt = $bdd->prepare("UPDATE danfaniment_caisses SET total_virement = COALESCE(total_virement, 0) + :montant WHERE id_caisse = :id_caisse");
            $stmt->execute([
                ':montant' => $montant,
                ':id_caisse' => $caisse['id_caisse']
            ]);
        }

        // Enregistrer l'opération dans le journal de caisse avec les champs requis
        $stmt = $bdd->prepare("
            INSERT INTO danfaniment_caisse_operations 
            (id_caisse, id_utilisateur, type_operation, reference, montant, montant_avant, montant_apres, description, created_at)
            VALUES 
            (:id_caisse, :id_user, 'depot', :reference, :montant, :montant_avant, :montant_apres, :description, NOW())
        ");
        $stmt->execute([
            ':id_caisse' => $caisse['id_caisse'],
            ':id_user' => $_SESSION['id'],
            ':reference' => $commande['numero_commande'],
            ':montant' => $montant,
            ':montant_avant' => $ancien_total_avance,
            ':montant_apres' => $nouveau_total_avance,
            ':description' => "Paiement " . ($type_paiement === 'solde' ? 'final' : 'supplémentaire') . " sur commande de confection #{$commande['numero_commande']} - Montant: " . number_format($montant, 0, ',', ' ') . " FCFA"
        ]);
    } else {
        error_log("Aucune session de caisse active trouvée pour le paiement de confection");
    }

    $bdd->commit();
    
    $_SESSION['paiement_ajoute'] = 1;
    header('Location: ../../pages/confections.php');
    exit;

} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur ajout paiement confection: " . $e->getMessage());
    $_SESSION['err_paiement'] = 1;
    $_SESSION['err_paiement_message'] = $e->getMessage();
    header('Location: ../../pages/confections.php');
    exit;
}
?>