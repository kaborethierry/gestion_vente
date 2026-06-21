<?php
// api/modules/details_produit.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo '<div class="alert alert-danger">Accès non autorisé</div>';
    exit;
}

if (empty($_GET['id_produit'])) {
    echo '<div class="alert alert-danger">ID produit manquant</div>';
    exit;
}

$id_produit = (int)$_GET['id_produit'];

require_once __DIR__ . '/connect_db_pdo.php';

try {
    $sql = "
        SELECT 
            p.*,
            DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS date_creation,
            DATE_FORMAT(p.updated_at, '%d/%m/%Y %H:%i') AS date_modification,
            u1.nom_utilisateur AS created_by_name,
            u2.nom_utilisateur AS updated_by_name,
            CASE 
                WHEN p.categorie = 'habits_traditionnels' THEN 'Habits traditionnels'
                WHEN p.categorie = 'pagnes' THEN 'Pagnes'
                WHEN p.categorie = 'vetements' THEN 'Vêtements'
                WHEN p.categorie = 'accessoires' THEN 'Accessoires'
                ELSE p.categorie
            END AS categorie_libelle
        FROM danfaniment_produits p
        LEFT JOIN utilisateurs u1 ON p.created_by = u1.id_utilisateur
        LEFT JOIN utilisateurs u2 ON p.updated_by = u2.id_utilisateur
        WHERE p.id_produit = :id
    ";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([':id' => $id_produit]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produit) {
        echo '<div class="alert alert-warning">Produit non trouvé</div>';
        exit;
    }
    
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 text-center">
                <?php if ($produit['photo']): ?>
                    <img src="../uploads/produits/<?php echo $produit['photo']; ?>" class="img-fluid rounded" style="max-height: 200px;">
                <?php else: ?>
                    <i class="fa fa-image fa-5x text-muted"></i>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <table class="table table-sm">
                    <tr><th>Code produit:</th><td><?php echo htmlspecialchars($produit['code_produit']); ?></td></tr>
                    <tr><th>Nom:</th><td><?php echo htmlspecialchars($produit['nom']); ?></td></tr>
                    <tr><th>Catégorie:</th><td><?php echo htmlspecialchars($produit['categorie_libelle']); ?></td></tr>
                    <?php if ($produit['sous_categorie']): ?>
                    <tr><th>Sous-catégorie:</th><td><?php echo htmlspecialchars($produit['sous_categorie']); ?></td></tr>
                    <?php endif; ?>
                    <tr><th>Prix d'achat:</th><td><?php echo number_format($produit['prix_achat'], 0, ',', ' '); ?> FCFA</td></tr>
                    <tr><th>Prix de vente:</th><td><?php echo number_format($produit['prix_vente'], 0, ',', ' '); ?> FCFA</td></tr>
                    <tr><th>Stock actuel:</th><td><?php echo $produit['stock_actuel']; ?> <?php echo $produit['unite_mesure']; ?></td></tr>
                    <tr><th>Stock initial:</th><td><?php echo $produit['stock_initial']; ?> <?php echo $produit['unite_mesure']; ?></td></tr>
                    <tr><th>Stock minimum:</th><td><?php echo $produit['stock_minimum']; ?> <?php echo $produit['unite_mesure']; ?></td></tr>
                    <tr><th>Statut:</th><td><?php echo $produit['statut'] === 'actif' ? '<span class="badge badge-success">Actif</span>' : '<span class="badge badge-danger">Inactif</span>'; ?></td></tr>
                    <tr><th>Créé le:</th><td><?php echo $produit['date_creation']; ?></td></tr>
                    <?php if ($produit['date_modification']): ?>
                    <tr><th>Modifié le:</th><td><?php echo $produit['date_modification']; ?></td></tr>
                    <?php endif; ?>
                    <?php if ($produit['description']): ?>
                    <tr><th>Description:</th><td><?php echo nl2br(htmlspecialchars($produit['description'])); ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <?php
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>