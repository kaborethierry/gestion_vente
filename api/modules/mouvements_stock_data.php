<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$draw = intval($_GET['draw'] ?? 0);
$start = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 10);
$searchValue = trim($_GET['search']['value'] ?? '');
$type = $_GET['type'] ?? '';
$produit = $_GET['produit'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';

// Total sans filtre
$totalStmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_stock_mouvements");
$recordsTotal = (int) $totalStmt->fetchColumn();

// Construction WHERE
$where = "WHERE 1=1";
$bindings = [];

if ($searchValue !== '') {
    $where .= " AND (p.nom LIKE :search OR m.reference LIKE :search OR m.motif LIKE :search)";
    $bindings[':search'] = "%{$searchValue}%";
}

if ($type !== '') {
    $where .= " AND m.type_mouvement = :type";
    $bindings[':type'] = $type;
}

if ($produit !== '') {
    $where .= " AND m.id_produit = :produit";
    $bindings[':produit'] = $produit;
}

if ($date_debut !== '') {
    $where .= " AND DATE(m.created_at) >= :date_debut";
    $bindings[':date_debut'] = $date_debut;
}

if ($date_fin !== '') {
    $where .= " AND DATE(m.created_at) <= :date_fin";
    $bindings[':date_fin'] = $date_fin;
}

// Total filtré
$countSql = "SELECT COUNT(*) FROM danfaniment_stock_mouvements m
             INNER JOIN danfaniment_produits p ON m.id_produit = p.id_produit
             $where";
$countStmt = $bdd->prepare($countSql);
foreach ($bindings as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$recordsFiltered = (int) $countStmt->fetchColumn();

// Requête data
$dataSql = "
    SELECT 
        m.id_mouvement,
        DATE_FORMAT(m.created_at, '%d/%m/%Y %H:%i') AS date_mouvement,
        p.nom AS produit_nom,
        m.type_mouvement,
        CASE 
            WHEN m.type_mouvement IN ('entree', 'ajustement') AND m.quantite > 0 THEN m.quantite
            WHEN m.type_mouvement IN ('sortie', 'vente') THEN -m.quantite
            ELSE m.quantite
        END AS quantite,
        m.stock_avant,
        m.stock_apres,
        m.reference,
        u.nom_complet AS utilisateur,
        m.motif
    FROM danfaniment_stock_mouvements m
    INNER JOIN danfaniment_produits p ON m.id_produit = p.id_produit
    INNER JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    $where
    ORDER BY m.id_mouvement DESC
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