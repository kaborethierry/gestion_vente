<?php
// api/modules/verifier_tables.php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Vérifier les tables
    $tables = ['danfaniment_prestataires', 'danfaniment_productions_prestataires', 'danfaniment_depenses'];
    $result = [];
    
    foreach ($tables as $table) {
        $stmt = $bdd->query("SHOW TABLES LIKE '$table'");
        $result[$table] = $stmt->rowCount() > 0;
    }
    
    // Compter les prestataires par type
    $stmt = $bdd->query("SELECT type_prestataire, COUNT(*) as total FROM danfaniment_prestataires GROUP BY type_prestataire");
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'tables' => $result,
        'prestataires_count' => $counts
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>