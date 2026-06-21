<?php
// api/modules/mesure_detail.php
// DANFANIMENT POS - Détail d'une mesure

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID invalide']);
    exit;
}

$id_mesure = (int) $_GET['id'];

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("
        SELECT 
            id_mesure, version,
            DATE_FORMAT(date_mesure, '%d/%m/%Y à %H:%i') AS date_mesure,
            tour_cou, largeur_epaule, tour_poitrine, tour_sous_poitrine,
            hauteur_poitrine, ecart_poitrine, tour_taille, hauteur_taille,
            tour_hanches, hauteur_hanches, longueur_dos, largeur_dos,
            longueur_devant, tour_bras, longueur_bras, longueur_manche,
            tour_poignet, longueur_totale_tenue, longueur_jupe, longueur_pantalon,
            tour_cuisse, tour_mollet, tour_cheville, hauteur_totale, poids,
            pointure_chaussure, taille_ceinture, notes
        FROM danfaniment_mesures_client
        WHERE id_mesure = :id
    ");
    $stmt->execute([':id' => $id_mesure]);
    $mesure = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($mesure) {
        echo json_encode(['success' => true, 'mesure' => $mesure]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Mesure non trouvée']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>