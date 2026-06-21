<?php
// Fichier : api/modules/intervention_piece_data.php

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // 🔐 Autorisation : accès réservé à l'Admin
    if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)

    // 📊 Paramètres DataTables
    $draw        = intval($_GET['draw'] ?? $_POST['draw'] ?? 0);
    $start       = max(0, intval($_GET['start'] ?? $_POST['start'] ?? 0));
    $length      = intval($_GET['length'] ?? $_POST['length'] ?? 10);
    $searchValue = trim($_GET['search']['value'] ?? $_POST['search']['value'] ?? '');

    // 📌 Total brut (sans filtre)
    $totalStmt = $bdd->query("SELECT COUNT(*) FROM intervention_pieces WHERE supprimer IS NULL OR supprimer = 'Non'");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // 🔎 WHERE dynamique
    $where    = "WHERE (ip.supprimer IS NULL OR ip.supprimer = 'Non')";
    $bindings = [];

    if ($searchValue !== '') {
        $where .= " AND (
            CAST(ip.id AS CHAR)            LIKE :search
         OR CAST(ip.id_intervention AS CHAR) LIKE :search
         OR CAST(ip.id_piece AS CHAR)    LIKE :search
         OR p.reference                   LIKE :search
         OR p.designation                 LIKE :search
         OR v.immatriculation             LIKE :search
         OR v.marque                      LIKE :search
         OR v.modele                      LIKE :search
         OR i.type_intervention           LIKE :search
         OR i.statut                      LIKE :search
         OR CAST(ip.quantite AS CHAR)     LIKE :search
         OR CAST(ip.prix_unitaire AS CHAR)LIKE :search
         OR DATE_FORMAT(ip.date_ajout, '%Y-%m-%d %H:%i:%s') LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // 📌 Total filtré
    $countSql = "
        SELECT COUNT(*)
        FROM intervention_pieces ip
        INNER JOIN interventions i ON i.id_intervention = ip.id_intervention
        INNER JOIN pieces p        ON p.id_piece = ip.id_piece
        LEFT JOIN vehicules v      ON v.id_vehicule = i.id_vehicule
        $where
    ";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) {
        $countStmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    // 🔀 Tri dynamique
    $orderColIndex = intval($_GET['order'][0]['column'] ?? $_POST['order'][0]['column'] ?? 0);
    $orderDirInput = strtolower($_GET['order'][0]['dir'] ?? $_POST['order'][0]['dir'] ?? 'desc');
    $orderDir      = $orderDirInput === 'asc' ? 'ASC' : 'DESC';

    // Mapping colonnes DataTables -> SQL
    $columnMap = [
        0  => 'ip.id',
        1  => 'i.id_intervention',
        2  => 'v.immatriculation',
        3  => 'p.designation',
        4  => 'p.reference',
        5  => 'ip.quantite',
        6  => 'ip.prix_unitaire',
        7  => '(ip.quantite * ip.prix_unitaire)',
        8  => 'ip.date_ajout'
    ];

    // Sécurise l’expression ORDER BY (pas d’expression calculée brute)
    $orderBy = $columnMap[$orderColIndex] ?? 'ip.id';
    // Cas colonne 7 (montant total) -> tri sur quantite puis prix_unitaire
    if ($orderColIndex === 7) {
        $orderClause = "ip.quantite $orderDir, ip.prix_unitaire $orderDir";
    } else {
        $orderClause = "$orderBy $orderDir";
    }

    // 🔄 Limitation
    $useLimit = $length !== -1;
    $limitSql = $useLimit ? "LIMIT :start, :length" : "";

    // 🧠 Requête principale
    $dataSql = "
        SELECT
            ip.id,
            ip.id_intervention,
            ip.id_piece,
            ip.quantite,
            ip.prix_unitaire,
            (ip.quantite * ip.prix_unitaire) AS montant_total,
            DATE_FORMAT(ip.date_ajout, '%Y-%m-%d %H:%i:%s') AS date_ajout,
            -- Infos intervention
            i.type_intervention,
            i.statut AS statut_intervention,
            DATE_FORMAT(i.date_debut, '%Y-%m-%d %H:%i:%s') AS date_debut,
            -- Infos véhicule
            v.immatriculation,
            v.marque,
            v.modele,
            -- Infos pièce
            p.reference,
            p.designation
        FROM intervention_pieces ip
        INNER JOIN interventions i ON i.id_intervention = ip.id_intervention
        INNER JOIN pieces p        ON p.id_piece = ip.id_piece
        LEFT JOIN vehicules v      ON v.id_vehicule = i.id_vehicule
        $where
        ORDER BY $orderClause
        $limitSql
    ";

    $stmt = $bdd->prepare($dataSql);

    foreach ($bindings as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    if ($useLimit) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🧾 Réponse
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
