<?php
// api/modules/enregistrer_production_couturier.php
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
$id_commande = (int)($_POST['id_commande'] ?? 0);

if ($id_prestataire <= 0 || $id_commande <= 0) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}

try {
    // Récupérer le tarif du couturier
    $stmt = $bdd->prepare("SELECT tarif_par_tenue FROM danfaniment_prestataires WHERE id_prestataire = :id");
    $stmt->execute([':id' => $id_prestataire]);
    $tarif = $stmt->fetchColumn();
    
    if (!$tarif) {
        $_SESSION['err_presta'] = 1;
        header('Location: ../../pages/prestataires.php');
        exit;
    }
    
    // Calculer les dates de début et fin de semaine
    $semaine_debut = date('Y-m-d', strtotime('monday this week'));
    $semaine_fin = date('Y-m-d', strtotime('sunday this week'));
    
    // Vérifier si une production existe déjà cette semaine
    $stmt = $bdd->prepare("
        SELECT id_production FROM danfaniment_productions_prestataires 
        WHERE id_prestataire = :id AND WEEK(semaine_debut) = WEEK(CURDATE())
    ");
    $stmt->execute([':id' => $id_prestataire]);
    
    if ($stmt->fetchColumn()) {
        // Mettre à jour la production existante
        $stmt = $bdd->prepare("
            UPDATE danfaniment_productions_prestataires 
            SET quantite = quantite + 1,
                montant_unitaire = :tarif,
                updated_at = NOW()
            WHERE id_prestataire = :id AND WEEK(semaine_debut) = WEEK(CURDATE())
        ");
        $stmt->execute([
            ':tarif' => $tarif,
            ':id' => $id_prestataire
        ]);
    } else {
        // Créer une nouvelle production
        $stmt = $bdd->prepare("
            INSERT INTO danfaniment_productions_prestataires 
            (id_prestataire, id_commande, type_production, quantite, semaine_debut, semaine_fin, montant_unitaire, statut_paiement, date_production)
            VALUES 
            (:id_prestataire, :id_commande, 'tenue', 1, :semaine_debut, :semaine_fin, :tarif, 'en_attente', NOW())
        ");
        $stmt->execute([
            ':id_prestataire' => $id_prestataire,
            ':id_commande' => $id_commande,
            ':semaine_debut' => $semaine_debut,
            ':semaine_fin' => $semaine_fin,
            ':tarif' => $tarif
        ]);
    }
    
    // Mettre à jour le statut de la commande confection
    $stmt = $bdd->prepare("UPDATE danfaniment_commandes_confection SET statut = 'termine', updated_at = NOW() WHERE id_commande = :id");
    $stmt->execute([':id' => $id_commande]);
    
    $_SESSION['prod_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}
?>