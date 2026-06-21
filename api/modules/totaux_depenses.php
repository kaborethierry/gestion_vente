<?php
// api/modules/totaux_depenses.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$categorie = $_GET['categorie'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$montant_min = $_GET['montant_min'] ?? '';
$montant_max = $_GET['montant_max'] ?? '';
$statut = $_GET['statut'] ?? '';
$date_unique = $_GET['date_unique'] ?? '';

try {
    $where = "WHERE 1=1";
    $params = [];
    
    if (!empty($categorie)) {
        $where .= " AND categorie = :categorie";
        $params[':categorie'] = $categorie;
    }
    
    if (!empty($date_unique)) {
        $where .= " AND date_depense = :date_unique";
        $params[':date_unique'] = $date_unique;
    } else {
        if (!empty($date_debut)) {
            $where .= " AND date_depense >= :date_debut";
            $params[':date_debut'] = $date_debut;
        }
        if (!empty($date_fin)) {
            $where .= " AND date_depense <= :date_fin";
            $params[':date_fin'] = $date_fin;
        }
    }
    
    if (!empty($montant_min)) {
        $where .= " AND montant >= :montant_min";
        $params[':montant_min'] = (float)$montant_min;
    }
    if (!empty($montant_max)) {
        $where .= " AND montant <= :montant_max";
        $params[':montant_max'] = (float)$montant_max;
    }
    if (!empty($statut)) {
        $where .= " AND statut = :statut";
        $params[':statut'] = $statut;
    }
    
    $sql = "
        SELECT 
            categorie,
            COUNT(*) as nombre,
            COALESCE(SUM(montant), 0) as total,
            CASE 
                WHEN categorie = 'salaire_prestataire_couturier' THEN '💰 Salaire couturier'
                WHEN categorie = 'salaire_prestataire_tisseuse' THEN '🪢 Salaire tisseuse'
                WHEN categorie = 'salaire_prestataire_brodeur' THEN '🪡 Salaire brodeur'
                WHEN categorie = 'salaire_prestataire_perleuse' THEN '💎 Salaire perleuse'
                WHEN categorie = 'salaire_prestataire_mercerie' THEN '📿 Salaire mercerie'
                WHEN categorie = 'commission_prestataire_vendeuse' THEN '🛍️ Commission vendeuse'
                WHEN categorie = 'livraison' THEN '🚚 Livraison'
                WHEN categorie = 'loyer' THEN '🏠 Loyer'
                WHEN categorie = 'fournitures' THEN '✂️ Fournitures'
                WHEN categorie = 'fournisseur_tissu' THEN '🧵 Fournisseur tissu'
                WHEN categorie = 'charges_diverses' THEN '📋 Charges diverses'
                WHEN categorie = 'tontines_entreprise' THEN '🤝 Tontines entreprise'
                ELSE categorie
            END AS categorie_libelle
        FROM danfaniment_depenses
        $where
        GROUP BY categorie
        ORDER BY total DESC
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
    error_log("Erreur totaux_depenses: " . $e->getMessage());
    echo json_encode([]);
}
?>