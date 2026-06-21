<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_mouvement = intval($_POST['id_mouvement'] ?? 0);
$type_mouvement = $_POST['type_mouvement'] ?? '';
$quantite = intval($_POST['quantite'] ?? 0);
$reference = trim($_POST['reference'] ?? '');
$motif = trim($_POST['motif'] ?? '');

if ($id_mouvement <= 0 || empty($type_mouvement) || $quantite <= 0 || empty($motif)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Récupérer l'ancien mouvement
    $stmt = $bdd->prepare("SELECT id_produit, quantite as ancienne_quantite, type_mouvement as ancien_type FROM danfaniment_stock_mouvements WHERE id_mouvement = :id");
    $stmt->execute([':id' => $id_mouvement]);
    $ancien = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ancien) {
        throw new Exception("Mouvement non trouvé");
    }
    
    // Récupérer le stock actuel
    $stmt = $bdd->prepare("SELECT stock_actuel FROM danfaniment_produits WHERE id_produit = :id");
    $stmt->execute([':id' => $ancien['id_produit']]);
    $stock_actuel = $stmt->fetchColumn();
    
    // Annuler l'effet de l'ancien mouvement
    $stock_corrige = $stock_actuel - $ancien['ancienne_quantite'];
    
    // Calculer le nouveau stock après modification
    $nouvelle_quantite = in_array($type_mouvement, ['entree', 'ajustement']) ? $quantite : -$quantite;
    $stock_apres = $stock_corrige + $nouvelle_quantite;
    
    if ($stock_apres < 0) {
        echo json_encode(['success' => false, 'message' => 'Cette modification rendrait le stock négatif']);
        exit;
    }
    
    // Mettre à jour le mouvement
    $stmt = $bdd->prepare("
        UPDATE danfaniment_stock_mouvements 
        SET type_mouvement = :type_mouvement,
            quantite = :quantite,
            stock_apres = :stock_apres,
            reference = :reference,
            motif = :motif,
            created_at = NOW()
        WHERE id_mouvement = :id_mouvement
    ");
    $stmt->execute([
        ':type_mouvement' => $type_mouvement,
        ':quantite' => $nouvelle_quantite,
        ':stock_apres' => $stock_apres,
        ':reference' => $reference ?: null,
        ':motif' => $motif,
        ':id_mouvement' => $id_mouvement
    ]);
    
    // Mettre à jour le stock du produit
    $stmt = $bdd->prepare("UPDATE danfaniment_produits SET stock_actuel = :stock, updated_at = NOW() WHERE id_produit = :id_produit");
    $stmt->execute([
        ':stock' => $stock_apres,
        ':id_produit' => $ancien['id_produit']
    ]);
    
    $bdd->commit();
    
    echo json_encode(['success' => true, 'message' => 'Mouvement modifié avec succès']);
    
} catch (Exception $e) {
    $bdd->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>