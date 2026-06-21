<?php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "caissier")) {
    die('Accès non autorisé');
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_paiement = intval($_GET['id_paiement'] ?? 0);

if ($id_paiement <= 0) {
    die('Paiement non trouvé');
}

$stmt = $bdd->prepare("
    SELECT 
        p.*,
        c.numero_commande,
        CONCAT(cl.nom, ' ', cl.prenom) AS client_nom,
        cl.telephone,
        cl.adresse,
        u.nom_complet AS caissier,
        c.montant_total,
        c.solde_restant,
        (SELECT COALESCE(SUM(montant), 0) FROM danfaniment_paiements_confection WHERE id_commande = c.id_commande) AS total_paye
    FROM danfaniment_paiements_confection p
    INNER JOIN danfaniment_commandes_confection c ON p.id_commande = c.id_commande
    INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
    INNER JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
    WHERE p.id_paiement = :id
");
$stmt->execute([':id' => $id_paiement]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    die('Paiement non trouvé');
}

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

// Calculer le solde après ce paiement
$montant_total = floatval($p['montant_total']);
$total_paye = floatval($p['total_paye']);
$solde_apres = $montant_total - $total_paye;
$est_solde = $solde_apres <= 0;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu - <?php echo htmlspecialchars($p['numero_recu']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            width: 80mm;
            background: white;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-success {
            color: #10B981;
        }
        .text-warning {
            color: #F59E0B;
        }
        .text-danger {
            color: #DC2626;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
        }
        @media print {
            @page {
                size: 80mm auto;
                margin: 0mm;
            }
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(function(){window.close();}, 1000);">
    <div class="text-center">
        <h3>DANFANIMENT</h3>
        <p><strong>REÇU DE PAIEMENT</strong></p>
        <p>N°: <?php echo htmlspecialchars($p['numero_recu']); ?></p>
        <p>Date: <?php echo date('d/m/Y H:i:s', strtotime($p['created_at'])); ?></p>
        <hr>
        <p>Commande: <strong><?php echo htmlspecialchars($p['numero_commande']); ?></strong></p>
        <p>Client: <?php echo htmlspecialchars($p['client_nom']); ?></p>
        <?php if ($p['telephone']): ?>
        <p>Tél: <?php echo htmlspecialchars($p['telephone']); ?></p>
        <?php endif; ?>
        <hr>
        <table>
            <tr>
                <td>Montant total:</td>
                <td class="text-right"><?php echo number_format($p['montant_total'], 0, ',', ' '); ?> CFA</td>
            </tr>
            <tr>
                <td>Type paiement:</td>
                <td class="text-right"><?php echo $typeLabels[$p['type_paiement']]; ?></td>
            </tr>
            <tr>
                <td>Mode paiement:</td>
                <td class="text-right"><?php echo $modeLabels[$p['mode_paiement']]; ?></td>
            </tr>
            <?php if ($p['reference_transaction']): ?>
            <tr>
                <td>Réf. transaction:</td>
                <td class="text-right"><?php echo htmlspecialchars($p['reference_transaction']); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total">
                <td><strong>Montant versé:</strong></td>
                <td class="text-right"><strong><?php echo number_format($p['montant'], 0, ',', ' '); ?> CFA</strong></td>
            </tr>
            <tr>
                <td>Solde après paiement:</td>
                <td class="text-right <?php echo $est_solde ? 'text-success' : ($solde_apres < 0 ? 'text-danger' : 'text-warning'); ?>">
                    <strong>
                        <?php 
                        if ($solde_apres <= 0) {
                            echo '0 CFA (Soldé)';
                        } else {
                            echo number_format($solde_apres, 0, ',', ' ') . ' CFA restant';
                        }
                        ?>
                    </strong>
                </td>
            </tr>
        </table>
        <hr>
        <p>Caissier: <?php echo htmlspecialchars($p['caissier']); ?></p>
        <?php if ($p['remarques']): ?>
        <p>Notes: <?php echo htmlspecialchars($p['remarques']); ?></p>
        <?php endif; ?>
        <hr>
        <p>Merci de votre confiance !</p>
        <p>À bientôt chez DANFANIMENT</p>
        <br>
        <p style="font-size: 10px;">Ce reçu fait office de justificatif de paiement</p>
    </div>
</body>
</html>