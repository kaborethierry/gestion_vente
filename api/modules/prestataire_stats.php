<?php
// api/modules/prestataire_stats.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || $_SESSION['role'] !== "admin") {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Total prestataires actifs
    $stmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_prestataires WHERE actif = 1");
    $total_prestataires = (int) $stmt->fetchColumn();
    
    // Total à payer (somme des montants des productions en attente)
    $stmt = $bdd->query("
        SELECT COALESCE(SUM(pr.quantite * pr.montant_unitaire), 0) 
        FROM danfaniment_productions_prestataires pr
        WHERE pr.statut_paiement = 'en_attente'
    ");
    $total_a_payer = (float) $stmt->fetchColumn();
    
    // Total payé (somme des total_paye)
    $stmt = $bdd->query("
        SELECT COALESCE(SUM(total_paye), 0) 
        FROM danfaniment_prestataires 
        WHERE actif = 1
    ");
    $total_paye_mois = (float) $stmt->fetchColumn();
    
    // Total productions de la semaine en cours
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(quantite), 0) 
        FROM danfaniment_productions_prestataires 
        WHERE statut_paiement = 'en_attente'
          AND YEARWEEK(date_production, 1) = YEARWEEK(CURDATE(), 1)
    ");
    $stmt->execute();
    $total_productions = (int) $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'total_prestataires' => $total_prestataires,
        'total_a_payer' => $total_a_payer,
        'total_paye_mois' => $total_paye_mois,
        'total_productions' => $total_productions
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>