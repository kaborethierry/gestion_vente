<?php
// api/modules/sync_avance_paiement.php
// Synchroniser les avances des commandes avec les paiements existants

session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $bdd->beginTransaction();
    
    // Récupérer toutes les commandes
    $stmt = $bdd->query("
        SELECT id_commande, numero_commande, montant_total, montant_avance, solde_restant 
        FROM danfaniment_commandes_confection 
        ORDER BY id_commande
    ");
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $synced = 0;
    $updated = 0;
    $errors = 0;
    
    foreach ($commandes as $commande) {
        // Calculer le total des paiements existants pour cette commande
        $stmt = $bdd->prepare("
            SELECT COALESCE(SUM(montant), 0) as total_paye 
            FROM danfaniment_paiements_confection 
            WHERE id_commande = :id_commande
        ");
        $stmt->execute([':id_commande' => $commande['id_commande']]);
        $total_paye = (float)$stmt->fetchColumn();
        
        $montant_avance_cmd = (float)$commande['montant_avance'];
        $montant_total = (float)$commande['montant_total'];
        $nouveau_solde = $montant_total - $total_paye;
        
        // Si l'avance dans la commande est différente du total des paiements
        if (abs($montant_avance_cmd - $total_paye) > 0.01) {
            // Mettre à jour la commande avec le bon montant d'avance
            $stmt = $bdd->prepare("
                UPDATE danfaniment_commandes_confection 
                SET montant_avance = :avance,
                    solde_restant = :solde,
                    updated_at = NOW()
                WHERE id_commande = :id_commande
            ");
            $stmt->execute([
                ':avance' => $total_paye,
                ':solde' => $nouveau_solde,
                ':id_commande' => $commande['id_commande']
            ]);
            $updated++;
            
            error_log("Commande {$commande['numero_commande']}: Avance corrigée de {$montant_avance_cmd} à {$total_paye}");
        }
        
        // Vérifier si un paiement d'avance existe
        $stmt = $bdd->prepare("
            SELECT COUNT(*) FROM danfaniment_paiements_confection 
            WHERE id_commande = :id_commande AND type_paiement = 'avance'
        ");
        $stmt->execute([':id_commande' => $commande['id_commande']]);
        $has_avance_paiement = $stmt->fetchColumn();
        
        // Si l'avance > 0 mais aucun paiement d'avance n'existe, en créer un
        if ($total_paye > 0 && $has_avance_paiement == 0) {
            $stmt = $bdd->prepare("
                SELECT COUNT(*) FROM danfaniment_paiements_confection WHERE DATE(created_at) = CURDATE()
            ");
            $stmt->execute();
            $paiements_jour = $stmt->fetchColumn();
            $numero_recu = 'RECU-' . date('Ymd') . '-' . str_pad($paiements_jour + $synced + 1, 4, '0', STR_PAD_LEFT);
            
            $stmt = $bdd->prepare("
                INSERT INTO danfaniment_paiements_confection 
                (id_commande, id_utilisateur, montant, type_paiement, mode_paiement, numero_recu, remarques, created_at)
                VALUES 
                (:id_commande, :id_utilisateur, :montant, 'avance', 'especes', :numero_recu, 'Synchronisation automatique', NOW())
            ");
            $stmt->execute([
                ':id_commande' => $commande['id_commande'],
                ':id_utilisateur' => $_SESSION['id'],
                ':montant' => $total_paye,
                ':numero_recu' => $numero_recu
            ]);
            $synced++;
            
            error_log("Commande {$commande['numero_commande']}: Paiement d'avance créé de {$total_paye}");
        }
    }
    
    $bdd->commit();
    
    $_SESSION['sync_paiement'] = "Synchronisation terminée : $synced paiements créés, $updated commandes mises à jour, $errors erreurs";
    header('Location: ../../pages/paiements_confection.php');
    exit;
    
} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur synchronisation: " . $e->getMessage());
    $_SESSION['err_paiement'] = 1;
    header('Location: ../../pages/paiements_confection.php');
    exit;
}
?>