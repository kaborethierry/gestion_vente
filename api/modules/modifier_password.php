<?php
// api/modules/modifier_password.php
// DANFANIMENT POS - Modification du mot de passe utilisateur

session_start();

// Vérification que l'utilisateur est connecté (admin ou caissier peuvent modifier leur mot de passe)
if (empty($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/profil.php');
    exit;
}

// Vérifier les champs requis
if (!isset($_POST['old_pass'], $_POST['new_pass'], $_POST['confirm_pass'])) {
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/profil.php');
    exit;
}

try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $idUser       = (int)($_SESSION['id'] ?? 0);
    $oldPassInput = trim((string)$_POST['old_pass']);
    $newPassInput = trim((string)$_POST['new_pass']);
    $confirmPass  = trim((string)$_POST['confirm_pass']);

    // Validation de base
    if ($idUser <= 0 || $oldPassInput === '' || $newPassInput === '' || $confirmPass === '') {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/profil.php');
        exit;
    }

    // Vérifier que les nouveaux mots de passe correspondent
    if ($newPassInput !== $confirmPass) {
        $_SESSION['anc_password'] = 1;
        header('Location: ../../pages/profil.php');
        exit;
    }

    // Vérifier la longueur minimale du mot de passe (6 caractères)
    if (strlen($newPassInput) < 6) {
        $_SESSION['anc_password'] = 1;
        header('Location: ../../pages/profil.php');
        exit;
    }

    // Récupération de l'utilisateur et de son mot de passe actuel
    $sel = $bdd->prepare("
        SELECT id_utilisateur, mot_de_passe
        FROM utilisateurs
        WHERE id_utilisateur = :id
        AND supprimer = 'Non'
        LIMIT 1
    ");
    $sel->execute([':id' => $idUser]);
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/profil.php');
        exit;
    }

    // Vérifier l'ancien mot de passe
    if (!password_verify($oldPassInput, (string)$row['mot_de_passe'])) {
        $_SESSION['anc_password'] = 1;
        header('Location: ../../pages/profil.php');
        exit;
    }

    // Hacher le nouveau mot de passe
    $newHash = password_hash($newPassInput, PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe
    $upd = $bdd->prepare("
        UPDATE utilisateurs
        SET mot_de_passe = :pwd
        WHERE id_utilisateur = :id
    ");
    $upd->execute([
        ':pwd' => $newHash,
        ':id'  => $idUser
    ]);

    $_SESSION['modif_password'] = 1;
    header('Location: ../../pages/profil.php');
    exit;

} catch (Exception $e) {
    error_log('modifier_password: ' . $e->getMessage());
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/profil.php');
    exit;
}
?>