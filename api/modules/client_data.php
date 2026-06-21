<?php
// api/modules/client_data.php
// DANFANIMENT POS - Récupération des données clients pour DataTables

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // Vérification de l'authentification
    if (empty($_SESSION['id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php';

    $draw        = intval($_REQUEST['draw'] ?? 0);
    $start       = intval($_REQUEST['start'] ?? 0);
    $length      = intval($_REQUEST['length'] ?? 10);
    $searchValue = trim($_REQUEST['search']['value'] ?? '');

    // Total sans filtre
    $totalStmt    = $bdd->query("SELECT COUNT(*) FROM danfaniment_clients WHERE supprimer = 'Non'");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // Filtre
    $where    = "WHERE c.supprimer = 'Non'";
    $bindings = [];
    if ($searchValue !== '') {
        $where .= " AND (
            c.nom LIKE :search 
            OR c.prenom LIKE :search 
            OR c.telephone LIKE :search 
            OR c.ville LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // Total après filtre
    $countSql  = "SELECT COUNT(*) FROM danfaniment_clients c $where";
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
        0 => 'c.id_client',
        1 => 'c.nom',
        2 => 'c.telephone',
        3 => 'c.ville',
        4 => 'c.total_depense',
        5 => 'c.nombre_visites',
        6 => 'c.date_derniere_visite'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'c.id_client') . " $orderDir";

    // Requête de données avec calcul des totaux à partir des ventes
    $dataSql = "
        SELECT 
            c.id_client,
            c.nom,
            c.prenom,
            c.telephone,
            c.email,
            c.adresse,
            c.ville,
            c.notes,
            COALESCE((
                SELECT SUM(v.total_ttc) 
                FROM danfaniment_ventes v 
                WHERE v.id_client = c.id_client AND v.statut = 'valide'
            ), 0) AS total_depense_calcule,
            COALESCE((
                SELECT COUNT(*) 
                FROM danfaniment_ventes v 
                WHERE v.id_client = c.id_client AND v.statut = 'valide'
            ), 0) AS nombre_visites_calcule,
            c.date_derniere_visite,
            DATE_FORMAT(COALESCE(c.date_derniere_visite, v2.last_visit), '%d/%m/%Y') AS date_derniere_visite_formatee,
            c.points_fidelite
        FROM danfaniment_clients c
        LEFT JOIN (
            SELECT id_client, MAX(date_vente) as last_visit
            FROM danfaniment_ventes
            WHERE statut = 'valide'
            GROUP BY id_client
        ) v2 ON c.id_client = v2.id_client
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
    
    // Mise à jour des clients avec les valeurs calculées
    foreach ($rows as &$row) {
        $row['total_depense'] = $row['total_depense_calcule'];
        $row['nombre_visites'] = $row['nombre_visites_calcule'];
    }

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