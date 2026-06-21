<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_vente = intval($_GET['id_vente'] ?? 0);
$type = $_GET['type'] ?? 'ticket';

if ($id_vente <= 0 && $type !== 'recu') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID vente invalide']);
    exit;
}

// Si c'est un reçu de paiement confection
if ($type === 'recu' && isset($_GET['id_paiement'])) {
    $id_paiement = intval($_GET['id_paiement']);
    
    $stmt = $bdd->prepare("
        SELECT 
            p.*,
            c.numero_commande,
            CONCAT(cl.nom, ' ', cl.prenom) AS client_nom,
            cl.telephone,
            u.nom_complet AS caissier,
            c.montant_total,
            (SELECT COALESCE(SUM(montant), 0) FROM danfaniment_paiements_confection WHERE id_commande = c.id_commande) AS total_paye
        FROM danfaniment_paiements_confection p
        INNER JOIN danfaniment_commandes_confection c ON p.id_commande = c.id_commande
        INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        INNER JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
        WHERE p.id_paiement = :id
    ");
    $stmt->execute([':id' => $id_paiement]);
    $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paiement) {
        echo json_encode(['success' => false, 'message' => 'Paiement non trouvé']);
        exit;
    }
    
    $montant_total = floatval($paiement['montant_total']);
    $total_paye = floatval($paiement['total_paye']);
    $solde_apres = $montant_total - $total_paye;
    $est_solde = $solde_apres <= 0;
    
    $typeLabels = ['avance' => 'AVANCE', 'acompte_supplementaire' => 'ACOMPTE', 'solde' => 'SOLDE'];
    $modeLabels = ['especes' => 'ESPECES', 'carte' => 'CARTE', 'mobile_money' => 'MOBILE MONEY', 'virement' => 'VIREMENT'];
    
    $commands = "";
    $commands .= "\x1B\x40";
    $commands .= "\x1B\x61\x01";
    $commands .= "DANFANIMENT\n";
    $commands .= "Mode & Confection\n";
    $commands .= str_repeat("-", 32) . "\n";
    $commands .= "RECU DE PAIEMENT\n";
    $commands .= str_repeat("-", 32) . "\n";
    $commands .= "\x1B\x61\x00";
    $commands .= "N°: " . $paiement['numero_recu'] . "\n";
    $commands .= "Date: " . date('d/m/Y H:i', strtotime($paiement['created_at'])) . "\n";
    $commands .= "Commande: " . $paiement['numero_commande'] . "\n";
    $commands .= "Client: " . $paiement['client_nom'] . "\n";
    if ($paiement['telephone']) {
        $commands .= "Tel: " . $paiement['telephone'] . "\n";
    }
    $commands .= str_repeat("-", 32) . "\n";
    $commands .= "Montant total: " . number_format($montant_total, 0, '', ' ') . " F\n";
    $commands .= "Type: " . ($typeLabels[$paiement['type_paiement']] ?? $paiement['type_paiement']) . "\n";
    $commands .= "Mode: " . ($modeLabels[$paiement['mode_paiement']] ?? $paiement['mode_paiement']) . "\n";
    if ($paiement['reference_transaction']) {
        $commands .= "Ref: " . $paiement['reference_transaction'] . "\n";
    }
    $commands .= str_repeat("-", 32) . "\n";
    $commands .= "\x1B\x61\x01";
    $commands .= "MONTANT VERSE: " . number_format($paiement['montant'], 0, '', ' ') . " F\n";
    if ($est_solde) {
        $commands .= "COMMANDE SOLDEE !\n";
    } else {
        $commands .= "Solde restant: " . number_format($solde_apres, 0, '', ' ') . " F\n";
    }
    $commands .= str_repeat("-", 32) . "\n";
    $commands .= "Caissier: " . $paiement['caissier'] . "\n";
    if ($paiement['remarques']) {
        $commands .= "Notes: " . $paiement['remarques'] . "\n";
    }
    $commands .= str_repeat("-", 32) . "\n";
    $commands .= "\x1B\x61\x01";
    $commands .= "Merci de votre confiance !\n";
    $commands .= "*** DANFANIMENT ***\n\n";
    $commands .= "\x1B\x61\x00";
    $commands .= "\x1B\x64\x03";
    $commands .= "\x1B\x6D";
    
    $commands = mb_convert_encoding($commands, 'CP437', 'UTF-8');
    
    echo json_encode([
        'success' => true,
        'commands' => base64_encode($commands),
        'printer_name' => 'POSPrinter POS80'
    ]);
    exit;
}

