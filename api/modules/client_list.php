<?php
// api/modules/client_list.php
// DANFANIMENT POS - Récupération de la liste des clients

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("
        SELECT id_client, nom, prenom, telephone 
        FROM danfaniment_clients 
        WHERE supprimer = 'Non' 
        ORDER BY nom, prenom
    ");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'clients' => $clients]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>