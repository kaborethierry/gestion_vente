<!-- pages/modals/modal_confection.php -->
<!-- DANFANIMENT POS - Modals pour la gestion des commandes confection -->

<!-- Modal : Ajouter une commande -->
<div class="modal fade" id="ajouter_confection" tabindex="-1" role="dialog" aria-labelledby="ajouterConfectionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterConfectionLabel">
          <i class="fas fa-tshirt"></i> Nouvelle commande confection
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_confection.php" method="POST">
        <div class="modal-body">
        
          <div class="form-group row">
            <div class="col-md-6">
              <label for="id_client">Client *</label>
              <select class="form-control" id="id_client" name="id_client" required>
                <option value="">Sélectionnez un client</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="date_livraison_prevue">Date livraison prévue *</label>
              <input type="date" class="form-control" id="date_livraison_prevue" name="date_livraison_prevue" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="type_tenue">Type de tenue *</label>
              <input type="text" class="form-control" id="type_tenue" name="type_tenue" required 
                     placeholder="Ex: Boubou, Ensemble, Robe, Costume, Kente, Bazin, Tailleur, etc.">
              <small class="form-text text-muted">Saisissez le type de tenue (libre)</small>
            </div>
            <div class="col-md-6">
              <label for="montant_total">Montant total (FCFA) *</label>
              <input type="number" step="0.01" class="form-control" id="montant_total" name="montant_total" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="description_tenue">Description de la tenue</label>
            <textarea class="form-control" id="description_tenue" name="description_tenue" rows="2" placeholder="Description détaillée (couleurs, motifs, etc.)..."></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-4">
              <label for="tissu_fourni_par">Tissu fourni par *</label>
              <select class="form-control" id="tissu_fourni_par" name="tissu_fourni_par" required>
                <option value="client">Client</option>
                <option value="boutique">Boutique</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="quantite_tissu">Quantité tissu (mètres)</label>
              <input type="number" step="0.01" class="form-control" id="quantite_tissu" name="quantite_tissu" placeholder="0.00">
            </div>
            <div class="col-md-4">
              <label for="reference_tissu">Référence tissu</label>
              <input type="text" class="form-control" id="reference_tissu" name="reference_tissu" placeholder="Référence">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="montant_avance">Avance (FCFA)</label>
              <input type="number" step="0.01" class="form-control" id="montant_avance" name="montant_avance" value="0">
            </div>
          </div>
          
          <!-- Section Prestataires multiples -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="fas fa-user-friends"></i> Prestataires intervenants *</h6>
            </div>
            <div class="card-body">
              <div id="prestataires-container">
                <div class="row prestataire-row mb-2">
                  <div class="col-md-5">
                    <select class="form-control prestataire-select" name="prestataires[0][id_prestataire]">
                      <option value="">Sélectionnez un prestataire</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <input type="number" step="0.01" class="form-control prestataire-montant" name="prestataires[0][montant]" placeholder="Montant (FCFA)">
                  </div>
                  <div class="col-md-2">
                    <select class="form-control prestataire-type" name="prestataires[0][type_production]">
                      <option value="tenue">Tenue</option>
                      <option value="pagne">Pagne</option>
                    </select>
                  </div>
                  <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-prestataire"><i class="fas fa-trash"></i></button>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-primary mt-2" id="add-prestataire">
                <i class="fas fa-plus"></i> Ajouter un prestataire
              </button>
              <small class="form-text text-muted">Le montant total des prestataires sera automatiquement additionné</small>
            </div>
          </div>
          
          <div class="form-group">
            <label for="instructions_couturier">Instructions générales</label>
            <textarea class="form-control" id="instructions_couturier" name="instructions_couturier" rows="2" placeholder="Instructions spéciales pour les prestataires..."></textarea>
          </div>
          
          <div class="form-group">
            <label for="remarques">Remarques générales</label>
            <textarea class="form-control" id="remarques" name="remarques" rows="2" placeholder="Remarques..."></textarea>
          </div>
          
          <div class="form-group">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
          </div>
        
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #DC2626; border-color: #DC2626;">Créer la commande</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Modifier une commande -->
<div class="modal fade" id="modifier_confection" tabindex="-1" role="dialog" aria-labelledby="modifierConfectionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierConfectionLabel">
          <i class="fas fa-edit"></i> Modification commande confection
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_confection.php" method="POST">
        <div class="modal-body">
        
          <input type="hidden" id="id_commande_modif" name="id_commande" value="">
        
          <div class="form-group row">
            <div class="col-md-6">
              <label for="id_client_modif">Client *</label>
              <select class="form-control" id="id_client_modif" name="id_client" required>
                <option value="">Sélectionnez un client</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="date_livraison_prevue_modif">Date livraison prévue *</label>
              <input type="date" class="form-control" id="date_livraison_prevue_modif" name="date_livraison_prevue" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="type_tenue_modif">Type de tenue *</label>
              <input type="text" class="form-control" id="type_tenue_modif" name="type_tenue" required>
              <small class="form-text text-muted">Ex: Boubou, Ensemble, Robe, Costume, Kente, etc.</small>
            </div>
            <div class="col-md-6">
              <label for="montant_total_modif">Montant total (FCFA) *</label>
              <input type="number" step="0.01" class="form-control" id="montant_total_modif" name="montant_total" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="description_tenue_modif">Description de la tenue</label>
            <textarea class="form-control" id="description_tenue_modif" name="description_tenue" rows="2"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-4">
              <label for="tissu_fourni_par_modif">Tissu fourni par</label>
              <select class="form-control" id="tissu_fourni_par_modif" name="tissu_fourni_par">
                <option value="client">Client</option>
                <option value="boutique">Boutique</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="quantite_tissu_modif">Quantité tissu (mètres)</label>
              <input type="number" step="0.01" class="form-control" id="quantite_tissu_modif" name="quantite_tissu">
            </div>
            <div class="col-md-4">
              <label for="reference_tissu_modif">Référence tissu</label>
              <input type="text" class="form-control" id="reference_tissu_modif" name="reference_tissu">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="montant_avance_modif">Avance (FCFA)</label>
              <input type="number" step="0.01" class="form-control" id="montant_avance_modif" name="montant_avance">
            </div>
          </div>
          
          <!-- Section Prestataires multiples pour modification -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="fas fa-user-friends"></i> Prestataires intervenants</h6>
            </div>
            <div class="card-body">
              <div id="prestataires-container-modif"></div>
              <button type="button" class="btn btn-sm btn-primary mt-2" id="add-prestataire-modif">
                <i class="fas fa-plus"></i> Ajouter un prestataire
              </button>
            </div>
          </div>
          
          <div class="form-group">
            <label for="instructions_couturier_modif">Instructions générales</label>
            <textarea class="form-control" id="instructions_couturier_modif" name="instructions_couturier" rows="2"></textarea>
          </div>
          
          <div class="form-group">
            <label for="remarques_modif">Remarques générales</label>
            <textarea class="form-control" id="remarques_modif" name="remarques" rows="2"></textarea>
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