// Sinon, c'est un ticket de vente normal
$stmt = $bdd->prepare("
    SELECT v.*, u.nom_complet AS caissier,
           CONCAT(COALESCE(c.nom, ''), ' ', COALESCE(c.prenom, '')) AS client_nom
    FROM danfaniment_ventes v
    LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
    LEFT JOIN danfaniment_clients c ON v.id_client = c.id_client
    WHERE v.id_vente = :id
");
$stmt->execute([':id' => $id_vente]);
$vente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vente) {
    echo json_encode(['success' => false, 'message' => 'Vente non trouvée']);
    exit;
}

$stmt = $bdd->prepare("SELECT * FROM danfaniment_lignes_ventes WHERE id_vente = :id");
$stmt->execute([':id' => $id_vente]);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Générer les commandes ESC/POS
$commands = "";
$commands .= "\x1B\x40";
$commands .= "\x1B\x61\x01";
$commands .= "DANFANIMENT\n";
$commands .= "Mode & Confection\n";
$commands .= str_repeat("-", 32) . "\n";
$commands .= "TICKET DE VENTE\n";
$commands .= str_repeat("-", 32) . "\n";
$commands .= "\x1B\x61\x00";
$commands .= "N°: " . $vente['numero_vente'] . "\n";
$commands .= "Date: " . date('d/m/Y H:i', strtotime($vente['date_vente'])) . "\n";
$commands .= "Caissier: " . ($vente['caissier'] ?? 'N/A') . "\n";
if (!empty($vente['client_nom']) && trim($vente['client_nom']) !== '') {
    $commands .= "Client: " . trim($vente['client_nom']) . "\n";
}
$commands .= str_repeat("-", 32) . "\n";

foreach ($lignes as $ligne) {
    $nom = mb_substr($ligne['nom_produit'], 0, 18);
    $qte = $ligne['quantite'];
    $prix = number_format($ligne['prix_unitaire'], 0, '', '');
    $total = number_format($ligne['total_ligne'], 0, '', '');
    $commands .= sprintf("%-18s %2s x %6s = %8s\n", $nom, $qte, $prix, $total);
}

$commands .= str_repeat("-", 32) . "\n";
$commands .= sprintf("%-20s %12s\n", "Sous-total:", number_format($vente['sous_total'], 0, '', '') . " F");
if ($vente['remise_montant'] > 0) {
    $commands .= sprintf("%-20s %12s\n", "Remise:", "-" . number_format($vente['remise_montant'], 0, '', '') . " F");
}
$commands .= sprintf("%-20s %12s\n", "TOTAL:", number_format($vente['total_ttc'], 0, '', '') . " F");
$commands .= str_repeat("-", 32) . "\n";

$methodLabels = ['especes' => 'ESPECES', 'carte' => 'CARTE', 'mobile_money' => 'MOBILE MONEY'];
$commands .= "Mode: " . ($methodLabels[$vente['mode_paiement']] ?? $vente['mode_paiement']) . "\n";
if ($vente['mode_paiement'] === 'especes') {
    $commands .= "Recu: " . number_format($vente['montant_recu'], 0, '', '') . " F\n";
    $commands .= "Rendu: " . number_format($vente['monnaie_rendue'], 0, '', '') . " F\n";
}
$commands .= str_repeat("-", 32) . "\n";
$commands .= "\x1B\x61\x01";
$commands .= "Merci de votre visite !\n";
$commands .= "*** DANFANIMENT ***\n\n";
$commands .= "\x1B\x61\x00";
$commands .= "\x1B\x64\x03";
$commands .= "\x1B\x6D";

$commands = mb_convert_encoding($commands, 'CP437', 'UTF-8');

echo json_encode([
    'success' => true,
    'commands' => base64_encode($commands),
    'printer_name' => 'POSPrinter POS80'
]);