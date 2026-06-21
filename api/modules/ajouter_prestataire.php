<?php
// api/modules/ajouter_prestataire.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/prestataires.php');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$email = trim($_POST['email'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$type_prestataire = trim($_POST['type_prestataire'] ?? 'couturier');
$specialites = trim($_POST['specialites'] ?? '');
$tarif_par_tenue = (float)($_POST['tarif_par_tenue'] ?? 0);
$tarif_par_pagne = (float)($_POST['tarif_par_pagne'] ?? 0);
$taux_horaire = (float)($_POST['taux_horaire'] ?? 0);
$commission_pourcentage = (float)($_POST['commission_pourcentage'] ?? 0);
$frequence_paiement = trim($_POST['frequence_paiement'] ?? 'hebdomadaire');
$notes = trim($_POST['notes'] ?? '');

if (empty($nom) || empty($prenom) || empty($telephone)) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}

try {
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_prestataires 
        (nom, prenom, telephone, email, adresse, type_prestataire, specialites, 
         tarif_par_tenue, tarif_par_pagne, taux_horaire, commission_pourcentage,
         frequence_paiement, notes, actif, created_at)
        VALUES 
        (:nom, :prenom, :telephone, :email, :adresse, :type_prestataire, :specialites,
         :tarif_par_tenue, :tarif_par_pagne, :taux_horaire, :commission_pourcentage,
         :frequence_paiement, :notes, 1, NOW())
    ");
    
    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':telephone' => $telephone,
        ':email' => $email ?: null,
        ':adresse' => $adresse ?: null,
        ':type_prestataire' => $type_prestataire,
        ':specialites' => $specialites ?: null,
        ':tarif_par_tenue' => $tarif_par_tenue,
        ':tarif_par_pagne' => $tarif_par_pagne,
        ':taux_horaire' => $taux_horaire,
        ':commission_pourcentage' => $commission_pourcentage,
        ':frequence_paiement' => $frequence_paiement,
        ':notes' => $notes ?: null
    ]);
    
    $_SESSION['ajout_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur ajout prestataire: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}
?>