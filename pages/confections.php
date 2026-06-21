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
    <title>DANFANIMENT POS – Gestion des commandes confection</title>

    <?php include('inclusion_haut.php'); ?>
</head>

<body id="page-top">
    <?php include('alert_confection.php'); ?>

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
                    <h1 class="h3 mb-4 text-gray-800">Gestion des commandes confection</h1>

                    <div class="text-left mb-3">
                        <button 
                            class="btn btn-primary open-Ajouter_CommandeConfection" 
                            data-toggle="modal" 
                            data-backdrop="false" 
                            href="#ajouter_confection"
                        >
                            Nouvelle commande <i class="fa fa-plus"></i>
                        </button>
                    </div>

                    <!-- Filtres -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Statut</label>
                                    <select id="filtre_statut" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="en_attente">En attente</option>
                                        <option value="en_cours">En cours</option>
                                        <option value="termine">Terminé</option>
                                        <option value="livre">Livré</option>
                                        <option value="annule">Annulé</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Type de tenue</label>
                                    <input type="text" id="filtre_type" class="form-control" placeholder="Rechercher un type...">
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
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des commandes confection</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table 
                                    class="table table-bordered" 
                                    id="dataTable" 
                                    width="100%" 
                                    cellspacing="0"
                                >
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">N° Commande</th>
                                            <th class="text-center">Client</th>
                                            <th class="text-center">Prestataire</th>
                                            <th class="text-center">Type tenue</th>
                                            <th class="text-center">Date commande</th>
                                            <th class="text-center">Livraison prévue</th>
                                            <th class="text-center">Montant total</th>
                                            <th class="text-center">Avance</th>
                                            <th class="text-center">Statut</th>
                                            <th class="text-center">Détails</th>
                                            <th class="text-center">Changer statut</th>
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Supprimer</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">N° Commande</th>
                                            <th class="text-center">Client</th>
                                            <th class="text-center">Prestataire</th>
                                            <th class="text-center">Type tenue</th>
                                            <th class="text-center">Date commande</th>
                                            <th class="text-center">Livraison prévue</th>
                                            <th class="text-center">Montant total</th>
                                            <th class="text-center">Avance</th>
                                            <th class="text-center">Statut</th>
                                            <th class="text-center">Détails</th>
                                            <th class="text-center">Changer statut</th>
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Supprimer</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <!-- Les enregistrements seront chargés dynamiquement via DataTables -->
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
    <script src="DataTables/data_table_confection.js?version=1.7"></script>

    <!-- Inclusion des modals -->
    <?php include('modals/modal_confection.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

</body>
</html>