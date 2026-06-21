<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_produit = intval($_POST['id_produit'] ?? 0);
$type_mouvement = $_POST['type_mouvement'] ?? '';
$quantite = intval($_POST['quantite'] ?? 0);
$reference = trim($_POST['reference'] ?? '');
$motif = trim($_POST['motif'] ?? '');

if ($id_produit <= 0 || empty($type_mouvement) || $quantite <= 0 || empty($motif)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Récupérer le stock actuel du produit
    $stmt = $bdd->prepare("SELECT stock_actuel FROM danfaniment_produits WHERE id_produit = :id");
    $stmt->execute([':id' => $id_produit]);
    $stock_actuel = $stmt->fetchColumn();
    
    if ($stock_actuel === false) {
        throw new Exception("Produit non trouvé");
    }
    
    // Pour une sortie, vérifier que le stock est suffisant
    if (in_array($type_mouvement, ['sortie', 'retour']) && $quantite > $stock_actuel) {
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant pour cette opération']);
        exit;
    }
    
    // Calculer la quantité pour le mouvement (positive pour entrée, négative pour sortie)
    $quantite_mouvement = in_array($type_mouvement, ['entree', 'ajustement']) ? $quantite : -$quantite;
    $stock_apres = $stock_actuel + $quantite_mouvement;
    
    // Insérer le mouvement
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_stock_mouvements 
        (id_produit, type_mouvement, quantite, stock_avant, stock_apres, reference, id_utilisateur, motif, created_at) 
        VALUES 
        (:id_produit, :type_mouvement, :quantite, :stock_avant, :stock_apres, :reference, :id_utilisateur, :motif, NOW())
    ");
    $stmt->execute([
        ':id_produit' => $id_produit,
        ':type_mouvement' => $type_mouvement,
        ':quantite' => $quantite_mouvement,
        ':stock_avant' => $stock_actuel,
        ':stock_apres' => $stock_apres,
        ':reference' => $reference ?: null,
        ':id_utilisateur' => $_SESSION['id'],
        ':motif' => $motif
    ]);
    
    $bdd->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Le mouvement de stock a été enregistré avec succès !',
        'stock_apres' => $stock_apres
    ]);
    
} catch (Exception $e) {
    $bdd->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>