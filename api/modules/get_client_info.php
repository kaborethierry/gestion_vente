<?php
// api/modules/get_client_info.php
// DANFANIMENT POS - Récupération des informations d'un client

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID invalide']);
    exit;
}

$id_client = (int) $_GET['id'];
require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("
        SELECT id_client, nom, prenom, telephone 
        FROM danfaniment_clients 
        WHERE id_client = :id AND supprimer = 'Non'
    ");
    $stmt->execute([':id' => $id_client]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($client) {
        echo json_encode(['success' => true, 'client' => $client]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Client non trouvé']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>