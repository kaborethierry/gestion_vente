<?php
// api/modules/get_clients.php
// Récupère la liste des clients pour les formulaires

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("SELECT id_client, nom, prenom, telephone FROM danfaniment_clients WHERE supprimer = 'Non' ORDER BY nom, prenom");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($clients);
} catch (Exception $e) {
    echo json_encode([]);
}
?>