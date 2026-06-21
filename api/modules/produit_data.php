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
    
    // Filtres supplémentaires
    $filtre_categorie = trim($_REQUEST['filtre_categorie'] ?? '');
    $filtre_statut = trim($_REQUEST['filtre_statut'] ?? '');
    $filtre_stock_min = trim($_REQUEST['filtre_stock_min'] ?? '');
    $filtre_stock_max = trim($_REQUEST['filtre_stock_max'] ?? '');

    // Total sans filtre
    $totalStmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_produits");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // Construction du filtre WHERE
    $where = "WHERE 1=1";
    $bindings = [];
    
    if ($searchValue !== '') {
        $where .= " AND (p.code_produit LIKE :search OR p.nom LIKE :search)";
        $bindings[':search'] = "%{$searchValue}%";
    }
    
    if ($filtre_categorie !== '') {
        $where .= " AND p.categorie = :categorie";
        $bindings[':categorie'] = $filtre_categorie;
    }
    
    if ($filtre_statut !== '') {
        $where .= " AND p.statut = :statut";
        $bindings[':statut'] = $filtre_statut;
    }
    
    if ($filtre_stock_min !== '' && is_numeric($filtre_stock_min)) {
        $where .= " AND p.stock_actuel >= :stock_min";
        $bindings[':stock_min'] = (int)$filtre_stock_min;
    }
    
    if ($filtre_stock_max !== '' && is_numeric($filtre_stock_max)) {
        $where .= " AND p.stock_actuel <= :stock_max";
        $bindings[':stock_max'] = (int)$filtre_stock_max;
    }

    // Total après filtre
    $countSql = "SELECT COUNT(*) FROM danfaniment_produits p $where";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) {
        $countStmt->bindValue($k, $v);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    // Tri
    $orderColIndex = intval($_REQUEST['order'][0]['column'] ?? 0);
    $orderDirInput = strtolower($_REQUEST['order'][0]['dir'] ?? 'desc');
    $orderDir      = $orderDirInput === 'asc' ? 'ASC' : 'DESC';
    $columnMap = [
        1 => 'p.code_produit',
        2 => 'p.nom',
        3 => 'p.categorie',
        4 => 'p.prix_achat',
        5 => 'p.prix_vente',
        6 => 'p.stock_actuel',
        7 => 'p.statut'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'p.id_produit') . " $orderDir";

    // Requête de données - CORRECTION DU CHEMIN DE LA PHOTO
    $dataSql = "
        SELECT 
            p.id_produit,
            p.code_produit,
            p.nom,
            p.description,
            p.categorie,
            p.sous_categorie,
            p.prix_achat,
            p.prix_vente,
            p.stock_initial,
            p.stock_actuel,
            p.stock_minimum,
            p.unite_mesure,
            p.photo,
            p.statut,
            CASE 
                WHEN p.categorie = 'habits_traditionnels' THEN 'Habits traditionnels'
                WHEN p.categorie = 'pagnes' THEN 'Pagnes'
                WHEN p.categorie = 'vetements' THEN 'Vêtements'
                WHEN p.categorie = 'accessoires' THEN 'Accessoires'
                ELSE p.categorie
            END AS categorie_libelle,
            CASE 
                WHEN p.statut = 'actif' THEN '<span class=\"badge badge-success\">Actif</span>'
                ELSE '<span class=\"badge badge-danger\">Inactif</span>'
            END AS statut_badge,
            FORMAT(p.prix_achat, 0) AS prix_achat_formate,
            FORMAT(p.prix_vente, 0) AS prix_vente_formate,
            p.prix_achat AS prix_achat_valeur,
            p.prix_vente AS prix_vente_valeur,
            CASE 
                WHEN p.photo IS NOT NULL AND p.photo != '' 
                THEN CONCAT('<img src=\"/garagee/uploads/produits/', p.photo, '\" style=\"width: 40px; height: 40px; object-fit: cover;\">')
                ELSE '<i class=\"fa fa-image fa-2x text-muted\"></i>'
            END AS photo_html
        FROM danfaniment_produits p
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