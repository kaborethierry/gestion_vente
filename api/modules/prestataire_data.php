<?php
// api/modules/prestataire_data.php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['data' => []]);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$type_prestataire = $_GET['type_prestataire'] ?? 'couturier';
$draw = (int)($_GET['draw'] ?? 1);
$start = (int)($_GET['start'] ?? 0);
$length = (int)($_GET['length'] ?? 10);
$search = $_GET['search']['value'] ?? '';

try {
    // Requête de base
    $sql_base = "FROM danfaniment_prestataires p 
                 LEFT JOIN (
                     SELECT id_prestataire, 
                            SUM(quantite) as total_prod,
                            SUM(quantite * montant_unitaire) as total_montant
                     FROM danfaniment_productions_prestataires 
                     WHERE statut_paiement IN ('en_attente', 'a_payer')
                     GROUP BY id_prestataire
                 ) prod ON p.id_prestataire = prod.id_prestataire
                 WHERE p.type_prestataire = :type";
    
    $params = [':type' => $type_prestataire];
    
    if (!empty($search)) {
        $sql_base .= " AND (p.nom LIKE :search OR p.prenom LIKE :search OR p.telephone LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Compter le total
    $countSql = "SELECT COUNT(*) as total " . $sql_base;
    $stmt = $bdd->prepare($countSql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Récupérer les données
    $sql = "SELECT 
            p.id_prestataire,
            p.nom,
            p.prenom,
            p.telephone,
            p.specialites,
            p.total_paye,
            p.ca_genere,
            CONCAT(p.nom, ' ', p.prenom) as nom_complet,
            COALESCE(prod.total_prod, 0) as productions_semaine,
            COALESCE(prod.total_montant, 0) as montant_du_valeur,
            CASE WHEN p.actif = 1 THEN '<span class=\"badge badge-success\">Actif</span>' 
                 ELSE '<span class=\"badge badge-danger\">Inactif</span>' 
            END as statut_badge
            " . $sql_base . " 
            ORDER BY p.id_prestataire DESC 
            LIMIT :start, :length";
    
    $stmt = $bdd->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les données
    $data = [];
    foreach ($prestataires as $p) {
        $specialites = !empty($p['specialites']) ? $p['specialites'] : '-';
        $montant_du = number_format($p['montant_du_valeur'], 0, ',', ' ') . ' FCFA';
        $total_paye = number_format($p['total_paye'] ?? 0, 0, ',', ' ') . ' FCFA';
        
        $row = [
            'id_prestataire' => $p['id_prestataire'],
            'nom_complet' => $p['nom_complet'],
            'telephone' => $p['telephone'],
            'specialites' => $specialites,
            'productions_semaine' => (int)$p['productions_semaine'],
            'montant_du' => $montant_du,
            'montant_du_valeur' => (float)$p['montant_du_valeur'],
            'total_paye' => $total_paye,
            'statut_badge' => $p['statut_badge'],
            'ca_semaine' => number_format($p['ca_genere'] ?? 0, 0, ',', ' ') . ' FCFA'
        ];
        
        $data[] = $row;
    }
    
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $data
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
}
?>