<?php
// api/modules/enregistrer_production_tisseuse.php
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
$quantite = (int)($_POST['quantite'] ?? 0);
$semaine_debut = trim($_POST['semaine_debut'] ?? '');
$semaine_fin = trim($_POST['semaine_fin'] ?? '');
$remarques = trim($_POST['remarques'] ?? '');

if ($id_prestataire <= 0 || $quantite <= 0 || empty($semaine_debut) || empty($semaine_fin)) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}

try {
    // Récupérer le tarif de la tisseuse
    $stmt = $bdd->prepare("SELECT tarif_par_pagne FROM danfaniment_prestataires WHERE id_prestataire = :id");
    $stmt->execute([':id' => $id_prestataire]);
    $tarif = $stmt->fetchColumn();
    
    if (!$tarif) {
        $_SESSION['err_presta'] = 1;
        header('Location: ../../pages/prestataires.php');
        exit;
    }
    
    $montant_total = $tarif * $quantite;
    
    // Créer la production
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_productions_prestataires 
        (id_prestataire, type_production, quantite, semaine_debut, semaine_fin, montant_unitaire, statut_paiement, date_production, remarques)
        VALUES 
        (:id_prestataire, 'pagne', :quantite, :semaine_debut, :semaine_fin, :tarif, 'en_attente', NOW(), :remarques)
    ");
    $stmt->execute([
        ':id_prestataire' => $id_prestataire,
        ':quantite' => $quantite,
        ':semaine_debut' => $semaine_debut,
        ':semaine_fin' => $semaine_fin,
        ':tarif' => $tarif,
        ':remarques' => $remarques ?: null
    ]);
    
    $_SESSION['prod_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}
?>