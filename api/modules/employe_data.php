<?php
// api/modules/employe_data.php
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

    // Total sans filtre
    $totalStmt    = $bdd->query("SELECT COUNT(*) FROM employes WHERE supprimer = 'Non'");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // Construction du WHERE pour le filtre
    $where    = "WHERE e.supprimer = 'Non'";
    $bindings = [];
    if ($searchValue !== '') {
        $where .= " AND (
              e.nom          LIKE :search
           OR e.prenom       LIKE :search
           OR e.poste        LIKE :search
           OR e.email        LIKE :search
           OR e.telephone    LIKE :search
           OR e.statut       LIKE :search
           OR DATE_FORMAT(e.date_embauche, '%d/%m/%Y') LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // Total après filtre
    $countSql = "
        SELECT COUNT(*)
          FROM employes e
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

    // Mapping des colonnes DataTables -> colonnes SQL
    $columnMap = [
        0 => 'e.id_employe',
        1 => 'e.nom',
        2 => 'e.prenom',
        3 => 'e.poste',
        4 => 'e.email',
        5 => 'e.telephone',
        6 => 'e.date_embauche',
        7 => 'e.salaire_base',
        8 => 'e.statut',
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'e.id_employe') . " $orderDir";

    // Limit/Pagination
    $limitClause = '';
    $useLimit = true;
    if ($length === -1) {
        $useLimit = false; // Afficher tout
    } else {
        $limitClause = "LIMIT :start, :length";
    }

    // Requête de données
    $dataSql = "
        SELECT
          e.id_employe,
          e.nom,
          e.prenom,
          e.poste,
          e.email,
          e.telephone,
          DATE_FORMAT(e.date_embauche, '%d/%m/%Y') AS date_embauche,
          e.salaire_base,
          e.statut
        FROM employes e
        $where
        ORDER BY $orderBy
        $limitClause
    ";
    $dataStmt = $bdd->prepare($dataSql);

    // Bindings du filtre
    foreach ($bindings as $k => $v) {
        $dataStmt->bindValue($k, $v, PDO::PARAM_STR);
    }

    // Bindings de la pagination
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

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur interne']);
}
