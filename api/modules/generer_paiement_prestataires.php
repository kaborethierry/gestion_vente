<?php
// api/modules/generer_paiement_prestataires.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

$type = $_GET['type'] ?? '';
if (empty($type)) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Récupérer tous les prestataires actifs du type spécifié
    $stmt = $bdd->prepare("
        SELECT id_prestataire, type_prestataire 
        FROM danfaniment_prestataires 
        WHERE type_prestataire = :type AND actif = 1
    ");
    $stmt->execute([':type' => $type]);
    $prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $semaine_debut = date('Y-m-d', strtotime('last saturday -6 days'));
    $semaine_fin = date('Y-m-d', strtotime('last saturday'));
    
    foreach ($prestataires as $prestataire) {
        // Mettre à jour les productions non payées avec statut 'a_payer'
        $stmt = $bdd->prepare("
            UPDATE danfaniment_productions_prestataires 
            SET statut_paiement = 'a_payer' 
            WHERE id_prestataire = :id 
            AND statut_paiement = 'en_attente'
        ");
        $stmt->execute([':id' => $prestataire['id_prestataire']]);
    }
    
    $_SESSION['paiement_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur génération paiement: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}
?>