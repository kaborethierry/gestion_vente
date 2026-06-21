<?php
// pages/facture.php
// DANFANIMENT POS - Gestion des factures

session_start();

// Vérification de l'authentification (admin ou caissier)
if (empty($_SESSION['id']) || !isset($_SESSION['role'])) {
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
    <title>DANFANIMENT – Gestion des factures</title>

    <?php include('inclusion_haut.php'); ?>
    <style>
        .facture-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .facture-brouillon { background: #6c757d; color: white; }
        .facture-envoyee { background: #3B82F6; color: white; }
        .facture-payee { background: #10B981; color: white; }
        .facture-annulee { background: #DC2626; color: white; }
    </style>
</head>

<body id="page-top">
    <?php include('alert_facture.php'); ?>

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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-invoice"></i> Gestion des factures
                        </h1>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#ajouter_facture">
                            <i class="fas fa-plus"></i> Nouvelle facture
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
                                        <option value="brouillon">Brouillon</option>
                                        <option value="envoyee">Envoyée</option>
                                        <option value="payee">Payée</option>
                                        <option value="annulee">Annulée</option>
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
                                    <label>Client</label>
                                    <input type="text" id="filtre_client" class="form-control" placeholder="Nom du client">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button id="btn_reset_filtres" class="btn btn-secondary">Réinitialiser les filtres</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des factures</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>N° Facture</th>
                                            <th>Client</th>
                                            <th>Date</th>
                                            <th>Échéance</th>
                                            <th>Total TTC</th>
                                            <th>Statut</th>
                                            <th class="text-center">Voir</th>
                                            <th class="text-center">Imprimer</th>
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Supprimer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                 </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include('footer.php'); ?>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include('inclusion_bas.php'); ?>
    <script src="DataTables/data_table_facture.js?version=1.0"></script>
    
    <?php include('modals/modal_facture.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>
</body>
</html>