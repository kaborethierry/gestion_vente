<?php
session_start();

// Seul un Caissier ou Admin peut accéder à cette page
if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/../api/modules/connect_db_pdo.php';

// Récupérer les statistiques
$stats = [];
try {
    // Total des paiements du jour
    $stmt = $bdd->prepare("SELECT COALESCE(SUM(montant), 0) FROM danfaniment_paiements_confection WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $stats['total_jour'] = $stmt->fetchColumn();
    
    // Total des paiements du mois
    $stmt = $bdd->prepare("SELECT COALESCE(SUM(montant), 0) FROM danfaniment_paiements_confection WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $stmt->execute();
    $stats['total_mois'] = $stmt->fetchColumn();
    
    // Nombre de commandes avec solde restant
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_commandes_confection WHERE solde_restant > 0");
    $stmt->execute();
    $stats['commandes_impayees'] = $stmt->fetchColumn();
    
    // Montant total des impayés
    $stmt = $bdd->prepare("SELECT COALESCE(SUM(solde_restant), 0) FROM danfaniment_commandes_confection WHERE solde_restant > 0");
    $stmt->execute();
    $stats['total_impaye'] = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $stats = ['total_jour' => 0, 'total_mois' => 0, 'commandes_impayees' => 0, 'total_impaye' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DANFANIMENT – Gestion des paiements confection</title>

    <?php include('inclusion_haut.php'); ?>
    <style>
        .stats-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stats-card .card-body {
            padding: 1rem;
        }
        .badge-solde {
            background-color: #10B981;
            color: white;
        }
        .badge-partiel {
            background-color: #F59E0B;
            color: white;
        }
        .badge-impaye {
            background-color: #DC2626;
            color: white;
        }
        .text-solde { color: #10B981; }
        .text-partiel { color: #F59E0B; }
        .text-impaye { color: #DC2626; }
        .btn-print-iframe {
            position: fixed;
            visibility: hidden;
            width: 0;
            height: 0;
            border: none;
        }
    </style>
</head>

<body id="page-top">
    <?php include('alert_paiement.php'); ?>

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

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-credit-card"></i> Gestion des paiements confection
                        </h1>
                        <button class="btn btn-success" data-toggle="modal" data-target="#ajouter_paiement" onclick="chargerCommandes()">
                            <i class="fas fa-plus"></i> Nouveau paiement
                        </button>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stats-card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Paiements aujourd'hui</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo number_format($stats['total_jour'], 0, ',', ' '); ?> CFA
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stats-card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Paiements ce mois</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo number_format($stats['total_mois'], 0, ',', ' '); ?> CFA
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-month fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stats-card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Commandes impayées</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['commandes_impayees']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stats-card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Total impayé</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo number_format($stats['total_impaye'], 0, ',', ' '); ?> CFA
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-filter"></i> Filtres
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Statut de paiement</label>
                                    <select id="filtre_statut" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="solde">Soldé</option>
                                        <option value="partiel">Partiellement payé</option>
                                        <option value="aucun">Aucun paiement</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Mode de paiement</label>
                                    <select id="filtre_mode" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="especes">Espèces</option>
                                        <option value="carte">Carte bancaire</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="virement">Virement</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Date début</label>
                                    <input type="date" id="filtre_date_debut" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label>Date fin</label>
                                    <input type="date" id="filtre_date_fin" class="form-control">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12 text-right">
                                    <button class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fas fa-undo"></i> Réinitialiser
                                    </button>
                                    <button class="btn btn-primary" onclick="applyFilters()">
                                        <i class="fas fa-search"></i> Appliquer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table des paiements -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list"></i> Historique des paiements
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTablePaiements" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>N° Paiement</th>
                                            <th>N° Commande</th>
                                            <th>Client</th>
                                            <th>Montant</th>
                                            <th>Type</th>
                                            <th>Mode</th>
                                            <th>Référence</th>
                                            <th>Caissier</th>
                                            <th>Date</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
                                            <th>Imprimer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Table des commandes avec statut paiement -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-pie"></i> Situation des commandes
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTableCommandes" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>N° Commande</th>
                                            <th>Client</th>
                                            <th>Montant total</th>
                                            <th>Avance versée</th>
                                            <th>Solde restant</th>
                                            <th>Statut paiement</th>
                                            <th>Statut commande</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
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
    <script src="DataTables/data_table_paiement.js?version=1.3"></script>
    <?php include('modals/modal_paiement.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

    <script>
        function resetFilters() {
            $('#filtre_statut').val('');
            $('#filtre_mode').val('');
            $('#filtre_date_debut').val('');
            $('#filtre_date_fin').val('');
            applyFilters();
        }
        
        function applyFilters() {
            if ($.fn.DataTable.isDataTable('#dataTableCommandes')) {
                $('#dataTableCommandes').DataTable().ajax.reload();
            }
            if ($.fn.DataTable.isDataTable('#dataTablePaiements')) {
                $('#dataTablePaiements').DataTable().ajax.reload();
            }
        }
        
        // Fonction pour charger les commandes dans le modal
        function chargerCommandes() {
            $.ajax({
                url: '../api/modules/paiement_data.php',
                type: 'GET',
                data: { action: 'get_commandes_select' },
                dataType: 'json',
                success: function(response) {
                    var select = $('#id_commande');
                    select.empty();
                    
                    if (response.success && response.commandes && response.commandes.length > 0) {
                        select.append('<option value="">Sélectionnez une commande</option>');
                        $.each(response.commandes, function(i, cmd) {
                            var solde = parseFloat(cmd.solde_restant);
                            if (solde > 0) {
                                select.append('<option value="' + cmd.id_commande + '" data-solde="' + cmd.solde_restant + '" data-total="' + cmd.montant_total + '">' +
                                    cmd.numero_commande + ' - ' + cmd.client_nom + ' (Solde: ' + new Intl.NumberFormat('fr-FR').format(solde) + ' CFA)' +
                                    '</option>');
                            }
                        });
                    } else {
                        select.append('<option value="">Aucune commande avec solde restant</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur chargement commandes:', error);
                    var select = $('#id_commande');
                    select.empty();
                    select.append('<option value="">Erreur de chargement des commandes</option>');
                }
            });
        }
    </script>

    <!-- Script d'impression simple (sans QZ Tray) -->
    <script>
    // Fonction d'impression du reçu - version simple
    function printRecuDirect(id_paiement) {
        var printWindow = window.open('../api/modules/imprimer_recu.php?id_paiement=' + id_paiement, '_blank', 'width=500,height=600');
        if (!printWindow) {
            Swal.fire({
                title: 'Popup bloqué',
                text: 'Veuillez autoriser les popups pour ce site.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }
    </script>
</body>
</html>