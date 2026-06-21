<!-- pages/modals/modal_produit.php -->
<!-- DANFANIMENT POS - Modals pour la gestion des produits -->

<!-- Modal : Ajouter un produit -->
<div class="modal fade" id="ajouter_produit" tabindex="-1" role="dialog" aria-labelledby="ajouterProduitLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterProduitLabel">
          <i class="fas fa-box"></i> Ajouter un produit
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_produit.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
        
          <div class="form-group row">
            <div class="col-md-6">
              <label for="code_produit">Code produit</label>
              <input type="text" class="form-control" id="code_produit" name="code_produit" placeholder="Auto-généré si vide">
              <small class="text-muted">Laissez vide pour auto-génération</small>
            </div>
            <div class="col-md-6">
              <label for="nom">Nom du produit *</label>
              <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="categorie">Catégorie *</label>
              <select class="form-control" id="categorie" name="categorie" required>
                <option value="habits_traditionnels">Habits traditionnels</option>
                <option value="pagnes">Pagnes</option>
                <option value="vetements">Vêtements</option>
                <option value="accessoires">Accessoires</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="sous_categorie">Sous-catégorie</label>
              <input type="text" class="form-control" id="sous_categorie" name="sous_categorie">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-4">
              <label for="prix_achat">Prix d'achat (FCFA) *</label>
              <input type="number" step="1" class="form-control" id="prix_achat" name="prix_achat" required>
            </div>
            <div class="col-md-4">
              <label for="prix_vente">Prix de vente (FCFA) *</label>
              <input type="number" step="1" class="form-control" id="prix_vente" name="prix_vente" required>
            </div>
            <div class="col-md-4">
              <label for="unite_mesure">Unité de mesure</label>
              <select class="form-control" id="unite_mesure" name="unite_mesure">
                <option value="piece">Pièce</option>
                <option value="metre">Mètre</option>
                <option value="kg">Kg</option>
                <option value="litre">Litre</option>
              </select>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-4">
              <label for="stock_initial">Stock initial *</label>
              <input type="number" step="1" class="form-control" id="stock_initial" name="stock_initial" required>
            </div>
            <div class="col-md-4">
              <label for="stock_minimum">Stock minimum</label>
              <input type="number" step="1" class="form-control" id="stock_minimum" name="stock_minimum" value="5">
            </div>
            <div class="col-md-4">
              <label for="statut">Statut</label>
              <select class="form-control" id="statut" name="statut">
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label for="photo">Photo du produit</label>
            <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
            <small class="text-muted">Formats acceptés: JPG, PNG, GIF. Max 2MB</small>
          </div>
          
          <div class="form-group">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
          </div>
        
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #DC2626; border-color: #DC2626;">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Modifier un produit -->
<div class="modal fade" id="modifier_produit" tabindex="-1" role="dialog" aria-labelledby="modifierProduitLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierProduitLabel">
          <i class="fas fa-edit"></i> Modification d'un produit
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_produit.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
        
          <input type="hidden" id="id_produit_modif" name="id_produit">
          <input type="hidden" id="photo_actuelle" name="photo_actuelle">
        
          <div class="form-group row">
            <div class="col-md-6">
              <label for="code_produit_modif">Code produit</label>
              <input type="text" class="form-control" id="code_produit_modif" name="code_produit" readonly>
            </div>
            <div class="col-md-6">
              <label for="nom_modif">Nom du produit *</label>
              <input type="text" class="form-control" id="nom_modif" name="nom" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="description_modif">Description</label>
            <textarea class="form-control" id="description_modif" name="description" rows="2"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="categorie_modif">Catégorie *</label>
              <select class="form-control" id="categorie_modif" name="categorie" required>
                <option value="habits_traditionnels">Habits traditionnels</option>
                <option value="pagnes">Pagnes</option>
                <option value="vetements">Vêtements</option>
                <option value="accessoires">Accessoires</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="sous_categorie_modif">Sous-catégorie</label>
              <input type="text" class="form-control" id="sous_categorie_modif" name="sous_categorie">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-4">
              <label for="prix_achat_modif">Prix d'achat (FCFA) *</label>
              <input type="number" step="1" class="form-control" id="prix_achat_modif" name="prix_achat" required>
            </div>
            <div class="col-md-4">
              <label for="prix_vente_modif">Prix de vente (FCFA) *</label>
              <input type="number" step="1" class="form-control" id="prix_vente_modif" name="prix_vente" required>
            </div>
            <div class="col-md-4">
              <label for="unite_mesure_modif">Unité de mesure</label>
              <select class="form-control" id="unite_mesure_modif" name="unite_mesure">
                <option value="piece">Pièce</option>
                <option value="metre">Mètre</option>
                <option value="kg">Kg</option>
                <option value="litre">Litre</option>
              </select>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-4">
              <label for="stock_actuel_modif">Stock actuel</label>
              <input type="number" step="1" class="form-control" id="stock_actuel_modif" name="stock_actuel" readonly>
            </div>
            <div class="col-md-4">
              <label for="stock_minimum_modif">Stock minimum</label>
              <input type="number" step="1" class="form-control" id="stock_minimum_modif" name="stock_minimum">
            </div>
            <div class="col-md-4">
              <label for="statut_modif">Statut</label>
              <select class="form-control" id="statut_modif" name="statut">
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label for="photo_modif">Photo du produit</label>
            <input type="file" class="form-control-file" id="photo_modif" name="photo" accept="image/*">
            <small class="text-muted">Laissez vide pour conserver la photo actuelle</small>
          </div>
          
          <div id="photo_preview" class="mt-2"></div>
        
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #10B981; border-color: #10B981;">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Détails du produit -->
<div class="modal fade" id="details_produit" tabindex="-1" role="dialog" aria-labelledby="detailsProduitLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1E3A8A, #3B82F6); color: white;">
        <h5 class="modal-title" id="detailsProduitLabel">
          <i class="fas fa-info-circle"></i> Détails du produit
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="details_contenu">
        <!-- Contenu chargé dynamiquement -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal : Code-barres -->
<div class="modal fade" id="modal_codebarres" tabindex="-1" role="dialog" aria-labelledby="codebarresLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="codebarresLabel">
          <i class="fa fa-barcode"></i> Code-barres du produit
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body text-center" id="codebarres_contenu">
        <!-- Contenu chargé dynamiquement -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" id="imprimer_codebarres"><i class="fa fa-print"></i> Imprimer</button>
      </div>
    </div>
  </div>
</div>