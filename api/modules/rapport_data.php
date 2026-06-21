<?php
// api/modules/rapport_data.php
// DANFANIMENT POS - Récupération des données pour rapports

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$type = $_GET['type'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';

try {
    $data = [];
    
    switch($type) {
        case 'journalier':
            $data = getRapportJournalier($bdd, $date_debut, $date_fin);
            break;
        case 'hebdomadaire':
            $data = getRapportHebdomadaire($bdd, $date_debut, $date_fin);
            break;
        case 'mensuel':
            $data = getRapportMensuel($bdd, $date_debut, $date_fin);
            break;
        case 'annuel':
            $data = getRapportAnnuel($bdd, $date_debut, $date_fin);
            break;
        case 'clients':
            $data = getRapportClients($bdd);
            break;
        case 'prestataires':
            $data = getRapportPrestataires($bdd);
            break;
        case 'personnalise':
            $data = getRapportPersonnalise($bdd, $date_debut, $date_fin);
            break;
        default:
            $data = ['error' => 'Type de rapport invalide'];
    }
    
    echo json_encode($data);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

// ==================== RAPPORT JOURNALIER DÉTAILLÉ ====================
function getRapportJournalier($bdd, $date_debut, $date_fin) {
    if (empty($date_debut)) {
        $date_debut = date('Y-m-d');
        $date_fin = date('Y-m-d');
    }
    
    // 1. Ventes du jour détaillées
    $stmt = $bdd->prepare("
        SELECT 
            v.numero_vente, 
            v.date_vente,
            v.total_ttc, 
            v.mode_paiement, 
            v.sous_total,
            v.remise_montant,
            v.reference_transaction,
            u.nom_complet as caissier,
            CONCAT(cl.nom, ' ', cl.prenom) as client
        FROM danfaniment_ventes v
        LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
        LEFT JOIN danfaniment_clients cl ON v.id_client = cl.id_client
        WHERE DATE(v.date_vente) = :date AND v.statut = 'valide'
    ");
    $stmt->execute([':date' => $date_debut]);
    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Détail des ventes par mode de paiement
    $stmt = $bdd->prepare("
        SELECT 
            mode_paiement, 
            COUNT(*) as nb_ventes,
            SUM(total_ttc) as montant_total
        FROM danfaniment_ventes
        WHERE DATE(date_vente) = :date AND statut = 'valide'
        GROUP BY mode_paiement
    ");
    $stmt->execute([':date' => $date_debut]);
    $ventes_par_mode = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Total ventes
    $stmt = $bdd->prepare("
        SELECT 
            COALESCE(SUM(total_ttc), 0) as total,
            COALESCE(SUM(sous_total), 0) as total_brut,
            COALESCE(SUM(remise_montant), 0) as total_remises,
            COUNT(*) as nb_ventes
        FROM danfaniment_ventes
        WHERE DATE(date_vente) = :date AND statut = 'valide'
    ");
    $stmt->execute([':date' => $date_debut]);
    $total_ventes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 4. Confections livrées détaillées
    $stmt = $bdd->prepare("
        SELECT 
            c.numero_commande, 
            CONCAT(cl.nom, ' ', cl.prenom) as client,
            c.type_tenue, 
            c.montant_total,
            c.montant_avance,
            c.date_livraison_reelle,
            c.description_tenue
        FROM danfaniment_commandes_confection c
        LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        WHERE DATE(c.date_livraison_reelle) = :date AND c.statut = 'livre'
    ");
    $stmt->execute([':date' => $date_debut]);
    $confections_livrees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 5. Dépenses du jour détaillées par catégorie
    $stmt = $bdd->prepare("
        SELECT 
            id_depense,
            libelle, 
            categorie,
            montant,
            beneficiaire,
            justification,
            mode_paiement,
            reference_transaction
        FROM danfaniment_depenses
        WHERE DATE(date_depense) = :date AND statut = 'valide'
        ORDER BY categorie, montant DESC
    ");
    $stmt->execute([':date' => $date_debut]);
    $depenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 6. Dépenses par catégorie
    $stmt = $bdd->prepare("
        SELECT 
            categorie, 
            COUNT(*) as nb_depenses,
            SUM(montant) as total
        FROM danfaniment_depenses
        WHERE DATE(date_depense) = :date AND statut = 'valide'
        GROUP BY categorie
    ");
    $stmt->execute([':date' => $date_debut]);
    $depenses_par_categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 7. Total dépenses
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(montant), 0) as total
        FROM danfaniment_depenses
        WHERE DATE(date_depense) = :date AND statut = 'valide'
    ");
    $stmt->execute([':date' => $date_debut]);
    $total_depenses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 8. Paiements confection du jour
    $stmt = $bdd->prepare("
        SELECT 
            p.*,
            c.numero_commande,
            CONCAT(cl.nom, ' ', cl.prenom) as client,
            u.nom_complet as caissier
        FROM danfaniment_paiements_confection p
        LEFT JOIN danfaniment_commandes_confection c ON p.id_commande = c.id_commande
        LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        LEFT JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
        WHERE DATE(p.created_at) = :date
    ");
    $stmt->execute([':date' => $date_debut]);
    $paiements_confection = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 9. Calcul du bénéfice
    $benefice = $total_ventes['total'] - $total_depenses;
    
    return [
        'date' => $date_debut,
        'ventes' => $ventes,
        'ventes_par_mode' => $ventes_par_mode,
        'total_ventes' => $total_ventes['total'],
        'total_ventes_brut' => $total_ventes['total_brut'],
        'total_remises' => $total_ventes['total_remises'],
        'nb_ventes' => $total_ventes['nb_ventes'],
        'confections_livrees' => $confections_livrees,
        'nb_confections_livrees' => count($confections_livrees),
        'depenses' => $depenses,
        'depenses_par_categorie' => $depenses_par_categorie,
        'total_depenses' => $total_depenses,
        'paiements_confection' => $paiements_confection,
        'nb_paiements_confection' => count($paiements_confection),
        'total_paiements_confection' => array_sum(array_column($paiements_confection, 'montant')),
        'benefice' => $benefice
    ];
}

// ==================== RAPPORT HEBDOMADAIRE DÉTAILLÉ ====================
function getRapportHebdomadaire($bdd, $date_debut, $date_fin) {
    if (empty($date_debut)) {
        $date_debut = date('Y-m-d', strtotime('monday this week'));
        $date_fin = date('Y-m-d');
    }
    
    // 1. Ventes par jour de la semaine
    $stmt = $bdd->prepare("
        SELECT 
            DATE(date_vente) as jour,
            COUNT(*) as nb_ventes,
            SUM(total_ttc) as total_ventes,
            SUM(remise_montant) as total_remises
        FROM danfaniment_ventes
        WHERE DATE(date_vente) BETWEEN :debut AND :fin AND statut = 'valide'
        GROUP BY DATE(date_vente)
        ORDER BY jour
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $ventes_par_jour = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Total CA semaine
    $stmt = $bdd->prepare("
        SELECT 
            COALESCE(SUM(total_ttc), 0) as ca,
            COALESCE(SUM(sous_total), 0) as ca_brut,
            COALESCE(SUM(remise_montant), 0) as total_remises,
            COUNT(*) as nb_ventes
        FROM danfaniment_ventes
        WHERE DATE(date_vente) BETWEEN :debut AND :fin AND statut = 'valide'
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. Ventes par mode de paiement
    $stmt = $bdd->prepare("
        SELECT 
            mode_paiement, 
            COUNT(*) as nb_ventes,
            SUM(total_ttc) as montant_total
        FROM danfaniment_ventes
        WHERE DATE(date_vente) BETWEEN :debut AND :fin AND statut = 'valide'
        GROUP BY mode_paiement
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $ventes_par_mode = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Dépenses semaine par catégorie
    $stmt = $bdd->prepare("
        SELECT 
            categorie, 
            COUNT(*) as nb_depenses,
            SUM(montant) as total
        FROM danfaniment_depenses
        WHERE DATE(date_depense) BETWEEN :debut AND :fin AND statut = 'valide'
        GROUP BY categorie
        ORDER BY total DESC
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $depenses_par_categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 5. Total dépenses
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(montant), 0) as total
        FROM danfaniment_depenses
        WHERE DATE(date_depense) BETWEEN :debut AND :fin AND statut = 'valide'
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $total_depenses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 6. Paiements prestataires détaillés
    $stmt = $bdd->prepare("
        SELECT 
            d.libelle,
            d.montant,
            d.date_depense,
            d.beneficiaire,
            d.reference_transaction,
            p.nom as prestataire_nom,
            p.prenom as prestataire_prenom,
            p.type_prestataire
        FROM danfaniment_depenses d
        LEFT JOIN danfaniment_prestataires p ON d.id_prestataire = p.id_prestataire
        WHERE DATE(d.date_depense) BETWEEN :debut AND :fin 
        AND d.categorie IN ('salaire_prestataire_couturier', 'salaire_prestataire_tisseuse', 
                           'salaire_prestataire_brodeur', 'salaire_prestataire_perleuse',
                           'salaire_prestataire_mercerie', 'commission_prestataire_vendeuse')
        AND d.statut = 'valide'
        ORDER BY d.date_depense DESC
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $paiements_prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 7. Confections en cours
    $stmt = $bdd->prepare("
        SELECT 
            c.numero_commande, 
            CONCAT(cl.nom, ' ', cl.prenom) as client,
            c.type_tenue,
            c.montant_total,
            c.montant_avance,
            c.solde_restant,
            c.date_livraison_prevue,
            c.statut,
            (SELECT GROUP_CONCAT(CONCAT(p.nom, ' ', p.prenom) SEPARATOR ', ')
             FROM danfaniment_commande_prestataires cp
             LEFT JOIN danfaniment_prestataires p ON cp.id_prestataire = p.id_prestataire
             WHERE cp.id_commande = c.id_commande) as prestataires
        FROM danfaniment_commandes_confection c
        LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        WHERE c.statut IN ('en_attente', 'en_cours')
        ORDER BY c.date_livraison_prevue ASC
    ");
    $stmt->execute();
    $confections_cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 8. Confections terminées dans la semaine
    $stmt = $bdd->prepare("
        SELECT 
            c.numero_commande, 
            CONCAT(cl.nom, ' ', cl.prenom) as client,
            c.type_tenue,
            c.montant_total,
            DATE(c.date_livraison_reelle) as date_livraison
        FROM danfaniment_commandes_confection c
        LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        WHERE DATE(c.date_livraison_reelle) BETWEEN :debut AND :fin AND c.statut = 'livre'
        ORDER BY c.date_livraison_reelle DESC
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $confections_terminees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $benefice = $result['ca'] - $total_depenses;
    
    return [
        'periode' => ['debut' => $date_debut, 'fin' => $date_fin],
        'ventes_par_jour' => $ventes_par_jour,
        'total_ca' => $result['ca'],
        'total_ca_brut' => $result['ca_brut'],
        'total_remises' => $result['total_remises'],
        'nb_ventes' => $result['nb_ventes'],
        'ventes_par_mode' => $ventes_par_mode,
        'depenses_par_categorie' => $depenses_par_categorie,
        'total_depenses' => $total_depenses,
        'paiements_prestataires' => $paiements_prestataires,
        'total_paiements_prestataires' => array_sum(array_column($paiements_prestataires, 'montant')),
        'confections_cours' => $confections_cours,
        'nb_confections_cours' => count($confections_cours),
        'confections_terminees' => $confections_terminees,
        'nb_confections_terminees' => count($confections_terminees),
        'benefice' => $benefice
    ];
}

// ==================== RAPPORT MENSUEL DÉTAILLÉ ====================
function getRapportMensuel($bdd, $date_debut, $date_fin) {
    if (empty($date_debut)) {
        $date_debut = date('Y-m-01');
        $date_fin = date('Y-m-t');
    }
    
    // 1. Ventes par jour du mois
    $stmt = $bdd->prepare("
        SELECT 
            DATE(date_vente) as jour,
            COUNT(*) as nb_ventes,
            SUM(total_ttc) as total_ventes,
            SUM(remise_montant) as total_remises
        FROM danfaniment_ventes
        WHERE DATE(date_vente) BETWEEN :debut AND :fin AND statut = 'valide'
        GROUP BY DATE(date_vente)
        ORDER BY jour
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $ventes_par_jour = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. CA mensuel
    $stmt = $bdd->prepare("
        SELECT 
            COALESCE(SUM(total_ttc), 0) as ca,
            COALESCE(SUM(sous_total), 0) as ca_brut,
            COALESCE(SUM(remise_montant), 0) as total_remises,
            COUNT(*) as nb_ventes
        FROM danfaniment_ventes
        WHERE DATE(date_vente) BETWEEN :debut AND :fin AND statut = 'valide'
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. Ventes par mode de paiement
    $stmt = $bdd->prepare("
        SELECT 
            mode_paiement, 
            COUNT(*) as nb_ventes,
            SUM(total_ttc) as montant_total
        FROM danfaniment_ventes
        WHERE DATE(date_vente) BETWEEN :debut AND :fin AND statut = 'valide'
        GROUP BY mode_paiement
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $ventes_par_mode = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Dépenses mensuelles par catégorie
    $stmt = $bdd->prepare("
        SELECT 
            categorie, 
            COUNT(*) as nb_depenses,
            SUM(montant) as total
        FROM danfaniment_depenses
        WHERE DATE(date_depense) BETWEEN :debut AND :fin AND statut = 'valide'
        GROUP BY categorie
        ORDER BY total DESC
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $depenses_par_categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 5. Total dépenses
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(montant), 0) as total
        FROM danfaniment_depenses
        WHERE DATE(date_depense) BETWEEN :debut AND :fin AND statut = 'valide'
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $total_depenses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 6. Clients actifs du mois
    $stmt = $bdd->prepare("
        SELECT 
            COUNT(DISTINCT id_client) as nb_clients,
            COALESCE(SUM(total_depense), 0) as total_depenses_clients
        FROM danfaniment_clients
        WHERE id_client IN (
            SELECT DISTINCT id_client FROM danfaniment_ventes 
            WHERE DATE(date_vente) BETWEEN :debut AND :fin AND id_client IS NOT NULL
        )
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $clients = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 7. Top clients du mois
    $stmt = $bdd->prepare("
        SELECT 
            CONCAT(cl.nom, ' ', cl.prenom) as client,
            cl.telephone,
            COUNT(v.id_vente) as nb_achats,
            SUM(v.total_ttc) as total_depense
        FROM danfaniment_ventes v
        LEFT JOIN danfaniment_clients cl ON v.id_client = cl.id_client
        WHERE DATE(v.date_vente) BETWEEN :debut AND :fin AND v.id_client IS NOT NULL AND v.statut = 'valide'
        GROUP BY v.id_client
        ORDER BY total_depense DESC
        LIMIT 10
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $top_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 8. Confections
    $stmt = $bdd->prepare("
        SELECT 
            COUNT(*) as nb_confections_terminees,
            COALESCE(SUM(montant_total), 0) as total_montant_confections
        FROM danfaniment_commandes_confection
        WHERE statut = 'termine'
        AND DATE(updated_at) BETWEEN :debut AND :fin
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $confections = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 9. Paiements confection du mois
    $stmt = $bdd->prepare("
        SELECT 
            type_paiement,
            COUNT(*) as nb_paiements,
            SUM(montant) as total
        FROM danfaniment_paiements_confection
        WHERE DATE(created_at) BETWEEN :debut AND :fin
        GROUP BY type_paiement
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $paiements_confection = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $benefice = $result['ca'] - $total_depenses;
    
    return [
        'mois' => date('F Y', strtotime($date_debut)),
        'ventes_par_jour' => $ventes_par_jour,
        'total_ca' => $result['ca'],
        'total_ca_brut' => $result['ca_brut'],
        'total_remises' => $result['total_remises'],
        'nb_ventes' => $result['nb_ventes'],
        'ventes_par_mode' => $ventes_par_mode,
        'depenses_par_categorie' => $depenses_par_categorie,
        'total_depenses' => $total_depenses,
        'nb_clients_actifs' => $clients['nb_clients'] ?? 0,
        'total_depenses_clients' => $clients['total_depenses_clients'] ?? 0,
        'top_clients' => $top_clients,
        'nb_confections_terminees' => $confections['nb_confections_terminees'] ?? 0,
        'total_montant_confections' => $confections['total_montant_confections'] ?? 0,
        'paiements_confection' => $paiements_confection,
        'benefice' => $benefice
    ];
}

// ==================== RAPPORT ANNUEL DÉTAILLÉ ====================
function getRapportAnnuel($bdd, $annee = null) {
    if (empty($annee)) {
        $annee = date('Y');
    }
    
    $date_debut = $annee . '-01-01';
    $date_fin = $annee . '-12-31';
    
    // 1. CA par mois
    $stmt = $bdd->prepare("
        SELECT 
            MONTH(date_vente) as mois,
            COUNT(*) as nb_ventes,
            SUM(total_ttc) as total_ventes,
            SUM(remise_montant) as total_remises
        FROM danfaniment_ventes
        WHERE YEAR(date_vente) = :annee AND statut = 'valide'
        GROUP BY MONTH(date_vente)
        ORDER BY mois
    ");
    $stmt->execute([':annee' => $annee]);
    $ca_par_mois = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. CA total annuel
    $stmt = $bdd->prepare("
        SELECT 
            COALESCE(SUM(total_ttc), 0) as ca,
            COALESCE(SUM(sous_total), 0) as ca_brut,
            COALESCE(SUM(remise_montant), 0) as total_remises,
            COUNT(*) as nb_ventes
        FROM danfaniment_ventes
        WHERE YEAR(date_vente) = :annee AND statut = 'valide'
    ");
    $stmt->execute([':annee' => $annee]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. Dépenses par mois
    $stmt = $bdd->prepare("
        SELECT 
            MONTH(date_depense) as mois,
            SUM(montant) as total_depenses
        FROM danfaniment_depenses
        WHERE YEAR(date_depense) = :annee AND statut = 'valide'
        GROUP BY MONTH(date_depense)
        ORDER BY mois
    ");
    $stmt->execute([':annee' => $annee]);
    $depenses_par_mois = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Total dépenses annuel
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(montant), 0) as total
        FROM danfaniment_depenses
        WHERE YEAR(date_depense) = :annee AND statut = 'valide'
    ");
    $stmt->execute([':annee' => $annee]);
    $total_depenses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 5. Ventes par mode de paiement sur l'année
    $stmt = $bdd->prepare("
        SELECT 
            mode_paiement, 
            COUNT(*) as nb_ventes,
            SUM(total_ttc) as montant_total
        FROM danfaniment_ventes
        WHERE YEAR(date_vente) = :annee AND statut = 'valide'
        GROUP BY mode_paiement
        ORDER BY montant_total DESC
    ");
    $stmt->execute([':annee' => $annee]);
    $ventes_par_mode = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 6. Dépenses par catégorie sur l'année
    $stmt = $bdd->prepare("
        SELECT 
            categorie, 
            COUNT(*) as nb_depenses,
            SUM(montant) as total
        FROM danfaniment_depenses
        WHERE YEAR(date_depense) = :annee AND statut = 'valide'
        GROUP BY categorie
        ORDER BY total DESC
    ");
    $stmt->execute([':annee' => $annee]);
    $depenses_par_categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 7. Nouveaux clients dans l'année
    $stmt = $bdd->prepare("
        SELECT COUNT(*) as nb_nouveaux_clients
        FROM danfaniment_clients
        WHERE YEAR(created_at) = :annee AND supprimer = 'Non'
    ");
    $stmt->execute([':annee' => $annee]);
    $nouveaux_clients = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 8. Total clients
    $stmt = $bdd->query("SELECT COUNT(*) as total_clients FROM danfaniment_clients WHERE supprimer = 'Non'");
    $total_clients = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 9. Confections de l'année
    $stmt = $bdd->prepare("
        SELECT 
            statut,
            COUNT(*) as nb_commandes,
            COALESCE(SUM(montant_total), 0) as total_montant
        FROM danfaniment_commandes_confection
        WHERE YEAR(created_at) = :annee
        GROUP BY statut
    ");
    $stmt->execute([':annee' => $annee]);
    $confections_par_statut = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $benefice = $result['ca'] - $total_depenses;
    
    return [
        'annee' => $annee,
        'ca_par_mois' => $ca_par_mois,
        'total_ca' => $result['ca'],
        'total_ca_brut' => $result['ca_brut'],
        'total_remises' => $result['total_remises'],
        'nb_ventes' => $result['nb_ventes'],
        'depenses_par_mois' => $depenses_par_mois,
        'total_depenses' => $total_depenses,
        'ventes_par_mode' => $ventes_par_mode,
        'depenses_par_categorie' => $depenses_par_categorie,
        'nb_nouveaux_clients' => $nouveaux_clients['nb_nouveaux_clients'] ?? 0,
        'total_clients' => $total_clients['total_clients'] ?? 0,
        'confections_par_statut' => $confections_par_statut,
        'benefice' => $benefice
    ];
}

// ==================== RAPPORT CLIENTS DÉTAILLÉ ====================
function getRapportClients($bdd) {
    // 1. Statistiques générales
    $stmt = $bdd->query("
        SELECT 
            COUNT(*) as total_clients,
            SUM(CASE WHEN nombre_visites >= 5 THEN 1 ELSE 0 END) as clients_fideles,
            SUM(total_depense) as total_depenses,
            SUM(points_fidelite) as total_points,
            AVG(total_depense) as depense_moyenne
        FROM danfaniment_clients
        WHERE supprimer = 'Non'
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 2. Top 10 clients
    $stmt = $bdd->query("
        SELECT 
            nom, 
            prenom, 
            telephone, 
            email,
            ville,
            total_depense, 
            nombre_visites, 
            points_fidelite,
            date_derniere_visite,
            date_premiere_visite
        FROM danfaniment_clients
        WHERE supprimer = 'Non'
        ORDER BY total_depense DESC
        LIMIT 10
    ");
    $top_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Clients avec le plus de visites
    $stmt = $bdd->query("
        SELECT 
            nom, 
            prenom, 
            telephone,
            total_depense, 
            nombre_visites
        FROM danfaniment_clients
        WHERE supprimer = 'Non'
        ORDER BY nombre_visites DESC
        LIMIT 10
    ");
    $clients_visites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Clients inactifs (pas de visite depuis 3 mois)
    $trois_mois = date('Y-m-d', strtotime('-3 months'));
    $stmt = $bdd->prepare("
        SELECT 
            nom, 
            prenom, 
            telephone,
            total_depense,
            nombre_visites,
            date_derniere_visite
        FROM danfaniment_clients
        WHERE supprimer = 'Non' 
        AND (date_derniere_visite < :date OR date_derniere_visite IS NULL)
        ORDER BY date_derniere_visite ASC
        LIMIT 20
    ");
    $stmt->execute([':date' => $trois_mois]);
    $clients_inactifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 5. Nouveaux clients ce mois
    $stmt = $bdd->prepare("
        SELECT COUNT(*) as nb_nouveaux
        FROM danfaniment_clients
        WHERE MONTH(created_at) = MONTH(CURDATE()) 
        AND YEAR(created_at) = YEAR(CURDATE())
        AND supprimer = 'Non'
    ");
    $stmt->execute();
    $nouveaux_mois = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 6. Nouveaux clients cette semaine
    $stmt = $bdd->prepare("
        SELECT COUNT(*) as nb_nouveaux
        FROM danfaniment_clients
        WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())
        AND supprimer = 'Non'
    ");
    $stmt->execute();
    $nouveaux_semaine = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'total_clients' => $stats['total_clients'] ?? 0,
        'clients_fideles' => $stats['clients_fideles'] ?? 0,
        'total_depenses_clients' => $stats['total_depenses'] ?? 0,
        'total_points_fidelite' => $stats['total_points'] ?? 0,
        'depense_moyenne' => round($stats['depense_moyenne'] ?? 0, 2),
        'top_clients' => $top_clients,
        'clients_visites' => $clients_visites,
        'clients_inactifs' => $clients_inactifs,
        'nb_inactifs' => count($clients_inactifs),
        'nouveaux_mois' => $nouveaux_mois['nb_nouveaux'] ?? 0,
        'nouveaux_semaine' => $nouveaux_semaine['nb_nouveaux'] ?? 0
    ];
}

// ==================== RAPPORT PRESTATAIRES DÉTAILLÉ ====================
function getRapportPrestataires($bdd) {
    // 1. Statistiques générales
    $stmt = $bdd->query("
        SELECT 
            COUNT(*) as total_prestataires,
            SUM(CASE WHEN type_prestataire = 'couturier' THEN 1 ELSE 0 END) as nb_couturiers,
            SUM(CASE WHEN type_prestataire = 'tisseuse' THEN 1 ELSE 0 END) as nb_tisseuses,
            SUM(CASE WHEN type_prestataire = 'brodeur' THEN 1 ELSE 0 END) as nb_brodeurs,
            SUM(CASE WHEN type_prestataire = 'perleuse' THEN 1 ELSE 0 END) as nb_perleuses,
            SUM(CASE WHEN type_prestataire = 'mercerie' THEN 1 ELSE 0 END) as nb_merceries,
            SUM(CASE WHEN type_prestataire = 'vendeuse' THEN 1 ELSE 0 END) as nb_vendeuses,
            SUM(total_a_payer) as total_a_payer,
            SUM(total_paye) as total_paye,
            SUM(total_a_payer - total_paye) as total_restant
        FROM danfaniment_prestataires
        WHERE actif = 1
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 2. Liste détaillée des prestataires
    $stmt = $bdd->query("
        SELECT 
            id_prestataire,
            nom, 
            prenom, 
            telephone, 
            email,
            type_prestataire,
            specialites,
            tarif_par_tenue,
            tarif_par_pagne,
            taux_horaire,
            commission_pourcentage,
            total_productions,
            total_a_payer,
            total_paye,
            (total_a_payer - total_paye) as reste_a_payer,
            actif,
            created_at
        FROM danfaniment_prestataires
        WHERE actif = 1
        ORDER BY type_prestataire, nom
    ");
    $prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Paiements du mois par prestataire
    $stmt = $bdd->prepare("
        SELECT 
            d.id_prestataire,
            CONCAT(p.nom, ' ', p.prenom) as prestataire,
            SUM(d.montant) as total_paye_mois
        FROM danfaniment_depenses d
        LEFT JOIN danfaniment_prestataires p ON d.id_prestataire = p.id_prestataire
        WHERE MONTH(d.date_depense) = MONTH(CURDATE()) 
        AND YEAR(d.date_depense) = YEAR(CURDATE())
        AND d.categorie IN ('salaire_prestataire_couturier', 'salaire_prestataire_tisseuse',
                           'salaire_prestataire_brodeur', 'salaire_prestataire_perleuse',
                           'salaire_prestataire_mercerie', 'commission_prestataire_vendeuse')
        AND d.statut = 'valide'
        GROUP BY d.id_prestataire
    ");
    $stmt->execute();
    $paiements_mois = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Productions en attente de paiement
    $stmt = $bdd->query("
        SELECT 
            cp.id_prestataire,
            CONCAT(p.nom, ' ', p.prenom) as prestataire,
            COUNT(*) as nb_productions,
            SUM(cp.montant_total) as total_a_payer
        FROM danfaniment_commande_prestataires cp
        LEFT JOIN danfaniment_prestataires p ON cp.id_prestataire = p.id_prestataire
        WHERE cp.statut_paiement = 'en_attente'
        GROUP BY cp.id_prestataire
    ");
    $productions_attente = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'total_prestataires' => $stats['total_prestataires'] ?? 0,
        'nb_couturiers' => $stats['nb_couturiers'] ?? 0,
        'nb_tisseuses' => $stats['nb_tisseuses'] ?? 0,
        'nb_brodeurs' => $stats['nb_brodeurs'] ?? 0,
        'nb_perleuses' => $stats['nb_perleuses'] ?? 0,
        'nb_merceries' => $stats['nb_merceries'] ?? 0,
        'nb_vendeuses' => $stats['nb_vendeuses'] ?? 0,
        'total_a_payer' => $stats['total_a_payer'] ?? 0,
        'total_paye' => $stats['total_paye'] ?? 0,
        'total_restant' => $stats['total_restant'] ?? 0,
        'prestataires' => $prestataires,
        'paiements_mois' => $paiements_mois,
        'productions_attente' => $productions_attente,
        'nb_productions_attente' => count($productions_attente),
        'total_productions_attente' => array_sum(array_column($productions_attente, 'total_a_payer'))
    ];
}

// ==================== RAPPORT PERSONNALISÉ DÉTAILLÉ ====================
function getRapportPersonnalise($bdd, $date_debut, $date_fin) {
    if (empty($date_debut) || empty($date_fin)) {
        return ['error' => 'Veuillez sélectionner une période'];
    }
    
    // 1. Ventes sur période détaillées
    $stmt = $bdd->prepare("
        SELECT 
            v.numero_vente,
            DATE(v.date_vente) as date_vente,
            v.total_ttc,
            v.sous_total,
            v.remise_montant,
            v.mode_paiement,
            u.nom_complet as caissier,
            CONCAT(cl.nom, ' ', cl.prenom) as client
        FROM danfaniment_ventes v
        LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
        LEFT JOIN danfaniment_clients cl ON v.id_client = cl.id_client
        WHERE DATE(v.date_vente) BETWEEN :debut AND :fin AND v.statut = 'valide'
        ORDER BY v.date_vente DESC
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Total ventes
    $stmt = $bdd->prepare("
        SELECT 
            COALESCE(SUM(total_ttc), 0) as total,
            COALESCE(SUM(sous_total), 0) as total_brut,
            COALESCE(SUM(remise_montant), 0) as total_remises,
            COUNT(*) as nb_ventes
        FROM danfaniment_ventes
        WHERE DATE(date_vente) BETWEEN :debut AND :fin AND statut = 'valide'
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $total_ventes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. Dépenses sur période détaillées
    $stmt = $bdd->prepare("
        SELECT 
            libelle,
            categorie,
            montant,
            beneficiaire,
            DATE(date_depense) as date_depense,
            mode_paiement
        FROM danfaniment_depenses
        WHERE DATE(date_depense) BETWEEN :debut AND :fin AND statut = 'valide'
        ORDER BY date_depense DESC
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $depenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Total dépenses
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(montant), 0) as total
        FROM danfaniment_depenses
        WHERE DATE(date_depense) BETWEEN :debut AND :fin AND statut = 'valide'
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $total_depenses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 5. Confections terminées sur la période
    $stmt = $bdd->prepare("
        SELECT 
            c.numero_commande,
            CONCAT(cl.nom, ' ', cl.prenom) as client,
            c.type_tenue,
            c.montant_total,
            DATE(c.date_livraison_reelle) as date_livraison
        FROM danfaniment_commandes_confection c
        LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        WHERE DATE(c.date_livraison_reelle) BETWEEN :debut AND :fin AND c.statut = 'livre'
        ORDER BY c.date_livraison_reelle DESC
    ");
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    $confections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $benefice = $total_ventes['total'] - $total_depenses;
    
    return [
        'periode' => ['debut' => $date_debut, 'fin' => $date_fin],
        'ventes' => $ventes,
        'total_ventes' => $total_ventes['total'],
        'total_ventes_brut' => $total_ventes['total_brut'],
        'total_remises' => $total_ventes['total_remises'],
        'nb_ventes' => $total_ventes['nb_ventes'],
        'depenses' => $depenses,
        'total_depenses' => $total_depenses,
        'confections' => $confections,
        'nb_confections' => count($confections),
        'benefice' => $benefice
    ];
}
?>