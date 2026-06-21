<!-- pages/modals/modal_categorie_piece.php -->

<!-- Modal : Ajouter une catégorie de pièce -->
<div class="modal fade" id="ajouter_categorie_piece" tabindex="-1" role="dialog" aria-labelledby="ajouterCategoriePieceLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="ajouterCategoriePieceLabel">Ajout d'une catégorie de pièce</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_categorie_piece.php" method="POST" id="form_ajouter_categorie_piece" novalidate>
        <div class="modal-body">
          <div id="alert_add_categorie" class="d-none"></div>

          <div class="form-group">
            <label for="libelle_add">Libellé <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="libelle_add" name="libelle" maxlength="50" placeholder="Entrez le libellé" required>
            <div class="invalid-feedback">Le libellé est requis (max 50 caractères).</div>
          </div>

          <div class="form-group">
            <label for="description_add">Description</label>
            <textarea class="form-control" id="description_add" name="description" rows="3" maxlength="1000" placeholder="Saisissez une description (optionnel)"></textarea>
          </div>

          <div class="form-group mb-0">
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

<!-- Modal : Modifier une catégorie de pièce -->
<div class="modal fade" id="modifier_categorie_piece" tabindex="-1" role="dialog" aria-labelledby="modifierCategoriePieceLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="modifierCategoriePieceLabel">Modification d'une catégorie de pièce</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_categorie_piece.php" method="POST" id="form_modifier_categorie_piece" novalidate>
        <div class="modal-body">
          <!-- ID caché pour identification -->
          <input type="hidden" id="id_categorie_edit" name="id_categorie" value="">

          <div id="alert_edit_categorie" class="d-none"></div>

          <div class="form-group">
            <label for="libelle_edit">Libellé <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="libelle_edit" name="libelle" maxlength="50" placeholder="Entrez le libellé" required>
            <div class="invalid-feedback">Le libellé est requis (max 50 caractères).</div>
          </div>

          <div class="form-group">
            <label for="description_edit">Description</label>
            <textarea class="form-control" id="description_edit" name="description" rows="3" maxlength="1000" placeholder="Saisissez une description (optionnel)"></textarea>
          </div>

          <div class="form-group mb-0">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
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
