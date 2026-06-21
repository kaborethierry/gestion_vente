<?php
session_start();

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
    <title>DANFANIMENT POS – Gestion des mesures clients</title>

    <?php include('inclusion_haut.php'); ?>
    <!-- Librairie jsPDF pour génération PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        .mesure-detail-modal .swal2-popup {
            padding: 0 !important;
        }
        .mesure-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            overflow: hidden;
        }
        .mesure-card-header {
            background: linear-gradient(135deg, #DC2626, #F59E0B);
            color: white;
            padding: 15px 20px;
        }
        .mesure-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
            border-left: 4px solid #DC2626;
        }
        .mesure-section h6 {
            color: #DC2626;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .mesure-badge {
            background: #DC2626;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .mesure-value {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 15px;
            margin-left: 5px;
        }
    </style>
</head>

<body id="page-top">
    <?php include('alert_mesure.php'); ?>

    <div id="wrapper">
        <?php include('menu_admin.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('entete.php'); ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-ruler-combined"></i> Gestion des mesures clients
                        </h1>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user"></i> Sélectionner un client
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" id="search_client_mesure" class="form-control" placeholder="Rechercher par nom, prénom ou téléphone...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <select id="client_select" class="form-control">
                                        <option value="">-- Sélectionnez un client --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4" id="mesures_history_card" style="display: none;">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-history"></i> Historique des mesures - <span id="client_name_display"></span>
                                <button class="btn btn-sm btn-success float-right" id="btn_nouvelle_mesure" data-toggle="modal" data-target="#ajouter_mesure">
                                    <i class="fas fa-plus"></i> Nouvelle mesure
                                </button>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTableMesures" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Version</th>
                                            <th class="text-center">Date mesure</th>
                                            <th class="text-center">Poitrine</th>
                                            <th class="text-center">Tour taille</th>
                                            <th class="text-center">Bassin</th>
                                            <th class="text-center">Long. robe</th>
                                            <th class="text-center">Voir</th>
                                            <th class="text-center">Imprimer</th>
                                            <th class="text-center">Modifier</th>
                                            <th class="text-center">Supprimer</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
    
    <script src="DataTables/data_table_mesure.js?version=2.0"></script>
    
    <?php include('modals/modal_mesure.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

    <script>
        var urlParams = new URLSearchParams(window.location.search);
        var savedClientId = urlParams.get('client_id');

        function loadClients() {
            $.ajax({
                url: '../api/modules/client_list.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var select = $('#client_select');
                    select.empty();
                    select.append('<option value="">-- Sélectionnez un client --</option>');
                    if (data.success && data.clients) {
                        $.each(data.clients, function(i, client) {
                            select.append('<option value="' + client.id_client + '">' + client.nom + ' ' + client.prenom + ' - ' + client.telephone + '</option>');
                        });
                    }
                    if (savedClientId) {
                        $('#client_select').val(savedClientId).trigger('change');
                    }
                }
            });
        }

        $('#search_client_mesure').on('keyup', function() {
            var search = $(this).val().toLowerCase();
            $('#client_select option').each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(search) > -1 || $(this).val() === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('#client_select').on('change', function() {
            var clientId = $(this).val();
            var clientName = $(this).find('option:selected').text();
            
            if (clientId) {
                $('#client_name_display').text(clientName);
                $('#mesures_history_card').show();
                if ($.fn.DataTable.isDataTable('#dataTableMesures')) {
                    $('#dataTableMesures').DataTable().destroy();
                }
                initMesuresTable(clientId);
                var newUrl = window.location.pathname + '?client_id=' + clientId;
                window.history.pushState({path: newUrl}, '', newUrl);
            } else {
                $('#mesures_history_card').hide();
                var newUrl = window.location.pathname;
                window.history.pushState({path: newUrl}, '', newUrl);
            }
        });

        $('#btn_nouvelle_mesure').on('click', function() {
            $('#mesure_id_client').val($('#client_select').val());
        });

        loadClients();
    </script>
</body>
</html>