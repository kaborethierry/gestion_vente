<?php
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
} else {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>BITECH - Projets</title>
    <?php include('inclusion_haut.php'); ?>
</head>
<body id="page-top">

    <?php include('alert_projet.php'); ?>

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include('menu_super_administrateur.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include('entete.php'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Gestion des projets</h1>
                    <div class="text-left">
                        <div class="col-md-6">
                            <span title="Ajouter un projet">
                                <button data-toggle="modal" style="color:#ffffff" data-backdrop="false" class="open-Ajouter_Projet btn btn-primary" href="#ajouter_projet">
                                    Ajouter un projet <i class="fa fa-plus"></i>
                                </button>
                            </span>
                        </div>

                        <div class="col-md-6">
                            <span title="Ajouter un projet">
                                <button data-toggle="modal" style="color:#ffffff" data-backdrop="false" class="open-Ajouter_Projet btn btn-primary" href="#ajouter_projet">
                                    Importer <i class="fa fa-plus"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                    <br>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des projets</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <!-- Nous afficherons 12 colonnes : 10 données issues du serveur et 2 colonnes d'actions -->
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">N°</th>
                                            <th class="text-center">Code projet</th>
                                            <th class="text-center">Nom du projet</th>
                                            <th class="text-center">Description</th>
                                            <th class="text-center">Date début</th>
                                            <th class="text-center">Date fin</th>
                                            <th class="text-center">Budget</th>
                                            <th class="text-center">Responsable</th>
                                            <th class="text-center">Statut</th>
                                            
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Supprimer</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-center">N°</th>
                                            <th class="text-center">Code projet</th>
                                            <th class="text-center">Nom du projet</th>
                                            <th class="text-center">Description</th>
                                            <th class="text-center">Date début</th>
                                            <th class="text-center">Date fin</th>
                                            <th class="text-center">Budget</th>
                                            <th class="text-center">Responsable</th>
                                            <th class="text-center">Statut</th>
                                            
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Supprimer</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <!-- Les données seront chargées via AJAX par DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            <?php include('footer.php'); ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Bouton de défilement vers le haut -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include('inclusion_bas.php'); ?>
    <!-- Script DataTables pour les projets -->
    <script type="text/javascript" src="DataTables/data_table_projet.js?version=1.0"></script>
    <?php include('modals/modal_projet.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>
</body>
</html>
<?php
}
?>
