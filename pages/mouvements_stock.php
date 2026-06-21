<?php
session_start();

// Seul un Admin peut accéder à cette page
if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/../api/modules/connect_db_pdo.php';

// Récupérer les statistiques
$stats = [];
try {
    // Nombre total de mouvements aujourd'hui
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_stock_mouvements WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $stats['mouvements_jour'] = $stmt->fetchColumn();
    
    // Quantité totale entrée aujourd'hui
    $stmt = $bdd->prepare("SELECT COALESCE(SUM(quantite), 0) FROM danfaniment_stock_mouvements WHERE type_mouvement = 'entree' AND DATE(created_at) = CURDATE()");
    $stmt->execute();
    $stats['entrees_jour'] = $stmt->fetchColumn();
    
    // Quantité totale sortie aujourd'hui
    $stmt = $bdd->prepare("SELECT COALESCE(SUM(quantite), 0) FROM danfaniment_stock_mouvements WHERE type_mouvement IN ('sortie', 'vente') AND DATE(created_at) = CURDATE()");
    $stmt->execute();
    $stats['sorties_jour'] = $stmt->fetchColumn();
    
    // Nombre de produits en alerte
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_produits WHERE stock_actuel <= stock_minimum AND stock_actuel > 0");
    $stmt->execute();
    $stats['alertes'] = $stmt->fetchColumn();
    
    // Nombre de produits en rupture
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_produits WHERE stock_actuel <= 0");
    $stmt->execute();
    $stats['ruptures'] = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $stats = ['mouvements_jour' => 0, 'entrees_jour' => 0, 'sorties_jour' => 0, 'alertes' => 0, 'ruptures' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DANFANIMENT – Mouvements de stock</title>

    <?php include('inclusion_haut.php'); ?>
    <style>
        .stats-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-entree { background-color: #10B981; color: white; }
        .badge-sortie { background-color: #F59E0B; color: white; }
        .badge-vente { background-color: #3B82F6; color: white; }
        .badge-ajustement { background-color: #8B5CF6; color: white; }
        .badge-retour { background-color: #EC4899; color: white; }
        .btn-modifier { background-color: #F59E0B; color: white; }
        .btn-modifier:hover { background-color: #D97706; color: white; }
        .btn-supprimer { background-color: #EF4444; color: white; }
        .btn-supprimer:hover { background-color: #DC2626; color: white; }
    </style>
</head>

<body id="page-top">
    <?php include('alert_mouvements_stock.php'); ?>

    <div id="wrapper">
        <?php include('menu_admin.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('entete.php'); ?>

                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-exchange-alt"></i> Mouvements de stock
                        </h1>
                        <div>
                            <button class="btn btn-success" data-toggle="modal" data-target="#ajouter_mouvement" onclick="resetForm()">
                                <i class="fas fa-plus"></i> Nouveau mouvement
                            </button>
                            <button class="btn btn-info" onclick="exportMouvements()">
                                <i class="fas fa-file-excel"></i> Exporter
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-2 col-md-4 mb-3">
                            <div class="card stats-card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Mouvements aujourd'hui</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['mouvements_jour']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-3">
                            <div class="card stats-card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Entrées (quantité)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo number_format($stats['entrees_jour'], 0, ',', ' '); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-3">
                            <div class="card stats-card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Sorties (quantité)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo number_format($stats['sorties_jour'], 0, ',', ' '); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-3">
                            <div class="card stats-card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Alertes stock</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['alertes']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-3">
                            <div class="card stats-card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Ruptures</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['ruptures']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-3">
                            <div class="card stats-card border-left-secondary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                                Valeur stock (est.)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $stmt = $bdd->query("SELECT COALESCE(SUM(stock_actuel * prix_achat), 0) FROM danfaniment_produits");
                                                $valeur = $stmt->fetchColumn();
                                                echo number_format($valeur, 0, ',', ' ') . ' CFA';
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-filter"></i> Filtres
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Type de mouvement</label>
                                    <select id="filtre_type" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="entree">Entrée</option>
                                        <option value="sortie">Sortie</option>
                                        <option value="ajustement">Ajustement</option>
                                        <option value="vente">Vente</option>
                                        <option value="retour">Retour</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Produit</label>
                                    <select id="filtre_produit" class="form-control">
                                        <option value="">Tous les produits</option>
                                        <?php
                                        $stmt = $bdd->query("SELECT id_produit, nom FROM danfaniment_produits WHERE statut = 'actif' ORDER BY nom");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . $row['id_produit'] . '">' . htmlspecialchars($row['nom']) . '</option>';
                                        }
                                        ?>
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
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12 text-right">
                                    <button class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fas fa-undo"></i> Réinitialiser
                                    </button>
                                    <button class="btn btn-primary" onclick="applyFilters()">
                                        <i class="fas fa-search"></i> Appliquer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table des mouvements -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list"></i> Historique des mouvements de stock
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTableMouvements" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Date</th>
                                            <th>Produit</th>
                                            <th>Type</th>
                                            <th>Quantité</th>
                                            <th>Stock avant</th>
                                            <th>Stock après</th>
                                            <th>Référence</th>
                                            <th>Utilisateur</th>
                                            <th>Motif</th>
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
            </div>

            <?php include('footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include('inclusion_bas.php'); ?>
    <script src="DataTables/data_table_mouvements_stock.js?version=2.0"></script>
    <?php include('modals/modal_mouvements_stock.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

    <script>
        function resetFilters() {
            $('#filtre_type').val('');
            $('#filtre_produit').val('');
            $('#filtre_date_debut').val('');
            $('#filtre_date_fin').val('');
            applyFilters();
        }
        
        function applyFilters() {
            if ($.fn.DataTable.isDataTable('#dataTableMouvements')) {
                $('#dataTableMouvements').DataTable().ajax.reload();
            }
        }
        
        function resetForm() {
            $('#form_ajouter_mouvement')[0].reset();
            $('#stock_info').hide();
            $('#quantite').val('');
            $('#alerte_stock').hide();
        }
        
        function exportMouvements() {
            window.location.href = '../api/modules/exporter_mouvements_stock.php?' + $.param({
                type: $('#filtre_type').val(),
                produit: $('#filtre_produit').val(),
                date_debut: $('#filtre_date_debut').val(),
                date_fin: $('#filtre_date_fin').val()
            });
        }
    </script>
</body>
</html>