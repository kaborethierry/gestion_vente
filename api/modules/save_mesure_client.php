<?php
// api/modules/save_mesure_client.php
// DANFANIMENT POS - Sauvegarde des mesures client

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    if (empty($_SESSION['id'])) {
        echo json_encode(['success' => false, 'error' => 'Non authentifié']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php';

    $id_client = isset($_POST['id_client']) ? (int)$_POST['id_client'] : 0;
    $id_mesure = isset($_POST['id_mesure']) && !empty($_POST['id_mesure']) ? (int)$_POST['id_mesure'] : null;

    if ($id_client <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID client invalide']);
        exit;
    }

    // Champs de mesures
    $champs = [
        'tour_cou', 'largeur_epaule', 'tour_poitrine', 'tour_sous_poitrine',
        'hauteur_poitrine', 'ecart_poitrine', 'tour_taille', 'hauteur_taille',
        'tour_hanches', 'hauteur_hanches', 'longueur_dos', 'largeur_dos',
        'longueur_devant', 'tour_bras', 'longueur_bras', 'longueur_manche',
        'tour_poignet', 'longueur_totale_tenue', 'longueur_jupe', 'longueur_pantalon',
        'tour_cuisse', 'tour_mollet', 'tour_cheville', 'hauteur_totale',
        'poids', 'pointure_chaussure', 'taille_ceinture'
    ];

    $data = [];
    foreach ($champs as $champ) {
        $data[$champ] = isset($_POST[$champ]) && $_POST[$champ] !== '' ? $_POST[$champ] : null;
    }
    $data['id_client'] = $id_client;
    $data['notes'] = isset($_POST['notes_mesure']) ? $_POST['notes_mesure'] : null;
    $data['date_mesure'] = date('Y-m-d H:i:s');

    // Désactiver les anciennes mesures
    $stmt = $bdd->prepare("UPDATE danfaniment_mesures_client SET is_current = 0 WHERE id_client = :id_client");
    $stmt->execute([':id_client' => $id_client]);
    
    // Insérer la nouvelle mesure
    $colonnes = array_keys($data);
    $placeholders = array_map(function($c) { return ":$c"; }, $colonnes);
    
    $sql = "INSERT INTO danfaniment_mesures_client (" . implode(', ', $colonnes) . ", is_current, version) 
            VALUES (" . implode(', ', $placeholders) . ", 1, 1)";
    $stmt = $bdd->prepare($sql);
    $stmt->execute($data);
    
    $id_mesure = $bdd->lastInsertId();

    echo json_encode(['success' => true, 'id_mesure' => $id_mesure]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>