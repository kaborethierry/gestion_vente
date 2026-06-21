<?php
// api/modules/get_client.php
// DANFANIMENT POS - Récupération des données d'un client avec historique complet

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    if (empty($_SESSION['id'])) {
        echo json_encode(['success' => false, 'error' => 'Non authentifié']);
        exit;
    }

    if (empty($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID client manquant']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php';

    $id_client = (int)$_GET['id'];

    // Informations du client avec calcul des totaux
    $stmt = $bdd->prepare("
        SELECT 
            c.*,
            COALESCE((
                SELECT SUM(v.total_ttc) 
                FROM danfaniment_ventes v 
                WHERE v.id_client = c.id_client AND v.statut = 'valide'
            ), 0) AS total_depense_calcule,
            COALESCE((
                SELECT COUNT(*) 
                FROM danfaniment_ventes v 
                WHERE v.id_client = c.id_client AND v.statut = 'valide'
            ), 0) AS nombre_visites_calcule,
            COALESCE((
                SELECT MAX(v.date_vente) 
                FROM danfaniment_ventes v 
                WHERE v.id_client = c.id_client AND v.statut = 'valide'
            ), c.date_derniere_visite) AS derniere_visite_calcule
        FROM danfaniment_clients c
        WHERE c.id_client = :id AND c.supprimer = 'Non'
    ");
    $stmt->execute([':id' => $id_client]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo json_encode(['success' => false, 'error' => 'Client non trouvé']);
        exit;
    }

    // Utiliser les valeurs calculées
    $client['total_depense'] = $client['total_depense_calcule'];
    $client['nombre_visites'] = $client['nombre_visites_calcule'];
    $client['date_derniere_visite'] = $client['derniere_visite_calcule'];

    // Récupération des mesures du client
    $stmt = $bdd->prepare("
        SELECT * FROM danfaniment_mesures_client 
        WHERE id_client = :id 
        ORDER BY date_mesure DESC
    ");
    $stmt->execute([':id' => $id_client]);
    $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $client['mesures'] = $mesures;
    $client['derniere_mesure'] = !empty($mesures) ? $mesures[0] : null;

    // Récupération des commandes de confection
    $stmt = $bdd->prepare("
        SELECT 
            c.*,
            p.nom as prestataire_nom,
            p.prenom as prestataire_prenom,
            p.type_prestataire,
            COALESCE((
                SELECT SUM(paiement.montant) 
                FROM danfaniment_paiements_confection paiement 
                WHERE paiement.id_commande = c.id_commande
            ), 0) AS total_paye
        FROM danfaniment_commandes_confection c
        LEFT JOIN danfaniment_prestataires p ON c.id_prestataire = p.id_prestataire
        WHERE c.id_client = :id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':id' => $id_client]);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pour chaque commande, récupérer les paiements détaillés
    foreach ($commandes as &$commande) {
        $stmt = $bdd->prepare("
            SELECT * FROM danfaniment_paiements_confection 
            WHERE id_commande = :id_commande 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':id_commande' => $commande['id_commande']]);
        $commande['paiements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $commande['solde_restant_calcule'] = $commande['montant_total'] - $commande['total_paye'];
    }
    $client['commandes_confection'] = $commandes;

    // Récupération de l'historique des achats (ventes)
    $stmt = $bdd->prepare("
        SELECT 
            v.id_vente,
            v.numero_vente,
            v.date_vente,
            v.total_ttc,
            v.mode_paiement,
            v.statut,
            u.nom_complet as caissier
        FROM danfaniment_ventes v
        LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
        WHERE v.id_client = :id AND v.statut = 'valide'
        ORDER BY v.date_vente DESC
    ");
    $stmt->execute([':id' => $id_client]);
    $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $client['historique_achats'] = $achats;
    $client['total_achats'] = array_sum(array_column($achats, 'total_ttc'));

    echo json_encode(['success' => true, 'client' => $client]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>