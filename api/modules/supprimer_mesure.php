<?php
session_start();

if (empty($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if (!isset($_GET['id_mesure']) || !is_numeric($_GET['id_mesure'])) {
    $_SESSION['err_mesure'] = 1;
    header('Location: ../../pages/mesures.php');
    exit;
}

$id_mesure = (int) $_GET['id_mesure'];

try {
    require_once __DIR__ . '/connect_db_pdo.php';
    
    $stmt = $bdd->prepare("SELECT id_client FROM danfaniment_mesures_client WHERE id_mesure = :id");
    $stmt->execute([':id' => $id_mesure]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_client = $client['id_client'] ?? 0;
    
    $stmt = $bdd->prepare("DELETE FROM danfaniment_mesures_client WHERE id_mesure = :id");
    $stmt->execute([':id' => $id_mesure]);
    
    $_SESSION['suppr_mesure'] = 1;
    header('Location: ../../pages/mesures.php?client_id=' . $id_client);
    exit;
} catch (Exception $e) {
    $_SESSION['err_mesure'] = 1;
    header('Location: ../../pages/mesures.php');
    exit;
}
?>