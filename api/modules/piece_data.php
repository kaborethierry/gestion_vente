<?php
// Fichier : api/modules/piece_data.php

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
    $totalStmt = $bdd->query("SELECT COUNT(*) FROM pieces WHERE supprimer = 'Non'");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // 🔎 Construction du WHERE dynamique
    $where      = "WHERE p.supprimer = 'Non'";
    $bindings   = [];

    if ($searchValue !== '') {
        $where .= " AND (
            p.reference           LIKE :search
         OR p.designation         LIKE :search
         OR p.fournisseur         LIKE :search
         OR cp.libelle            LIKE :search
         OR CAST(p.prix_achat AS CHAR) LIKE :search
         OR CAST(p.prix_vente AS CHAR) LIKE :search
         OR CAST(p.quantite_stock AS CHAR) LIKE :search
         OR CAST(p.seuil_minimal AS CHAR) LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // 📌 Total filtré
    $countSql = "
        SELECT COUNT(*)
        FROM pieces p
        LEFT JOIN categories_pieces cp ON cp.id_categorie = p.id_categorie
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
        0 => 'p.id_piece',
        1 => 'p.reference',
        2 => 'p.designation',
        3 => 'p.prix_achat',
        4 => 'p.prix_vente',
        5 => 'p.quantite_stock',
        6 => 'p.seuil_minimal',
        7 => 'p.fournisseur',
        8 => 'cp.libelle',
        9 => 'p.date_ajout'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'p.id_piece') . " $orderDir";

    // 🔄 Requête principale
    $useLimit = $length !== -1;
    $limitSql = $useLimit ? "LIMIT :start, :length" : "";

    $dataSql = "
        SELECT
            p.id_piece,
            p.reference,
            p.designation,
            p.prix_achat,
            p.prix_vente,
            p.quantite_stock,
            p.seuil_minimal,
            p.fournisseur,
            p.id_categorie,
            COALESCE(cp.libelle, '') AS nom_categorie,
            DATE_FORMAT(p.date_ajout, '%Y-%m-%d %H:%i:%s') AS date_ajout
        FROM pieces p
        LEFT JOIN categories_pieces cp ON cp.id_categorie = p.id_categorie
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

    // 🧾 Réponse JSON finale
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
