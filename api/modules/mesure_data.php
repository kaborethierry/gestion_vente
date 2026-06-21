<?php
// api/modules/mesure_data.php
// DANFANIMENT POS - Récupération des données mesures pour DataTables

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || !isset($_GET['id_client']) || !is_numeric($_GET['id_client'])) {
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

$id_client = (int) $_GET['id_client'];
require_once __DIR__ . '/connect_db_pdo.php';

try {
    $draw = intval($_REQUEST['draw'] ?? 0);
    $start = intval($_REQUEST['start'] ?? 0);
    $length = intval($_REQUEST['length'] ?? 10);
    $searchValue = trim($_REQUEST['search']['value'] ?? '');

    $totalStmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_mesures_client WHERE id_client = :id");
    $totalStmt->execute([':id' => $id_client]);
    $recordsTotal = (int) $totalStmt->fetchColumn();

    $where = "WHERE id_client = :id_client";
    $bindings = [':id_client' => $id_client];
    if ($searchValue !== '') {
        $where .= " AND (CAST(version AS CHAR) LIKE :search OR date_mesure LIKE :search)";
        $bindings[':search'] = "%{$searchValue}%";
    }

    $countSql = "SELECT COUNT(*) FROM danfaniment_mesures_client $where";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) {
        $countStmt->bindValue($k, $v);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    $dataSql = "
        SELECT 
            id_mesure, 
            version, 
            DATE_FORMAT(date_mesure, '%d/%m/%Y %H:%i') AS date_mesure, 
            COALESCE(poitrine, '-') AS poitrine,
            COALESCE(tour_taille, '-') AS tour_taille,
            COALESCE(bassin, '-') AS bassin,
            COALESCE(long_robe, '-') AS long_robe
        FROM danfaniment_mesures_client 
        $where 
        ORDER BY version DESC 
        LIMIT :start, :length
    ";
    $dataStmt = $bdd->prepare($dataSql);
    foreach ($bindings as $k => $v) {
        $dataStmt->bindValue($k, $v);
    }
    $dataStmt->bindValue(':start', $start, PDO::PARAM_INT);
    $dataStmt->bindValue(':length', $length, PDO::PARAM_INT);
    $dataStmt->execute();
    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $rows
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>