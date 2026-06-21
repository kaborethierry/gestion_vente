<?php
// api/modules/get_facture.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_facture = (int)($_GET['id_facture'] ?? 0);

if ($id_facture <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID facture invalide']);
    exit;
}

try {
    // Récupérer la facture
    $stmt = $bdd->prepare("
        SELECT f.*, 
               DATE_FORMAT(f.date_facture, '%d/%m/%Y') as date_facture_formatee,
               CONCAT(COALESCE(c.nom, ''), ' ', COALESCE(c.prenom, '')) as client_nom
        FROM danfaniment_factures f
        LEFT JOIN danfaniment_clients c ON f.id_client = c.id_client
        WHERE f.id_facture = :id
    ");
    $stmt->execute([':id' => $id_facture]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$facture) {
        echo json_encode(['success' => false, 'message' => 'Facture non trouvée']);
        exit;
    }
    
    // Récupérer les lignes
    $stmt = $bdd->prepare("SELECT * FROM danfaniment_facture_lignes WHERE id_facture = :id");
    $stmt->execute([':id' => $id_facture]);
    $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'facture' => $facture,
        'lignes' => $lignes
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
}
?>