<?php
// api/modules/supprimer_produit.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: /garagee/index.php?erreur=3');
    exit;
}

if (empty($_GET['id_produit']) || !ctype_digit($_GET['id_produit'])) {
    $_SESSION['err_produit'] = 1;
    header('Location: /garagee/pages/produits.php');
    exit;
}

$id_produit = (int)$_GET['id_produit'];

require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Récupérer la photo pour la supprimer
    $stmt = $bdd->prepare("SELECT photo FROM danfaniment_produits WHERE id_produit = :id");
    $stmt->execute([':id' => $id_produit]);
    $photo = $stmt->fetchColumn();
    
    if ($photo) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/garagee/uploads/produits/';
        if (file_exists($upload_dir . $photo)) {
            unlink($upload_dir . $photo);
        }
    }
    
    $stmt = $bdd->prepare("DELETE FROM danfaniment_produits WHERE id_produit = :id");
    $stmt->execute([':id' => $id_produit]);
    
    $_SESSION['supr_produit'] = 1;
} catch (Exception $e) {
    $_SESSION['err_produit'] = 1;
}

header('Location: /garagee/pages/produits.php');
exit;
?>