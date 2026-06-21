<?php
session_start();

// Seul un Caissier ou Admin peut accéder à cette page
if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit;
}

// Vérifier si une session de caisse est ouverte
require_once __DIR__ . '/../api/modules/connect_db_pdo.php';
$session_active = false;
$session_data = null;
$id_caisse_active = null;

try {
    $id_utilisateur_caisse = $_SESSION['id'];
    
    $stmt = $bdd->prepare("SELECT * FROM danfaniment_caisses WHERE id_utilisateur = ? AND statut = 'ouverte' ORDER BY id_caisse DESC LIMIT 1");
    $stmt->execute([$id_utilisateur_caisse]);
    $session_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session_data) {
        $session_active = true;
        $id_caisse_active = $session_data['id_caisse'];
        $_SESSION['id_caisse_active'] = $id_caisse_active;
    } else {
        if ($_SESSION['role'] === 'admin') {
            $stmt = $bdd->prepare("SELECT * FROM danfaniment_caisses WHERE statut = 'ouverte' ORDER BY id_caisse DESC LIMIT 1");
            $stmt->execute();
            $session_data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($session_data) {
                $session_active = true;
                $id_caisse_active = $session_data['id_caisse'];
                $_SESSION['id_caisse_active'] = $id_caisse_active;
            }
        }
    }
} catch (PDOException $e) {
    error_log("Erreur vérification caisse: " . $e->getMessage());
}

