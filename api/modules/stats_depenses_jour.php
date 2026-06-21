<?php
// api/modules/stats_depenses_jour.php
// Statistiques des dépenses par jour

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$statut = $_GET['statut'] ?? '';
$date_unique = $_GET['date_unique'] ?? '';

try {
    $where = "WHERE 1=1";
    $params = [];
    
    if (!empty($date_unique)) {
        $where .= " AND date_depense = :date_unique";
        $params[':date_unique'] = $date_unique;
    } elseif (!empty($date_debut) && !empty($date_fin)) {
        $where .= " AND date_depense BETWEEN :date_debut AND :date_fin";
        $params[':date_debut'] = $date_debut;
        $params[':date_fin'] = $date_fin;
    } elseif (!empty($date_debut)) {
        $where .= " AND date_depense >= :date_debut";
        $params[':date_debut'] = $date_debut;
    } elseif (!empty($date_fin)) {
        $where .= " AND date_depense <= :date_fin";
        $params[':date_fin'] = $date_fin;
    }
    
    if (!empty($statut)) {
        $where .= " AND statut = :statut";
        $params[':statut'] = $statut;
    }
    
    // Par défaut, les 30 derniers jours
    if (empty($date_debut) && empty($date_fin) && empty($date_unique)) {
        $where .= " AND date_depense >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    }
    
    $sql = "
        SELECT 
            date_depense,
            DATE_FORMAT(date_depense, '%d/%m/%Y') as date_label,
            COALESCE(SUM(montant), 0) as total,
            COUNT(*) as nombre
        FROM danfaniment_depenses
        $where
        GROUP BY date_depense
        ORDER BY date_depense ASC
    ";
    
    $stmt = $bdd->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ✅ CORRECTION : S'assurer que total est un nombre
    foreach ($results as &$row) {
        $row['total'] = (float)$row['total'];
        $row['nombre'] = (int)$row['nombre'];
    }
    
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Erreur stats_depenses_jour: " . $e->getMessage());
    echo json_encode([]);
}
?>