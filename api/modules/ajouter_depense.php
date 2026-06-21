<?php
// api/modules/ajouter_depense.php
// DANFANIMENT POS - Ajout d'une dépense

session_start();

// Seul un Admin peut ajouter une dépense
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

// Vérifier la présence des champs obligatoires
$required = ['libelle', 'categorie', 'beneficiaire', 'justification', 'montant', 'date_depense', 'mode_paiement', 'statut'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['err_depense'] = 1;
        header('Location: ../../pages/depenses.php');
        exit;
    }
}

// Récupération et assainissement des données
$libelle = trim($_POST['libelle']);
$categorie = trim($_POST['categorie']);
$beneficiaire = trim($_POST['beneficiaire']);
$justification = trim($_POST['justification']);
$montant = (float)$_POST['montant'];
$date_depense = trim($_POST['date_depense']);
$reference_piece = trim($_POST['reference_piece'] ?? '');
$mode_paiement = trim($_POST['mode_paiement']);
$reference_transaction = trim($_POST['reference_transaction'] ?? '');
$statut = trim($_POST['statut']);
$id_utilisateur = $_SESSION['id'];

// Validation supplémentaire
if ($montant <= 0) {
    $_SESSION['err_depense'] = 1;
    header('Location: ../../pages/depenses.php');
    exit;
}

// Validation de la date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_depense)) {
    $_SESSION['err_depense'] = 1;
    header('Location: ../../pages/depenses.php');
    exit;
}

try {
    // Génération d'une référence unique
    $annee = date('Y');
    $mois = date('m');
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_depenses WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
    $stmt->execute();
    $count = (int)$stmt->fetchColumn();
    $reference = sprintf("DEP-%s%s-%04d", $annee, $mois, $count + 1);
    
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_depenses 
        (reference, libelle, categorie, beneficiaire, justification, montant, date_depense, reference_piece, mode_paiement, reference_transaction, statut, id_utilisateur, origine, created_at) 
        VALUES 
        (:reference, :libelle, :categorie, :beneficiaire, :justification, :montant, :date_depense, :reference_piece, :mode_paiement, :reference_transaction, :statut, :id_utilisateur, 'manuelle', NOW())
    ");
    
    $stmt->execute([
        ':reference' => $reference,
        ':libelle' => $libelle,
        ':categorie' => $categorie,
        ':beneficiaire' => $beneficiaire,
        ':justification' => $justification,
        ':montant' => $montant,
        ':date_depense' => $date_depense,
        ':reference_piece' => $reference_piece ?: null,
        ':mode_paiement' => $mode_paiement,
        ':reference_transaction' => $reference_transaction ?: null,
        ':statut' => $statut,
        ':id_utilisateur' => $id_utilisateur
    ]);

    $_SESSION['ajout_depense'] = 1;
    header('Location: ../../pages/depenses.php');
    exit;

} catch (PDOException $e) {
    error_log("Erreur ajout dépense: " . $e->getMessage());
    $_SESSION['err_depense'] = 1;
    header('Location: ../../pages/depenses.php');
    exit;
}
?>