<?php
// api/modules/get_prestataire.php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(null);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(null);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("SELECT * FROM danfaniment_prestataires WHERE id_prestataire = :id");
    $stmt->execute([':id' => $id]);
    $prestataire = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($prestataire);
} catch (PDOException $e) {
    error_log("Erreur get_prestataire: " . $e->getMessage());
    echo json_encode(null);
}
?>