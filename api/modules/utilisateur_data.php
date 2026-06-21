<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // Vérification Admin
    if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php';

    // Paramètres DataTables
    $draw        = intval($_REQUEST['draw'] ?? 0);
    $start       = intval($_REQUEST['start'] ?? 0);
    $length      = intval($_REQUEST['length'] ?? 10);
    $searchValue = trim($_REQUEST['search']['value'] ?? '');

    // Total sans filtre
    $totalStmt    = $bdd->query("SELECT COUNT(*) FROM utilisateurs WHERE supprimer = 'Non'");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // Filtre
    $where    = "WHERE u.supprimer = 'Non'";
    $bindings = [];
    if ($searchValue !== '') {
        $where .= " AND (
            u.nom_complet       LIKE :search
            OR u.nom_utilisateur LIKE :search
            OR u.email           LIKE :search
            OR u.telephone       LIKE :search
            OR u.role            LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // Total après filtre
    $countSql  = "SELECT COUNT(*) FROM utilisateurs u $where";
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
    $columnMap = [
        0 => 'u.id_utilisateur',
        1 => 'u.nom_complet',
        2 => 'u.nom_utilisateur',
        3 => 'u.email',
        4 => 'u.telephone',
        5 => 'u.role',
        6 => 'u.dernier_acces',
        7 => 'u.actif'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'u.id_utilisateur') . " $orderDir";

    // Requête de données
    $dataSql = "
        SELECT
            u.id_utilisateur,
            u.nom_complet,
            u.nom_utilisateur,
            u.email,
            u.telephone,
            u.role,
            DATE_FORMAT(u.dernier_acces, '%d/%m/%Y %H:%i') AS dernier_acces,
            CASE WHEN u.actif = 1 THEN 'actif' ELSE 'inactif' END AS status,
            CASE WHEN u.actif = 1 THEN 'Actif' ELSE 'Inactif' END AS statut
        FROM utilisateurs u
        $where
        ORDER BY $orderBy
        LIMIT :start, :length
    ";
    $dataStmt = $bdd->prepare($dataSql);
    foreach ($bindings as $k => $v) {
        $dataStmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $dataStmt->bindValue(':start',  $start,  PDO::PARAM_INT);
    $dataStmt->bindValue(':length', $length, PDO::PARAM_INT);
    $dataStmt->execute();

    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur interne : ' . $e->getMessage()]);
}
?>