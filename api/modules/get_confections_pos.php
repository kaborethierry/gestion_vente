<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("
        SELECT 
            c.id_commande,
            CONCAT(cl.nom, ' ', cl.prenom) as client_nom,
            c.montant_total,
            COALESCE(SUM(p.montant), 0) as total_paye,
            (c.montant_total - COALESCE(SUM(p.montant), 0)) as reste_a_payer
        FROM danfaniment_commandes c
        INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        LEFT JOIN danfaniment_paiements_confection p ON c.id_commande = p.id_commande
        WHERE c.statut != 'livre' AND c.statut != 'annule'
        GROUP BY c.id_commande
        HAVING reste_a_payer > 0
        ORDER BY c.date_commande DESC
    ");
    $stmt->execute();
    $confections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($confections, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>