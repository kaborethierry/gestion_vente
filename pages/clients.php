<?php
session_start();

// Vérification de l'authentification (admin ou caissier peuvent accéder)
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
    <title>DANFANIMENT POS – Gestion des clients</title>

    <?php include('inclusion_haut.php'); ?>
    
    <style>
        .client-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #DC2626, #F59E0B);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 8px;
        }
        .nav-tabs-custom .nav-link {
            color: #4a5568;
            font-weight: 500;
        }
        .nav-tabs-custom .nav-link.active {
            color: #DC2626;
            border-bottom: 2px solid #DC2626;
        }
    </style>
</head>

<body id="page-top">
    <?php include('alert_client.php'); ?>

    <div id="wrapper">
        <?php include('menu_admin.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('entete.php'); ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-users"></i> Gestion des clients-
                        </h1>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#ajouter_client">
                            <i class="fas fa-plus"></i> Ajouter un client
                        </button>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des clients</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">N°</th>
                                            <th class="text-center">Nom complet</th>
                                            <th class="text-center">Téléphone</th>
                                            <th class="text-center">Ville</th>
                                            <!-- Colonne Total dépensé masquée -->
                                            <th class="text-center" style="display: none;">Total dépensé</th>
                                            <th class="text-center">Nb visites</th>
                                            <th class="text-center">Dernière visite</th>
                                            <th class="text-center">Voir</th>
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
    
    <script src="DataTables/data_table_client.js?version=1.3"></script>
    
    <?php include('modals/modal_client.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>
</body>
</html>