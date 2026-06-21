<?php
// api/modules/ventes_data.php
// DANFANIMENT POS - Liste des ventes pour DataTable (traçabilité)

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$draw = intval($_REQUEST['draw'] ?? 0);
$start = intval($_REQUEST['start'] ?? 0);
$length = intval($_REQUEST['length'] ?? 10);
$searchValue = trim($_REQUEST['search']['value'] ?? '');

// Total sans filtre
$totalStmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_ventes");
$recordsTotal = (int) $totalStmt->fetchColumn();

// Construction WHERE
$where = "WHERE 1=1";
$bindings = [];

if ($searchValue !== '') {
    $where .= " AND (v.numero_vente LIKE :search OR u.nom_complet LIKE :search)";
    $bindings[':search'] = "%{$searchValue}%";
}

// Total filtré
$countSql = "SELECT COUNT(*) FROM danfaniment_ventes v 
             LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur 
             $where";
$countStmt = $bdd->prepare($countSql);
foreach ($bindings as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$recordsFiltered = (int) $countStmt->fetchColumn();

// Tri
$orderColIndex = intval($_REQUEST['order'][0]['column'] ?? 0);
$orderDirInput = strtolower($_REQUEST['order'][0]['dir'] ?? 'desc');
$orderDir = $orderDirInput === 'asc' ? 'ASC' : 'DESC';

$columnMap = [
    0 => 'v.numero_vente',
    1 => 'v.created_at',
    2 => 'u.nom_complet',
    3 => 'CONCAT(c.nom, " ", c.prenom)',
    4 => 'v.total_ttc',
    5 => 'v.mode_paiement',
    6 => 'v.statut'
];
$orderBy = ($columnMap[$orderColIndex] ?? 'v.id_vente') . " $orderDir";

// Requête data avec détails complets pour traçabilité
$dataSql = "
    SELECT 
        v.id_vente,
        v.numero_vente,
        DATE_FORMAT(v.created_at, '%d/%m/%Y %H:%i:%s') AS date_vente,
        u.nom_complet AS caissier,
        CONCAT(COALESCE(c.nom, ''), ' ', COALESCE(c.prenom, '')) AS client,
        v.total_ttc,
        FORMAT(v.total_ttc, 0) AS total_ttc_formate,
        v.sous_total,
        v.remise_montant,
        v.montant_recu,
        v.monnaie_rendue,
        v.mode_paiement,
        CASE 
            WHEN v.mode_paiement = 'especes' THEN 'Espèces'
            WHEN v.mode_paiement = 'carte' THEN 'Carte'
            WHEN v.mode_paiement = 'mobile_money' THEN 'Mobile Money'
            WHEN v.mode_paiement = 'virement' THEN 'Virement'
            ELSE v.mode_paiement
        END AS mode_paiement_label,
        v.statut,
        CASE 
            WHEN v.statut = 'valide' THEN '<span class=\"badge badge-success\">Validée</span>'
            WHEN v.statut = 'annule' THEN '<span class=\"badge badge-danger\">Annulée</span>'
            ELSE '<span class=\"badge badge-warning\">En attente</span>'
        END AS statut_badge
    FROM danfaniment_ventes v
    LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
    LEFT JOIN danfaniment_clients c ON v.id_client = c.id_client
    $where
    ORDER BY $orderBy
    LIMIT :start, :length
";

$dataStmt = $bdd->prepare($dataSql);
foreach ($bindings as $k => $v) $dataStmt->bindValue($k, $v);
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
?>