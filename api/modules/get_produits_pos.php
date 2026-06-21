<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("
        SELECT id_produit, code_produit, code_barre, nom, description, 
               categorie, prix_vente, stock_actuel, stock_minimum, 
               photo_path, statut
        FROM danfaniment_produits 
        WHERE statut = 'actif' AND stock_actuel > 0
        ORDER BY nom ASC
    ");
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($produits, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>