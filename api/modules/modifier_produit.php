<?php
// api/modules/modifier_produit.php
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

$id_produit = (int)($_POST['id_produit'] ?? 0);
$nom = trim($_POST['nom'] ?? '');
$description = trim($_POST['description'] ?? '');
$categorie = trim($_POST['categorie'] ?? 'vetements');
$sous_categorie = trim($_POST['sous_categorie'] ?? '');
$prix_achat = (float)($_POST['prix_achat'] ?? 0);
$prix_vente = (float)($_POST['prix_vente'] ?? 0);
$stock_minimum = (int)($_POST['stock_minimum'] ?? 5);
$unite_mesure = trim($_POST['unite_mesure'] ?? 'piece');
$statut = trim($_POST['statut'] ?? 'actif');
$photo_actuelle = trim($_POST['photo_actuelle'] ?? '');

if ($id_produit <= 0 || empty($nom) || $prix_achat <= 0 || $prix_vente <= 0) {
    $_SESSION['err_produit'] = 1;
    header('Location: /garagee/pages/produits.php');
    exit;
}

// Gestion de la photo
$photo_name = $photo_actuelle;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/garagee/uploads/produits/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Supprimer l'ancienne photo
    if ($photo_actuelle && file_exists($upload_dir . $photo_actuelle)) {
        unlink($upload_dir . $photo_actuelle);
    }
    
    $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($extension, $allowed)) {
        $stmt = $bdd->prepare("SELECT code_produit FROM danfaniment_produits WHERE id_produit = :id");
        $stmt->execute([':id' => $id_produit]);
        $code_produit = $stmt->fetchColumn();
        
        $photo_name = $code_produit . '.' . $extension;
        $upload_path = $upload_dir . $photo_name;
        move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path);
    }
}

try {
    $stmt = $bdd->prepare("
        UPDATE danfaniment_produits 
        SET nom = :nom,
            description = :description,
            categorie = :categorie,
            sous_categorie = :sous_categorie,
            prix_achat = :prix_achat,
            prix_vente = :prix_vente,
            stock_minimum = :stock_minimum,
            unite_mesure = :unite_mesure,
            photo = :photo,
            statut = :statut,
            updated_by = :updated_by,
            updated_at = NOW()
        WHERE id_produit = :id_produit
    ");
    
    $stmt->execute([
        ':id_produit' => $id_produit,
        ':nom' => $nom,
        ':description' => $description,
        ':categorie' => $categorie,
        ':sous_categorie' => $sous_categorie ?: null,
        ':prix_achat' => $prix_achat,
        ':prix_vente' => $prix_vente,
        ':stock_minimum' => $stock_minimum,
        ':unite_mesure' => $unite_mesure,
        ':photo' => $photo_name,
        ':statut' => $statut,
        ':updated_by' => $_SESSION['id']
    ]);
    
    $_SESSION['mod_produit'] = 1;
    header('Location: /garagee/pages/produits.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['err_produit'] = 1;
    header('Location: /garagee/pages/produits.php');
    exit;
}
?>