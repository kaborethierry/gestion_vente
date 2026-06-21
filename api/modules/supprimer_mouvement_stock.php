<?php
session_start();

if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_mouvement = intval($_GET['id_mouvement'] ?? 0);

if ($id_mouvement <= 0) {
    $_SESSION['err_mouvement'] = 1;
    header('Location: ../../pages/mouvements_stock.php');
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Récupérer les informations du mouvement
    $stmt = $bdd->prepare("SELECT id_produit, quantite FROM danfaniment_stock_mouvements WHERE id_mouvement = :id");
    $stmt->execute([':id' => $id_mouvement]);
    $mouvement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mouvement) {
        throw new Exception("Mouvement non trouvé");
    }
    
    // Annuler l'effet du mouvement sur le stock (inverse de la quantité)
    $quantite_inverse = -$mouvement['quantite'];
    
    // Mettre à jour le stock du produit
    $stmt = $bdd->prepare("UPDATE danfaniment_produits SET stock_actuel = stock_actuel + :quantite, updated_at = NOW() WHERE id_produit = :id_produit");
    $stmt->execute([
        ':quantite' => $quantite_inverse,
        ':id_produit' => $mouvement['id_produit']
    ]);
    
    // Supprimer le mouvement
    $stmt = $bdd->prepare("DELETE FROM danfaniment_stock_mouvements WHERE id_mouvement = :id");
    $stmt->execute([':id' => $id_mouvement]);
    
    $bdd->commit();
    
    $_SESSION['mouvement_supprime'] = 1;
    
} catch (Exception $e) {
    $bdd->rollBack();
    $_SESSION['err_mouvement'] = 1;
}

header('Location: ../../pages/mouvements_stock.php');
exit;
?>