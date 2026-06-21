<?php
// api/modules/supprimer_prestataire.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if (empty($_GET['id_prestataire']) || !ctype_digit($_GET['id_prestataire'])) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}

$id_prestataire = (int)$_GET['id_prestataire'];

require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Vérifier si le prestataire a des productions
    $stmt = $bdd->prepare("SELECT COUNT(*) as nb FROM danfaniment_productions_prestataires WHERE id_prestataire = :id");
    $stmt->execute([':id' => $id_prestataire]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['nb'] > 0) {
        // Le prestataire a des productions, on les supprime d'abord
        $stmt = $bdd->prepare("DELETE FROM danfaniment_productions_prestataires WHERE id_prestataire = :id");
        $stmt->execute([':id' => $id_prestataire]);
    }
    
    // Vérifier si le prestataire a des dépenses
    $stmt = $bdd->prepare("SELECT COUNT(*) as nb FROM danfaniment_depenses WHERE id_prestataire = :id");
    $stmt->execute([':id' => $id_prestataire]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['nb'] > 0) {
        // Le prestataire a des dépenses, on les supprime d'abord ou on met id_prestataire à NULL
        $stmt = $bdd->prepare("UPDATE danfaniment_depenses SET id_prestataire = NULL WHERE id_prestataire = :id");
        $stmt->execute([':id' => $id_prestataire]);
    }
    
    // Maintenant on peut supprimer le prestataire
    $stmt = $bdd->prepare("DELETE FROM danfaniment_prestataires WHERE id_prestataire = :id");
    $stmt->execute([':id' => $id_prestataire]);
    
    $_SESSION['supr_presta'] = 1;
} catch (PDOException $e) {
    error_log("Erreur suppression prestataire: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
} catch (Exception $e) {
    error_log("Erreur suppression prestataire: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
}

header('Location: ../../pages/prestataires.php');
exit;
?>