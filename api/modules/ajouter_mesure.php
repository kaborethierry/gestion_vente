<?php
session_start();

if (empty($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if (!isset($_POST['id_client']) || !is_numeric($_POST['id_client'])) {
    $_SESSION['err_mesure'] = 1;
    header('Location: ../../pages/mesures.php');
    exit;
}

$id_client = (int) $_POST['id_client'];
require_once __DIR__ . '/connect_db_pdo.php';

try {
    $stmt = $bdd->prepare("SELECT COALESCE(MAX(version), 0) + 1 as next_version FROM danfaniment_mesures_client WHERE id_client = :id");
    $stmt->execute([':id' => $id_client]);
    $next_version = $stmt->fetch(PDO::FETCH_ASSOC)['next_version'] ?? 1;
    
    $sql = "INSERT INTO danfaniment_mesures_client (
        id_client, version,
        dos, epaule, poitrine, long_manche, tour_manche, long_taille, col, tour_taille, pinces, poignet,
        long_camisole, long_robe, frappe, long_chemise,
        ceinture, bassin, cuisse, genoux, long_jupe, long_pantalon, bas,
        hauteur_totale, poids, pointure_chaussure, taille_ceinture, notes
    ) VALUES (
        :id_client, :version,
        :dos, :epaule, :poitrine, :long_manche, :tour_manche, :long_taille, :col, :tour_taille, :pinces, :poignet,
        :long_camisole, :long_robe, :frappe, :long_chemise,
        :ceinture, :bassin, :cuisse, :genoux, :long_jupe, :long_pantalon, :bas,
        :hauteur_totale, :poids, :pointure_chaussure, :taille_ceinture, :notes
    )";
    
    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->bindValue(':version', $next_version, PDO::PARAM_INT);
    
    $fields = ['dos', 'epaule', 'poitrine', 'long_manche', 'tour_manche', 'long_taille', 'col', 'tour_taille', 'pinces', 'poignet', 'long_camisole', 'long_robe', 'frappe', 'long_chemise', 'ceinture', 'bassin', 'cuisse', 'genoux', 'long_jupe', 'long_pantalon', 'bas', 'hauteur_totale', 'poids', 'pointure_chaussure', 'taille_ceinture', 'notes'];
    
    foreach ($fields as $field) {
        $value = isset($_POST[$field]) && $_POST[$field] !== '' ? str_replace(',', '.', $_POST[$field]) : null;
        $stmt->bindValue(":$field", $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }
    
    $stmt->execute();
    
    $_SESSION['ajout_mesure'] = 1;
    header('Location: ../../pages/mesures.php?client_id=' . $id_client);
    exit;
    
} catch (PDOException $e) {
    $_SESSION['err_mesure'] = 1;
    header('Location: ../../pages/mesures.php');
    exit;
}
?>