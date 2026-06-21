<?php
// api/modules/ajouter_client.php
// DANFANIMENT POS - Ajout d'un client

session_start();

// Vérification de l'authentification (admin ou caissier peuvent ajouter)
if (empty($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// Vérifier la présence des champs obligatoires
if (!isset($_POST['nom'], $_POST['prenom'], $_POST['telephone'])) {
    $_SESSION['err_client'] = 1;
    header('Location: ../../pages/clients.php');
    exit;
}

// Récupération et assainissement des données
$nom        = trim($_POST['nom']);
$prenom     = trim($_POST['prenom']);
$telephone  = trim($_POST['telephone']);
$email      = trim($_POST['email'] ?? '');
$adresse    = trim($_POST['adresse'] ?? '');
$ville      = trim($_POST['ville'] ?? '');
$notes      = trim($_POST['notes'] ?? '');

// Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Vérifier l'unicité du téléphone
    $stmt = $bdd->prepare('SELECT COUNT(*) FROM danfaniment_clients WHERE telephone = :telephone AND supprimer = "Non"');
    $stmt->execute([':telephone' => $telephone]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['imp_client'] = 1;
        header('Location: ../../pages/clients.php');
        exit;
    }

    // Insérer le nouveau client
    $stmt = $bdd->prepare('
        INSERT INTO danfaniment_clients 
        (nom, prenom, telephone, email, adresse, ville, notes, date_premiere_visite, date_derniere_visite, nombre_visites) 
        VALUES (:nom, :prenom, :telephone, :email, :adresse, :ville, :notes, NOW(), NOW(), 1)
    ');
    $stmt->execute([
        ':nom'       => $nom,
        ':prenom'    => $prenom,
        ':telephone' => $telephone,
        ':email'     => $email,
        ':adresse'   => $adresse,
        ':ville'     => $ville,
        ':notes'     => $notes
    ]);

    $_SESSION['ajout_client'] = 1;
    header('Location: ../../pages/clients.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['err_client'] = 1;
    header('Location: ../../pages/clients.php');
    exit;
}
?>