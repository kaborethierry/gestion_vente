<!-- pages/modals/modal_depense.php -->
<!-- DANFANIMENT POS - Modals pour la gestion des dépenses -->

<!-- Modal : Ajouter une dépense -->
<div class="modal fade" id="ajouter_depense" tabindex="-1" role="dialog" aria-labelledby="ajouterDepenseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterDepenseLabel">
          <i class="fas fa-money-bill-wave"></i> Nouvelle dépense
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_depense.php" method="POST">
        <div class="modal-body">
        
          <div class="form-group">
            <label for="libelle">Libellé de la dépense *</label>
            <input type="text" class="form-control" id="libelle" name="libelle" required placeholder="Ex: Achat de tissu, Loyer mensuel...">
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="categorie">Catégorie *</label>
              <select class="form-control" id="categorie" name="categorie" required>
                <option value="">Sélectionnez une catégorie</option>
                <option value="salaire_prestataire_couturier">💰 Salaire couturier</option>
                <option value="salaire_prestataire_tisseuse">🪢 Salaire tisseuse</option>
                <option value="salaire_prestataire_brodeur">🪡 Salaire brodeur</option>
                <option value="salaire_prestataire_perleuse">💎 Salaire perleuse</option>
                <option value="salaire_prestataire_mercerie">📿 Salaire mercerie</option>
                <option value="commission_prestataire_vendeuse">🛍️ Commission vendeuse</option>
                <option value="livraison">🚚 Livraison</option>
                <option value="loyer">🏠 Loyer</option>
                <option value="fournitures">✂️ Fournitures</option>
                <option value="fournisseur_tissu">🧵 Fournisseur tissu</option>
                <option value="charges_diverses">📋 Charges diverses</option>
                <option value="tontines_entreprise">🤝 Tontines entreprise</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="beneficiaire">Bénéficiaire *</label>
              <input type="text" class="form-control" id="beneficiaire" name="beneficiaire" required placeholder="Nom du bénéficiaire">
            </div>
          </div>
          
          <div class="form-group">
            <label for="justification">Justification *</label>
            <textarea class="form-control" id="justification" name="justification" rows="2" required placeholder="Pourquoi cette dépense ?"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-4">
              <label for="montant">Montant (FCFA) *</label>
              <input type="number" step="1" class="form-control" id="montant" name="montant" required>
            </div>
            <div class="col-md-4">
              <label for="date_depense">Date de la dépense *</label>
              <input type="date" class="form-control" id="date_depense" name="date_depense" required>
            </div>
            <div class="col-md-4">
              <label for="reference_piece">Référence pièce justificative</label>
              <input type="text" class="form-control" id="reference_piece" name="reference_piece" placeholder="Optionnel">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="mode_paiement">Mode de paiement *</label>
              <select class="form-control" id="mode_paiement" name="mode_paiement" required>
                <option value="especes">💰 Espèces</option>
                <option value="carte">💳 Carte bancaire</option>
                <option value="mobile_money">📱 Mobile Money</option>
                <option value="virement">🏦 Virement</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="reference_transaction">Référence transaction</label>
              <input type="text" class="form-control" id="reference_transaction" name="reference_transaction" placeholder="Optionnel">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="statut">Statut *</label>
              <select class="form-control" id="statut" name="statut" required>
                <option value="valide">✅ Validé</option>
                <option value="en_attente">⏳ En attente</option>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
          </div>
        
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #DC2626; border-color: #DC2626;">Enregistrer la dépense</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Modifier une dépense -->
<div class="modal fade" id="modifier_depense" tabindex="-1" role="dialog" aria-labelledby="modifierDepenseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierDepenseLabel">
          <i class="fas fa-edit"></i> Modification d'une dépense
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_depense.php" method="POST">
        <div class="modal-body">
        
          <input type="hidden" id="id_depense_modif" name="id_depense">
        
          <div class="form-group">
            <label for="libelle_modif">Libellé de la dépense *</label>
            <input type="text" class="form-control" id="libelle_modif" name="libelle" required>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="categorie_modif">Catégorie *</label>
              <select class="form-control" id="categorie_modif" name="categorie" required>
                <option value="salaire_prestataire_couturier">💰 Salaire couturier</option>
                <option value="salaire_prestataire_tisseuse">🪢 Salaire tisseuse</option>
                <option value="salaire_prestataire_brodeur">🪡 Salaire brodeur</option>
                <option value="salaire_prestataire_perleuse">💎 Salaire perleuse</option>
                <option value="salaire_prestataire_mercerie">📿 Salaire mercerie</option>
                <option value="commission_prestataire_vendeuse">🛍️ Commission vendeuse</option>
                <option value="livraison">🚚 Livraison</option>
                <option value="loyer">🏠 Loyer</option>
                <option value="fournitures">✂️ Fournitures</option>
                <option value="fournisseur_tissu">🧵 Fournisseur tissu</option>
                <option value="charges_diverses">📋 Charges diverses</option>
                <option value="tontines_entreprise">🤝 Tontines entreprise</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="beneficiaire_modif">Bénéficiaire *</label>
              <input type="text" class="form-control" id="beneficiaire_modif" name="beneficiaire" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="justification_modif">Justification *</label>
            <textarea class="form-control" id="justification_modif" name="justification" rows="2" required></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-4">
              <label for="montant_modif">Montant (FCFA) *</label>
              <input type="number" step="1" class="form-control" id="montant_modif" name="montant" required>
            </div>
            <div class="col-md-4">
              <label for="date_depense_modif">Date de la dépense *</label>
              <input type="date" class="form-control" id="date_depense_modif" name="date_depense" required>
            </div>
            <div class="col-md-4">
              <label for="reference_piece_modif">Référence pièce justificative</label>
              <input type="text" class="form-control" id="reference_piece_modif" name="reference_piece">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="mode_paiement_modif">Mode de paiement *</label>
              <select class="form-control" id="mode_paiement_modif" name="mode_paiement" required>
                <option value="especes">💰 Espèces</option>
                <option value="carte">💳 Carte bancaire</option>
                <option value="mobile_money">📱 Mobile Money</option>
                <option value="virement">🏦 Virement</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="reference_transaction_modif">Référence transaction</label>
              <input type="text" class="form-control" id="reference_transaction_modif" name="reference_transaction">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="statut_modif">Statut *</label>
              <select class="form-control" id="statut_modif" name="statut" required>
                <option value="valide">✅ Validé</option>
                <option value="en_attente">⏳ En attente</option>
              </select>
            </div>
          </div>
        
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #10B981; border-color: #10B981;">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Détails de la dépense -->
<div class="modal fade" id="details_depense" tabindex="-1" role="dialog" aria-labelledby="detailsDepenseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1E3A8A, #3B82F6); color: white;">
        <h5 class="modal-title" id="detailsDepenseLabel">
          <i class="fas fa-info-circle"></i> Détails de la dépense
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