<?php
// api/modules/categories_pieces_data.php
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // Vérification Admin
    if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (instance PDO)

    // Paramètres DataTables
    $draw        = intval($_REQUEST['draw'] ?? 0);
    $start       = max(0, intval($_REQUEST['start'] ?? 0));
    $length      = intval($_REQUEST['length'] ?? 10);
    $searchValue = trim($_REQUEST['search']['value'] ?? '');

    // Total sans filtre (uniquement non supprimées)
    $totalStmt    = $bdd->query("SELECT COUNT(*) FROM categories_pieces WHERE supprimer = 'Non'");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // WHERE pour le filtre
    $where    = "WHERE c.supprimer = 'Non'";
    $bindings = [];
    if ($searchValue !== '') {
        $where .= " AND (
              c.libelle     LIKE :search
           OR c.description LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // Total après filtre
    $countSql = "
        SELECT COUNT(*)
          FROM categories_pieces c
        $where
    ";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) {
        $countStmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    // Tri
    $orderColIndex = intval($_REQUEST['order'][0]['column'] ?? 0);
    $orderDirInput = strtolower($_REQUEST['order'][0]['dir'] ?? 'desc');
    $orderDir      = $orderDirInput === 'asc' ? 'ASC' : 'DESC';

    // Mapping colonnes DataTables -> SQL
    // Indexs attendus côté DataTables: 0 => N°, 1 => libellé, 2 => description
    $columnMap = [
        0 => 'c.id_categorie',
        1 => 'c.libelle',
        2 => 'c.description',
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'c.id_categorie') . " $orderDir";

    // Pagination
    $limitClause = '';
    $useLimit = true;
    if ($length === -1) {
        $useLimit = false; // Afficher tout
    } else {
        $limitClause = "LIMIT :start, :length";
    }

    // Données
    $dataSql = "
        SELECT
          c.id_categorie,
          c.libelle,
          c.description
        FROM categories_pieces c
        $where
        ORDER BY $orderBy
        $limitClause
    ";
    $dataStmt = $bdd->prepare($dataSql);

    // Bindings du filtre
    foreach ($bindings as $k => $v) {
        $dataStmt->bindValue($k, $v, PDO::PARAM_STR);
    }

    // Bindings pagination
    if ($useLimit) {
        $dataStmt->bindValue(':start', $start, PDO::PARAM_INT);
        $dataStmt->bindValue(':length', $length, PDO::PARAM_INT);
    }

    $dataStmt->execute();
    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur interne']);
}
