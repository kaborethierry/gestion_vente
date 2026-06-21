<?php
// api/modules/supprimer_utilisateur.php
// DANFANIMENT POS - Suppression d'un utilisateur

session_start();

// 1. Vérification de la session et du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation du paramètre GET id_utilisateur
if (empty($_GET['id_utilisateur']) || !ctype_digit($_GET['id_utilisateur'])) {
    $_SESSION['err_util'] = 1;
    header('Location: ../../pages/utilisateur.php');
    exit;
}

// 3. Cast sécurisé de l'ID
$idUtilisateur = (int) $_GET['id_utilisateur'];

// 4. Empêcher la suppression de son propre compte
if ($idUtilisateur === $_SESSION['id']) {
    $_SESSION['err_util'] = 1;
    header('Location: ../../pages/utilisateur.php');
    exit;
}

try {
    // 5. Connexion PDO
    require_once __DIR__ . '/connect_db_pdo.php';

    // 6. Suppression logique (soft delete) ou définitive
    // Pour DANFANIMENT, on fait un soft delete en mettant supprimer = 'Oui'
    $stmt = $bdd->prepare('UPDATE utilisateurs SET supprimer = "Oui", actif = 0 WHERE id_utilisateur = :id');
    $stmt->execute([':id' => $idUtilisateur]);

    $_SESSION['supr'] = 1;

} catch (Exception $e) {
    $_SESSION['err_util'] = 1;
}

header('Location: ../../pages/utilisateur.php');
exit;
?>