<?php
session_start();

if (empty($_SESSION['id'])) {
    die('Accès non autorisé');
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_vente = intval($_GET['id_vente'] ?? 0);

if ($id_vente <= 0) {
    die('Vente non trouvée');
}

$stmt = $bdd->prepare("
    SELECT v.*, u.nom_complet AS caissier, 
           CONCAT(COALESCE(c.nom, ''), ' ', COALESCE(c.prenom, '')) AS client_nom,
           c.telephone as client_telephone
    FROM danfaniment_ventes v
    LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
    LEFT JOIN danfaniment_clients c ON v.id_client = c.id_client
    WHERE v.id_vente = :id
");
$stmt->execute([':id' => $id_vente]);
$vente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vente) {
    die('Vente non trouvée');
}

$stmt = $bdd->prepare("SELECT * FROM danfaniment_lignes_ventes WHERE id_vente = :id");
$stmt->execute([':id' => $id_vente]);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$methodLabels = [
    'especes' => 'Espèces',
    'carte' => 'Carte bancaire',
    'mobile_money' => 'Mobile Money',
    'virement' => 'Virement',
    'avance_confection' => 'Avance confection'
];
?>
<style>
    .details-container {
        font-family: monospace;
        font-size: 13px;
    }
    .details-container table {
        width: 100%;
        border-collapse: collapse;
    }
    .details-container th, .details-container td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #e3e6f0;
    }
    .details-container .total-row {
        font-weight: bold;
        background: #f8f9fc;
    }
    .badge-success { background: #10B981; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; }
    .badge-warning { background: #F59E0B; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; }
    .badge-danger { background: #DC2626; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; }
</style>
<div class="details-container">
    <div style="text-align: center; margin-bottom: 15px;">
        <h4>DANFANIMENT</h4>
        <p><strong>Ticket de vente N° <?php echo htmlspecialchars($vente['numero_vente']); ?></strong></p>
    </div>
    
    <table>
        <tr><td width="40%"><strong>Date:</strong></td><td><?php echo date('d/m/Y H:i:s', strtotime($vente['date_vente'])); ?></td></tr>
        <tr><td><strong>Caissier:</strong></td><td><?php echo htmlspecialchars($vente['caissier'] ?? 'N/A'); ?></td></tr>
        <tr><td><strong>Client:</strong></td><td><?php echo !empty($vente['client_nom']) ? htmlspecialchars(trim($vente['client_nom'])) : '<em>Client anonyme</em>'; ?></td></tr>
        <?php if (!empty($vente['client_telephone'])): ?>
        <tr><td><strong>Téléphone:</strong></td><td><?php echo htmlspecialchars($vente['client_telephone']); ?></td></tr>
        <?php endif; ?>
        <tr><td><strong>Mode de paiement:</strong></td><td><?php echo $methodLabels[$vente['mode_paiement']] ?? $vente['mode_paiement']; ?></td></tr>
        <?php if (!empty($vente['reference_transaction'])): ?>
        <tr><td><strong>Réf. transaction:</strong></td><td><?php echo htmlspecialchars($vente['reference_transaction']); ?></td></tr>
        <?php endif; ?>
        <tr><td><strong>Statut:</strong></td><td><span class="badge-<?php echo $vente['statut'] === 'valide' ? 'success' : ($vente['statut'] === 'annule' ? 'danger' : 'warning'); ?>"><?php echo $vente['statut']; ?></span></td></tr>
    </table>
    
    <hr>
    
    <table>
        <thead>
            <tr><th>Article</th><th class="text-center">Qté</th><th class="text-right">Prix</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $ligne): ?>
            <tr>
                <td><?php echo htmlspecialchars($ligne['nom_produit']); ?></td>
                <td class="text-center"><?php echo $ligne['quantite']; ?></td>
                <td class="text-right"><?php echo number_format($ligne['prix_unitaire'], 0, ',', ' '); ?> CFA</td>
                <td class="text-right"><?php echo number_format($ligne['total_ligne'], 0, ',', ' '); ?> CFA</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="text-right"><strong>Sous-total:</strong></td><td class="text-right"><?php echo number_format($vente['sous_total'], 0, ',', ' '); ?> CFA</td></tr>
            <?php if ($vente['remise_montant'] > 0): ?>
            <tr><td colspan="3" class="text-right"><strong>Remise:</strong></td><td class="text-right">-<?php echo number_format($vente['remise_montant'], 0, ',', ' '); ?> CFA</td></tr>
            <?php endif; ?>
            <tr class="total-row"><td colspan="3" class="text-right"><strong>TOTAL:</strong></td><td class="text-right"><strong><?php echo number_format($vente['total_ttc'], 0, ',', ' '); ?> CFA</strong></td></tr>
            <?php if ($vente['montant_recu'] > 0 && $vente['mode_paiement'] === 'especes'): ?>
            <tr><td colspan="3" class="text-right">Montant reçu:</td><td class="text-right"><?php echo number_format($vente['montant_recu'], 0, ',', ' '); ?> CFA</td></tr>
            <tr><td colspan="3" class="text-right">Monnaie rendue:</td><td class="text-right"><?php echo number_format($vente['monnaie_rendue'], 0, ',', ' '); ?> CFA</td></tr>
            <?php endif; ?>
        </tfoot>
    </table>
    
    <hr>
    <div class="text-center">
        <p>Merci de votre visite !</p>
        <p><strong>DANFANIMENT - Mode & Confection</strong></p>
    </div>
</div>