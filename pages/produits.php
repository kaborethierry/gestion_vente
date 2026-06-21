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
    <title>DANFANIMENT POS – Gestion des produits</title>

    <?php include('inclusion_haut.php'); ?>
</head>

<body id="page-top">
    <?php include('alert_produit.php'); ?>

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
                    <h1 class="h3 mb-4 text-gray-800">Gestion des produits</h1>

                    <div class="text-left mb-3">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#ajouter_produit">
                            Ajouter un produit <i class="fa fa-plus"></i>
                        </button>
                        <button class="btn btn-success ml-2" id="btn_import_csv">
                            <i class="fa fa-file-excel"></i> Import CSV
                        </button>
                        <input type="file" id="csv_file_input" accept=".csv" style="display:none;">
                    </div>

                    <!-- Filtres -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Catégorie</label>
                                    <select id="filtre_categorie" class="form-control">
                                        <option value="">Toutes</option>
                                        <option value="habits_traditionnels">Habits traditionnels</option>
                                        <option value="pagnes">Pagnes</option>
                                        <option value="vetements">Vêtements</option>
                                        <option value="accessoires">Accessoires</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Statut</label>
                                    <select id="filtre_statut" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="actif">Actif</option>
                                        <option value="inactif">Inactif</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Stock min</label>
                                    <input type="number" id="filtre_stock_min" class="form-control" placeholder="Stock minimum">
                                </div>
                                <div class="col-md-3">
                                    <label>Stock max</label>
                                    <input type="number" id="filtre_stock_max" class="form-control" placeholder="Stock maximum">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des produits</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Photo</th>
                                            <th class="text-center">Code</th>
                                            <th class="text-center">Nom</th>
                                            <th class="text-center">Catégorie</th>
                                            <th class="text-center">Prix achat</th>
                                            <th class="text-center">Prix vente</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Statut</th>
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Supprimer</th>
                                            <th class="text-center">Détail</th>
                                            <th class="text-center">Code-barres</th>
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
    <script src="DataTables/data_table_produit.js?version=1.1"></script>

    <!-- Inclusion des modals -->
    <?php include('modals/modal_produit.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

</body>
</html>