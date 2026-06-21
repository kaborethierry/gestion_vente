<?php
// api/modules/test_datatable.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/connect_db_pdo.php';

$type_prestataire = $_GET['type'] ?? 'couturier';

try {
    $stmt = $bdd->prepare("
        SELECT 
            p.id_prestataire,
            CONCAT(p.nom, ' ', p.prenom) as nom_complet,
            p.telephone,
            p.specialites,
            COALESCE(SUM(pp.quantite), 0) as productions_semaine,
            COALESCE(SUM(pp.quantite * pp.montant_unitaire), 0) as montant_du,
            p.total_paye
        FROM danfaniment_prestataires p
        LEFT JOIN danfaniment_productions_prestataires pp ON p.id_prestataire = pp.id_prestataire 
            AND pp.statut_paiement IN ('en_attente', 'a_payer')
        WHERE p.type_prestataire = :type
        GROUP BY p.id_prestataire
        ORDER BY p.id_prestataire DESC
    ");
    $stmt->execute([':type' => $type_prestataire]);
    $prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $data = [];
    foreach ($prestataires as $p) {
        $data[] = [
            'id_prestataire' => $p['id_prestataire'],
            'nom_complet' => $p['nom_complet'],
            'telephone' => $p['telephone'],
            'specialites' => $p['specialites'] ?: '-',
            'productions_semaine' => (int)$p['productions_semaine'],
            'montant_du' => number_format($p['montant_du'], 0, ',', ' ') . ' FCFA',
            'montant_du_valeur' => (float)$p['montant_du'],
            'total_paye' => number_format($p['total_paye'], 0, ',', ' ') . ' FCFA',
            'statut_badge' => '<span class="badge badge-success">Actif</span>'
        ];
    }
    
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
?>