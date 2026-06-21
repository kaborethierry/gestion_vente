<?php
// api/modules/details_depense.php
session_start();
header('Content-Type: text/html; charset=utf-8');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo '<div class="alert alert-danger">Accès non autorisé</div>';
    exit;
}

$id_depense = (int)($_GET['id_depense'] ?? 0);
if ($id_depense <= 0) {
    echo '<div class="alert alert-danger">ID de dépense invalide</div>';
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $sql = "
        SELECT 
            d.*,
            u.nom_utilisateur as saisi_par,
            CASE 
                WHEN d.categorie = 'salaire_prestataire_couturier' THEN '💰 Salaire couturier'
                WHEN d.categorie = 'salaire_prestataire_tisseuse' THEN '🪢 Salaire tisseuse'
                WHEN d.categorie = 'salaire_prestataire_brodeur' THEN '🪡 Salaire brodeur'
                WHEN d.categorie = 'salaire_prestataire_perleuse' THEN '💎 Salaire perleuse'
                WHEN d.categorie = 'salaire_prestataire_mercerie' THEN '📿 Salaire mercerie'
                WHEN d.categorie = 'commission_prestataire_vendeuse' THEN '🛍️ Commission vendeuse'
                WHEN d.categorie = 'livraison' THEN '🚚 Livraison'
                WHEN d.categorie = 'loyer' THEN '🏠 Loyer'
                WHEN d.categorie = 'fournitures' THEN '✂️ Fournitures'
                WHEN d.categorie = 'fournisseur_tissu' THEN '🧵 Fournisseur tissu'
                WHEN d.categorie = 'charges_diverses' THEN '📋 Charges diverses'
                WHEN d.categorie = 'tontines_entreprise' THEN '🤝 Tontines entreprise'
                ELSE d.categorie
            END AS categorie_libelle,
            CASE 
                WHEN d.statut = 'valide' THEN '✅ Validé'
                WHEN d.statut = 'en_attente' THEN '⏳ En attente'
                ELSE d.statut
            END AS statut_libelle,
            CASE 
                WHEN d.mode_paiement = 'especes' THEN '💰 Espèces'
                WHEN d.mode_paiement = 'carte' THEN '💳 Carte bancaire'
                WHEN d.mode_paiement = 'mobile_money' THEN '📱 Mobile Money'
                WHEN d.mode_paiement = 'virement' THEN '🏦 Virement'
                ELSE d.mode_paiement
            END AS mode_paiement_libelle,
            DATE_FORMAT(d.date_depense, '%d/%m/%Y') as date_formatee,
            DATE_FORMAT(d.created_at, '%d/%m/%Y à %H:%i:%s') as created_at_formatee,
            DATE_FORMAT(d.updated_at, '%d/%m/%Y à %H:%i:%s') as updated_at_formatee
        FROM danfaniment_depenses d
        LEFT JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
        WHERE d.id_depense = :id
    ";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([':id' => $id_depense]);
    $depense = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$depense) {
        echo '<div class="alert alert-danger">Dépense non trouvée</div>';
        exit;
    }
    
    ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <th style="width: 30%;">ID</th>
                <td><?php echo $depense['id_depense']; ?></td>
            </tr>
            <tr>
                <th>Référence</th>
                <td><?php echo htmlspecialchars($depense['reference']); ?></td>
            </tr>
            <tr>
                <th>Date de la dépense</th>
                <td><?php echo $depense['date_formatee']; ?></td>
            </tr>
            <tr>
                <th>Libellé</th>
                <td><?php echo htmlspecialchars($depense['libelle']); ?></td>
            </tr>
            <tr>
                <th>Catégorie</th>
                <td><?php echo $depense['categorie_libelle']; ?></td>
            </tr>
            <tr>
                <th>Bénéficiaire</th>
                <td><?php echo htmlspecialchars($depense['beneficiaire']); ?></td>
            </tr>
            <tr>
                <th>Justification</th>
                <td><?php echo nl2br(htmlspecialchars($depense['justification'])); ?></td>
            </tr>
            <tr>
                <th>Montant</th>
                <td><strong class="text-danger"><?php echo number_format($depense['montant'], 0, ',', ' '); ?> FCFA</strong></td>
            </tr>
            <tr>
                <th>Référence pièce</th>
                <td><?php echo htmlspecialchars($depense['reference_piece'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Mode de paiement</th>
                <td><?php echo $depense['mode_paiement_libelle']; ?></td>
            </tr>
            <tr>
                <th>Référence transaction</th>
                <td><?php echo htmlspecialchars($depense['reference_transaction'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Origine</th>
                <td><?php echo ucfirst($depense['origine'] ?? 'manuelle'); ?></td>
            </tr>
            <tr>
                <th>Statut</th>
                <td><?php echo $depense['statut_libelle']; ?></td>
            </tr>
            <tr>
                <th>Saisi par</th>
                <td><?php echo htmlspecialchars($depense['saisi_par'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Date de création</th>
                <td><?php echo $depense['created_at_formatee']; ?></td>
            </tr>
            <?php if ($depense['updated_at']): ?>
            <tr>
                <th>Dernière modification</th>
                <td><?php echo $depense['updated_at_formatee']; ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Erreur lors du chargement des détails: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>