<?php
// api/modules/ajouter_utilisateur.php
// DANFANIMENT POS - Ajout d'un utilisateur

session_start();

// Seul un Admin peut ajouter un utilisateur
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// Vérifier la présence des champs obligatoires
if (!isset($_POST['nom_complet'], $_POST['nom_utilisateur'], $_POST['mot_de_passe'], $_POST['role'], $_POST['status'])) {
    header('Location: ../../pages/utilisateur.php');
    exit;
}

// Récupération et assainissement des données
$nom_complet    = trim($_POST['nom_complet']);
$username       = trim($_POST['nom_utilisateur']);
$password       = $_POST['mot_de_passe'];
$email          = trim($_POST['email'] ?? '');
$telephone      = trim($_POST['telephone'] ?? '');
$role           = $_POST['role'];
$status         = $_POST['status'];

// Convertir status en entier pour la BDD
$actif = ($status === 'actif') ? 1 : 0;

// Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // 1) Vérifier l'unicité du nom d'utilisateur
    $stmt = $bdd->prepare('SELECT COUNT(*) FROM utilisateurs WHERE nom_utilisateur = :username AND supprimer = "Non"');
    $stmt->execute([':username' => $username]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/utilisateur.php');
        exit;
    }

    // 2) Hacher le mot de passe
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // 3) Insérer le nouvel utilisateur
    $stmt = $bdd->prepare('
        INSERT INTO utilisateurs 
        (nom_complet, nom_utilisateur, mot_de_passe, email, telephone, role, actif, supprimer, date_creation) 
        VALUES (:nom_complet, :username, :pwd, :email, :telephone, :role, :actif, "Non", NOW())
    ');
    $stmt->execute([
        ':nom_complet' => $nom_complet,
        ':username'    => $username,
        ':pwd'         => $hash,
        ':email'       => $email,
        ':telephone'   => $telephone,
        ':role'        => $role,
        ':actif'       => $actif
    ]);

    $_SESSION['ajout'] = 1;
    header('Location: ../../pages/utilisateur.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['err_util'] = 1;
    header('Location: ../../pages/utilisateur.php');
    exit;
}
?>