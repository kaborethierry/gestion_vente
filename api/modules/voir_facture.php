<?php
// api/modules/voir_facture.php
session_start();

if (empty($_SESSION['id'])) {
    die('Accès non autorisé');
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_facture = (int)($_GET['id_facture'] ?? 0);

if ($id_facture <= 0) {
    die('Facture non trouvée');
}

// Récupérer la facture
$stmt = $bdd->prepare("
    SELECT f.*, u.nom_complet as created_by,
           CONCAT(COALESCE(c.nom, ''), ' ', COALESCE(c.prenom, '')) as client_nom,
           c.adresse as client_adresse, c.telephone as client_telephone, c.email as client_email
    FROM danfaniment_factures f
    LEFT JOIN utilisateurs u ON f.id_utilisateur = u.id_utilisateur
    LEFT JOIN danfaniment_clients c ON f.id_client = c.id_client
    WHERE f.id_facture = :id
");
$stmt->execute([':id' => $id_facture]);
$facture = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    die('Facture non trouvée');
}

// Récupérer les lignes
$stmt = $bdd->prepare("SELECT * FROM danfaniment_facture_lignes WHERE id_facture = :id");
$stmt->execute([':id' => $id_facture]);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statutLabels = [
    'brouillon' => 'Brouillon',
    'envoyee' => 'Envoyée',
    'payee' => 'Payée',
    'annulee' => 'Annulée'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facture <?php echo $facture['numero_facture']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .facture-container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
        }
        .header { margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #DC2626; }
        .facture-title { font-size: 20px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #DC2626; padding-bottom: 10px; }
        .entreprise-info { float: left; width: 50%; }
        .facture-info { float: right; width: 45%; text-align: right; }
        .client-info { margin: 20px 0; padding: 15px; background: #f8f9fc; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e3e6f0; }
        th { background: #f8f9fc; font-weight: bold; }
        .text-right { text-align: right; }
        .totaux { float: right; width: 300px; margin-top: 20px; }
        .totaux table { width: 100%; }
        .totaux td { border: none; padding: 5px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #6c757d; border-top: 1px solid #e3e6f0; padding-top: 20px; }
        .clearfix { clear: both; }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="facture-container">
    <div class="header">
        <div class="entreprise-info">
            <div class="logo">DANFANIMENT</div>
            <p>Mode & Confection<br>
            Ouagadougou, Burkina Faso<br>
            Tél: +226 74 50 41 41</p>
        </div>
        <div class="facture-info">
            <div class="facture-title">FACTURE</div>
            <p><strong>N°:</strong> <?php echo $facture['numero_facture']; ?><br>
            <strong>Date:</strong> <?php echo date('d/m/Y', strtotime($facture['date_facture'])); ?><br>
            <?php if ($facture['date_echeance']): ?>
            <strong>Échéance:</strong> <?php echo date('d/m/Y', strtotime($facture['date_echeance'])); ?><br>
            <?php endif; ?>
            <strong>Statut:</strong> <span style="color: <?php echo $facture['statut'] === 'payee' ? '#10B981' : ($facture['statut'] === 'annulee' ? '#DC2626' : '#F59E0B'); ?>"><?php echo $statutLabels[$facture['statut']] ?? $facture['statut']; ?></span></p>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="client-info">
        <strong>Client:</strong><br>
        <?php if ($facture['client_nom']): ?>
            <?php echo htmlspecialchars($facture['client_nom']); ?><br>
            <?php echo htmlspecialchars($facture['client_adresse'] ?? ''); ?><br>
            Tél: <?php echo htmlspecialchars($facture['client_telephone'] ?? ''); ?>
        <?php else: ?>
            Client non renseigné
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Description</th>
                <th class="text-right">Qté</th>
                <th class="text-right">Prix HT</th>
                <th class="text-right">Remise</th>
                <th class="text-right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $ligne): ?>
            <tr>
                <td><?php echo htmlspecialchars($ligne['designation']); ?></td>
                <td><?php echo htmlspecialchars($ligne['description'] ?? '-'); ?></td>
                <td class="text-right"><?php echo number_format($ligne['quantite'], 2, ',', ' '); ?></td>
                <td class="text-right"><?php echo number_format($ligne['prix_unitaire_ht'], 0, ',', ' '); ?> FCFA</td>
                <td class="text-right"><?php echo number_format($ligne['remise_ligne'], 0, ',', ' '); ?> FCFA</td>
                <td class="text-right"><?php echo number_format($ligne['total_ht'], 0, ',', ' '); ?> FCFA</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totaux">
        <table>
            <tr><td>Sous-total HT:</td><td class="text-right"><?php echo number_format($facture['sous_total'], 0, ',', ' '); ?> FCFA</td></tr>
            <?php if ($facture['remise_montant'] > 0): ?>
            <tr><td>Remise:</td><td class="text-right">-<?php echo number_format($facture['remise_montant'], 0, ',', ' '); ?> FCFA</td></tr>
            <?php endif; ?>
            <tr><td>Total HT:</td><td class="text-right"><?php echo number_format($facture['total_ht'], 0, ',', ' '); ?> FCFA</td></tr>
            <tr><td>TVA (<?php echo $facture['taux_tva']; ?>%):</td><td class="text-right"><?php echo number_format($facture['montant_tva'], 0, ',', ' '); ?> FCFA</td></tr>
            <tr style="font-weight: bold; font-size: 14px;"><td>TOTAL TTC:</td><td class="text-right"><?php echo number_format($facture['total_ttc'], 0, ',', ' '); ?> FCFA</td></tr>
        </table>
    </div>

    <div class="clearfix"></div>

    <?php if ($facture['conditions_reglement']): ?>
    <div style="margin-top: 30px; padding: 10px; background: #f8f9fc; border-radius: 8px;">
        <strong>Conditions de règlement:</strong><br>
        <?php echo nl2br(htmlspecialchars($facture['conditions_reglement'])); ?>
    </div>
    <?php endif; ?>

    <?php if ($facture['notes']): ?>
    <div style="margin-top: 15px; padding: 10px; background: #f8f9fc; border-radius: 8px;">
        <strong>Notes:</strong><br>
        <?php echo nl2br(htmlspecialchars($facture['notes'])); ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Merci de votre confiance !</p>
        <p>DANFANIMENT - Mode & Confection</p>
    </div>
</div>
</body>
</html>