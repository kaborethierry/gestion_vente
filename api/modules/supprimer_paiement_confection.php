<?php
// api/modules/supprimer_paiement_confection.php
// DANFANIMENT POS - Suppression d'un paiement sur une commande de confection

session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if (empty($_GET['id_paiement']) || !ctype_digit($_GET['id_paiement'])) {
    $_SESSION['err_paiement'] = 1;
    header('Location: ../../pages/confections.php');
    exit;
}

$id_paiement = (int)$_GET['id_paiement'];

try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->beginTransaction();
    
    // Récupérer le paiement et la commande associée
    $stmt = $bdd->prepare("
        SELECT p.*, c.numero_commande, c.montant_avance, c.montant_total 
        FROM danfaniment_paiements_confection p
        JOIN danfaniment_commandes_confection c ON p.id_commande = c.id_commande
        WHERE p.id_paiement = :id
    ");
    $stmt->execute([':id' => $id_paiement]);
    $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paiement) {
        throw new Exception("Paiement non trouvé");
    }
    
    $montant_paiement = (float)$paiement['montant'];
    $id_commande = $paiement['id_commande'];
    $numero_commande = $paiement['numero_commande'];
    
    // Mettre à jour la commande (réduire l'avance)
    $nouveau_montant_avance = $paiement['montant_avance'] - $montant_paiement;
    $nouveau_solde_restant = $paiement['montant_total'] - $nouveau_montant_avance;
    
    $stmt = $bdd->prepare("
        UPDATE danfaniment_commandes_confection 
        SET montant_avance = :avance,
            solde_restant = :solde,
            updated_at = NOW()
        WHERE id_commande = :id
    ");
    $stmt->execute([
        ':avance' => $nouveau_montant_avance,
        ':solde' => $nouveau_solde_restant,
        ':id' => $id_commande
    ]);
    
    // Supprimer le paiement
    $stmt = $bdd->prepare("DELETE FROM danfaniment_paiements_confection WHERE id_paiement = :id");
    $stmt->execute([':id' => $id_paiement]);
    
    // Mettre à jour la caisse
    $stmt = $bdd->prepare("
        SELECT id_caisse FROM danfaniment_caisses 
        WHERE statut = 'ouverte' 
        ORDER BY id_caisse DESC LIMIT 1
    ");
    $stmt->execute();
    $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caisse) {
        $stmt = $bdd->prepare("SELECT total_avance_confection FROM danfaniment_caisses WHERE id_caisse = :id_caisse");
        $stmt->execute([':id_caisse' => $caisse['id_caisse']]);
        $ancien_total = (float)$stmt->fetchColumn();
        $nouveau_total = $ancien_total - $montant_paiement;
        
        $stmt = $bdd->prepare("
            UPDATE danfaniment_caisses 
            SET total_avance_confection = COALESCE(total_avance_confection, 0) - :montant,
                total_ventes_brut = COALESCE(total_ventes_brut, 0) - :montant,
                total_ventes_net = COALESCE(total_ventes_net, 0) - :montant
            WHERE id_caisse = :id_caisse
        ");
        $stmt->execute([
            ':montant' => $montant_paiement,
            ':id_caisse' => $caisse['id_caisse']
        ]);
        
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
            ':montant' => $montant_paiement,
            ':montant_avant' => $ancien_total,
            ':montant_apres' => $nouveau_total,
            ':description' => "Suppression de paiement sur commande #{$numero_commande} - Montant: " . number_format($montant_paiement, 0, ',', ' ') . " FCFA"
        ]);
    }
    
    $bdd->commit();
    $_SESSION['paiement_supprime'] = 1;
    
} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur suppression paiement: " . $e->getMessage());
    $_SESSION['err_paiement'] = 1;
}

header('Location: ../../pages/confections.php');
exit;
?>