<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'get_paiements':
        getPaiements($bdd);
        break;
    case 'get_commandes':
        getCommandes($bdd);
        break;
    case 'get_commandes_select':
        getCommandesSelect($bdd);
        break;
    default:
        echo json_encode(['error' => 'Action non reconnue']);
}

function getPaiements($bdd) {
    $draw = intval($_GET['draw'] ?? 0);
    $start = intval($_GET['start'] ?? 0);
    $length = intval($_GET['length'] ?? 10);
    $searchValue = trim($_GET['search']['value'] ?? '');
    $mode_paiement = $_GET['mode_paiement'] ?? '';
    $date_debut = $_GET['date_debut'] ?? '';
    $date_fin = $_GET['date_fin'] ?? '';
    
    $totalStmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_paiements_confection");
    $recordsTotal = (int) $totalStmt->fetchColumn();
    
    $where = "WHERE 1=1";
    $bindings = [];
    
    if ($searchValue !== '') {
        $where .= " AND (c.numero_commande LIKE :search OR CONCAT(cl.nom, ' ', cl.prenom) LIKE :search)";
        $bindings[':search'] = "%{$searchValue}%";
    }
    
    if ($mode_paiement !== '') {
        $where .= " AND p.mode_paiement = :mode";
        $bindings[':mode'] = $mode_paiement;
    }
    
    if ($date_debut !== '') {
        $where .= " AND DATE(p.created_at) >= :date_debut";
        $bindings[':date_debut'] = $date_debut;
    }
    
    if ($date_fin !== '') {
        $where .= " AND DATE(p.created_at) <= :date_fin";
        $bindings[':date_fin'] = $date_fin;
    }
    
    $countSql = "SELECT COUNT(*) FROM danfaniment_paiements_confection p
                 INNER JOIN danfaniment_commandes_confection c ON p.id_commande = c.id_commande
                 INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
                 $where";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) $countStmt->bindValue($k, $v);
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();
    
    $dataSql = "
        SELECT 
            p.id_paiement,
            c.numero_commande,
            CONCAT(cl.nom, ' ', cl.prenom) AS client_nom,
            p.montant,
            p.type_paiement,
            p.mode_paiement,
            p.reference_transaction,
            p.remarques,
            DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS date_paiement,
            u.nom_complet AS caissier,
            (SELECT role FROM utilisateurs WHERE id_utilisateur = " . intval($_SESSION['id']) . ") AS user_role
        FROM danfaniment_paiements_confection p
        INNER JOIN danfaniment_commandes_confection c ON p.id_commande = c.id_commande
        INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        INNER JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
        $where
        ORDER BY p.created_at DESC
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
}

function getCommandes($bdd) {
    $draw = intval($_GET['draw'] ?? 0);
    $start = intval($_GET['start'] ?? 0);
    $length = intval($_GET['length'] ?? 10);
    $searchValue = trim($_GET['search']['value'] ?? '');
    $statut_paiement = $_GET['statut_paiement'] ?? '';
    
    $totalStmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_commandes_confection WHERE statut != 'annule'");
    $recordsTotal = (int) $totalStmt->fetchColumn();
    
    $where = "WHERE c.statut != 'annule'";
    $bindings = [];
    
    if ($searchValue !== '') {
        $where .= " AND (c.numero_commande LIKE :search OR CONCAT(cl.nom, ' ', cl.prenom) LIKE :search)";
        $bindings[':search'] = "%{$searchValue}%";
    }
    
    if ($statut_paiement !== '') {
        if ($statut_paiement === 'solde') {
            $where .= " AND c.solde_restant <= 0";
        } elseif ($statut_paiement === 'partiel') {
            $where .= " AND c.montant_avance > 0 AND c.solde_restant > 0";
        } elseif ($statut_paiement === 'aucun') {
            $where .= " AND c.montant_avance = 0 AND c.solde_restant > 0";
        }
    }
    
    $countSql = "SELECT COUNT(*) FROM danfaniment_commandes_confection c
                 INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
                 $where";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) $countStmt->bindValue($k, $v);
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();
    
    $dataSql = "
        SELECT 
            c.id_commande,
            c.numero_commande,
            CONCAT(cl.nom, ' ', cl.prenom) AS client_nom,
            cl.telephone,
            c.montant_total,
            c.montant_avance,
            c.solde_restant,
            c.statut AS statut_commande,
            CASE 
                WHEN c.solde_restant <= 0 THEN 'Soldé'
                WHEN c.montant_avance > 0 AND c.solde_restant > 0 THEN 'Partiellement payé'
                WHEN c.montant_avance = 0 AND c.solde_restant > 0 THEN 'Aucun paiement'
                ELSE 'En attente'
            END AS statut_paiement,
            DATE_FORMAT(c.date_commande, '%d/%m/%Y') AS date_commande
        FROM danfaniment_commandes_confection c
        INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        $where
        ORDER BY c.solde_restant DESC
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
}

// NOUVELLE FONCTION pour le select du modal
function getCommandesSelect($bdd) {
    try {
        $stmt = $bdd->prepare("
            SELECT c.id_commande, c.numero_commande, c.montant_total, c.solde_restant,
                   CONCAT(cl.nom, ' ', cl.prenom) AS client_nom
            FROM danfaniment_commandes_confection c
            INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
            WHERE c.solde_restant > 0 AND c.statut != 'annule'
            ORDER BY c.numero_commande DESC
        ");
        $stmt->execute();
        $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'commandes' => $commandes
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'commandes' => [],
            'message' => $e->getMessage()
        ]);
    }
}
?>