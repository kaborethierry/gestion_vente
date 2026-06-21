<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

define('ID_PRODUIT_MANUEL', 4);

if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_session = $_SESSION['id'];

try {
    $stmt = $bdd->prepare("SELECT id_utilisateur, nom_utilisateur, nom_complet, role FROM utilisateurs WHERE id_utilisateur = :id AND (supprimer = 'Non' OR supprimer IS NULL) AND actif = 1");
    $stmt->execute([':id' => $id_session]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$utilisateur) {
        $stmt = $bdd->prepare("SELECT id_utilisateur, nom_utilisateur, nom_complet, role FROM utilisateurs WHERE id_utilisateur IN (1,2) AND (supprimer = 'Non' OR supprimer IS NULL) AND actif = 1 LIMIT 1");
        $stmt->execute();
        $utilisateur_defaut = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utilisateur_defaut) {
            $_SESSION['id'] = $utilisateur_defaut['id_utilisateur'];
            $_SESSION['role'] = $utilisateur_defaut['role'];
            $_SESSION['nom_complet'] = $utilisateur_defaut['nom_complet'];
            $_SESSION['nom_utilisateur'] = $utilisateur_defaut['nom_utilisateur'];
            $id_utilisateur = $utilisateur_defaut['id_utilisateur'];
            $role_utilisateur = $utilisateur_defaut['role'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Aucun utilisateur valide trouvé']);
            exit;
        }
    } else {
        $id_utilisateur = $utilisateur['id_utilisateur'];
        $role_utilisateur = $utilisateur['role'];
        $_SESSION['id'] = $id_utilisateur;
        $_SESSION['role'] = $utilisateur['role'];
        $_SESSION['nom_complet'] = $utilisateur['nom_complet'];
        $_SESSION['nom_utilisateur'] = $utilisateur['nom_utilisateur'];
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
    exit;
}

$stmt = $bdd->prepare("SELECT id_caisse FROM danfaniment_caisses WHERE id_utilisateur = ? AND statut = 'ouverte' ORDER BY id_caisse DESC LIMIT 1");
$stmt->execute([$id_utilisateur]);
$caisse = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$caisse && $role_utilisateur === 'admin') {
    $stmt = $bdd->prepare("SELECT id_caisse FROM danfaniment_caisses WHERE statut = 'ouverte' ORDER BY id_caisse DESC LIMIT 1");
    $stmt->execute();
    $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$caisse) {
    echo json_encode(['success' => false, 'message' => 'Aucune session de caisse active']);
    exit;
}

$id_caisse = $caisse['id_caisse'];

$cart = json_decode($_POST['cart'] ?? '[]', true);
$payment_method = $_POST['payment_method'] ?? '';
$discount = floatval($_POST['discount'] ?? 0);
$discount_type = $_POST['discount_type'] ?? 'amount';
$montant_recu = floatval($_POST['montant_recu'] ?? 0);
$reference_transaction = trim($_POST['reference_transaction'] ?? '');
$client_id = !empty($_POST['client_id']) ? intval($_POST['client_id']) : null;

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Panier vide']);
    exit;
}

if (empty($payment_method)) {
    echo json_encode(['success' => false, 'message' => 'Mode de paiement manquant']);
    exit;
}

