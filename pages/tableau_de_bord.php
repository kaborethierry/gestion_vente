<?php
// pages/tableau_de_bord.php
// DANFANIMENT POS - Tableau de bord principal

session_start();

// Vérification de l'authentification (admin ou caissier)
if (empty($_SESSION['id']) || !isset($_SESSION['role'])) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/../api/modules/connect_db_pdo.php';

// Initialisation des variables
$ca_jour = 0;
$ca_mois = 0;
$nb_ventes_jour = 0;
$nb_ventes_mois = 0;
$confections_en_cours = 0;
$confections_terminees_mois = 0;
$clients_fideles = 0;
$total_clients = 0;
$total_prestataires = 0;
$total_prestataires_a_payer = 0;

$ventes_par_mode = [];
$ventes_journalieres_labels = [];
$ventes_journalieres_values = [];
$top_produits_labels = [];
$top_produits_values = [];
$confections_par_statut_labels = [];
$confections_par_statut_values = [];

$last_ventes_rows = [];
$last_confections_rows = [];

try {
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ========== KPI PRINCIPAUX ==========
    
    // CA du jour
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(total_ttc), 0) as total, COUNT(*) as nb
        FROM danfaniment_ventes 
        WHERE DATE(date_vente) = CURDATE() AND statut = 'valide'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $ca_jour = $result['total'];
    $nb_ventes_jour = $result['nb'];
    
    // CA du mois
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(total_ttc), 0) as total, COUNT(*) as nb
        FROM danfaniment_ventes 
        WHERE YEAR(date_vente) = YEAR(CURDATE()) 
        AND MONTH(date_vente) = MONTH(CURDATE()) 
        AND statut = 'valide'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $ca_mois = $result['total'];
    $nb_ventes_mois = $result['nb'];
    
    // Commandes de confection en cours
    $stmt = $bdd->prepare("
        SELECT COUNT(*) as nb FROM danfaniment_commandes_confection 
        WHERE statut IN ('en_attente', 'en_cours')
    ");
    $stmt->execute();
    $confections_en_cours = $stmt->fetch(PDO::FETCH_ASSOC)['nb'];
    
    // Confections terminées ce mois
    $stmt = $bdd->prepare("
        SELECT COUNT(*) as nb FROM danfaniment_commandes_confection 
        WHERE statut = 'termine' 
        AND YEAR(updated_at) = YEAR(CURDATE()) 
        AND MONTH(updated_at) = MONTH(CURDATE())
    ");
    $stmt->execute();
    $confections_terminees_mois = $stmt->fetch(PDO::FETCH_ASSOC)['nb'];
    
    // Clients fidèles (plus de 5 visites ou plus de 100000 FCFA de dépenses)
    $stmt = $bdd->prepare("
        SELECT COUNT(*) as nb FROM danfaniment_clients 
        WHERE (supprimer = 'Non' OR supprimer IS NULL) AND (nombre_visites >= 5 OR total_depense >= 100000)
    ");
    $stmt->execute();
    $clients_fideles = $stmt->fetch(PDO::FETCH_ASSOC)['nb'];
    
    // Total clients
    $stmt = $bdd->prepare("SELECT COUNT(*) as nb FROM danfaniment_clients WHERE supprimer = 'Non' OR supprimer IS NULL");
    $stmt->execute();
    $total_clients = $stmt->fetch(PDO::FETCH_ASSOC)['nb'];
    
    // Total prestataires
    $stmt = $bdd->prepare("SELECT COUNT(*) as nb FROM danfaniment_prestataires WHERE actif = 1");
    $stmt->execute();
    $total_prestataires = $stmt->fetch(PDO::FETCH_ASSOC)['nb'];
    
    // Total à payer aux prestataires
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(total_a_payer - total_paye), 0) as total 
        FROM danfaniment_prestataires WHERE actif = 1
    ");
    $stmt->execute();
    $total_prestataires_a_payer = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // ========== GRAPHIQUES ==========
    
    // Ventes par mode de paiement
    $stmt = $bdd->prepare("
        SELECT mode_paiement, COALESCE(SUM(total_ttc), 0) as total
        FROM danfaniment_ventes 
        WHERE statut = 'valide' AND DATE(date_vente) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY mode_paiement
    ");
    $stmt->execute();
    $ventes_par_mode = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ventes des 7 derniers jours (pour graphique linéaire)
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $bdd->prepare("
            SELECT COALESCE(SUM(total_ttc), 0) as total 
            FROM danfaniment_ventes 
            WHERE DATE(date_vente) = :date AND statut = 'valide'
        ");
        $stmt->execute([':date' => $date]);
        $ventes_journalieres_labels[] = date('d/m', strtotime($date));
        $ventes_journalieres_values[] = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    // Top 5 produits les plus vendus (à partir des lignes de vente)
    $stmt = $bdd->prepare("
        SELECT l.nom_produit, COALESCE(SUM(l.total_ligne), 0) as total_ventes
        FROM danfaniment_lignes_ventes l
        INNER JOIN danfaniment_ventes v ON l.id_vente = v.id_vente
        WHERE v.statut = 'valide'
        GROUP BY l.nom_produit
        ORDER BY total_ventes DESC
        LIMIT 5
    ");
    $stmt->execute();
    $top_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($top_produits) > 0) {
        foreach ($top_produits as $p) {
            $top_produits_labels[] = $p['nom_produit'];
            $top_produits_values[] = (float) $p['total_ventes'];
        }
    } else {
        $top_produits_labels = ['Aucune vente'];
        $top_produits_values = [0];
    }
    
    // Confections par statut
    $stmt = $bdd->prepare("
        SELECT statut, COUNT(*) as nb
        FROM danfaniment_commandes_confection
        GROUP BY statut
    ");
    $stmt->execute();
    $confections_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($confections_stats as $stat) {
        $confections_par_statut_labels[] = $stat['statut'];
        $confections_par_statut_values[] = (int) $stat['nb'];
    }
    
    // ========== TABLEAUX ==========
    
    // Dernières ventes
    $stmt = $bdd->prepare("
        SELECT v.numero_vente, v.total_ttc, v.date_vente, u.nom_complet as caissier, v.mode_paiement
        FROM danfaniment_ventes v
        LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
        WHERE v.statut = 'valide'
        ORDER BY v.date_vente DESC
        LIMIT 10
    ");
    $stmt->execute();
    $last_ventes_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Dernières commandes de confection
    $stmt = $bdd->prepare("
        SELECT c.numero_commande, 
               CONCAT(COALESCE(cl.nom, ''), ' ', COALESCE(cl.prenom, '')) as client,
               c.type_tenue, 
               c.montant_total,
               c.statut,
               c.date_livraison_prevue
        FROM danfaniment_commandes_confection c
        LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $last_confections_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log('[Tableau de bord] Erreur: ' . $e->getMessage());
}

// Fonctions d'affichage
function format_money($amount) {
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

function format_number($n) {
    return number_format((float)$n, 0, ',', ' ');
}

function get_statut_badge($statut) {
    $badges = [
        'valide' => 'success',
        'annule' => 'danger',
        'en_attente' => 'warning',
        'en_cours' => 'info',
        'termine' => 'success',
        'livre' => 'primary'
    ];
    $color = $badges[$statut] ?? 'secondary';
    return "<span class='badge badge-$color'>$statut</span>";
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DANFANIMENT POS – Tableau de bord</title>

    <?php include('inclusion_haut.php'); ?>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        .kpi-card {
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .kpi-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .kpi-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        .kpi-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0;
            opacity: 0.9;
        }
        .bg-gradient-red { background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%); }
        .bg-gradient-green { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
        .bg-gradient-orange { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); }
        .bg-gradient-blue { background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%); }
        .bg-gradient-purple { background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%); }
        .bg-gradient-pink { background: linear-gradient(135deg, #EC4899 0%, #BE185D 100%); }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .statut-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .statut-valide { background: #10B981; color: white; }
        .statut-annule { background: #EF4444; color: white; }
        .statut-en_attente { background: #F59E0B; color: white; }
        .statut-en_cours { background: #3B82F6; color: white; }
        .statut-termine { background: #10B981; color: white; }
        .statut-livre { background: #8B5CF6; color: white; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php 
        if ($_SESSION['role'] === "admin") {
            include('menu_admin.php');
        } else {
            include('menu_caissier.php');
        }
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('entete.php'); ?>

                <div class="container-fluid">
                    <!-- En-tête -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </h1>
                    </div>

                    <!-- Cartes KPI -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="kpi-card bg-gradient-red text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="kpi-value"><?= format_money($ca_jour) ?></div>
                                        <div class="kpi-label">Chiffre d'affaires jour</div>
                                        <small><?= $nb_ventes_jour ?> vente(s)</small>
                                    </div>
                                    <div class="kpi-icon">
                                        <i class="fas fa-cash-register"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="kpi-card bg-gradient-green text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="kpi-value"><?= format_money($ca_mois) ?></div>
                                        <div class="kpi-label">Chiffre d'affaires mois</div>
                                        <small><?= $nb_ventes_mois ?> vente(s)</small>
                                    </div>
                                    <div class="kpi-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="kpi-card bg-gradient-orange text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="kpi-value"><?= format_number($confections_en_cours) ?></div>
                                        <div class="kpi-label">Confections en cours</div>
                                        <small><?= format_number($confections_terminees_mois) ?> terminées ce mois</small>
                                    </div>
                                    <div class="kpi-icon">
                                        <i class="fas fa-cut"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deuxième ligne KPI -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="kpi-card bg-gradient-blue text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="kpi-value"><?= format_number($total_clients) ?></div>
                                        <div class="kpi-label">Clients enregistrés</div>
                                        <small><?= format_number($clients_fideles) ?> clients fidèles</small>
                                    </div>
                                    <div class="kpi-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="kpi-card bg-gradient-purple text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="kpi-value"><?= format_number($total_prestataires) ?></div>
                                        <div class="kpi-label">Prestataires actifs</div>
                                        <small>À payer: <?= format_money($total_prestataires_a_payer) ?></small>
                                    </div>
                                    <div class="kpi-icon">
                                        <i class="fas fa-user-friends"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphiques -->
                    <div class="row">
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-line"></i> Évolution des ventes (7 derniers jours)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="ventesJournalieresChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-pie"></i> Ventes par mode de paiement (30 jours)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="ventesModeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-bar"></i> Top produits vendus
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="topProduitsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-donut"></i> Confections par statut
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="confectionsStatutChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableaux récents -->
                    <div class="row">
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-shopping-cart"></i> Dernières ventes
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>N° Vente</th>
                                                    <th>Montant</th>
                                                    <th>Mode</th>
                                                    <th>Date</th>
                                                    <th>Caissier</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($last_ventes_rows) > 0): ?>
                                                    <?php foreach ($last_ventes_rows as $v): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($v['numero_vente']) ?></td>
                                                        <td class="text-right"><?= format_money($v['total_ttc']) ?></td>
                                                        <td>
                                                            <span class="badge badge-info"><?= $v['mode_paiement'] ?></span>
                                                        </td>
                                                        <td><?= date('d/m/H:i', strtotime($v['date_vente'])) ?></td>
                                                        <td><?= htmlspecialchars($v['caissier'] ?? '-') ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            Aucune vente récente
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-tshirt"></i> Dernières commandes de confection
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>N° Commande</th>
                                                    <th>Client</th>
                                                    <th>Type tenue</th>
                                                    <th>Montant</th>
                                                    <th>Statut</th>
                                                    <th>Livraison prévue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($last_confections_rows) > 0): ?>
                                                    <?php foreach ($last_confections_rows as $c): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($c['numero_commande']) ?></td>
                                                        <td><?= htmlspecialchars($c['client'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($c['type_tenue']) ?></td>
                                                        <td class="text-right"><?= format_money($c['montant_total']) ?></td>
                                                        <td>
                                                            <span class="statut-badge statut-<?= $c['statut'] ?>">
                                                                <?= $c['statut'] ?>
                                                            </span>
                                                        </td>
                                                        <td><?= date('d/m/Y', strtotime($c['date_livraison_prevue'])) ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">
                                                            Aucune commande de confection
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php include('footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include('inclusion_bas.php'); ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique: Ventes journalières
        const ventesCtx = document.getElementById('ventesJournalieresChart');
        if (ventesCtx) {
            new Chart(ventesCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($ventes_journalieres_labels) ?>,
                    datasets: [{
                        label: 'Chiffre d\'affaires (FCFA)',
                        data: <?= json_encode($ventes_journalieres_values) ?>,
                        borderColor: '#DC2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { callbacks: { label: function(ctx) { return ctx.raw.toLocaleString() + ' FCFA'; } } }
                    },
                    scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return v.toLocaleString(); } } } }
                }
            });
        }

        // Graphique: Ventes par mode de paiement
        const modeCtx = document.getElementById('ventesModeChart');
        if (modeCtx && <?= json_encode(count($ventes_par_mode)) ?> > 0) {
            const modeLabels = <?= json_encode(array_column($ventes_par_mode, 'mode_paiement')) ?>;
            const modeValues = <?= json_encode(array_column($ventes_par_mode, 'total')) ?>;
            new Chart(modeCtx, {
                type: 'doughnut',
                data: {
                    labels: modeLabels,
                    datasets: [{
                        data: modeValues,
                        backgroundColor: ['#DC2626', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // Graphique: Top produits
        const topCtx = document.getElementById('topProduitsChart');
        if (topCtx) {
            new Chart(topCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($top_produits_labels) ?>,
                    datasets: [{
                        label: 'Montant vendu (FCFA)',
                        data: <?= json_encode($top_produits_values) ?>,
                        backgroundColor: '#DC2626',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return v.toLocaleString(); } } } }
                }
            });
        }

        // Graphique: Confections par statut
        const confCtx = document.getElementById('confectionsStatutChart');
        if (confCtx && <?= json_encode(count($confections_par_statut_labels)) ?> > 0) {
            new Chart(confCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($confections_par_statut_labels) ?>,
                    datasets: [{
                        data: <?= json_encode($confections_par_statut_values) ?>,
                        backgroundColor: ['#DC2626', '#F59E0B', '#3B82F6', '#10B981', '#8B5CF6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });
    </script>

    <?php include('modals/modal_deconnexion.php'); ?>
</body>
</html>