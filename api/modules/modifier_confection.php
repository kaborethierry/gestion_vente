<?php
// api/modules/modifier_confection.php
// DANFANIMENT POS - Modification d'une commande confection

session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if (empty($_POST['id_commande']) || empty($_POST['id_client']) || empty($_POST['type_tenue']) || 
    empty($_POST['date_livraison_prevue']) || empty($_POST['montant_total'])) {
    $_SESSION['err_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;
}

$id_commande = (int)$_POST['id_commande'];
$id_client = (int)$_POST['id_client'];
$type_tenue = trim($_POST['type_tenue']);
$date_livraison_prevue = trim($_POST['date_livraison_prevue']);
$description_tenue = trim($_POST['description_tenue'] ?? '');
$tissu_fourni_par = trim($_POST['tissu_fourni_par'] ?? 'client');
$quantite_tissu = !empty($_POST['quantite_tissu']) ? (float)$_POST['quantite_tissu'] : null;
$reference_tissu = trim($_POST['reference_tissu'] ?? '');
$montant_total = (float)$_POST['montant_total'];
$montant_avance = (float)($_POST['montant_avance'] ?? 0);
$instructions_couturier = trim($_POST['instructions_couturier'] ?? '');
$remarques = trim($_POST['remarques'] ?? '');

$prestataires_modif = $_POST['prestataires_modif'] ?? [];

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $bdd->beginTransaction();
    
    // Récupérer l'ancienne commande pour connaître l'ancienne avance
    $stmt = $bdd->prepare("SELECT montant_avance, numero_commande FROM danfaniment_commandes_confection WHERE id_commande = :id");
    $stmt->execute([':id' => $id_commande]);
    $ancienne_commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ancienne_commande) {
        throw new Exception("Commande non trouvée");
    }
    
    $ancien_montant_avance = (float)$ancienne_commande['montant_avance'];
    $difference_avance = $montant_avance - $ancien_montant_avance;

    $total_prestataires = 0;
    foreach ($prestataires_modif as $p) {
        $total_prestataires += (float)($p['montant'] ?? 0);
    }
    
    $sql = "
        UPDATE danfaniment_commandes_confection 
        SET id_client = :id_client,
            type_tenue = :type_tenue,
            description_tenue = :description_tenue,
            tissu_fourni_par = :tissu_fourni_par,
            quantite_tissu = :quantite_tissu,
            reference_tissu = :reference_tissu,
            date_livraison_prevue = :date_livraison_prevue,
            montant_total = :montant_total,
            montant_avance = :montant_avance,
            cout_couturier = :cout_couturier,
            instructions_couturier = :instructions_couturier,
            remarques = :remarques,
            updated_at = NOW()
        WHERE id_commande = :id_commande
    ";
    
    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':id_commande', $id_commande, PDO::PARAM_INT);
    $stmt->bindValue(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->bindValue(':type_tenue', $type_tenue, PDO::PARAM_STR);
    $stmt->bindValue(':description_tenue', $description_tenue, PDO::PARAM_STR);
    $stmt->bindValue(':tissu_fourni_par', $tissu_fourni_par, PDO::PARAM_STR);
    $stmt->bindValue(':quantite_tissu', $quantite_tissu, is_null($quantite_tissu) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':reference_tissu', $reference_tissu, PDO::PARAM_STR);
    $stmt->bindValue(':date_livraison_prevue', $date_livraison_prevue, PDO::PARAM_STR);
    $stmt->bindValue(':montant_total', $montant_total, PDO::PARAM_STR);
    $stmt->bindValue(':montant_avance', $montant_avance, PDO::PARAM_STR);
    $stmt->bindValue(':cout_couturier', $total_prestataires, PDO::PARAM_STR);
    $stmt->bindValue(':instructions_couturier', $instructions_couturier, PDO::PARAM_STR);
    $stmt->bindValue(':remarques', $remarques, PDO::PARAM_STR);
    $stmt->execute();
    
    // Mettre à jour le solde restant
    $nouveau_solde_restant = $montant_total - $montant_avance;
    $stmt = $bdd->prepare("UPDATE danfaniment_commandes_confection SET solde_restant = :solde WHERE id_commande = :id");
    $stmt->execute([':solde' => $nouveau_solde_restant, ':id' => $id_commande]);
    
    // Supprimer et réinsérer les prestataires
    $stmt = $bdd->prepare("DELETE FROM danfaniment_commande_prestataires WHERE id_commande = :id_commande");
    $stmt->execute([':id_commande' => $id_commande]);
    
    foreach ($prestataires_modif as $p) {
        $id_prestataire = (int)($p['id_prestataire'] ?? 0);
        $montant = (float)($p['montant'] ?? 0);
        $type_production = trim($p['type_production'] ?? 'tenue');
        
        if ($id_prestataire > 0) {
            $stmt = $bdd->prepare("
                INSERT INTO danfaniment_commande_prestataires (
                    id_commande, id_prestataire, type_production,
                    montant_unitaire, montant_total, statut_paiement, created_at
                ) VALUES (
                    :id_commande, :id_prestataire, :type_production,
                    :montant, :montant, 'en_attente', NOW()
                )
            ");
            $stmt->execute([
                ':id_commande' => $id_commande,
                ':id_prestataire' => $id_prestataire,
                ':type_production' => $type_production,
                ':montant' => $montant
            ]);
        }
    }
    
    // ✅ Mettre à jour la caisse si l'avance a changé
    if ($difference_avance != 0) {
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
            $nouveau_total = $ancien_total + $difference_avance;
            
            // Mettre à jour la caisse
            $stmt = $bdd->prepare("
                UPDATE danfaniment_caisses 
                SET total_avance_confection = COALESCE(total_avance_confection, 0) + :difference,
                    total_ventes_brut = COALESCE(total_ventes_brut, 0) + :difference,
                    total_ventes_net = COALESCE(total_ventes_net, 0) + :difference
                WHERE id_caisse = :id_caisse
            ");
            $stmt->execute([
                ':difference' => $difference_avance,
                ':id_caisse' => $caisse['id_caisse']
            ]);
            
            // Enregistrer l'opération
            $stmt = $bdd->prepare("
                INSERT INTO danfaniment_caisse_operations 
                (id_caisse, id_utilisateur, type_operation, reference, montant, montant_avant, montant_apres, description, created_at)
                VALUES 
                (:id_caisse, :id_user, 'correction', :reference, :montant, :montant_avant, :montant_apres, :description, NOW())
            ");
            $type_operation = $difference_avance > 0 ? 'Augmentation avance' : 'Diminution avance';
            $stmt->execute([
                ':id_caisse' => $caisse['id_caisse'],
                ':id_user' => $_SESSION['id'],
                ':reference' => $ancienne_commande['numero_commande'],
                ':montant' => abs($difference_avance),
                ':montant_avant' => $ancien_total,
                ':montant_apres' => $nouveau_total,
                ':description' => $type_operation . " sur commande #{$ancienne_commande['numero_commande']} - Différence: " . number_format($difference_avance, 0, ',', ' ') . " FCFA"
            ]);
        }
    }
    
    $bdd->commit();
    $_SESSION['mod_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;

} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur modification confection: " . $e->getMessage());
    $_SESSION['err_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;
}
?>