<?php
// api/modules/modifier_utilisateur.php
// DANFANIMENT POS - Modification d'un utilisateur

session_start();

// 1. Vérification du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation des champs POST obligatoires
$required = ['id_utilisateur', 'nom_complet', 'nom_utilisateur', 'role', 'status'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['err_util'] = 1;
        header('Location: ../../pages/utilisateur.php');
        exit;
    }
}

// 3. Récupération et assainissement des données
$id           = (int) $_POST['id_utilisateur'];
$nom_complet  = trim($_POST['nom_complet']);
$username     = trim($_POST['nom_utilisateur']);
$role         = trim($_POST['role']);
$status       = trim($_POST['status']);
$email        = isset($_POST['email']) ? trim($_POST['email']) : '';
$telephone    = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
$new_password = isset($_POST['mot_de_passe']) ? trim($_POST['mot_de_passe']) : '';

// Convertir status en entier
$actif = ($status === 'actif') ? 1 : 0;

// 4. Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // 5. Vérifier l'unicité du nom_utilisateur (exclure l'utilisateur actuel)
    $stmt = $bdd->prepare("
        SELECT COUNT(*) 
        FROM utilisateurs 
        WHERE nom_utilisateur = :username 
        AND id_utilisateur <> :id
        AND supprimer = 'Non'
    ");
    $stmt->execute([
        ':username' => $username,
        ':id'       => $id
    ]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/utilisateur.php');
        exit;
    }

    // 6. Construction dynamique de la requête
    if (!empty($new_password)) {
        // Si nouveau mot de passe fourni
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "
            UPDATE utilisateurs 
            SET nom_complet = :nom_complet,
                nom_utilisateur = :username,
                email = :email,
                telephone = :telephone,
                role = :role,
                actif = :actif,
                mot_de_passe = :pwd
            WHERE id_utilisateur = :id
        ";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            ':nom_complet' => $nom_complet,
            ':username'    => $username,
            ':email'       => $email,
            ':telephone'   => $telephone,
            ':role'        => $role,
            ':actif'       => $actif,
            ':pwd'         => $hash,
            ':id'          => $id
        ]);
    } else {
        // Sans modification du mot de passe
        $sql = "
            UPDATE utilisateurs 
            SET nom_complet = :nom_complet,
                nom_utilisateur = :username,
                email = :email,
                telephone = :telephone,
                role = :role,
                actif = :actif
            WHERE id_utilisateur = :id
        ";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            ':nom_complet' => $nom_complet,
            ':username'    => $username,
            ':email'       => $email,
            ':telephone'   => $telephone,
            ':role'        => $role,
            ':actif'       => $actif,
            ':id'          => $id
        ]);
    }

    $_SESSION['mod'] = 1;
    // CORRECTION : REDIRECTION CORRECTE
    header('Location: ../../pages/utilisateur.php');
    exit;

} catch (Exception $e) {
    $_SESSION['err_util'] = 1;
    header('Location: ../../pages/utilisateur.php');
    exit;
}
?>