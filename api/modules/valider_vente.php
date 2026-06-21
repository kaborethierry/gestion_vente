<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $bdd->beginTransaction();
    
    // Générer numéro vente
    $stmt = $bdd->query("SELECT COUNT(*) + 1 FROM danfaniment_ventes WHERE DATE(date_vente) = CURDATE()");
    $compteur = $stmt->fetchColumn();
    $numero_vente = 'VT-' . date('Ymd') . '-' . str_pad($compteur, 4, '0', STR_PAD_LEFT);
    
    // Calcul remise
    $remise_montant = 0;
    if ($input['remise_type'] === 'pourcentage') {
        $remise_montant = $input['sous_total'] * ($input['remise_valeur'] / 100);
    } else {
        $remise_montant = $input['remise_valeur'];
    }
    
    // Insérer vente
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_ventes 
        (numero_vente, id_utilisateur, date_vente, sous_total, remise_type, 
         remise_valeur, remise_montant, total_ttc, montant_recu, monnaie_rendue, 
         mode_paiement, statut, sync_status) 
        VALUES 
        (:numero, :id_user, NOW(), :sous_total, :remise_type, 
         :remise_valeur, :remise_montant, :total, :recu, :monnaie, 
         :mode, 'valide', 'pending')
    ");
    $stmt->execute([
        ':numero' => $numero_vente,
        ':id_user' => $_SESSION['id'],
        ':sous_total' => $input['sous_total'],
        ':remise_type' => $input['remise_type'],
        ':remise_valeur' => $input['remise_valeur'],
        ':remise_montant' => $remise_montant,
        ':total' => $input['total_ttc'],
        ':recu' => $input['montant_recu'],
        ':monnaie' => $input['monnaie_rendue'],
        ':mode' => $input['mode_paiement']
    ]);
    
    $id_vente = $bdd->lastInsertId();
    
    // Insérer lignes et mettre à jour stock
    foreach ($input['panier'] as $item) {
        $stmt = $bdd->prepare("
            INSERT INTO danfaniment_lignes_ventes 
            (id_vente, id_produit, code_produit, nom_produit, quantite, 
             prix_unitaire, remise_ligne, total_ligne) 
            VALUES 
            (:id_vente, :id_produit, :code, :nom, :qte, :prix, 0, :total)
        ");
        $stmt->execute([
            ':id_vente' => $id_vente,
            ':id_produit' => $item['id_produit'],
            ':code' => $item['code_produit'],
            ':nom' => $item['nom_produit'],
            ':qte' => $item['quantite'],
            ':prix' => $item['prix_unitaire'],
            ':total' => $item['total_ligne']
        ]);
        
        // Mettre à jour stock
        $stmt = $bdd->prepare("
            UPDATE danfaniment_produits 
            SET stock_actuel = stock_actuel - :qte,
                updated_at = NOW()
            WHERE id_produit = :id_produit
        ");
        $stmt->execute([
            ':qte' => $item['quantite'],
            ':id_produit' => $item['id_produit']
        ]);
    }
    
    // Mettre à jour caisse
    $stmt = $bdd->prepare("
        UPDATE danfaniment_caisses 
        SET nombre_ventes = nombre_ventes + 1,
            total_ventes_net = total_ventes_net + :total,
            total_" . $input['mode_paiement'] . " = total_" . $input['mode_paiement'] . " + :total,
            montant_final_theorique = montant_final_theorique + :total,
            dernier_numero_vente = :numero,
            updated_at = NOW()
        WHERE id_caisse = :id_caisse
    ");
    $stmt->execute([
        ':total' => $input['total_ttc'],
        ':numero' => $numero_vente,
        ':id_caisse' => $input['id_caisse']
    ]);
    
    // Log operation
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_caisse_operations 
        (id_caisse, id_utilisateur, type_operation, reference, montant, description) 
        VALUES 
        (:id_caisse, :id_user, 'vente', :reference, :montant, :description)
    ");
    $stmt->execute([
        ':id_caisse' => $input['id_caisse'],
        ':id_user' => $_SESSION['id'],
        ':reference' => $numero_vente,
        ':montant' => $input['total_ttc'],
        ':description' => "Vente #{$numero_vente} - {$input['mode_paiement']}"
    ]);
    
    $bdd->commit();
    
    echo json_encode(['success' => true, 'numero_vente' => $numero_vente]);
    
} catch (Exception $e) {
    $bdd->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>