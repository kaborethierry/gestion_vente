<?php
session_start();

// Seul un Admin peut supprimer un paiement
if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_paiement = intval($_GET['id_paiement'] ?? 0);

if ($id_paiement <= 0) {
    $_SESSION['err_paiement'] = 1;
    header('Location: ../../pages/paiements.php');
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Récupérer l'id_commande avant suppression
    $stmt = $bdd->prepare("SELECT id_commande FROM danfaniment_paiements_confection WHERE id_paiement = :id");
    $stmt->execute([':id' => $id_paiement]);
    $id_commande = $stmt->fetchColumn();
    
    if (!$id_commande) {
        throw new Exception("Paiement non trouvé");
    }
    
    // Supprimer le paiement
    $stmt = $bdd->prepare("DELETE FROM danfaniment_paiements_confection WHERE id_paiement = :id");
    $stmt->execute([':id' => $id_paiement]);
    
    // Recalculer le total des paiements et mettre à jour la commande
    $stmt = $bdd->prepare("
        UPDATE danfaniment_commandes_confection c
        SET c.montant_avance = (
            SELECT COALESCE(SUM(montant), 0)
            FROM danfaniment_paiements_confection
            WHERE id_commande = c.id_commande
        ),
        c.solde_restant = c.montant_total - (
            SELECT COALESCE(SUM(montant), 0)
            FROM danfaniment_paiements_confection
            WHERE id_commande = c.id_commande
        ),
        c.updated_at = NOW()
        WHERE c.id_commande = :id_commande
    ");
    $stmt->execute([':id_commande' => $id_commande]);
    
    $bdd->commit();
    
    $_SESSION['paiement_supprime'] = 1;
    
} catch (Exception $e) {
    $bdd->rollBack();
    $_SESSION['err_paiement'] = 1;
}

header('Location: ../../pages/paiements.php');
exit;
?>