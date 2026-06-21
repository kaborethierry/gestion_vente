<?php
session_start();

// Seul un Admin peut accéder à cette page
if (empty($_SESSION['id']) || $_SESSION['role'] !== "admin") {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DANFANIMENT POS – Rapports</title>

    <?php include('inclusion_haut.php'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
</head>

<body id="page-top">
    <?php include('alert_rapports.php'); ?>

    <div id="wrapper">
        <?php include('menu_admin.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('entete.php'); ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">
                        <i class="fas fa-chart-line"></i> Rapports et documents PDF
                    </h1>

                    <div class="row">
                        <!-- Rapport Journalier -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Rapport Journalier</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Ventes, confections livrées, dépenses</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary btn-sm" onclick="genererRapport('journalier')">
                                            <i class="fas fa-file-pdf"></i> Générer PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport Hebdomadaire -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Rapport Hebdomadaire</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Résumé semaine, paiements prestataires</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-calendar-week fa-2x text-gray-300"></i></div>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-success btn-sm" onclick="genererRapport('hebdomadaire')">
                                            <i class="fas fa-file-pdf"></i> Générer PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport Mensuel -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Rapport Mensuel</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Bilan complet, CA, bénéfice</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300"></i></div>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-info btn-sm" onclick="genererRapport('mensuel')">
                                            <i class="fas fa-file-pdf"></i> Générer PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport Annuel -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-purple shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">Rapport Annuel</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Bilan annuel complet</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-purple btn-sm" onclick="genererRapport('annuel')">
                                            <i class="fas fa-file-pdf"></i> Générer PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport Clients -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rapport Clients</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Clients fidèles, historique d'achats</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-danger btn-sm" onclick="genererRapport('clients')">
                                            <i class="fas fa-file-pdf"></i> Générer PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport Prestataires -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Rapport Prestataires</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Paiements, productions</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-user-friends fa-2x text-gray-300"></i></div>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-secondary btn-sm" onclick="genererRapport('prestataires')">
                                            <i class="fas fa-file-pdf"></i> Générer PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sélecteur de période -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-calendar"></i> Sélection de période personnalisée
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Date début</label>
                                    <input type="date" id="date_debut" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>Date fin</label>
                                    <input type="date" id="date_fin" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary form-control" onclick="genererRapport('personnalise')">
                                        <i class="fas fa-file-pdf"></i> Générer rapport personnalisé
                                    </button>
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
    <script src="DataTables/data_table_rapports.js?version=3.1"></script>
    <?php include('modals/modal_deconnexion.php'); ?>

    <style>
        .border-left-purple { border-left: 4px solid #6f42c1; }
        .text-purple { color: #6f42c1; }
        .btn-purple { background-color: #6f42c1; border-color: #6f42c1; color: white; }
        .btn-purple:hover { background-color: #5a32a3; border-color: #5a32a3; }
    </style>

</body>
</html>