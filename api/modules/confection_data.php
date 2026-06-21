<?php
// api/modules/confection_data.php
// DANFANIMENT POS - Récupération des données commandes confection pour DataTables

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
    $filtre_statut = trim($_REQUEST['filtre_statut'] ?? '');
    $filtre_type = trim($_REQUEST['filtre_type'] ?? '');
    $filtre_date_debut = trim($_REQUEST['filtre_date_debut'] ?? '');
    $filtre_date_fin = trim($_REQUEST['filtre_date_fin'] ?? '');

    // Total sans filtre
    $totalStmt    = $bdd->query("SELECT COUNT(*) FROM danfaniment_commandes_confection");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // Construction du filtre WHERE
    $where = "WHERE 1=1";
    $bindings = [];
    
    if ($searchValue !== '') {
        $where .= " AND (
            c.numero_commande LIKE :search
            OR cl.nom LIKE :search
            OR cl.prenom LIKE :search
            OR c.type_tenue LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }
    
    if ($filtre_statut !== '') {
        $where .= " AND c.statut = :statut";
        $bindings[':statut'] = $filtre_statut;
    }
    
    if ($filtre_type !== '') {
        $where .= " AND c.type_tenue LIKE :type_tenue";
        $bindings[':type_tenue'] = "%{$filtre_type}%";
    }
    
    if ($filtre_date_debut !== '') {
        $where .= " AND DATE(c.date_commande) >= :date_debut";
        $bindings[':date_debut'] = $filtre_date_debut;
    }
    
    if ($filtre_date_fin !== '') {
        $where .= " AND DATE(c.date_commande) <= :date_fin";
        $bindings[':date_fin'] = $filtre_date_fin;
    }

    // Total après filtre
    $countSql = "SELECT COUNT(*) FROM danfaniment_commandes_confection c 
                 LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client 
                 $where";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) {
        $countStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    // Tri
    $orderColIndex = intval($_REQUEST['order'][0]['column'] ?? 0);
    $orderDirInput = strtolower($_REQUEST['order'][0]['dir'] ?? 'desc');
    $orderDir      = $orderDirInput === 'asc' ? 'ASC' : 'DESC';
    $columnMap = [
        0 => 'c.id_commande',
        1 => 'c.numero_commande',
        2 => "CONCAT(cl.nom, ' ', cl.prenom)",
        3 => 'c.id_commande',
        4 => 'c.type_tenue',
        5 => 'c.date_commande',
        6 => 'c.date_livraison_prevue',
        7 => 'c.montant_total',
        8 => 'c.montant_avance',
        9 => 'c.statut'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'c.id_commande') . " $orderDir";

    // Requête de données - récupérer aussi les prestataires multiples
    $dataSql = "
        SELECT
            c.id_commande,
            c.numero_commande,
            c.id_client,
            c.type_tenue,
            c.description_tenue,
            c.tissu_fourni_par,
            c.quantite_tissu,
            c.reference_tissu,
            c.montant_total,
            c.montant_avance,
            c.cout_couturier,
            c.remarques,
            c.instructions_couturier,
            c.statut,
            DATE_FORMAT(c.date_commande, '%d/%m/%Y %H:%i') AS date_commande_formatee,
            DATE_FORMAT(c.date_livraison_prevue, '%d/%m/%Y') AS date_livraison_prevue,
            c.date_livraison_prevue AS date_livraison_prevue_originale,
            cl.nom AS client_nom,
            cl.prenom AS client_prenom,
            CASE 
                WHEN c.statut = 'en_attente' THEN 'En attente'
                WHEN c.statut = 'en_cours' THEN 'En cours'
                WHEN c.statut = 'termine' THEN 'Terminé'
                WHEN c.statut = 'livre' THEN 'Livré'
                WHEN c.statut = 'annule' THEN 'Annulé'
                ELSE c.statut
            END AS statut_label,
            CASE 
                WHEN c.statut = 'en_attente' THEN 'warning'
                WHEN c.statut = 'en_cours' THEN 'info'
                WHEN c.statut = 'termine' THEN 'success'
                WHEN c.statut = 'livre' THEN 'primary'
                WHEN c.statut = 'annule' THEN 'danger'
                ELSE 'secondary'
            END AS statut_badge_class,
            (
                SELECT GROUP_CONCAT(CONCAT('#', p.id_prestataire, ' - ', p.nom, ' ', p.prenom) SEPARATOR '<br>')
                FROM danfaniment_commande_prestataires cp
                LEFT JOIN danfaniment_prestataires p ON cp.id_prestataire = p.id_prestataire
                WHERE cp.id_commande = c.id_commande
            ) AS prestataires_liste
        FROM danfaniment_commandes_confection c
        LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client AND (cl.supprimer = 'Non' OR cl.supprimer IS NULL)
        $where
        ORDER BY $orderBy
        LIMIT :start, :length
    ";
    $dataStmt = $bdd->prepare($dataSql);
    foreach ($bindings as $k => $v) {
        $dataStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $dataStmt->bindValue(':start',  $start,  PDO::PARAM_INT);
    $dataStmt->bindValue(':length', $length, PDO::PARAM_INT);
    $dataStmt->execute();

    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ajouter le badge HTML pour chaque ligne
    foreach ($rows as &$row) {
        $row['statut_badge'] = '<span class="badge badge-' . $row['statut_badge_class'] . '">' . htmlspecialchars($row['statut_label']) . '</span>';
        $row['prestataire_nom'] = $row['prestataires_liste'] ?: '-';
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