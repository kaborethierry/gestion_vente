<?php
// api/modules/modifier_depense.php
// DANFANIMENT POS - Modification d'une dépense

session_start();

// 1. Vérification du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation des champs POST obligatoires
$required = ['id_depense', 'libelle', 'categorie', 'beneficiaire', 'justification', 'montant', 'date_depense', 'mode_paiement', 'statut'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['err_depense'] = 1;
        header('Location: ../../pages/depenses.php');
        exit;
    }
}

// 3. Récupération et assainissement des données
$id_depense = (int)$_POST['id_depense'];
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

if ($montant <= 0) {
    $_SESSION['err_depense'] = 1;
    header('Location: ../../pages/depenses.php');
    exit;
}

// 4. Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // 5. Vérifier que la dépense existe
    $stmt = $bdd->prepare("SELECT id_depense FROM danfaniment_depenses WHERE id_depense = :id");
    $stmt->execute([':id' => $id_depense]);
    if ($stmt->fetchColumn() === false) {
        $_SESSION['err_depense'] = 1;
        header('Location: ../../pages/depenses.php');
        exit;
    }

    // 6. Mise à jour
    $sql = "
        UPDATE danfaniment_depenses 
        SET libelle = :libelle,
            categorie = :categorie,
            beneficiaire = :beneficiaire,
            justification = :justification,
            montant = :montant,
            date_depense = :date_depense,
            reference_piece = :reference_piece,
            mode_paiement = :mode_paiement,
            reference_transaction = :reference_transaction,
            statut = :statut,
            updated_at = NOW()
        WHERE id_depense = :id_depense
    ";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':id_depense' => $id_depense,
        ':libelle' => $libelle,
        ':categorie' => $categorie,
        ':beneficiaire' => $beneficiaire,
        ':justification' => $justification,
        ':montant' => $montant,
        ':date_depense' => $date_depense,
        ':reference_piece' => $reference_piece ?: null,
        ':mode_paiement' => $mode_paiement,
        ':reference_transaction' => $reference_transaction ?: null,
        ':statut' => $statut
    ]);

    $_SESSION['mod_depense'] = 1;
    header('Location: ../../pages/depenses.php');
    exit;

} catch (Exception $e) {
    error_log("Erreur modification dépense: " . $e->getMessage());
    $_SESSION['err_depense'] = 1;
    header('Location: ../../pages/depenses.php');
    exit;
}
?>