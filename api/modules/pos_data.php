<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // Vérification des droits (admin ou caissier)
    if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php';

    $action = $_GET['action'] ?? '';

    switch($action) {
        case 'get_products':
            getProducts($bdd);
            break;
        case 'get_cart':
            getCart();
            break;
        case 'search_client':
            searchClient($bdd);
            break;
        case 'get_product_by_barcode':
            getProductByBarcode($bdd);
            break;
        case 'get_daily_sales':
            getDailySales($bdd);
            break;
        case 'get_daily_stats':
            getDailyStats($bdd);
            break;
        case 'get_payment_breakdown':
            getPaymentBreakdown($bdd);
            break;
        case 'get_recent_sales':
            getRecentSales($bdd);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}

function getProducts($bdd) {
    $categorie = $_GET['categorie'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT id_produit, code_produit, nom, prix_vente, stock_actuel, photo, categorie 
            FROM danfaniment_produits 
            WHERE statut = 'actif'";
    $params = [];
    
    if ($categorie !== 'all') {
        $sql .= " AND categorie = :categorie";
        $params[':categorie'] = $categorie;
    }
    
    if (!empty($search)) {
        $sql .= " AND (code_produit LIKE :search OR nom LIKE :search OR code_barre LIKE :search)";
        $params[':search'] = "%{$search}%";
    }
    
    $sql .= " ORDER BY nom ASC LIMIT 100";
    
    $stmt = $bdd->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as &$product) {
        $product['prix_vente'] = (float)$product['prix_vente'];
        $product['stock_actuel'] = (int)$product['stock_actuel'];
        $product['photo_url'] = null;
        
        if (!empty($product['photo']) && file_exists('../../uploads/produits/' . $product['photo'])) {
            $product['photo_url'] = '../../uploads/produits/' . $product['photo'];
        }
        
        $product['prix_vente_formate'] = number_format($product['prix_vente'], 0, ',', ' ');
    }
    
    echo json_encode(['success' => true, 'products' => $products]);
}

function getCart() {
    $cart = $_SESSION['pos_cart'] ?? [];
    echo json_encode(['success' => true, 'cart' => $cart]);
}

function searchClient($bdd) {
    $query = trim($_GET['query'] ?? '');
    
    if (strlen($query) < 2) {
        echo json_encode(['success' => false, 'message' => 'Terme de recherche trop court', 'clients' => []]);
        return;
    }
    
    $sql = "SELECT id_client, CONCAT(nom, ' ', prenom) AS nom_complet, telephone, points_fidelite 
            FROM danfaniment_clients 
            WHERE (nom LIKE :query OR prenom LIKE :query OR telephone LIKE :query) 
            AND (supprimer IS NULL OR supprimer = 'Non')
            LIMIT 10";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([':query' => "%{$query}%"]);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'clients' => $clients]);
}

function getProductByBarcode($bdd) {
    $barcode = $_GET['barcode'] ?? '';
    
    if (empty($barcode)) {
        echo json_encode(['success' => false, 'message' => 'Code barre manquant']);
        return;
    }
    
    $sql = "SELECT id_produit, code_produit, nom, prix_vente, stock_actuel 
            FROM danfaniment_produits 
            WHERE (code_barre = :barcode OR code_produit = :barcode) 
            AND statut = 'actif'";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([':barcode' => $barcode]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
    }
}

function getDailySales($bdd) {
    $stmt = $bdd->prepare("
        SELECT 
            COALESCE(SUM(total_ttc), 0) as total, 
            COUNT(*) as nb_ventes
        FROM danfaniment_ventes 
        WHERE DATE(created_at) = CURDATE() 
        AND statut = 'valide'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'total' => (float)$result['total'], 
        'nb_ventes' => (int)$result['nb_ventes']
    ]);
}

