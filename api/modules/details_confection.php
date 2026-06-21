<?php
// api/modules/details_confection.php
// DANFANIMENT POS - Détails d'une commande confection

session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo '<div class="alert alert-danger">Accès refusé</div>';
    exit;
}

if (empty($_GET['id_commande'])) {
    echo '<div class="alert alert-danger">ID commande manquant</div>';
    exit;
}

$id_commande = (int)$_GET['id_commande'];
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Récupérer les détails de la commande et du client
    $sql = "
        SELECT 
            c.*,
            DATE_FORMAT(c.date_commande, '%d/%m/%Y %H:%i') AS date_commande_formatee,
            DATE_FORMAT(c.date_livraison_prevue, '%d/%m/%Y') AS date_livraison_prevue_formatee,
            DATE_FORMAT(c.date_livraison_reelle, '%d/%m/%Y %H:%i') AS date_livraison_reelle_formatee,
            DATE_FORMAT(c.statut_changed_at, '%d/%m/%Y %H:%i') AS statut_changed_at_formatee,
            cl.nom AS client_nom,
            cl.prenom AS client_prenom,
            cl.telephone AS client_telephone,
            cl.email AS client_email,
            u.nom_utilisateur AS created_by
        FROM danfaniment_commandes_confection c
        LEFT JOIN danfaniment_clients cl ON c.id_client = cl.id_client
        LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
        WHERE c.id_commande = :id
    ";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([':id' => $id_commande]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$commande) {
        echo '<div class="alert alert-danger">Commande non trouvée</div>';
        exit;
    }
    
    // Récupérer les prestataires liés à la commande (MULTIPLES)
    $sqlPrestataires = "
        SELECT 
            cp.*,
            p.nom,
            p.prenom,
            p.telephone,
            p.type_prestataire
        FROM danfaniment_commande_prestataires cp
        LEFT JOIN danfaniment_prestataires p ON cp.id_prestataire = p.id_prestataire
        WHERE cp.id_commande = :id
        ORDER BY cp.id ASC
    ";
    $stmtPrestataires = $bdd->prepare($sqlPrestataires);
    $stmtPrestataires->execute([':id' => $id_commande]);
    $prestataires = $stmtPrestataires->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les paiements
    $sqlPaiements = "
        SELECT 
            p.*,
            DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS date_paiement_formatee,
            u.nom_utilisateur
        FROM danfaniment_paiements_confection p
        LEFT JOIN utilisateurs u ON p.id_utilisateur = u.id_utilisateur
        WHERE p.id_commande = :id
        ORDER BY p.created_at DESC
    ";
    $stmtPaiements = $bdd->prepare($sqlPaiements);
    $stmtPaiements->execute([':id' => $id_commande]);
    $paiements = $stmtPaiements->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcul des totaux
    $total_paye = array_sum(array_column($paiements, 'montant'));
    $solde_restant = $commande['montant_total'] - $total_paye;
    
    // Affichage des détails
    ?>
    <style>
        .detail-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #DC2626;
        }
        .detail-section h6 {
            color: #DC2626;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .info-row {
            display: flex;
            padding: 6px 0;
            border-bottom: 1px solid #e3e6f0;
        }
        .info-label {
            width: 35%;
            font-weight: bold;
            color: #4e73df;
        }
        .info-value {
            width: 65%;
            color: #5a5c69;
        }
        .prestataire-card {
            background: white;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .prestataire-card h6 {
            color: #DC2626;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .badge-statut {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-en_attente { background: #F59E0B; color: white; }
        .badge-en_cours { background: #3B82F6; color: white; }
        .badge-termine { background: #10B981; color: white; }
        .badge-livre { background: #8B5CF6; color: white; }
        .badge-annule { background: #EF4444; color: white; }
    </style>

    <div class="container-fluid">
        <!-- Informations générales -->
        <div class="detail-section">
            <h6><i class="fas fa-info-circle"></i> Informations générales</h6>
            <div class="info-row">
                <div class="info-label">N° Commande :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['numero_commande']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date commande :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['date_commande_formatee']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date livraison prévue :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['date_livraison_prevue_formatee']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date livraison réelle :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['date_livraison_reelle_formatee'] ?? 'Non livrée'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Statut :</div>
                <div class="info-value">
                    <span class="badge-statut badge-<?php echo $commande['statut']; ?>">
                        <?php 
                        $statutLabels = [
                            'en_attente' => 'En attente',
                            'en_cours' => 'En cours',
                            'termine' => 'Terminé',
                            'livre' => 'Livré',
                            'annule' => 'Annulé'
                        ];
                        echo htmlspecialchars($statutLabels[$commande['statut']] ?? $commande['statut']); 
                        ?>
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Créé par :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['created_by'] ?? 'N/A'); ?></div>
            </div>
        </div>

        <!-- Informations client -->
        <div class="detail-section">
            <h6><i class="fas fa-user"></i> Informations client</h6>
            <div class="info-row">
                <div class="info-label">Nom complet :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['client_nom'] . ' ' . $commande['client_prenom']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Téléphone :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['client_telephone']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['client_email'] ?? 'Non renseigné'); ?></div>
            </div>
        </div>

        <!-- Prestataires (MULTIPLES) -->
        <div class="detail-section">
            <h6><i class="fas fa-user-friends"></i> Prestataires intervenants</h6>
            <?php if (count($prestataires) > 0): ?>
                <?php foreach ($prestataires as $p): ?>
                <div class="prestataire-card">
                    <h6><i class="fas fa-user"></i> <?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?></h6>
                    <div class="info-row">
                        <div class="info-label">Type :</div>
                        <div class="info-value"><?php echo $p['type_prestataire'] == 'couturier' ? 'Couturier' : 'Tisseuse'; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Téléphone :</div>
                        <div class="info-value"><?php echo htmlspecialchars($p['telephone'] ?? '-'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Type production :</div>
                        <div class="info-value"><?php echo htmlspecialchars($p['type_production']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Montant :</div>
                        <div class="info-value"><?php echo number_format($p['montant_unitaire'], 0, ',', ' '); ?> FCFA</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Statut paiement :</div>
                        <div class="info-value">
                            <span class="badge badge-<?php echo $p['statut_paiement'] == 'paye' ? 'success' : ($p['statut_paiement'] == 'partiel' ? 'warning' : 'secondary'); ?>">
                                <?php echo $p['statut_paiement'] == 'en_attente' ? 'En attente' : ($p['statut_paiement'] == 'paye' ? 'Payé' : 'Partiel'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">Aucun prestataire associé à cette commande</div>
            <?php endif; ?>
        </div>

        <!-- Détails de la tenue -->
        <div class="detail-section">
            <h6><i class="fas fa-tshirt"></i> Détails de la tenue</h6>
            <div class="info-row">
                <div class="info-label">Type de tenue :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['type_tenue']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Tissu fourni par :</div>
                <div class="info-value"><?php echo $commande['tissu_fourni_par'] == 'client' ? 'Client' : 'Boutique'; ?></div>
            </div>
            <?php if ($commande['quantite_tissu']): ?>
            <div class="info-row">
                <div class="info-label">Quantité tissu :</div>
                <div class="info-value"><?php echo number_format($commande['quantite_tissu'], 2, ',', ' '); ?> mètres</div>
            </div>
            <?php endif; ?>
            <?php if ($commande['reference_tissu']): ?>
            <div class="info-row">
                <div class="info-label">Référence tissu :</div>
                <div class="info-value"><?php echo htmlspecialchars($commande['reference_tissu']); ?></div>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-label">Description :</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($commande['description_tenue'] ?? 'Aucune description')); ?></div>
            </div>
        </div>

        <!-- Informations financières -->
        <div class="detail-section">
            <h6><i class="fas fa-money-bill-wave"></i> Informations financières</h6>
            <div class="info-row">
                <div class="info-label">Montant total :</div>
                <div class="info-value"><strong><?php echo number_format($commande['montant_total'], 0, ',', ' '); ?> FCFA</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Avance :</div>
                <div class="info-value"><?php echo number_format($commande['montant_avance'], 0, ',', ' '); ?> FCFA</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total payé :</div>
                <div class="info-value"><strong><?php echo number_format($total_paye, 0, ',', ' '); ?> FCFA</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Solde restant :</div>
                <div class="info-value">
                    <strong class="text-<?php echo $solde_restant > 0 ? 'danger' : 'success'; ?>">
                        <?php echo number_format($solde_restant, 0, ',', ' '); ?> FCFA
                    </strong>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Coût main d'œuvre :</div>
                <div class="info-value"><?php echo number_format($commande['cout_couturier'], 0, ',', ' '); ?> FCFA</div>
            </div>
        </div>

        <!-- Instructions et remarques -->
        <?php if ($commande['instructions_couturier']): ?>
        <div class="detail-section">
            <h6><i class="fas fa-clipboard-list"></i> Instructions pour le couturier</h6>
            <p><?php echo nl2br(htmlspecialchars($commande['instructions_couturier'])); ?></p>
        </div>
        <?php endif; ?>

        <?php if ($commande['remarques']): ?>
        <div class="detail-section">
            <h6><i class="fas fa-sticky-note"></i> Remarques générales</h6>
            <p><?php echo nl2br(htmlspecialchars($commande['remarques'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Historique des paiements -->
        <?php if (count($paiements) > 0): ?>
        <div class="detail-section">
            <h6><i class="fas fa-receipt"></i> Historique des paiements</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Enregistré par</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paiements as $paiement): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($paiement['date_paiement_formatee']); ?></td>
                            <td><?php echo $paiement['type_paiement'] == 'avance' ? 'Avance' : ($paiement['type_paiement'] == 'solde' ? 'Solde' : 'Acompte'); ?></td>
                            <td class="text-right"><?php echo number_format($paiement['montant'], 0, ',', ' '); ?> FCFA</td>
                            <td><?php echo htmlspecialchars($paiement['mode_paiement']); ?></td>
                            <td><?php echo htmlspecialchars($paiement['nom_utilisateur'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>