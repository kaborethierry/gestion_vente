<?php
session_start();

if (empty($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if (!isset($_POST['id_mesure']) || !is_numeric($_POST['id_mesure'])) {
    $_SESSION['err_mesure'] = 1;
    header('Location: ../../pages/mesures.php');
    exit;
}

$id_mesure = (int) $_POST['id_mesure'];
require_once __DIR__ . '/connect_db_pdo.php';

// Récupérer l'id_client avant modification
$stmt = $bdd->prepare("SELECT id_client FROM danfaniment_mesures_client WHERE id_mesure = :id");
$stmt->execute([':id' => $id_mesure]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
$id_client = $client['id_client'] ?? 0;

$fields = ['dos', 'epaule', 'poitrine', 'long_manche', 'tour_manche', 'long_taille', 'col', 'tour_taille', 'pinces', 'poignet', 'long_camisole', 'long_robe', 'frappe', 'long_chemise', 'ceinture', 'bassin', 'cuisse', 'genoux', 'long_jupe', 'long_pantalon', 'bas', 'hauteur_totale', 'poids', 'pointure_chaussure', 'taille_ceinture', 'notes'];

$sql = "UPDATE danfaniment_mesures_client SET ";
foreach ($fields as $f) { $sql .= "$f = :$f, "; }
$sql .= "date_mesure = NOW() WHERE id_mesure = :id_mesure";

try {
    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':id_mesure', $id_mesure, PDO::PARAM_INT);
    foreach ($fields as $f) {
        $v = isset($_POST[$f]) && $_POST[$f] !== '' ? $_POST[$f] : null;
        $stmt->bindValue(":$f", $v, $v === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }
    $stmt->execute();
    $_SESSION['mod_mesure'] = 1;
    header('Location: ../../pages/mesures.php?client_id=' . $id_client);
    exit;
} catch (Exception $e) {
    $_SESSION['err_mesure'] = 1;
    header('Location: ../../pages/mesures.php');
    exit;
}
?>