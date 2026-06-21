<?php
// api/modules/get_commande_prestataires.php
// DANFANIMENT POS - Récupération des prestataires d'une commande

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
    exit;
}

if (empty($_GET['id_commande'])) {
    echo json_encode(['success' => false, 'prestataires' => []]);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("
        SELECT id, id_prestataire, type_production, montant_unitaire, montant_total, statut_paiement
        FROM danfaniment_commande_prestataires
        WHERE id_commande = :id_commande
        ORDER BY id ASC
    ");
    $stmt->execute([':id_commande' => (int)$_GET['id_commande']]);
    $prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'prestataires' => $prestataires]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>