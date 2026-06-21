<?php
// api/modules/ajouter_produit.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: /garagee/index.php?erreur=3');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /garagee/pages/produits.php');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

// Récupération des données
$code_produit = trim($_POST['code_produit'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$description = trim($_POST['description'] ?? '');
$categorie = trim($_POST['categorie'] ?? 'vetements');
$sous_categorie = trim($_POST['sous_categorie'] ?? '');
$prix_achat = (float)($_POST['prix_achat'] ?? 0);
$prix_vente = (float)($_POST['prix_vente'] ?? 0);
$stock_initial = (int)($_POST['stock_initial'] ?? 0);
$stock_minimum = (int)($_POST['stock_minimum'] ?? 5);
$unite_mesure = trim($_POST['unite_mesure'] ?? 'piece');
$statut = trim($_POST['statut'] ?? 'actif');

if (empty($nom) || $prix_achat <= 0 || $prix_vente <= 0) {
    $_SESSION['err_produit'] = 1;
    header('Location: /garagee/pages/produits.php');
    exit;
}

// Génération du code produit si vide
if (empty($code_produit)) {
    $annee = date('Y');
    $mois = date('m');
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_produits WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
    $stmt->execute();
    $count = (int)$stmt->fetchColumn();
    $code_produit = sprintf("PRD-%s%s-%04d", $annee, $mois, $count + 1);
}

// Vérifier l'unicité du code produit
$stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_produits WHERE code_produit = :code");
$stmt->execute([':code' => $code_produit]);
if ($stmt->fetchColumn() > 0) {
    $_SESSION['code_exist'] = 1;
    header('Location: /garagee/pages/produits.php');
    exit;
}

// Gestion de la photo - Correction du chemin
$photo_name = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    // Chemin absolu depuis la racine du projet
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/garagee/uploads/produits/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($extension, $allowed)) {
        $photo_name = $code_produit . '.' . $extension;
        $upload_path = $upload_dir . $photo_name;
        move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path);
    }
}

try {
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_produits 
        (code_produit, nom, description, categorie, sous_categorie, prix_achat, prix_vente, 
         stock_initial, stock_actuel, stock_minimum, unite_mesure, photo, statut, created_by, created_at) 
        VALUES 
        (:code_produit, :nom, :description, :categorie, :sous_categorie, :prix_achat, :prix_vente, 
         :stock_initial, :stock_actuel, :stock_minimum, :unite_mesure, :photo, :statut, :created_by, NOW())
    ");
    
    $stmt->execute([
        ':code_produit' => $code_produit,
        ':nom' => $nom,
        ':description' => $description,
        ':categorie' => $categorie,
        ':sous_categorie' => $sous_categorie ?: null,
        ':prix_achat' => $prix_achat,
        ':prix_vente' => $prix_vente,
        ':stock_initial' => $stock_initial,
        ':stock_actuel' => $stock_initial,
        ':stock_minimum' => $stock_minimum,
        ':unite_mesure' => $unite_mesure,
        ':photo' => $photo_name,
        ':statut' => $statut,
        ':created_by' => $_SESSION['id']
    ]);
    
    $_SESSION['ajout_produit'] = 1;
    header('Location: /garagee/pages/produits.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['err_produit'] = 1;
    header('Location: /garagee/pages/produits.php');
    exit;
}
?>