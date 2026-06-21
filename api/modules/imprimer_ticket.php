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

// Récupérer les infos de la vente
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
    'especes' => 'Espèces',
    'carte' => 'Carte bancaire',
    'mobile_money' => 'Mobile Money',
    'virement' => 'Virement',
    'avance_confection' => 'Avance confection'
];

$date_vente = $vente['date_vente'] ?? $vente['created_at'] ?? date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de vente - <?php echo htmlspecialchars($vente['numero_vente']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            width: 80mm;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px 0;
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
        .brand {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .footer {
            margin-top: 10px;
        }
        .btn-print {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #DC2626;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }
        .btn-print:hover {
            background: #B91C1C;
        }
        @media print {
            @page {
                size: 80mm auto;
                margin: 0mm;
            }
            body {
                margin: 0;
                padding: 5px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="brand">DANFANIMENT</div>
        <p>Mode & Confection</p>
        <hr>
        <p><strong>Ticket de vente</strong></p>
        <hr>
        <p>N°: <?php echo htmlspecialchars($vente['numero_vente']); ?></p>
        <p>Date: <?php echo date('d/m/Y H:i:s', strtotime($date_vente)); ?></p>
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
            <?php if (empty($lignes)): ?>
            <tr>
                <td colspan="4" class="text-center">Aucun détail</td>
            </tr>
            <?php else: ?>
                <?php foreach ($lignes as $ligne): ?>
                <tr>
                    <td class="text-left"><?php echo htmlspecialchars(mb_substr($ligne['nom_produit'], 0, 20)); ?></td>
                    <td class="text-right"><?php echo (int)$ligne['quantite']; ?></td>
                    <td class="text-right"><?php echo number_format((float)$ligne['prix_unitaire'], 0, ',', ' '); ?></td>
                    <td class="text-right"><?php echo number_format((float)$ligne['total_ligne'], 0, ',', ' '); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <hr>
    
    <table>
        <tr>
            <td class="text-left"><strong>Sous-total:</strong></td>
            <td class="text-right"><?php echo number_format((float)$vente['sous_total'], 0, ',', ' '); ?> CFA</td>
        </tr>
        <?php if ((float)$vente['remise_montant'] > 0): ?>
        <tr>
            <td class="text-left"><strong>Remise:</strong></td>
            <td class="text-right">-<?php echo number_format((float)$vente['remise_montant'], 0, ',', ' '); ?> CFA</td>
        </tr>
        <?php endif; ?>
        <tr class="total">
            <td class="text-left"><strong>TOTAL:</strong></td>
            <td class="text-right"><strong><?php echo number_format((float)$vente['total_ttc'], 0, ',', ' '); ?> CFA</strong></td>
        </tr>
        <tr>
            <td class="text-left"><strong>Mode:</strong></td>
            <td class="text-right"><?php echo $methodLabels[$vente['mode_paiement']] ?? $vente['mode_paiement']; ?></td>
        </tr>
        <?php if ($vente['mode_paiement'] === 'especes'): ?>
        <tr>
            <td class="text-left">Montant reçu:</td>
            <td class="text-right"><?php echo number_format((float)$vente['montant_recu'], 0, ',', ' '); ?> CFA</td>
        </tr>
        <tr>
            <td class="text-left">Monnaie rendue:</td>
            <td class="text-right"><?php echo number_format((float)$vente['monnaie_rendue'], 0, ',', ' '); ?> CFA</td>
        </tr>
        <?php elseif (!empty($vente['reference_transaction'])): ?>
        <tr>
            <td class="text-left">Réf. transaction:</td>
            <td class="text-right"><?php echo htmlspecialchars($vente['reference_transaction']); ?></td>
        </tr>
        <?php endif; ?>
    </table>
    
    <hr>
    
    <div class="text-center footer">
        <p>Merci de votre visite !</p>
        <p>À bientôt chez DANFANIMENT</p>
        <p>***</p>
    </div>
    
    <button class="btn-print no-print" onclick="imprimerAvecQZ()">🖨️ Imprimer le ticket</button>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var idVente = <?php echo $id_vente; ?>;
        
        async function imprimerAvecQZ() {
            // Vérifier si la fonction parente existe (ouverte depuis POS)
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
            Swal.fire({
                title: 'Impression standard',
                text: 'QZ Tray non disponible, utilisation de l\'impression standard',
                icon: 'info',
                timer: 1500,
                showConfirmButton: false
            });
            setTimeout(function() {
                window.print();
            }, 500);
        }
        
        // Essayer d'abord QZ Tray, sinon fallback
        setTimeout(function() {
            imprimerAvecQZ();
        }, 100);
        
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 500);
        };
        
        setTimeout(function() {
            if (window.matchMedia) {
                var mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function(mql) {
                    if (!mql.matches) {
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    }
                });
            }
        }, 1000);
    </script>
</body>
</html>