<?php
// api/modules/supprimer_facture.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if (empty($_GET['id_facture']) || !ctype_digit($_GET['id_facture'])) {
    $_SESSION['err_facture'] = 1;
    header('Location: ../../pages/facture.php');
    exit;
}

$id_facture = (int) $_GET['id_facture'];

try {
    require_once __DIR__ . '/connect_db_pdo.php';
    
    $bdd->beginTransaction();
    
    // Vérifier que la facture n'est pas payée
    $stmt = $bdd->prepare("SELECT statut FROM danfaniment_factures WHERE id_facture = :id");
    $stmt->execute([':id' => $id_facture]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$facture) {
        throw new Exception("Facture non trouvée");
    }
    
    if ($facture['statut'] === 'payee') {
        throw new Exception("Impossible de supprimer une facture payée");
    }
    
    // Supprimer les lignes
    $stmt = $bdd->prepare("DELETE FROM danfaniment_facture_lignes WHERE id_facture = :id");
    $stmt->execute([':id' => $id_facture]);
    
    // Supprimer la facture
    $stmt = $bdd->prepare("DELETE FROM danfaniment_factures WHERE id_facture = :id");
    $stmt->execute([':id' => $id_facture]);
    
    $bdd->commit();
    
    $_SESSION['facture_supprimee'] = 1;

} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur suppression facture: " . $e->getMessage());
    $_SESSION['err_facture'] = 1;
}

header('Location: ../../pages/facture.php');
exit;
?>