$nom_caissier = $_SESSION['nom_complet'] ?? $_SESSION['nom_utilisateur'] ?? 'Caissier';
$is_admin = ($_SESSION['role'] === 'admin') ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DANFANIMENT POS – Point de vente</title>

    <?php include('inclusion_haut.php'); ?>
    <style>
        .product-card {
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 15px;
            background: white;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-color: #DC2626;
        }
        .product-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .cart-item {
            border-bottom: 1px solid #e3e6f0;
            padding: 10px 0;
        }
        .total-amount {
            font-size: 2rem;
            font-weight: bold;
            color: #DC2626;
        }
        .payment-method {
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            border-radius: 8px;
        }
        .payment-method.active {
            border-color: #DC2626;
            background-color: #fef3f2;
        }
        .payment-method:hover {
            background-color: #fef3f2;
        }
        #search-product {
            font-size: 1.2rem;
            padding: 15px;
        }
        .quick-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .quick-actions .btn {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            margin: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .numeric-keypad {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 15px;
        }
        .numeric-keypad .btn {
            margin: 5px;
            width: 70px;
            height: 70px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .session-info {
            background: linear-gradient(135deg, #DC2626, #F59E0B);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .daily-sales {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .daily-expenses {
            background: linear-gradient(135deg, #DC2626, #B91C1C);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .cash-balance {
            background: linear-gradient(135deg, #3B82F6, #1E3A8A);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .payment-stats {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #e3e6f0;
        }
        .payment-stats h6 {
            font-size: 0.8rem;
            margin-bottom: 5px;
            color: #6c757d;
        }
        .payment-stats h4 {
            margin: 0;
            font-weight: bold;
        }
        .payment-especes { border-left: 4px solid #10B981; }
        .payment-carte { border-left: 4px solid #3B82F6; }
        .payment-mobile { border-left: 4px solid #F59E0B; }
        .payment-virement { border-left: 4px solid #8B5CF6; }
        
        .client-selected {
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }
        .manual-sale-item {
            background-color: #FFF3E0;
            border-left: 3px solid #F59E0B;
        }
        .stat-card {
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .recent-sales-table {
            font-size: 0.85rem;
        }
        .recent-sales-table th {
            background: #f8f9fc;
            font-weight: 600;
        }
        .recent-sales-table td {
            vertical-align: middle;
            padding: 8px;
        }
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            margin: 0 2px;
        }
    </style>
</head>

<body id="page-top" data-is-admin="<?php echo $is_admin; ?>">
    <?php include('alert_pos.php'); ?>

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
                    <?php if (!$session_active): ?>
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Aucune session de caisse active.</strong> 
                        <a href="caisse.php" class="alert-link">Cliquez ici pour ouvrir une session</a>
                    </div>
                    <?php else: ?>
                    
                    <!-- Session Info -->
                    <div class="session-info">
                        <div class="row">
                            <div class="col-md-3">
                                <i class="fas fa-cash-register"></i> 
                                Session: <?php echo htmlspecialchars($session_data['id_session'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-user"></i> 
                                Caissier: <?php echo htmlspecialchars($nom_caissier); ?>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-money-bill"></i> 
                                Fond initial: <?php echo number_format($session_data['montant_initial'] ?? 0, 0, ',', ' '); ?> CFA
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-chart-line"></i> 
                                Ventes: <?php echo number_format($session_data['nombre_ventes'] ?? 0, 0, ',', ' '); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- LIGNE 1 : Encaissements du jour, Dépenses du jour, Solde caisse -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="daily-sales stat-card">
                                <div class="row align-items-center">
                                    <div class="col-4 text-center">
                                        <i class="fas fa-chart-line fa-3x"></i>
                                    </div>
                                    <div class="col-8 text-right">
                                        <small>Encaissements du jour</small>
                                        <h3 class="mb-0" id="total_encaissements_jour">0 CFA</h3>
                                        <small id="nb_ventes_jour">0 vente(s)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="daily-expenses stat-card">
                                <div class="row align-items-center">
                                    <div class="col-4 text-center">
                                        <i class="fas fa-arrow-down fa-3x"></i>
                                    </div>
                                    <div class="col-8 text-right">
                                        <small>Dépenses du jour</small>
                                        <h3 class="mb-0" id="total_depenses_jour">0 CFA</h3>
                                        <small id="nb_depenses_jour">0 dépense(s)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="cash-balance stat-card">
                                <div class="row align-items-center">
                                    <div class="col-4 text-center">
                                        <i class="fas fa-wallet fa-3x"></i>
                                    </div>
                                    <div class="col-8 text-right">
                                        <small>Solde caisse estimé</small>
                                        <h3 class="mb-0" id="solde_caisse">0 CFA</h3>
                                        <small>Fond initial + Encaissements - Dépenses</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LIGNE 2 - Types d'encaissement -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="payment-stats payment-especes text-center">
                                <h6><i class="fas fa-money-bill-wave"></i> Espèces</h6>
                                <h4 id="total_especes">0 CFA</h4>
                                <small id="nb_especes">0 vente(s)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-stats payment-mobile text-center">
                                <h6><i class="fas fa-mobile-alt"></i> Mobile Money</h6>
                                <h4 id="total_mobile_money">0 CFA</h4>
                                <small id="nb_mobile_money">0 vente(s)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-stats payment-carte text-center">
                                <h6><i class="fas fa-credit-card"></i> Carte</h6>
                                <h4 id="total_carte">0 CFA</h4>
                                <small id="nb_carte">0 vente(s)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-stats payment-virement text-center">
                                <h6><i class="fas fa-university"></i> Virement</h6>
                                <h4 id="total_virement">0 CFA</h4>
                                <small id="nb_virement">0 vente(s)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Vente rapide</h6>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-warning btn-lg btn-block" onclick="showManualSaleModal()" style="padding: 30px;">
                                        <i class="fas fa-plus-circle fa-3x mb-2 d-block"></i>
                                        <strong>Vente manuelle</strong><br>
                                        <small>Ajouter un produit/service non référencé</small>
                                    </button>
                                    <hr>
                                    <button class="btn btn-info btn-block" onclick="openCaissePage()">
                                        <i class="fas fa-cash-register"></i> Gérer la caisse
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Client fidèle -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Client fidèle</h6>
                                </div>
                                <div class="card-body">
                                    <div class="input-group">
                                        <input type="text" id="client-search" class="form-control" placeholder="Téléphone ou nom du client">
                                        <div class="input-group-append">
                                            <button class="btn btn-info" onclick="searchClient()"><i class="fas fa-search"></i></button>
                                            <button class="btn btn-success" onclick="showAddClientModal()"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <div id="client-info" style="display: none;">
                                        <div class="client-selected">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><i class="fas fa-user"></i> <span id="client-nom"></span></strong><br>
                                                    <small><i class="fas fa-star text-warning"></i> <span id="client-points"></span> points</small>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger" onclick="clearClient()"><i class="fas fa-times"></i></button>
                                            </div>
                                            <input type="hidden" id="client-id">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Cart -->
                        <div class="col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Panier</h6>
                                </div>
                                <div class="card-body">
                                    <div id="cart-items" style="max-height: 250px; overflow-y: auto;">
                                        <div class="text-center text-muted py-5"><i class="fas fa-shopping-cart fa-4x mb-3"></i><p>Panier vide</p></div>
                                    </div>
                                    <hr>
                                    <div class="row mb-2">
                                        <div class="col-6"><strong>Sous-total:</strong></div>
                                        <div class="col-6 text-right"><span id="subtotal">0</span> CFA</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6"><strong>Remise:</strong></div>
                                        <div class="col-6 text-right">
                                            <div class="input-group">
                                                <input type="number" id="discount-input" class="form-control form-control-sm" placeholder="0" value="0" onchange="updateTotal()">
                                                <div class="input-group-append">
                                                    <select id="discount-type" class="form-control-sm" onchange="updateTotal()">
                                                        <option value="amount">CFA</option>
                                                        <option value="percentage">%</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6"><h4>Total:</h4></div>
                                        <div class="col-6 text-right"><h4 class="total-amount" id="total">0</h4></div>
                                    </div>
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <label class="font-weight-bold">Mode de paiement:</label>
                                            <div class="row">
                                                <div class="col-3"><div class="payment-method text-center p-2 rounded active" onclick="selectPayment('especes')" data-method="especes"><i class="fas fa-money-bill-wave fa-2x"></i><p class="mb-0 small">Espèces</p></div></div>
                                                <div class="col-3"><div class="payment-method text-center p-2 rounded" onclick="selectPayment('carte')" data-method="carte"><i class="fas fa-credit-card fa-2x"></i><p class="mb-0 small">Carte</p></div></div>
                                                <div class="col-3"><div class="payment-method text-center p-2 rounded" onclick="selectPayment('mobile_money')" data-method="mobile_money"><i class="fas fa-mobile-alt fa-2x"></i><p class="mb-0 small">Mobile Money</p></div></div>
                                                <div class="col-3"><div class="payment-method text-center p-2 rounded" onclick="selectPayment('virement')" data-method="virement"><i class="fas fa-university fa-2x"></i><p class="mb-0 small">Virement</p></div></div>
                                            </div>
                                            <input type="text" id="reference-transaction" class="form-control mt-2" placeholder="Référence transaction (optionnel)">
                                            <div class="row mt-2" id="especes-details">
                                                <div class="col-6"><label>Montant reçu:</label><input type="number" id="montant_recu" class="form-control" placeholder="Montant reçu" oninput="calculateMonnaie()"></div>
                                                <div class="col-6"><label>Monnaie à rendre:</label><input type="text" id="monnaie_rendue" class="form-control" readonly></div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6"><button class="btn btn-danger btn-block" onclick="clearCart()"><i class="fas fa-trash"></i> Vider</button></div>
                                        <div class="col-6"><button class="btn btn-success btn-block" onclick="processSale()"><i class="fas fa-check"></i> Valider</button></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau de traçabilité des ventes -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-history"></i> Historique des ventes du jour
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover recent-sales-table" id="recent-sales-table" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>N° Vente</th>
                                                    <th>Client</th>
                                                    <th>Montant</th>
                                                    <th>Mode</th>
                                                    <th>Date</th>
                                                    <th>Caissier</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="recent-sales-tbody">
                                                <tr><td colspan="7" class="text-center text-muted">Chargement...<\/td><\/tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php endif; ?>
                </div>
            </div>

            <?php include('footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <div class="quick-actions no-print">
        <button class="btn btn-primary" onclick="quickSale()" title="Vente rapide"><i class="fas fa-bolt"></i></button>
        <button class="btn btn-warning" onclick="showManualSaleModal()" title="Vente manuelle"><i class="fas fa-plus"></i></button>
        <button class="btn btn-info" onclick="openNumerique()" title="Clavier numérique"><i class="fas fa-keyboard"></i></button>
    </div>

    <?php include('inclusion_bas.php'); ?>
    <script src="pos/js/pos.js?version=12.1"></script>
    <?php include('modals/modal_pos.php'); ?>
    
    <!-- Modal Détails Vente -->
    <div class="modal fade" id="saleDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
                    <h5 class="modal-title"><i class="fas fa-receipt"></i> Détails de la vente</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="saleDetailsContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i> Chargement...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="printDetailTicket"><i class="fas fa-print"></i> Imprimer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var sessionActive = <?php echo $session_active ? 'true' : 'false'; ?>;
        <?php if (!$session_active): ?>
        $(document).ready(function() {
            Swal.fire({
                title: 'Session de caisse requise',
                html: 'Vous devez ouvrir une session de caisse avant de pouvoir effectuer des ventes.<br><br><a href="caisse.php" class="btn btn-danger">Ouvrir une session</a>',
                icon: 'warning',
                showConfirmButton: false,
                allowOutsideClick: false
            });
        });
        <?php endif; ?>
    </script>

    <!-- Impression simple via navigateur -->
    <script>
    function autoPrintTicket(id_vente) {
        if (id_vente) {
            window.open('../api/modules/imprimer_thermal.php?id_vente=' + id_vente, '_blank', 'width=450,height=650');
        }
    }
    window.autoPrintTicket = autoPrintTicket;
    </script>
</body>
</html>