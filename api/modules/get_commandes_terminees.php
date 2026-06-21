<?php
// api/modules/get_commandes_terminees.php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode([]);
    exit;
}

if (empty($_GET['id_prestataire'])) {
    echo json_encode([]);
    exit;
}

$id_prestataire = (int)$_GET['id_prestataire'];
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Vérifier la connexion
    if (!$bdd) {
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    
    $stmt = $bdd->prepare("
        SELECT 
            c.id_commande,
            c.numero_commande,
            c.type_tenue,
            CONCAT(cl.nom, ' ', cl.prenom) AS client_nom
        FROM danfaniment_commandes_confection c
        INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        WHERE c.id_prestataire = :id_prestataire
            AND c.statut = 'termine'
            AND c.id_commande NOT IN (
                SELECT COALESCE(id_commande, 0) 
                FROM danfaniment_productions_prestataires 
                WHERE id_commande IS NOT NULL
            )
        ORDER BY c.date_commande DESC
    ");
    $stmt->execute([':id_prestataire' => $id_prestataire]);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // S'assurer que le résultat est un tableau
    if (!$commandes) {
        $commandes = [];
    }
    
    echo json_encode($commandes);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>