<?php
// Fichier : pages/modals/modal_piece.php

// Connexion PDO
require_once dirname(__DIR__, 2) . '/api/modules/connect_db_pdo.php';

$listeCategories = [];

try {
  // Requête sécurisée avec fallback
  $stmt = $bdd->query("
    SELECT id_categorie, libelle
    FROM categories_pieces
    WHERE supprimer = 'Non'
    ORDER BY libelle ASC
  ");
  $listeCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-warning">Erreur chargement catégories : ' . htmlspecialchars($e->getMessage()) . '</div>';
  $listeCategories = [];
}
?>

<!-- Modal : Ajouter une pièce -->
<div class="modal fade" id="ajouter_piece" tabindex="-1" role="dialog" aria-labelledby="ajouterPieceLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterPieceLabel">Ajout d'une nouvelle pièce</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/ajouter_piece.php" method="POST" novalidate>
        <div class="modal-body">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="ref_add">Référence <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="ref_add" name="reference" placeholder="Entrez la référence" maxlength="50" required>
            </div>
            <div class="col-md-6">
              <label for="designation_add">Désignation <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="designation_add" name="designation" placeholder="Entrez la désignation" maxlength="100" required>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="prix_achat_add">Prix d'achat</label>
              <input type="number" step="0.01" min="0" class="form-control" id="prix_achat_add" name="prix_achat" placeholder="Ex: 15000.00">
            </div>
            <div class="col-md-6">
              <label for="prix_vente_add">Prix de vente</label>
              <input type="number" step="0.01" min="0" class="form-control" id="prix_vente_add" name="prix_vente" placeholder="Ex: 20000.00">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="quantite_add">Quantité en stock</label>
              <input type="number" min="0" class="form-control" id="quantite_add" name="quantite_stock" placeholder="Ex: 10">
            </div>
            <div class="col-md-6">
              <label for="seuil_add">Seuil minimal</label>
              <input type="number" min="0" class="form-control" id="seuil_add" name="seuil_minimal" placeholder="Ex: 3">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="fournisseur_add">Fournisseur</label>
              <input type="text" class="form-control" id="fournisseur_add" name="fournisseur" maxlength="100" placeholder="Entrez le fournisseur">
            </div>
            <div class="col-md-6">
              <label for="categorie_add">Catégorie</label>
              <select class="form-control" id="categorie_add" name="id_categorie">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeCategories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat['id_categorie']) ?>">
                    <?= htmlspecialchars($cat['libelle']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Modifier une pièce -->
<div class="modal fade" id="modifier_piece" tabindex="-1" role="dialog" aria-labelledby="modifierPieceLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierPieceLabel">Modification d'une pièce</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/modifier_piece.php" method="POST" novalidate>
        <div class="modal-body">

          <input type="hidden" id="id_piece_modif" name="id_piece" value="">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="ref_modif">Référence <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="ref_modif" name="reference" placeholder="Entrez la référence" maxlength="50" required>
            </div>
            <div class="col-md-6">
              <label for="designation_modif">Désignation <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="designation_modif" name="designation" placeholder="Entrez la désignation" maxlength="100" required>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="prix_achat_modif">Prix d'achat</label>
              <input type="number" step="0.01" min="0" class="form-control" id="prix_achat_modif" name="prix_achat" placeholder="Ex: 15000.00">
            </div>
            <div class="col-md-6">
              <label for="prix_vente_modif">Prix de vente</label>
              <input type="number" step="0.01" min="0" class="form-control" id="prix_vente_modif" name="prix_vente" placeholder="Ex: 20000.00">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="quantite_modif">Quantité en stock</label>
              <input type="number" min="0" class="form-control" id="quantite_modif" name="quantite_stock" placeholder="Ex: 10">
            </div>
            <div class="col-md-6">
              <label for="seuil_modif">Seuil minimal</label>
              <input type="number" min="0" class="form-control" id="seuil_modif" name="seuil_minimal" placeholder="Ex: 3">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="fournisseur_modif">Fournisseur</label>
              <input type="text" class="form-control" id="fournisseur_modif" name="fournisseur" maxlength="100" placeholder="Entrez le fournisseur">
            </div>
            <div class="col-md-6">
              <label for="categorie_modif">Catégorie</label>
              <select class="form-control" id="categorie_modif" name="id_categorie">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeCategories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat['id_categorie']) ?>">
                    <?= htmlspecialchars($cat['libelle']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <small class="text-muted">
              <span class="text-danger">*</span> Champs obligatoires.
            </small>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>