function getDailyStats($bdd) {
    try {
        // 1. Encaissements du jour (ventes en espèces)
        $stmt = $bdd->prepare("
            SELECT COALESCE(SUM(total_ttc), 0) as total, COUNT(*) as nb
            FROM danfaniment_ventes 
            WHERE DATE(created_at) = CURDATE() 
            AND statut = 'valide'
        ");
        $stmt->execute();
        $ventes = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 2. Paiements des commandes de confection du jour (avances et soldes)
        $stmt = $bdd->prepare("
            SELECT COALESCE(SUM(montant), 0) as total, COUNT(*) as nb
            FROM danfaniment_paiements_confection 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $paiements_confection = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Total des encaissements = ventes + paiements confection
        $total_encaissements = (float)$ventes['total'] + (float)$paiements_confection['total'];
        $nb_encaissements = (int)$ventes['nb'] + (int)$paiements_confection['nb'];
        
        // 3. Dépenses du jour
        $stmt = $bdd->prepare("
            SELECT COALESCE(SUM(montant), 0) as total, COUNT(*) as nb
            FROM danfaniment_depenses 
            WHERE DATE(date_depense) = CURDATE() 
            AND statut = 'valide'
        ");
        $stmt->execute();
        $depenses = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 4. Récupérer le fond initial de la session active
        $id_utilisateur = $_SESSION['id'];
        $stmt = $bdd->prepare("
            SELECT montant_initial 
            FROM danfaniment_caisses 
            WHERE id_utilisateur = :id AND statut = 'ouverte' 
            ORDER BY id_caisse DESC LIMIT 1
        ");
        $stmt->execute([':id' => $id_utilisateur]);
        $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$caisse && $_SESSION['role'] === 'admin') {
            $stmt = $bdd->prepare("
                SELECT montant_initial 
                FROM danfaniment_caisses 
                WHERE statut = 'ouverte' 
                ORDER BY id_caisse DESC LIMIT 1
            ");
            $stmt->execute();
            $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $montant_initial = $caisse ? (float)$caisse['montant_initial'] : 0;
        
        // 5. Calcul du solde caisse estimé
        $solde_caisse = $montant_initial + $total_encaissements - (float)$depenses['total'];
        
        echo json_encode([
            'success' => true,
            'total_encaissements' => $total_encaissements,
            'nb_ventes' => $nb_encaissements,
            'total_depenses' => (float)$depenses['total'],
            'nb_depenses' => (int)$depenses['nb'],
            'solde_caisse' => $solde_caisse,
            'montant_initial' => $montant_initial
        ]);
        
    } catch (PDOException $e) {
        error_log("Erreur getDailyStats: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'total_encaissements' => 0,
            'nb_ventes' => 0,
            'total_depenses' => 0,
            'nb_depenses' => 0,
            'solde_caisse' => 0
        ]);
    }
}

// ============================================
// NOUVELLE FONCTION : RÉPARTITION DES PAIEMENTS PAR MODE
// ============================================
function getPaymentBreakdown($bdd) {
    try {
        $modes = ['especes', 'mobile_money', 'carte', 'virement'];
        $result = [];
        
        foreach ($modes as $mode) {
            $stmt = $bdd->prepare("
                SELECT COALESCE(SUM(total_ttc), 0) as total, COUNT(*) as nb
                FROM danfaniment_ventes 
                WHERE DATE(created_at) = CURDATE() 
                AND statut = 'valide'
                AND mode_paiement = :mode
            ");
            $stmt->execute([':mode' => $mode]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $result[$mode] = [
                'total' => (float)$data['total'],
                'nb' => (int)$data['nb']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'especes' => $result['especes'],
            'mobile_money' => $result['mobile_money'],
            'carte' => $result['carte'],
            'virement' => $result['virement']
        ]);
    } catch (PDOException $e) {
        error_log("Erreur getPaymentBreakdown: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'especes' => ['total' => 0, 'nb' => 0],
            'mobile_money' => ['total' => 0, 'nb' => 0],
            'carte' => ['total' => 0, 'nb' => 0],
            'virement' => ['total' => 0, 'nb' => 0]
        ]);
    }
}

// ============================================
// NOUVELLE FONCTION : VENTES RÉCENTES DU JOUR
// ============================================
function getRecentSales($bdd) {
    try {
        $stmt = $bdd->prepare("
            SELECT 
                v.id_vente,
                v.numero_vente,
                v.total_ttc,
                v.mode_paiement,
                v.date_vente,
                v.statut,
                DATE_FORMAT(v.date_vente, '%d/%m/%Y %H:%i') as date_heure,
                u.nom_complet as caissier,
                CONCAT(COALESCE(c.nom, ''), ' ', COALESCE(c.prenom, '')) as client_nom,
                c.id_client
            FROM danfaniment_ventes v
            LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
            LEFT JOIN danfaniment_clients c ON v.id_client = c.id_client
            WHERE DATE(v.date_vente) = CURDATE()
            AND v.statut = 'valide'
            ORDER BY v.id_vente DESC
            LIMIT 50
        ");
        $stmt->execute();
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Nettoyer les valeurs null pour client_nom
        foreach ($sales as &$sale) {
            if (empty($sale['client_nom']) || trim($sale['client_nom']) === '') {
                $sale['client_nom'] = null;
            }
        }
        
        echo json_encode([
            'success' => true,
            'sales' => $sales
        ]);
    } catch (PDOException $e) {
        error_log("Erreur getRecentSales: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'sales' => []
        ]);
    }
}
?>