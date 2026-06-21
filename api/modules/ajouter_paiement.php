<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_commande = intval($_POST['id_commande'] ?? 0);
$montant = floatval($_POST['montant'] ?? 0);
$type_paiement = $_POST['type_paiement'] ?? 'avance';
$mode_paiement = $_POST['mode_paiement'] ?? 'especes';
$reference_transaction = trim($_POST['reference_transaction'] ?? '');
$remarques = trim($_POST['remarques'] ?? '');

if ($id_commande <= 0 || $montant <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Vérifier le solde restant
    $stmt = $bdd->prepare("SELECT montant_total, solde_restant, numero_commande, montant_avance FROM danfaniment_commandes_confection WHERE id_commande = :id");
    $stmt->execute([':id' => $id_commande]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$commande) {
        throw new Exception("Commande non trouvée");
    }
    
    $solde_restant = floatval($commande['solde_restant']);
    
    // Vérification : ne pas dépasser le solde restant
    if ($montant > $solde_restant) {
        // Si le solde est négatif, on ne peut pas ajouter de paiement
        if ($solde_restant < 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Cette commande a déjà un dépassement de ' . number_format(abs($solde_restant), 0, ',', ' ') . ' CFA. Veuillez corriger les paiements existants.'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => false, 
            'message' => 'Le montant (' . number_format($montant, 0, ',', ' ') . ' CFA) dépasse le solde restant (' . number_format($solde_restant, 0, ',', ' ') . ' CFA).'
        ]);
        exit;
    }
    
    // Générer le numéro de reçu
    $stmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_paiements_confection WHERE DATE(created_at) = CURDATE()");
    $paiements_jour = $stmt->fetchColumn();
    $numero_recu = 'RECU-' . date('Ymd') . '-' . str_pad($paiements_jour + 1, 4, '0', STR_PAD_LEFT);
    
    // Insérer le paiement
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_paiements_confection 
        (id_commande, id_utilisateur, montant, type_paiement, mode_paiement, reference_transaction, numero_recu, remarques, created_at) 
        VALUES 
        (:id_commande, :id_utilisateur, :montant, :type_paiement, :mode_paiement, :reference_transaction, :numero_recu, :remarques, NOW())
    ");
    $stmt->execute([
        ':id_commande' => $id_commande,
        ':id_utilisateur' => $_SESSION['id'],
        ':montant' => $montant,
        ':type_paiement' => $type_paiement,
        ':mode_paiement' => $mode_paiement,
        ':reference_transaction' => $reference_transaction ?: null,
        ':numero_recu' => $numero_recu,
        ':remarques' => $remarques ?: null
    ]);
    
    $id_paiement = $bdd->lastInsertId();
    
    // Recalculer le total des paiements pour cette commande
    $stmt = $bdd->prepare("SELECT COALESCE(SUM(montant), 0) FROM danfaniment_paiements_confection WHERE id_commande = :id_commande");
    $stmt->execute([':id_commande' => $id_commande]);
    $total_paye = floatval($stmt->fetchColumn());
    $montant_total = floatval($commande['montant_total']);
    $nouveau_solde = $montant_total - $total_paye;
    
    // Mettre à jour la commande avec les valeurs correctes
    $stmt = $bdd->prepare("
        UPDATE danfaniment_commandes_confection 
        SET montant_avance = :total_paye,
            solde_restant = :nouveau_solde,
            updated_at = NOW()
        WHERE id_commande = :id_commande
    ");
    $stmt->execute([
        ':total_paye' => $total_paye,
        ':nouveau_solde' => $nouveau_solde,
        ':id_commande' => $id_commande
    ]);
    
    // Si le solde est à zéro, marquer la commande comme terminée
    if ($nouveau_solde <= 0) {
        $stmt = $bdd->prepare("
            UPDATE danfaniment_commandes_confection 
            SET statut = 'termine',
                updated_at = NOW()
            WHERE id_commande = :id_commande AND statut != 'termine'
        ");
        $stmt->execute([':id_commande' => $id_commande]);
    }
    
    $bdd->commit();
    
    // Générer le reçu HTML
    $recu_html = genererRecu($id_paiement, $bdd);
    
    echo json_encode([
        'success' => true,
        'id_paiement' => $id_paiement,
        'numero_recu' => $numero_recu,
        'recu_html' => $recu_html
    ]);
    
} catch (Exception $e) {
    $bdd->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function genererRecu($id_paiement, $bdd) {
    $stmt = $bdd->prepare("
        SELECT 
            p.*,
            c.numero_commande,
            CONCAT(cl.nom, ' ', cl.prenom) AS client_nom,
            cl.telephone,
            u.nom_complet AS caissier,
            c.montant_total,
            c.solde_restant
        FROM danfaniment_paiements_confection p
        INNER JOIN danfaniment_commandes_confection c ON p.id_commande = c.id_commande
        INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        INNER JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
        WHERE p.id_paiement = :id
    ");
    $stmt->execute([':id' => $id_paiement]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $typeLabels = [
        'avance' => 'Avance',
        'acompte_supplementaire' => 'Acompte supplémentaire',
        'solde' => 'Solde final'
    ];
    
    $modeLabels = [
        'especes' => 'Espèces',
        'carte' => 'Carte bancaire',
        'mobile_money' => 'Mobile Money',
        'virement' => 'Virement'
    ];
    
    $solde_affiche = floatval($p['solde_restant']);
    $solde_class = $solde_affiche < 0 ? 'text-danger' : ($solde_affiche == 0 ? 'text-success' : 'text-warning');
    $solde_texte = $solde_affiche < 0 ? 'Dépassement de ' . number_format(abs($solde_affiche), 0, ',', ' ') . ' CFA' : ($solde_affiche == 0 ? 'Soldé' : number_format($solde_affiche, 0, ',', ' ') . ' CFA restant');
    
    $html = '<div style="text-align: center; font-family: monospace; font-size: 12px; width: 80mm;">';
    $html .= '<h3>DANFANIMENT</h3>';
    $html .= '<p><strong>REÇU DE PAIEMENT</strong></p>';
    $html .= '<p>N°: ' . htmlspecialchars($p['numero_recu']) . '</p>';
    $html .= '<p>Date: ' . date('d/m/Y H:i:s', strtotime($p['created_at'])) . '</p>';
    $html .= '<hr>';
    $html .= '<p>Commande: <strong>' . htmlspecialchars($p['numero_commande']) . '</strong></p>';
    $html .= '<p>Client: ' . htmlspecialchars($p['client_nom']) . '</p>';
    if ($p['telephone']) {
        $html .= '<p>Tél: ' . htmlspecialchars($p['telephone']) . '</p>';
    }
    $html .= '<hr>';
    $html .= '<table style="width: 100%;">';
    $html .= '<tr><td>Montant total:↕<td class="text-right">' . number_format($p['montant_total'], 0, ',', ' ') . ' CFA↕</tr>';
    $html .= '<tr><td>Type paiement:↕<td class="text-right">' . $typeLabels[$p['type_paiement']] . '↕</tr>';
    $html .= '<tr><td>Mode paiement:↕<td class="text-right">' . $modeLabels[$p['mode_paiement']] . '↕</tr>';
    $html .= '<tr><td><strong>Montant versé:</strong>↕<td class="text-right"><strong>' . number_format($p['montant'], 0, ',', ' ') . ' CFA</strong>↕</tr>';
    $html .= '<tr><td>Solde:↕<td class="text-right ' . $solde_class . '"><strong>' . $solde_texte . '</strong>↕</tr>';
    $html .= '</table>';
    $html .= '<hr>';
    $html .= '<p>Caissier: ' . htmlspecialchars($p['caissier']) . '</p>';
    if ($p['reference_transaction']) {
        $html .= '<p>Réf: ' . htmlspecialchars($p['reference_transaction']) . '</p>';
    }
    if ($p['remarques']) {
        $html .= '<p>Notes: ' . htmlspecialchars($p['remarques']) . '</p>';
    }
    $html .= '<hr>';
    $html .= '<p>Merci de votre confiance !</p>';
    $html .= '<p>À bientôt chez DANFANIMENT</p>';
    $html .= '<br>';
    $html .= '<p style="font-size: 10px;">Ce reçu fait office de justificatif de paiement</p>';
    $html .= '</div>';
    
    return $html;
}
?>