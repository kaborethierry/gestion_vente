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
    <title>DANFANIMENT POS – Gestion des caisses</title>

    <?php include('inclusion_haut.php'); ?>
</head>

<body id="page-top">
    <?php include('alert_caisse.php'); ?>

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
                    <h1 class="h3 mb-4 text-gray-800">Gestion des sessions de caisse</h1>

                    <div class="text-left mb-3">
                        <button 
                            class="btn btn-primary open-Ajouter_Caisse" 
                            data-toggle="modal" 
                            data-backdrop="false" 
                            href="#ajouter_caisse"
                        >
                            Ouvrir une session de caisse <i class="fa fa-plus"></i>
                        </button>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des sessions de caisse</h6>
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
                                            <th class="text-center">ID Caisse</th>
                                            <th class="text-center">Session</th>
                                            <th class="text-center">Caissier</th>
                                            <th class="text-center">Date ouverture</th>
                                            <th class="text-center">Date fermeture</th>
                                            <th class="text-center">Montant initial</th>
                                            <th class="text-center">Montant final</th>
                                            <th class="text-center">Statut</th>
                                            <th class="text-center">Chiffre d'affaires</th>
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Fermer</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-center">ID Caisse</th>
                                            <th class="text-center">Session</th>
                                            <th class="text-center">Caissier</th>
                                            <th class="text-center">Date ouverture</th>
                                            <th class="text-center">Date fermeture</th>
                                            <th class="text-center">Montant initial</th>
                                            <th class="text-center">Montant final</th>
                                            <th class="text-center">Statut</th>
                                            <th class="text-center">Chiffre d'affaires</th>
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Fermer</th>
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
    <script src="DataTables/data_table_caisse.js?version=1.4"></script>

    <!-- Inclusion des modals -->
    <?php include('modals/modal_caisse.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

</body>
</html>