<?php
// api/modules/modifier_profil.php
// DANFANIMENT POS - Modification du profil utilisateur

session_start();

// Autorisation: uniquement Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// Accepter uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/profil.php');
    exit;
}

// Champs requis
if (!isset($_POST['id_user'], $_POST['username'], $_POST['nom_complet'], $_POST['role'])) {
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/profil.php');
    exit;
}

try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Nettoyage
    $idUser   = (int)($_POST['id_user'] ?? 0);
    $username = trim((string)($_POST['username'] ?? ''));
    $nomComplet = trim((string)($_POST['nom_complet'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $telephone = trim((string)($_POST['telephone'] ?? ''));
    $roleIn   = trim((string)($_POST['role'] ?? ''));

    if ($idUser <= 0 || $username === '' || $nomComplet === '') {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/profil.php');
        exit;
    }

    // Normalisation du rôle selon l'ENUM de la table
    $role = in_array($roleIn, ['admin', 'caissier'], true) ? $roleIn : 'caissier';

    // Vérifier que l'utilisateur existe
    $sel = $bdd->prepare("
        SELECT * FROM utilisateurs
        WHERE id_utilisateur = :id AND supprimer = 'Non'
        LIMIT 1
    ");
    $sel->execute([':id' => $idUser]);
    $old = $sel->fetch(PDO::FETCH_ASSOC);

    if (!$old) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/profil.php');
        exit;
    }

    // Contrôle de doublon sur nom_utilisateur (exclure l'utilisateur courant)
    $dup = $bdd->prepare("
        SELECT COUNT(*) 
        FROM utilisateurs
        WHERE supprimer = 'Non' AND nom_utilisateur = :u AND id_utilisateur <> :id
    ");
    $dup->execute([':u' => $username, ':id' => $idUser]);
    if ((int)$dup->fetchColumn() > 0) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/profil.php');
        exit;
    }

    // Mise à jour
    $upd = $bdd->prepare("
        UPDATE utilisateurs
        SET nom_utilisateur = :u,
            nom_complet = :nc,
            email = :email,
            telephone = :tel,
            role = :r
        WHERE id_utilisateur = :id
        AND supprimer = 'Non'
    ");
    $upd->execute([
        ':u'   => $username,
        ':nc'  => $nomComplet,
        ':email' => $email,
        ':tel' => $telephone,
        ':r'   => $role,
        ':id'  => $idUser
    ]);

    // Rafraîchir la session si c'est l'utilisateur courant
    if ((int)$_SESSION['id'] === $idUser) {
        $_SESSION['username'] = $username;
        $_SESSION['role']     = $role;
        $_SESSION['nom_complet'] = $nomComplet;
        $_SESSION['email'] = $email;
        $_SESSION['telephone'] = $telephone;
    }

    $_SESSION['modif_username'] = 1;
    header('Location: ../../pages/profil.php');
    exit;

} catch (Exception $e) {
    error_log('modifier_profil: ' . $e->getMessage());
    $_SESSION['err_profil'] = 1;
    header('Location: ../../pages/profil.php');
    exit;
}
?>