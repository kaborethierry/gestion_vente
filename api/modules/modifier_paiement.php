<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Seul un Admin peut modifier un paiement
if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé. Réservé aux administrateurs.']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_paiement = intval($_POST['id_paiement'] ?? 0);
$montant = floatval($_POST['montant'] ?? 0);
$type_paiement = $_POST['type_paiement'] ?? 'avance';
$mode_paiement = $_POST['mode_paiement'] ?? 'especes';
$reference_transaction = trim($_POST['reference_transaction'] ?? '');
$remarques = trim($_POST['remarques'] ?? '');

if ($id_paiement <= 0 || $montant <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Récupérer l'ancien montant et la commande
    $stmt = $bdd->prepare("SELECT id_commande, montant FROM danfaniment_paiements_confection WHERE id_paiement = :id");
    $stmt->execute([':id' => $id_paiement]);
    $ancien = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ancien) {
        throw new Exception("Paiement non trouvé");
    }
    
    $id_commande = $ancien['id_commande'];
    $difference = $montant - $ancien['montant'];
    
    // Vérifier le solde restant si augmentation
    if ($difference > 0) {
        $stmt = $bdd->prepare("SELECT solde_restant FROM danfaniment_commandes_confection WHERE id_commande = :id");
        $stmt->execute([':id' => $id_commande]);
        $solde = $stmt->fetchColumn();
        
        if ($difference > $solde) {
            throw new Exception("Le nouveau montant dépasse le solde restant de la commande");
        }
    }
    
    // Mettre à jour le paiement
    $stmt = $bdd->prepare("
        UPDATE danfaniment_paiements_confection 
        SET montant = :montant,
            type_paiement = :type_paiement,
            mode_paiement = :mode_paiement,
            reference_transaction = :reference_transaction,
            remarques = :remarques
        WHERE id_paiement = :id
    ");
    $stmt->execute([
        ':montant' => $montant,
        ':type_paiement' => $type_paiement,
        ':mode_paiement' => $mode_paiement,
        ':reference_transaction' => $reference_transaction ?: null,
        ':remarques' => $remarques ?: null,
        ':id' => $id_paiement
    ]);
    
    // Recalculer le total des paiements et mettre à jour la commande
    $stmt = $bdd->prepare("
        UPDATE danfaniment_commandes_confection c
        SET c.montant_avance = (
            SELECT COALESCE(SUM(montant), 0)
            FROM danfaniment_paiements_confection
            WHERE id_commande = c.id_commande
        ),
        c.solde_restant = c.montant_total - (
            SELECT COALESCE(SUM(montant), 0)
            FROM danfaniment_paiements_confection
            WHERE id_commande = c.id_commande
        ),
        c.updated_at = NOW()
        WHERE c.id_commande = :id_commande
    ");
    $stmt->execute([':id_commande' => $id_commande]);
    
    $bdd->commit();
    
    // Mettre à jour la session
    $_SESSION['paiement_modifie'] = 1;
    header('Location: ../../pages/paiements.php');
    
} catch (Exception $e) {
    $bdd->rollBack();
    $_SESSION['err_paiement'] = 1;
    header('Location: ../../pages/paiements.php');
}
?>