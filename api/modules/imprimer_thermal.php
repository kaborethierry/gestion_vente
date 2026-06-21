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
           CONCAT(COALESCE(c.nom, ''), ' ', COALESCE(c.prenom, '')) AS client_nom
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
    'especes' => 'ESPECES',
    'carte' => 'CARTE',
    'mobile_money' => 'MOBILE MONEY',
    'virement' => 'VIREMENT'
];
$methodLabel = $methodLabels[$vente['mode_paiement']] ?? $vente['mode_paiement'];

$date = date('d/m/Y H:i:s', strtotime($vente['date_vente']));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket - <?php echo $vente['numero_vente']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .brand { font-size: 16px; font-weight: bold; letter-spacing: 2px; }
        hr { border: none; border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 0; }
        .total { font-weight: bold; font-size: 13px; }
        .footer { margin-top: 10px; }
        @media print {
            @page {
                size: 80mm auto;
                margin: 0mm;
            }
            body { margin: 0; padding: 5px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="brand">DANFANIMENT</div>
        <p>Mode & Confection</p>
        <hr>
        <p><strong>TICKET DE VENTE</strong></p>
        <hr>
        <p>N°: <?php echo $vente['numero_vente']; ?></p>
        <p>Date: <?php echo $date; ?></p>
        <p>Caissier: <?php echo htmlspecialchars($vente['caissier'] ?? 'N/A'); ?></p>
        <?php if (!empty($vente['client_nom']) && trim($vente['client_nom']) !== ''): ?>
        <p>Client: <?php echo htmlspecialchars(trim($vente['client_nom'])); ?></p>
        <?php endif; ?>
        <hr>
    </div>

    <table>
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th class="text-left">Article</th>
                <th class="text-right">Qté</th>
                <th class="text-right">Prix</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $ligne): ?>
            <tr>
                <td class="text-left"><?php echo htmlspecialchars(mb_substr($ligne['nom_produit'], 0, 20)); ?></td>
                <td class="text-right"><?php echo $ligne['quantite']; ?></td>
                <td class="text-right"><?php echo number_format($ligne['prix_unitaire'], 0, ',', ' '); ?></td>
                <td class="text-right"><?php echo number_format($ligne['total_ligne'], 0, ',', ' '); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>
    <table>
        <tr><td class="text-left">Sous-total:</td><td class="text-right"><?php echo number_format($vente['sous_total'], 0, ',', ' '); ?> CFA</td></tr>
        <?php if ($vente['remise_montant'] > 0): ?>
        <tr><td class="text-left">Remise:</td><td class="text-right">-<?php echo number_format($vente['remise_montant'], 0, ',', ' '); ?> CFA</td></tr>
        <?php endif; ?>
        <tr class="total"><td class="text-left">TOTAL:</td><td class="text-right"><?php echo number_format($vente['total_ttc'], 0, ',', ' '); ?> CFA</td></tr>
        <tr><td class="text-left">Mode:</td><td class="text-right"><?php echo $methodLabel; ?></td></tr>
        <?php if ($vente['mode_paiement'] === 'especes'): ?>
        <tr><td class="text-left">Montant reçu:</td><td class="text-right"><?php echo number_format($vente['montant_recu'], 0, ',', ' '); ?> CFA</td></tr>
        <tr><td class="text-left">Monnaie rendue:</td><td class="text-right"><?php echo number_format($vente['monnaie_rendue'], 0, ',', ' '); ?> CFA</td></tr>
        <?php endif; ?>
    </table>

    <hr>
    <div class="text-center footer">
        <p>Merci de votre visite !</p>
        <p>*** DANFANIMENT ***</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 15px;">
        <button onclick="imprimerAvecQZ()" style="padding: 10px 20px;">🖨️ Imprimer</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var idVente = <?php echo $id_vente; ?>;
        
        async function imprimerAvecQZ() {
            if (window.opener && window.opener.imprimerTicketQZ) {
                try {
                    await window.opener.imprimerTicketQZ(idVente);
                    window.close();
                } catch(e) {
                    console.error('Erreur QZ:', e);
                    fallbackPrint();
                }
            } else {
                fallbackPrint();
            }
        }
        
        function fallbackPrint() {
            window.print();
            setTimeout(function() {
                window.close();
            }, 500);
        }
        
        window.onload = function() {
            setTimeout(function() {
                imprimerAvecQZ();
            }, 100);
        };
    </script>
</body>
</html>