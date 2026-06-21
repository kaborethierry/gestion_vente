<?php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
    header('Location: /garagee/index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$email = trim($_POST['email'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$ville = trim($_POST['ville'] ?? '');

if (empty($nom) || empty($prenom) || empty($telephone)) {
    $_SESSION['err_pos'] = 1;
    header('Location: /garagee/pages/pos.php');
    exit;
}

try {
    $stmt = $bdd->prepare("INSERT INTO danfaniment_clients (nom, prenom, telephone, email, adresse, ville, date_premiere_visite, date_derniere_visite) 
                           VALUES (:nom, :prenom, :telephone, :email, :adresse, :ville, NOW(), NOW())");
    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':telephone' => $telephone,
        ':email' => $email ?: null,
        ':adresse' => $adresse ?: null,
        ':ville' => $ville ?: null
    ]);
    
    $_SESSION['client_ajoute'] = 1;
    
} catch (PDOException $e) {
    $_SESSION['err_pos'] = 1;
}

header('Location: /garagee/pages/pos.php');
exit;
?>