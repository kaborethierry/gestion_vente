<?php
// api/modules/facture_data.php
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin' && ($_SESSION['role'] ?? '') !== 'caissier') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php';

    $draw = intval($_REQUEST['draw'] ?? 0);
    $start = intval($_REQUEST['start'] ?? 0);
    $length = intval($_REQUEST['length'] ?? 10);
    $searchValue = trim($_REQUEST['search']['value'] ?? '');
    
    $filtre_statut = trim($_REQUEST['filtre_statut'] ?? '');
    $filtre_date_debut = trim($_REQUEST['filtre_date_debut'] ?? '');
    $filtre_date_fin = trim($_REQUEST['filtre_date_fin'] ?? '');
    $filtre_client = trim($_REQUEST['filtre_client'] ?? '');

    $totalStmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_factures");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    $where = "WHERE 1=1";
    $bindings = [];
    
    if ($searchValue !== '') {
        $where .= " AND (f.numero_facture LIKE :search OR CONCAT(c.nom, ' ', c.prenom) LIKE :search)";
        $bindings[':search'] = "%{$searchValue}%";
    }
    
    if ($filtre_statut !== '') {
        $where .= " AND f.statut = :statut";
        $bindings[':statut'] = $filtre_statut;
    }
    
    if ($filtre_date_debut !== '') {
        $where .= " AND DATE(f.date_facture) >= :date_debut";
        $bindings[':date_debut'] = $filtre_date_debut;
    }
    
    if ($filtre_date_fin !== '') {
        $where .= " AND DATE(f.date_facture) <= :date_fin";
        $bindings[':date_fin'] = $filtre_date_fin;
    }
    
    if ($filtre_client !== '') {
        $where .= " AND (c.nom LIKE :client OR c.prenom LIKE :client)";
        $bindings[':client'] = "%{$filtre_client}%";
    }

    $countSql = "SELECT COUNT(*) FROM danfaniment_factures f LEFT JOIN danfaniment_clients c ON f.id_client = c.id_client $where";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) {
        $countStmt->bindValue($k, $v);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    $orderColIndex = intval($_REQUEST['order'][0]['column'] ?? 0);
    $orderDirInput = strtolower($_REQUEST['order'][0]['dir'] ?? 'desc');
    $orderDir = $orderDirInput === 'asc' ? 'ASC' : 'DESC';
    $columnMap = [
        0 => 'f.numero_facture',
        1 => 'client_nom',
        2 => 'f.date_facture',
        3 => 'f.date_echeance',
        4 => 'f.total_ttc',
        5 => 'f.statut'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'f.id_facture') . " $orderDir";

    $dataSql = "
        SELECT 
            f.id_facture,
            f.numero_facture,
            f.date_facture,
            DATE_FORMAT(f.date_facture, '%d/%m/%Y') as date_facture_formatee,
            f.date_echeance,
            DATE_FORMAT(f.date_echeance, '%d/%m/%Y') as date_echeance_formatee,
            f.total_ttc,
            f.statut,
            CONCAT(COALESCE(c.nom, ''), ' ', COALESCE(c.prenom, '')) as client_nom,
            COALESCE(c.id_client, 0) as id_client,
            FORMAT(f.total_ttc, 0) as total_ttc_formate
        FROM danfaniment_factures f
        LEFT JOIN danfaniment_clients c ON f.id_client = c.id_client
        $where
        ORDER BY $orderBy
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
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur interne : ' . $e->getMessage()]);
}
?>