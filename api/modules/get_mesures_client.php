<?php
// api/modules/get_mesures_client.php
// DANFANIMENT POS - Récupération des mesures d'un client

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    if (empty($_SESSION['id'])) {
        echo json_encode(['success' => false, 'error' => 'Non authentifié']);
        exit;
    }

    if (empty($_GET['id_client'])) {
        echo json_encode(['success' => false, 'error' => 'ID client manquant']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php';

    $id_client = (int)$_GET['id_client'];

    $stmt = $bdd->prepare("
        SELECT 
            id_mesure,
            id_client,
            DATE_FORMAT(date_mesure, '%d/%m/%Y') AS date_mesure_formatee,
            dos,
            epaule,
            poitrine,
            long_manche,
            tour_manche,
            long_taille,
            col,
            tour_taille,
            pinces,
            poignet,
            long_camisole,
            long_robe,
            frappe,
            long_chemise,
            ceinture,
            bassin,
            cuisse,
            genoux,
            long_jupe,
            long_pantalon,
            bas,
            hauteur_totale,
            poids,
            pointure_chaussure,
            taille_ceinture,
            notes,
            version,
            is_current
        FROM danfaniment_mesures_client 
        WHERE id_client = :id_client 
        ORDER BY version DESC
    ");
    $stmt->execute([':id_client' => $id_client]);
    $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // S'assurer que la date formatée est présente
    foreach ($mesures as &$mesure) {
        if (!isset($mesure['date_mesure_formatee']) || empty($mesure['date_mesure_formatee'])) {
            $mesure['date_mesure_formatee'] = '-';
        }
    }

    echo json_encode(['success' => true, 'mesures' => $mesures]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>