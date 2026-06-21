<?php
// Fichier : api/modules/intervention_data.php

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // 🔐 Sécurité : accès restreint à l'Admin
    if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php'; // $bdd doit être défini

    // 📊 Paramètres DataTables
    $draw        = intval($_GET['draw'] ?? $_POST['draw'] ?? 0);
    $start       = max(0, intval($_GET['start'] ?? $_POST['start'] ?? 0));
    $length      = intval($_GET['length'] ?? $_POST['length'] ?? 10);
    $searchValue = trim($_GET['search']['value'] ?? $_POST['search']['value'] ?? '');

    // 📌 Total brut
    $recordsTotal = (int) $bdd->query("SELECT COUNT(*) FROM interventions WHERE supprimer = 'Non'")->fetchColumn();

    // 🔍 WHERE dynamique
    $where    = "WHERE i.supprimer = 'Non'";
    $bindings = [];

    if ($searchValue !== '') {
        $where .= " AND (
            i.type_intervention             LIKE :search
         OR i.statut                       LIKE :search
         OR i.priorite                     LIKE :search
         OR i.description                  LIKE :search
         OR i.remarques                    LIKE :search
         OR v.immatriculation              LIKE :search
         OR COALESCE(v.marque, '')         LIKE :search
         OR COALESCE(v.modele, '')         LIKE :search
         OR e.nom                          LIKE :search
         OR e.prenom                       LIKE :search
         OR CAST(i.kilometrage AS CHAR)    LIKE :search
         OR CAST(i.temps_estime AS CHAR)   LIKE :search
         OR CAST(i.temps_reel AS CHAR)     LIKE :search
         OR CAST(i.main_oeuvre_ht AS CHAR) LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // 📌 Total filtré
    $countStmt = $bdd->prepare("
        SELECT COUNT(*)
        FROM interventions i
        LEFT JOIN vehicules v ON v.id_vehicule = i.id_vehicule
        LEFT JOIN employes  e ON e.id_employe  = i.id_employe
        $where
    ");
    foreach ($bindings as $k => $v) {
        $countStmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    // 🔀 Tri dynamique
    $orderColIndex = intval($_GET['order'][0]['column'] ?? $_POST['order'][0]['column'] ?? 0);
    $orderDir      = (strtolower($_GET['order'][0]['dir'] ?? $_POST['order'][0]['dir'] ?? '') === 'asc') ? 'ASC' : 'DESC';

    $columnMap = [
        0  => 'i.id_intervention',
        1  => 'v.immatriculation',
        2  => 'e.nom, e.prenom',
        3  => 'i.type_intervention',
        4  => 'i.date_debut',
        5  => 'i.date_fin',
        6  => 'i.kilometrage',
        7  => 'i.statut',
        8  => 'i.priorite',
        9  => 'i.temps_estime',
        10 => 'i.temps_reel',
        11 => 'i.main_oeuvre_ht',
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'i.id_intervention') . " $orderDir";

    // 🔄 LIMIT conditionnel
    $limitSql = ($length !== -1) ? "LIMIT :start, :length" : "";

    // 📥 Sélection principale
    $stmt = $bdd->prepare("
        SELECT
            i.id_intervention,
            i.id_vehicule,
            i.id_employe,
            i.type_intervention,
            DATE_FORMAT(i.date_debut, '%Y-%m-%d %H:%i:%s') AS date_debut,
            DATE_FORMAT(i.date_fin,   '%Y-%m-%d %H:%i:%s') AS date_fin,
            i.kilometrage,
            i.statut,
            i.priorite,
            i.temps_estime,
            i.temps_reel,
            i.main_oeuvre_ht,
            COALESCE(i.description, '') AS description,
            COALESCE(i.remarques,   '') AS remarques,
            CONCAT(
                COALESCE(v.immatriculation, ''),
                ' | ',
                TRIM(CONCAT(COALESCE(v.marque, ''), ' ', COALESCE(v.modele, '')))
            ) AS vehicule_label,
            TRIM(CONCAT(COALESCE(e.nom, ''), ' ', COALESCE(e.prenom, ''))) AS employe_label
        FROM interventions i
        LEFT JOIN vehicules v ON v.id_vehicule = i.id_vehicule
        LEFT JOIN employes  e ON e.id_employe  = i.id_employe
        $where
        ORDER BY $orderBy
        $limitSql
    ");
    foreach ($bindings as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    if ($length !== -1) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 📦 Réponse DataTables
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur serveur: ' . $e->getMessage(),
        'hint'  => 'Vérifiez connect_db_pdo.php, jointures ou param GET mal formé'
    ]);
}
