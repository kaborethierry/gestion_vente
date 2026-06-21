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
    <title>DANFANIMENT POS – Gestion des prestataires</title>

    <?php include('inclusion_haut.php'); ?>
</head>

<body id="page-top">
    <?php include('alert_prestataire.php'); ?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include('menu_admin.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php include('entete.php'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Gestion des prestataires</h1>

                    <div class="text-left mb-3">
                        <button 
                            class="btn btn-primary" 
                            data-toggle="modal" 
                            data-target="#ajouter_prestataire"
                        >
                            Ajouter un prestataire <i class="fa fa-plus"></i>
                        </button>
                    </div>

                    <!-- Section Couturiers -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-warning text-white">
                            <h6 class="m-0 font-weight-bold">👔 Couturiers</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-right mb-3">
                                <button class="btn btn-success btn-sm generer-paiement" data-type="couturier">
                                    <i class="fa fa-money-bill-wave"></i> Générer paiement du samedi
                                </button>
                                <button class="btn btn-info btn-sm enregistrer-production" data-toggle="modal" data-target="#production_prestataire" data-type="couturier">
                                    <i class="fa fa-tshirt"></i> Enregistrer production
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered prestataire-table" id="tableCouturiers" width="100%" cellspacing="0" data-type="couturier">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom complet</th>
                                            <th>Téléphone</th>
                                            <th>Spécialités</th>
                                            <th>Productions semaine</th>
                                            <th>Montant dû</th>
                                            <th>Total payé</th>
                                            <th>Statut</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
                                            <th>Payer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Tisseuses -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-info text-white">
                            <h6 class="m-0 font-weight-bold">🪢 Tisseuses</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-right mb-3">
                                <button class="btn btn-success btn-sm generer-paiement" data-type="tisseuse">
                                    <i class="fa fa-money-bill-wave"></i> Générer paiement
                                </button>
                                <button class="btn btn-info btn-sm enregistrer-production" data-toggle="modal" data-target="#production_prestataire" data-type="tisseuse">
                                    <i class="fa fa-fill-drip"></i> Enregistrer production
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered prestataire-table" id="tableTisseuses" width="100%" cellspacing="0" data-type="tisseuse">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom complet</th>
                                            <th>Téléphone</th>
                                            <th>Spécialités</th>
                                            <th>Productions semaine</th>
                                            <th>Montant dû</th>
                                            <th>Total payé</th>
                                            <th>Statut</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
                                            <th>Payer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Brodeurs -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">🪡 Brodeurs</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-right mb-3">
                                <button class="btn btn-success btn-sm generer-paiement" data-type="brodeur">
                                    <i class="fa fa-money-bill-wave"></i> Générer paiement
                                </button>
                                <button class="btn btn-info btn-sm enregistrer-production" data-toggle="modal" data-target="#production_prestataire" data-type="brodeur">
                                    <i class="fa fa-thread"></i> Enregistrer production
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered prestataire-table" id="tableBrodeurs" width="100%" cellspacing="0" data-type="brodeur">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom complet</th>
                                            <th>Téléphone</th>
                                            <th>Spécialités</th>
                                            <th>Heures semaine</th>
                                            <th>Montant dû</th>
                                            <th>Total payé</th>
                                            <th>Statut</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
                                            <th>Payer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Perleuses -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">💎 Perleuses</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-right mb-3">
                                <button class="btn btn-success btn-sm generer-paiement" data-type="perleuse">
                                    <i class="fa fa-money-bill-wave"></i> Générer paiement
                                </button>
                                <button class="btn btn-info btn-sm enregistrer-production" data-toggle="modal" data-target="#production_prestataire" data-type="perleuse">
                                    <i class="fa fa-gem"></i> Enregistrer production
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered prestataire-table" id="tablePerleuses" width="100%" cellspacing="0" data-type="perleuse">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom complet</th>
                                            <th>Téléphone</th>
                                            <th>Spécialités</th>
                                            <th>Heures semaine</th>
                                            <th>Montant dû</th>
                                            <th>Total payé</th>
                                            <th>Statut</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
                                            <th>Payer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Merceries -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-secondary text-white">
                            <h6 class="m-0 font-weight-bold">📿 Merceries</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-right mb-3">
                                <button class="btn btn-success btn-sm generer-paiement" data-type="mercerie">
                                    <i class="fa fa-money-bill-wave"></i> Générer paiement
                                </button>
                                <button class="btn btn-info btn-sm enregistrer-production" data-toggle="modal" data-target="#production_prestataire" data-type="mercerie">
                                    <i class="fa fa-cut"></i> Enregistrer production
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered prestataire-table" id="tableMerceries" width="100%" cellspacing="0" data-type="mercerie">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom complet</th>
                                            <th>Téléphone</th>
                                            <th>Spécialités</th>
                                            <th>Heures semaine</th>
                                            <th>Montant dû</th>
                                            <th>Total payé</th>
                                            <th>Statut</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
                                            <th>Payer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Vendeuses -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">🛍️ Vendeuses</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-right mb-3">
                                <button class="btn btn-success btn-sm generer-paiement" data-type="vendeuse">
                                    <i class="fa fa-money-bill-wave"></i> Générer paiement
                                </button>
                                <button class="btn btn-info btn-sm enregistrer-production" data-toggle="modal" data-target="#production_prestataire" data-type="vendeuse">
                                    <i class="fa fa-chart-line"></i> Enregistrer commission
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered prestataire-table" id="tableVendeuses" width="100%" cellspacing="0" data-type="vendeuse">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom complet</th>
                                            <th>Téléphone</th>
                                            <th>Spécialités</th>
                                            <th>CA généré</th>
                                            <th>Commission due</th>
                                            <th>Total payé</th>
                                            <th>Statut</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
                                            <th>Payer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php include('footer.php'); ?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Inclusion des scripts JS -->
    <?php include('inclusion_bas.php'); ?>
    <script src="DataTables/data_table_prestataire.js?version=2.1"></script>

    <!-- Inclusion des modals -->
    <?php include('modals/modal_prestataire.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

</body>
</html>