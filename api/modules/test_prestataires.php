<?php
// api/modules/test_prestataires.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/connect_db_pdo.php';

$type = $_GET['type'] ?? 'couturier';

// Test direct
$sql = "SELECT 
            p.id_prestataire,
            CONCAT(p.nom, ' ', p.prenom) AS nom_complet,
            p.telephone,
            COALESCE((
                SELECT SUM(pr.quantite) 
                FROM danfaniment_productions_prestataires pr
                WHERE pr.id_prestataire = p.id_prestataire 
                  AND pr.statut_paiement = 'en_attente'
            ), 0) AS production_semaine,
            COALESCE((
                SELECT SUM(pr.quantite * pr.montant_unitaire) 
                FROM danfaniment_productions_prestataires pr
                WHERE pr.id_prestataire = p.id_prestataire 
                  AND pr.statut_paiement = 'en_attente'
            ), 0) AS montant_du
        FROM danfaniment_prestataires p
        WHERE p.type_prestataire = :type AND p.actif = 1";

$stmt = $bdd->prepare($sql);
$stmt->execute([':type' => $type]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'type' => $type,
    'count' => count($results),
    'data' => $results
], JSON_PRETTY_PRINT);
?>