<?php
// api/modules/supprimer_client.php
// DANFANIMENT POS - Suppression d'un client

session_start();

// Vérification de l'authentification
if (empty($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// Validation du paramètre GET id_client
if (empty($_GET['id_client']) || !ctype_digit($_GET['id_client'])) {
    $_SESSION['err_client'] = 1;
    header('Location: ../../pages/clients.php');
    exit;
}

// Cast sécurisé de l'ID
$id_client = (int) $_GET['id_client'];

try {
    // Connexion PDO
    require_once __DIR__ . '/connect_db_pdo.php';

    // Suppression logique (soft delete)
    $stmt = $bdd->prepare('UPDATE danfaniment_clients SET supprimer = "Oui" WHERE id_client = :id');
    $stmt->execute([':id' => $id_client]);

    $_SESSION['supr_client'] = 1;

} catch (Exception $e) {
    $_SESSION['err_client'] = 1;
}

header('Location: ../../pages/clients.php');
exit;
?>