try {
    $bdd->beginTransaction();
    
    $subtotal = 0;
    $client_info_to_save = null;
    
    foreach ($cart as $item) {
        $subtotal += $item['prix_vente'] * $item['quantite'];
        if (isset($item['is_manual']) && $item['is_manual'] && isset($item['client_info']) && !empty($item['client_info'])) {
            $client_info_to_save = $item['client_info'];
        }
    }
    
    if ($discount_type === 'percentage') {
        $discount_amount = $subtotal * ($discount / 100);
    } else {
        $discount_amount = $discount;
    }
    
    $total_ttc = max(0, $subtotal - $discount_amount);
    $monnaie_rendue = ($payment_method === 'especes') ? max(0, $montant_recu - $total_ttc) : 0;
    
    // Gestion du client
    if (!$client_id && $client_info_to_save && (!empty($client_info_to_save['nom_client']) || !empty($client_info_to_save['telephone_client']))) {
        $telephone = $client_info_to_save['telephone_client'] ?? '';
        if (!empty($telephone)) {
            $stmt = $bdd->prepare("SELECT id_client FROM danfaniment_clients WHERE telephone = :telephone AND (supprimer IS NULL OR supprimer = 'Non') LIMIT 1");
            $stmt->execute([':telephone' => $telephone]);
            $existing_client = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing_client) {
                $client_id = $existing_client['id_client'];
            }
        }
        
        if (!$client_id) {
            $nom_client = $client_info_to_save['nom_client'] ?? 'Client';
            $prenom_client = '';
            $nom_parts = explode(' ', $nom_client, 2);
            if (count($nom_parts) >= 2) {
                $nom_client = $nom_parts[0];
                $prenom_client = $nom_parts[1];
            }
            
            $telephone_client = $client_info_to_save['telephone_client'] ?? null;
            
            if (empty($telephone_client)) {
                $telephone_client = '00000000' . rand(100, 999);
            }
            
            $stmt = $bdd->prepare("INSERT INTO danfaniment_clients (nom, prenom, telephone, email, ville, adresse, date_premiere_visite, created_at) 
                                  VALUES (:nom, :prenom, :telephone, :email, :ville, :adresse, NOW(), NOW())");
            $stmt->execute([
                ':nom' => $nom_client,
                ':prenom' => $prenom_client,
                ':telephone' => $telephone_client,
                ':email' => $client_info_to_save['email_client'] ?? null,
                ':ville' => $client_info_to_save['ville_client'] ?? null,
                ':adresse' => $client_info_to_save['adresse_client'] ?? null
            ]);
            $client_id = $bdd->lastInsertId();
        }
    }
    
    // ============================================
    // GÉNÉRATION DU NUMÉRO DE VENTE SANS DOUBLON
    // ============================================
    $date_jour = date('Ymd');
    $prefix = 'VEN-' . $date_jour . '-';
    
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_ventes WHERE numero_vente LIKE :prefix");
    $stmt->execute([':prefix' => $prefix . '%']);
    $ventes_jour = $stmt->fetchColumn();
    
    $new_num = $ventes_jour + 1;
    $numero_vente = $prefix . str_pad($new_num, 4, '0', STR_PAD_LEFT);
    
    $stmt = $bdd->prepare("SELECT id_vente FROM danfaniment_ventes WHERE numero_vente = :numero");
    $stmt->execute([':numero' => $numero_vente]);
    if ($stmt->fetch()) {
        $i = 1;
        while(true) {
            $test_num = $prefix . str_pad($ventes_jour + $i, 4, '0', STR_PAD_LEFT);
            $stmt = $bdd->prepare("SELECT id_vente FROM danfaniment_ventes WHERE numero_vente = :numero");
            $stmt->execute([':numero' => $test_num]);
            if (!$stmt->fetch()) {
                $numero_vente = $test_num;
                break;
            }
            $i++;
        }
    }
    
    // Insertion de la vente
    $sql = "INSERT INTO danfaniment_ventes (
                numero_vente, id_utilisateur, id_client, date_vente,
                sous_total, remise_type, remise_valeur, remise_montant, total_ttc,
                montant_recu, monnaie_rendue, mode_paiement, reference_transaction,
                statut, created_at
            ) VALUES (
                :numero_vente, :id_utilisateur, :id_client, NOW(),
                :subtotal, :remise_type, :remise_valeur, :remise_montant, :total_ttc,
                :montant_recu, :monnaie_rendue, :mode_paiement, :reference_transaction,
                'valide', NOW()
            )";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':numero_vente' => $numero_vente,
        ':id_utilisateur' => $id_utilisateur,
        ':id_client' => $client_id,
        ':subtotal' => $subtotal,
        ':remise_type' => $discount_type === 'percentage' ? 'pourcentage' : 'montant',
        ':remise_valeur' => $discount,
        ':remise_montant' => $discount_amount,
        ':total_ttc' => $total_ttc,
        ':montant_recu' => $payment_method === 'especes' ? $montant_recu : $total_ttc,
        ':monnaie_rendue' => $monnaie_rendue,
        ':mode_paiement' => $payment_method,
        ':reference_transaction' => $reference_transaction ?: null
    ]);
    
    $id_vente = $bdd->lastInsertId();
    
    // Insertion des lignes de vente
    foreach ($cart as $item) {
        $id_produit_a_inserer = (isset($item['is_manual']) && $item['is_manual']) ? ID_PRODUIT_MANUEL : ($item['id_produit'] ?? null);
        
        $stmt = $bdd->prepare("INSERT INTO danfaniment_lignes_ventes (id_vente, id_produit, code_produit, 
                              nom_produit, quantite, prix_unitaire, total_ligne, created_at) 
                              VALUES (:id_vente, :id_produit, :code_produit, :nom_produit, :quantite, :prix_unitaire, :total_ligne, NOW())");
        $stmt->execute([
            ':id_vente' => $id_vente,
            ':id_produit' => $id_produit_a_inserer,
            ':code_produit' => $item['code_produit'] ?? 'MANUAL',
            ':nom_produit' => $item['nom'],
            ':quantite' => $item['quantite'],
            ':prix_unitaire' => $item['prix_vente'],
            ':total_ligne' => $item['prix_vente'] * $item['quantite']
        ]);
    }
    
    // Mise à jour de la caisse
    $updateCaisseSql = "UPDATE danfaniment_caisses SET 
                          nombre_ventes = nombre_ventes + 1,
                          nombre_tickets = nombre_tickets + 1,
                          total_ventes_brut = total_ventes_brut + :subtotal,
                          total_remises = total_remises + :remise,
                          total_ventes_net = total_ventes_net + :total,
                          dernier_numero_vente = :numero_vente";
    
    if ($payment_method === 'especes') {
        $updateCaisseSql .= ", total_especes = total_especes + :total";
    } elseif ($payment_method === 'carte') {
        $updateCaisseSql .= ", total_carte = total_carte + :total";
    } elseif ($payment_method === 'mobile_money') {
        $updateCaisseSql .= ", total_mobile_money = total_mobile_money + :total";
    } elseif ($payment_method === 'virement') {
        $updateCaisseSql .= ", total_virement = total_virement + :total";
    } elseif ($payment_method === 'avance_confection') {
        $updateCaisseSql .= ", total_avance_confection = total_avance_confection + :total";
    }
    
    $updateCaisseSql .= " WHERE id_caisse = :id_caisse";
    
    $stmt = $bdd->prepare($updateCaisseSql);
    $stmt->execute([
        ':subtotal' => $subtotal,
        ':remise' => $discount_amount,
        ':total' => $total_ttc,
        ':numero_vente' => $numero_vente,
        ':id_caisse' => $id_caisse
    ]);
    
    if ($client_id) {
        $stmt = $bdd->prepare("UPDATE danfaniment_clients SET 
                              total_depense = total_depense + :total,
                              nombre_visites = nombre_visites + 1,
                              points_fidelite = points_fidelite + FLOOR(:total / 1000),
                              date_derniere_visite = NOW(),
                              updated_at = NOW()
                              WHERE id_client = :id_client");
        $stmt->execute([':total' => $total_ttc, ':id_client' => $client_id]);
    }
    
    $bdd->commit();
    
    $ticket_html = generateTicket($numero_vente, $cart, $subtotal, $discount_amount, $total_ttc, $payment_method, $client_id);
    
    unset($_SESSION['pos_cart']);
    
    echo json_encode([
        'success' => true, 
        'id_vente' => $id_vente,
        'numero_vente' => $numero_vente,
        'ticket_html' => $ticket_html
    ]);
    
} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur ajout vente: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}

