<?php
// api/modules/supprimer_confection.php
// DANFANIMENT POS - Suppression d'une commande confection

session_start();

// 1. Vérification de la session et du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation du paramètre GET id_commande
if (empty($_GET['id_commande']) || !ctype_digit($_GET['id_commande'])) {
    $_SESSION['err_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;
}

// 3. Cast sécurisé de l'ID
$idCommande = (int) $_GET['id_commande'];

try {
    // 4. Connexion PDO
    require_once __DIR__ . '/connect_db_pdo.php';
    
    $bdd->beginTransaction();

    // 5. Récupérer la commande pour connaître le montant de l'avance
    $stmt = $bdd->prepare("SELECT montant_avance, numero_commande FROM danfaniment_commandes_confection WHERE id_commande = :id");
    $stmt->execute([':id' => $idCommande]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$commande) {
        throw new Exception("Commande non trouvée");
    }
    
    $montant_avance = (float)$commande['montant_avance'];
    $numero_commande = $commande['numero_commande'];

    // 6. Supprimer les paiements associés
    $stmt = $bdd->prepare("DELETE FROM danfaniment_paiements_confection WHERE id_commande = :id");
    $stmt->execute([':id' => $idCommande]);

    // 7. Supprimer les prestataires associés
    $stmt = $bdd->prepare("DELETE FROM danfaniment_commande_prestataires WHERE id_commande = :id");
    $stmt->execute([':id' => $idCommande]);

    // 8. Supprimer la commande
    $stmt = $bdd->prepare("DELETE FROM danfaniment_commandes_confection WHERE id_commande = :id");
    $stmt->execute([':id' => $idCommande]);
    
    // ✅ Mettre à jour la caisse si une avance avait été payée
    if ($montant_avance > 0) {
        // Récupérer la session de caisse active
        $stmt = $bdd->prepare("
            SELECT id_caisse FROM danfaniment_caisses 
            WHERE statut = 'ouverte' 
            ORDER BY id_caisse DESC LIMIT 1
        ");
        $stmt->execute();
        $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$caisse) {
            $stmt = $bdd->prepare("
                SELECT id_caisse FROM danfaniment_caisses 
                WHERE id_utilisateur = :id_user AND statut = 'ouverte' 
                ORDER BY id_caisse DESC LIMIT 1
            ");
            $stmt->execute([':id_user' => $_SESSION['id']]);
            $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($caisse) {
            // Récupérer l'ancien total avant modification
            $stmt = $bdd->prepare("SELECT total_avance_confection FROM danfaniment_caisses WHERE id_caisse = :id_caisse");
            $stmt->execute([':id_caisse' => $caisse['id_caisse']]);
            $ancien_total = (float)$stmt->fetchColumn();
            $nouveau_total = $ancien_total - $montant_avance;
            
            // Mettre à jour la caisse (soustraction)
            $stmt = $bdd->prepare("
                UPDATE danfaniment_caisses 
                SET total_avance_confection = COALESCE(total_avance_confection, 0) - :montant,
                    total_ventes_brut = COALESCE(total_ventes_brut, 0) - :montant,
                    total_ventes_net = COALESCE(total_ventes_net, 0) - :montant
                WHERE id_caisse = :id_caisse
            ");
            $stmt->execute([
                ':montant' => $montant_avance,
                ':id_caisse' => $caisse['id_caisse']
            ]);
            
            // Enregistrer l'opération dans le journal de caisse
            $stmt = $bdd->prepare("
                INSERT INTO danfaniment_caisse_operations 
                (id_caisse, id_utilisateur, type_operation, reference, montant, montant_avant, montant_apres, description, created_at)
                VALUES 
                (:id_caisse, :id_user, 'correction', :reference, :montant, :montant_avant, :montant_apres, :description, NOW())
            ");
            $stmt->execute([
                ':id_caisse' => $caisse['id_caisse'],
                ':id_user' => $_SESSION['id'],
                ':reference' => $numero_commande,
                ':montant' => $montant_avance,
                ':montant_avant' => $ancien_total,
                ':montant_apres' => $nouveau_total,
                ':description' => "Suppression de commande #{$numero_commande} - Avance retirée: " . number_format($montant_avance, 0, ',', ' ') . " FCFA"
            ]);
        }
    }

    $bdd->commit();
    $_SESSION['supr_conf'] = 1;

} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur suppression confection: " . $e->getMessage());
    $_SESSION['err_conf'] = 1;
}

header('Location: ../../pages/confections.php');
exit;
?>