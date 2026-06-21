<?php
// api/modules/supprimer_depense.php
// DANFANIMENT POS - Suppression d'une dépense

session_start();

// 1. Vérification de la session et du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation du paramètre GET id_depense
if (empty($_GET['id_depense']) || !ctype_digit($_GET['id_depense'])) {
    $_SESSION['err_depense'] = 1;
    header('Location: ../../pages/depenses.php');
    exit;
}

// 3. Cast sécurisé de l'ID
$idDepense = (int) $_GET['id_depense'];

try {
    // 4. Connexion PDO
    require_once __DIR__ . '/connect_db_pdo.php';

    // 5. Vérifier que la dépense existe
    $stmt = $bdd->prepare("SELECT id_depense FROM danfaniment_depenses WHERE id_depense = :id");
    $stmt->execute([':id' => $idDepense]);
    if ($stmt->fetchColumn() === false) {
        $_SESSION['err_depense'] = 1;
        header('Location: ../../pages/depenses.php');
        exit;
    }

    // 6. Supprimer la dépense
    $stmt = $bdd->prepare("DELETE FROM danfaniment_depenses WHERE id_depense = :id");
    $stmt->execute([':id' => $idDepense]);

    $_SESSION['supr_depense'] = 1;

} catch (Exception $e) {
    error_log("Erreur suppression dépense: " . $e->getMessage());
    $_SESSION['err_depense'] = 1;
}

header('Location: ../../pages/depenses.php');
exit;
?>