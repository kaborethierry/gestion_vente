<?php
// api/modules/connection.php
// DANFANIMENT POS - Authentification utilisateur

session_start();

// Réinitialisation des flags de session (logique conservée)
$_SESSION['password'] = 0;
$_SESSION['supr']     = 0;
$_SESSION['mod']      = 0;
$_SESSION['ajout']    = 0;
$_SESSION['err']      = 0;

// Vérification des champs
if (empty($_POST['username']) || empty($_POST['password'])) {
    $_SESSION['err'] = 2;  // Identifiants manquants
    header('Location: ../../index.php');
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // 1) Récupération de l'utilisateur actif (logique conservée)
    $stmt = $bdd->prepare("
        SELECT 
            id_utilisateur, 
            nom_utilisateur, 
            mot_de_passe, 
            nom_complet,
            email,
            telephone,
            role 
        FROM utilisateurs 
        WHERE nom_utilisateur = :username 
          AND actif = 1 
          AND supprimer = 'Non'
        LIMIT 1
    ");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['err'] = 3;  // Erreur technique
    header('Location: ../../index.php');
    exit;
}

// Vérification du mot de passe (identique à la logique originale)
if (!$user || !password_verify($password, $user['mot_de_passe'])) {
    $_SESSION['err'] = 1;  // Identifiants invalides
    header('Location: ../../index.php');
    exit;
}

// Initialisation des variables de session (logique conservée)
$_SESSION['id']       = $user['id_utilisateur'];
$_SESSION['username'] = $user['nom_utilisateur'];
$_SESSION['nom_complet'] = $user['nom_complet'];
$_SESSION['role']     = $user['role'];
$_SESSION['auth_time'] = time();

// Mise à jour de la date du dernier accès (identique à l'original)
try {
    $upd = $bdd->prepare("
        UPDATE utilisateurs 
           SET dernier_acces = NOW() 
         WHERE id_utilisateur = :uid
    ");
    $upd->execute([':uid' => $user['id_utilisateur']]);
} catch (PDOException $e) {
    // On ignore l'erreur de mise à jour (comme dans l'original)
}

// Redirection vers le tableau de bord (adapté pour DANFANIMENT)
// On garde la même structure de dossiers
header('Location: ../../pages/tableau_de_bord.php');
exit;