function generateTicket($numero_vente, $cart, $subtotal, $discount_amount, $total_ttc, $payment_method, $client_id) {
    $date = date('d/m/Y H:i:s');
    $methodLabels = [
        'especes' => 'Espèces',
        'carte' => 'Carte bancaire',
        'mobile_money' => 'Mobile Money',
        'virement' => 'Virement',
        'avance_confection' => 'Avance confection'
    ];
    $methodLabel = $methodLabels[$payment_method] ?? $payment_method;
    
    $html = '<div style="text-align: center; font-family: monospace; font-size: 12px;">';
    $html .= '<h3>DANFANIMENT</h3>';
    $html .= '<p><strong>Ticket de vente</strong></p>';
    $html .= '<hr>';
    $html .= '<p>N°: ' . htmlspecialchars($numero_vente) . '</p>';
    $html .= '<p>Date: ' . $date . '</p>';
    $html .= '<p>Mode: ' . $methodLabel . '</p>';
    if ($client_id) {
        $html .= '<p>Client ID: ' . $client_id . '</p>';
    }
    $html .= '<hr>';
    $html .= '<table style="width: 100%;">';
    $html .= '<tr><th>Article</th><th>Qté</th><th>Prix</th><th>Total</th></tr>';
    
    foreach ($cart as $item) {
        $total = $item['prix_vente'] * $item['quantite'];
        $nom = strlen($item['nom']) > 20 ? substr($item['nom'], 0, 17) . '...' : $item['nom'];
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($nom) . '</td>';
        $html .= '<td style="text-align: center;">' . $item['quantite'] . '</td>';
        $html .= '<td style="text-align: right;">' . number_format($item['prix_vente'], 0, ',', ' ') . '</td>';
        $html .= '<td style="text-align: right;">' . number_format($total, 0, ',', ' ') . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '<tr><td colspan="3" style="text-align: right;"><strong>Sous-total:</strong></td>';
    $html .= '<td style="text-align: right;">' . number_format($subtotal, 0, ',', ' ') . '</td></tr>';
    
    if ($discount_amount > 0) {
        $html .= '<tr><td colspan="3" style="text-align: right;"><strong>Remise:</strong></td>';
        $html .= '<td style="text-align: right;">-' . number_format($discount_amount, 0, ',', ' ') . '</td></tr>';
    }
    
    $html .= '<tr style="font-weight: bold;"><td colspan="3" style="text-align: right;">TOTAL:</td>';
    $html .= '<td style="text-align: right;">' . number_format($total_ttc, 0, ',', ' ') . ' CFA</td></tr>';
    $html .= '<table>';
    $html .= '<hr>';
    $html .= '<p>Merci de votre visite !</p>';
    $html .= '</div>';
    
    return $html;
}
?>