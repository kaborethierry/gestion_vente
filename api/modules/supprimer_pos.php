<?php
session_start();

// Seul un Admin peut supprimer définitivement une vente
if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: /garagee/index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_vente = intval($_GET['id_vente'] ?? 0);

if ($id_vente <= 0) {
    $_SESSION['err_pos'] = 1;
    header('Location: /garagee/pages/ventes.php');
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Vérifier que la vente est annulée ou remboursée avant suppression
    $stmt = $bdd->prepare("SELECT statut FROM danfaniment_ventes WHERE id_vente = :id");
    $stmt->execute([':id' => $id_vente]);
    $statut = $stmt->fetchColumn();
    
    if ($statut !== 'annulee' && $statut !== 'remboursee') {
        throw new Exception("Seules les ventes annulées ou remboursées peuvent être supprimées");
    }
    
    // Supprimer les lignes de vente
    $stmt = $bdd->prepare("DELETE FROM danfaniment_ventes_lignes WHERE id_vente = :id");
    $stmt->execute([':id' => $id_vente]);
    
    // Supprimer la vente
    $stmt = $bdd->prepare("DELETE FROM danfaniment_ventes WHERE id_vente = :id");
    $stmt->execute([':id' => $id_vente]);
    
    $bdd->commit();
    
    $_SESSION['vente_supprimee'] = 1;
    
} catch (Exception $e) {
    $bdd->rollBack();
    $_SESSION['err_pos'] = 1;
}

header('Location: /garagee/pages/ventes.php');
exit;
?>