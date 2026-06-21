<?php
// api/modules/get_mesure_by_id.php
// DANFANIMENT POS - Récupération d'une mesure par son ID

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
    exit;
}

$id_mesure = (int) $_GET['id'];
require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("SELECT * FROM danfaniment_mesures_client WHERE id_mesure = :id");
    $stmt->execute([':id' => $id_mesure]);
    $mesure = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'mesure' => $mesure]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>