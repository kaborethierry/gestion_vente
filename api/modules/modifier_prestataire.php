<?php
// api/modules/modifier_prestataire.php
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

$id_prestataire = (int)($_POST['id_prestataire'] ?? 0);
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
$actif = (int)($_POST['actif'] ?? 1);

if ($id_prestataire <= 0 || empty($nom) || empty($prenom) || empty($telephone)) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}

try {
    $stmt = $bdd->prepare("
        UPDATE danfaniment_prestataires 
        SET nom = :nom,
            prenom = :prenom,
            telephone = :telephone,
            email = :email,
            adresse = :adresse,
            type_prestataire = :type_prestataire,
            specialites = :specialites,
            tarif_par_tenue = :tarif_par_tenue,
            tarif_par_pagne = :tarif_par_pagne,
            taux_horaire = :taux_horaire,
            commission_pourcentage = :commission_pourcentage,
            frequence_paiement = :frequence_paiement,
            notes = :notes,
            actif = :actif,
            updated_at = NOW()
        WHERE id_prestataire = :id_prestataire
    ");
    
    $stmt->execute([
        ':id_prestataire' => $id_prestataire,
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
        ':notes' => $notes ?: null,
        ':actif' => $actif
    ]);
    
    $_SESSION['mod_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur modification prestataire: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}
?>