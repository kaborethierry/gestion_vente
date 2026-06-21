<?php
// Fichier : api/modules/mouvement_data.php

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // 🔐 Autorisation : accès réservé à l'Admin
    if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd

    // 📊 Paramètres DataTables
    $draw        = intval($_GET['draw'] ?? $_POST['draw'] ?? 0);
    $start       = max(0, intval($_GET['start'] ?? $_POST['start'] ?? 0));
    $length      = intval($_GET['length'] ?? $_POST['length'] ?? 10);
    $searchValue = trim($_GET['search']['value'] ?? $_POST['search']['value'] ?? '');

    // 📌 Total brut (sans filtre)
    $totalStmt = $bdd->query("SELECT COUNT(*) FROM mouvements_stock WHERE supprimer = 'Non'");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // 🔎 Construction du WHERE dynamique
    $where    = "WHERE m.supprimer = 'Non'";
    $bindings = [];

    if ($searchValue !== '') {
        $where .= " AND (
            p.reference           LIKE :search
         OR p.designation         LIKE :search
         OR m.type_mouvement      LIKE :search
         OR m.motif               LIKE :search
         OR CAST(m.quantite AS CHAR) LIKE :search
         OR DATE_FORMAT(m.date_mouvement, '%Y-%m-%d %H:%i:%s') LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // 📌 Total filtré
    $countSql = "
        SELECT COUNT(*)
        FROM mouvements_stock m
        LEFT JOIN pieces p ON p.id_piece = m.id_piece
        $where
    ";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $key => $val) {
        $countStmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    // 🔀 Tri dynamique
    $orderColIndex = intval($_GET['order'][0]['column'] ?? $_POST['order'][0]['column'] ?? 0);
    $orderDirInput = strtolower($_GET['order'][0]['dir'] ?? $_POST['order'][0]['dir'] ?? 'desc');
    $orderDir      = $orderDirInput === 'asc' ? 'ASC' : 'DESC';

    $columnMap = [
        0 => 'm.id_mouvement',
        1 => 'p.reference',
        2 => 'm.type_mouvement',
        3 => 'm.quantite',
        4 => 'm.motif',
        5 => 'm.date_mouvement'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'm.id_mouvement') . " $orderDir";

    // 🔄 Requête principale
    $useLimit = $length !== -1;
    $limitSql = $useLimit ? "LIMIT :start, :length" : "";

    $dataSql = "
        SELECT
            m.id_mouvement,
            m.id_piece,
            m.type_mouvement,
            m.quantite,
            m.motif,
            DATE_FORMAT(m.date_mouvement, '%Y-%m-%d %H:%i:%s') AS date_mouvement,
            p.reference,
            p.designation,
            CONCAT(p.reference, ' — ', p.designation) AS piece_label
        FROM mouvements_stock m
        LEFT JOIN pieces p ON p.id_piece = m.id_piece
        $where
        ORDER BY $orderBy
        $limitSql
    ";
    $dataStmt = $bdd->prepare($dataSql);

    foreach ($bindings as $key => $val) {
        $dataStmt->bindValue($key, $val, PDO::PARAM_STR);
    }

    if ($useLimit) {
        $dataStmt->bindValue(':start', $start, PDO::PARAM_INT);
        $dataStmt->bindValue(':length', $length, PDO::PARAM_INT);
    }

    $dataStmt->execute();
    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    // 🧾 Réponse finale JSON
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
