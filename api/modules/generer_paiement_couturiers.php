<?php
// api/modules/generer_paiement_couturiers.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Récupérer tous les couturiers avec des productions en attente cette semaine
    $stmt = $bdd->prepare("
        SELECT 
            p.id_prestataire,
            p.nom,
            p.prenom,
            SUM(pr.montant_unitaire * pr.quantite) AS montant_total,
            GROUP_CONCAT(pr.id_production) AS production_ids
        FROM danfaniment_prestataires p
        INNER JOIN danfaniment_productions_prestataires pr ON p.id_prestataire = pr.id_prestataire
        WHERE p.type_prestataire = 'couturier'
            AND pr.statut_paiement = 'en_attente'
            AND WEEK(pr.semaine_debut) = WEEK(CURDATE())
        GROUP BY p.id_prestataire
    ");
    $stmt->execute();
    $couturiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($couturiers as $couturier) {
        // Mettre à jour les productions comme étant à payer
        $ids = explode(',', $couturier['production_ids']);
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt2 = $bdd->prepare("
                UPDATE danfaniment_productions_prestataires 
                SET statut_paiement = 'a_payer' 
                WHERE id_production IN ($placeholders)
            ");
            $stmt2->execute($ids);
        }
    }
    
    $_SESSION['paiement_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
    
} catch (Exception $e) {
    error_log("Erreur génération paiement couturiers: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}
?>