<!-- Modal : Changer le statut -->
<div class="modal fade" id="changer_statut_confection" tabindex="-1" role="dialog" aria-labelledby="changerStatutLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #F59E0B, #DC2626); color: white;">
        <h5 class="modal-title" id="changerStatutLabel">
          <i class="fas fa-tasks"></i> Changer le statut de la commande
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/changer_statut_confection.php" method="POST">
        <div class="modal-body">
          <input type="hidden" id="id_commande_statut" name="id_commande" value="">
          
          <div class="form-group">
            <label for="nouveau_statut">Nouveau statut *</label>
            <select class="form-control" id="nouveau_statut" name="statut" required>
              <option value="en_attente">En attente</option>
              <option value="en_cours">En cours</option>
              <option value="termine">Terminé</option>
              <option value="livre">Livré</option>
              <option value="annule">Annulé</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="statut_remarques">Remarques sur le changement</label>
            <textarea class="form-control" id="statut_remarques" name="statut_remarques" rows="2" placeholder="Raison du changement..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Mettre à jour</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Détails de la commande -->
<div class="modal fade" id="details_confection" tabindex="-1" role="dialog" aria-labelledby="detailsConfectionLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1E3A8A, #3B82F6); color: white;">
        <h5 class="modal-title" id="detailsConfectionLabel">
          <i class="fas fa-info-circle"></i> Détails de la commande
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

<script>
$(document).ready(function() {
    // Charger les clients
    function chargerClients() {
        $.ajax({
            url: '../api/modules/get_clients.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var options = '<option value="">Sélectionnez un client</option>';
                $.each(data, function(i, client) {
                    options += '<option value="' + client.id_client + '">#' + client.id_client + ' - ' + client.nom + ' ' + client.prenom + '</option>';
                });
                $('#id_client, #id_client_modif').html(options);
            },
            error: function() {
                console.error('Erreur chargement clients');
            }
        });
    }
    
    chargerClients();
});
</script>