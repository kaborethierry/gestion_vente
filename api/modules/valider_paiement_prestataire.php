<?php
// api/modules/valider_paiement_prestataire.php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$log_file = __DIR__ . '/paiement_debug.log';

function debug_log($message) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

debug_log("=== Début paiement ===");
debug_log("POST: " . print_r($_POST, true));

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    debug_log("Erreur: Non autorisé");
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_log("Erreur: Méthode non POST");
    header('Location: ../../pages/prestataires.php');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_prestataire = (int)($_POST['id_prestataire'] ?? 0);
$montant = (float)($_POST['montant'] ?? 0);
$mode_paiement = $_POST['mode_paiement'] ?? 'especes';
$reference_transaction = trim($_POST['reference_transaction'] ?? '');
$remarques = trim($_POST['remarques'] ?? '');

debug_log("id_prestataire: $id_prestataire, montant: $montant");

if ($id_prestataire <= 0 || $montant <= 0) {
    debug_log("Erreur: id ou montant invalide");
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}

try {
    // Récupérer les infos du prestataire
    $stmt = $bdd->prepare("SELECT nom, prenom, type_prestataire FROM danfaniment_prestataires WHERE id_prestataire = :id");
    $stmt->execute([':id' => $id_prestataire]);
    $prestataire = $stmt->fetch(PDO::FETCH_ASSOC);
    
    debug_log("Prestataire trouvé: " . print_r($prestataire, true));
    
    if (!$prestataire) {
        debug_log("Erreur: Prestataire non trouvé");
        $_SESSION['err_presta'] = 1;
        header('Location: ../../pages/prestataires.php');
        exit;
    }
    
    // Déterminer la catégorie de dépense selon le type de prestataire
    $categorieMap = [
        'couturier' => 'salaire_prestataire_couturier',
        'tisseuse' => 'salaire_prestataire_tisseuse',
        'brodeur' => 'salaire_prestataire_brodeur',
        'perleuse' => 'salaire_prestataire_perleuse',
        'mercerie' => 'salaire_prestataire_mercerie',
        'vendeuse' => 'commission_prestataire_vendeuse'
    ];
    
    $categorie = $categorieMap[$prestataire['type_prestataire']] ?? 'charges_diverses';
    $libelle = "Paiement - " . $prestataire['nom'] . " " . $prestataire['prenom'];
    $beneficiaire = $prestataire['nom'] . " " . $prestataire['prenom'];
    $justification = "Paiement pour " . $prestataire['type_prestataire'];
    if (!empty($remarques)) {
        $justification .= " - Remarques: " . $remarques;
    }
    
    $reference_depense = 'DEP-' . date('Ymd') . '-' . rand(1000, 9999);
    
    debug_log("Catégorie: $categorie, Référence: $reference_depense");
    
    // CRÉER LA DÉPENSE
    $sql = "INSERT INTO danfaniment_depenses 
            (reference, libelle, categorie, beneficiaire, justification, montant, date_depense, 
             mode_paiement, reference_transaction, origine, id_prestataire, id_utilisateur, statut, created_at)
            VALUES 
            (:reference, :libelle, :categorie, :beneficiaire, :justification, :montant, CURDATE(), 
             :mode_paiement, :ref_transaction, 'prestataire', :id_prestataire, :id_utilisateur, 'valide', NOW())";
    
    $stmt = $bdd->prepare($sql);
    $result = $stmt->execute([
        ':reference' => $reference_depense,
        ':libelle' => $libelle,
        ':categorie' => $categorie,
        ':beneficiaire' => $beneficiaire,
        ':justification' => $justification,
        ':montant' => $montant,
        ':mode_paiement' => $mode_paiement,
        ':ref_transaction' => $reference_transaction ?: null,
        ':id_prestataire' => $id_prestataire,
        ':id_utilisateur' => $_SESSION['id']
    ]);
    
    if (!$result) {
        throw new Exception("Erreur lors de l'insertion de la dépense");
    }
    
    debug_log("Dépense créée avec succès, ID: " . $bdd->lastInsertId());
    
    // Mettre à jour les totaux du prestataire
    $stmt = $bdd->prepare("UPDATE danfaniment_prestataires SET total_paye = total_paye + :montant, updated_at = NOW() WHERE id_prestataire = :id");
    $result = $stmt->execute([':montant' => $montant, ':id' => $id_prestataire]);
    debug_log("Mise à jour total_paye: " . ($result ? "OK" : "ECHEC"));
    
    // Marquer les productions comme payées
    $stmt = $bdd->prepare("UPDATE danfaniment_productions_prestataires SET statut_paiement = 'paye', date_paiement = NOW() WHERE id_prestataire = :id AND statut_paiement IN ('en_attente', 'a_payer')");
    $result = $stmt->execute([':id' => $id_prestataire]);
    debug_log("Mise à jour productions: " . ($result ? "OK" : "ECHEC") . ", lignes affectées: " . $stmt->rowCount());
    
    $_SESSION['paiement_presta'] = 1;
    debug_log("Succès! Redirection");
    header('Location: ../../pages/prestataires.php');
    exit;
    
} catch (PDOException $e) {
    debug_log("ERREUR PDO: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
} catch (Exception $e) {
    debug_log("ERREUR: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}
?>