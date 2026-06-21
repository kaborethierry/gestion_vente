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
    $totalStmt    = $bdd->query("SELECT COUNT(*) FROM danfaniment_caisses");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // Filtre
    $where    = "WHERE 1=1";
    $bindings = [];
    if ($searchValue !== '') {
        $where .= " AND (
            u.nom_complet LIKE :search
            OR c.id_session LIKE :search
            OR c.date_ouverture LIKE :search
            OR c.statut LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // Total après filtre
    $countSql  = "SELECT COUNT(*) FROM danfaniment_caisses c 
                  LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur 
                  $where";
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
        0 => 'c.id_caisse',
        1 => 'c.id_session',
        2 => 'u.nom_complet',
        3 => 'c.date_ouverture',
        4 => 'c.date_fermeture',
        5 => 'c.montant_initial',
        6 => 'c.montant_final_reel',
        7 => 'c.statut',
        8 => 'chiffre_affaires'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'c.id_caisse') . " $orderDir";

    // Requête de données avec calcul du chiffre d'affaires (ventes + confections)
    $dataSql = "
        SELECT
            c.id_caisse,
            c.id_session,
            c.id_utilisateur,
            u.nom_complet AS caissier,
            DATE_FORMAT(c.date_ouverture, '%d/%m/%Y %H:%i') AS date_ouverture,
            CASE 
                WHEN c.date_fermeture IS NOT NULL 
                THEN DATE_FORMAT(c.date_fermeture, '%d/%m/%Y %H:%i')
                ELSE '-'
            END AS date_fermeture,
            c.montant_initial,
            c.montant_final_reel,
            c.statut,
            c.notes_ouverture,
            c.total_ventes_net,
            c.total_avance_confection,
            -- Calcul du chiffre d'affaires total (ventes + confections)
            COALESCE(c.total_ventes_net, 0) + COALESCE(c.total_avance_confection, 0) AS chiffre_affaires
        FROM danfaniment_caisses c
        LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
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