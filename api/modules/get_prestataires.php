<?php
// api/modules/get_prestataires.php
// DANFANIMENT POS - Récupération de la liste des prestataires

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode([]);
    exit;
}

$type_filter = $_GET['type'] ?? '';
$actif = isset($_GET['actif']) ? (int)$_GET['actif'] : 1;
require_once __DIR__ . '/connect_db_pdo.php';

try {
    $sql = "SELECT id_prestataire, nom, prenom, telephone, type_prestataire, actif 
            FROM danfaniment_prestataires 
            WHERE actif = :actif";
    $params = [':actif' => $actif];
    
    if (!empty($type_filter)) {
        $sql .= " AND type_prestataire = :type";
        $params[':type'] = $type_filter;
    }
    
    $sql .= " ORDER BY type_prestataire, nom, prenom";
    
    $stmt = $bdd->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($prestataires);
} catch (Exception $e) {
    error_log("Erreur get_prestataires: " . $e->getMessage());
    echo json_encode([]);
}
?>