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
    <title>DANFANIMENT POS – Gestion des dépenses</title>

    <?php include('inclusion_haut.php'); ?>
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>

<body id="page-top">
    <?php include('alert_depense.php'); ?>

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
                    <h1 class="h3 mb-4 text-gray-800">Gestion des dépenses</h1>

                    <div class="text-left mb-3">
                        <button 
                            class="btn btn-primary" 
                            data-toggle="modal" 
                            data-target="#ajouter_depense"
                        >
                            Nouvelle dépense <i class="fa fa-plus"></i>
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
                                    <label>Catégorie</label>
                                    <select id="filtre_categorie" class="form-control">
                                        <option value="">Toutes</option>
                                        <option value="salaire_prestataire_couturier">Salaire couturier</option>
                                        <option value="salaire_prestataire_tisseuse">Salaire tisseuse</option>
                                        <option value="salaire_prestataire_brodeur">Salaire brodeur</option>
                                        <option value="salaire_prestataire_perleuse">Salaire perleuse</option>
                                        <option value="salaire_prestataire_mercerie">Salaire mercerie</option>
                                        <option value="commission_prestataire_vendeuse">Commission vendeuse</option>
                                        <option value="livraison">Livraison</option>
                                        <option value="loyer">Loyer</option>
                                        <option value="fournitures">Fournitures</option>
                                        <option value="fournisseur_tissu">Fournisseur tissu</option>
                                        <option value="charges_diverses">Charges diverses</option>
                                        <option value="tontines_entreprise">Tontines entreprise</option>
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
                                <div class="col-md-3">
                                    <label>Montant min (FCFA)</label>
                                    <input type="number" id="filtre_montant_min" class="form-control" placeholder="0">
                                </div>
                                <div class="col-md-3 mt-2">
                                    <label>Montant max (FCFA)</label>
                                    <input type="number" id="filtre_montant_max" class="form-control" placeholder="9999999">
                                </div>
                                <div class="col-md-3 mt-2">
                                    <label>Statut</label>
                                    <select id="filtre_statut" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="valide">Validé</option>
                                        <option value="en_attente">En attente</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <label>Par jour</label>
                                    <input type="date" id="filtre_date_unique" class="form-control" placeholder="Date spécifique">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button id="btn_reset_filtres" class="btn btn-secondary">Réinitialiser les filtres</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Totaux par catégorie -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Totaux par catégorie</h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="totaux_categories">
                                <div class="col-md-12 text-center">Chargement des totaux...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique camembert -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Répartition des dépenses par catégorie</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="chartDepenses" style="max-height: 400px;"></canvas>
                        </div>
                    </div>

                    <!-- Graphique par jour -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Évolution des dépenses par jour</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="chartDepensesJour" style="max-height: 400px;"></canvas>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des dépenses</h6>
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
                                            <th>ID</th>
                                            <th>Date</th>
                                            <th>Référence</th>
                                            <th>Libellé</th>
                                            <th>Catégorie</th>
                                            <th>Bénéficiaire</th>
                                            <th>Justification</th>
                                            <th>Montant</th>
                                            <th>Origine</th>
                                            <th>Saisi par</th>
                                            <th>Statut</th>
                                            <th>Détails</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
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
    <script src="DataTables/data_table_depense.js?version=2.0"></script>

    <!-- Inclusion des modals -->
    <?php include('modals/modal_depense.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

</body>
</html>