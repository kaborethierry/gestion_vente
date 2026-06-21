<!-- pages/modals/modal_prestataire.php -->
<!-- DANFANIMENT POS - Modals pour la gestion des prestataires -->

<!-- Modal : Ajouter un prestataire -->
<div class="modal fade" id="ajouter_prestataire" tabindex="-1" role="dialog" aria-labelledby="ajouterPrestataireLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterPrestataireLabel">
          <i class="fas fa-user-plus"></i> Ajouter un prestataire
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_prestataire.php" method="POST">
        <div class="modal-body">
        
          <div class="form-group row">
            <div class="col-md-6">
              <label for="nom">Nom *</label>
              <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="col-md-6">
              <label for="prenom">Prénom *</label>
              <input type="text" class="form-control" id="prenom" name="prenom" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="telephone">Téléphone *</label>
              <input type="text" class="form-control" id="telephone" name="telephone" required>
            </div>
            <div class="col-md-6">
              <label for="email">Email</label>
              <input type="email" class="form-control" id="email" name="email">
            </div>
          </div>
          
          <div class="form-group">
            <label for="adresse">Adresse</label>
            <textarea class="form-control" id="adresse" name="adresse" rows="2"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="type_prestataire">Type de prestataire *</label>
              <select class="form-control" id="type_prestataire" name="type_prestataire" required>
                <option value="couturier">Couturier</option>
                <option value="tisseuse">Tisseuse</option>
                <option value="brodeur">Brodeur</option>
                <option value="perleuse">Perleuse</option>
                <option value="mercerie">Mercerie</option>
                <option value="vendeuse">Vendeuse</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="frequence_paiement">Fréquence de paiement</label>
              <select class="form-control" id="frequence_paiement" name="frequence_paiement">
                <option value="hebdomadaire">Hebdomadaire (samedi)</option>
                <option value="bihebdomadaire">Bihebdomadaire (mercredi & samedi)</option>
              </select>
            </div>
          </div>
          
          <!-- Section spécifique selon type -->
          <div id="section_specifique_ajout">
            <!-- Contenu dynamique via JS -->
          </div>
          
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
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

<!-- Modal : Modifier un prestataire -->
<div class="modal fade" id="modifier_prestataire" tabindex="-1" role="dialog" aria-labelledby="modifierPrestataireLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierPrestataireLabel">
          <i class="fas fa-user-edit"></i> Modifier un prestataire
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_prestataire.php" method="POST">
        <div class="modal-body">
        
          <input type="hidden" id="id_prestataire_modif" name="id_prestataire">
        
          <div class="form-group row">
            <div class="col-md-6">
              <label for="nom_modif">Nom *</label>
              <input type="text" class="form-control" id="nom_modif" name="nom" required>
            </div>
            <div class="col-md-6">
              <label for="prenom_modif">Prénom *</label>
              <input type="text" class="form-control" id="prenom_modif" name="prenom" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="telephone_modif">Téléphone *</label>
              <input type="text" class="form-control" id="telephone_modif" name="telephone" required>
            </div>
            <div class="col-md-6">
              <label for="email_modif">Email</label>
              <input type="email" class="form-control" id="email_modif" name="email">
            </div>
          </div>
          
          <div class="form-group">
            <label for="adresse_modif">Adresse</label>
            <textarea class="form-control" id="adresse_modif" name="adresse" rows="2"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="type_prestataire_modif">Type de prestataire *</label>
              <select class="form-control" id="type_prestataire_modif" name="type_prestataire" required>
                <option value="couturier">Couturier</option>
                <option value="tisseuse">Tisseuse</option>
                <option value="brodeur">Brodeur</option>
                <option value="perleuse">Perleuse</option>
                <option value="mercerie">Mercerie</option>
                <option value="vendeuse">Vendeuse</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="frequence_paiement_modif">Fréquence de paiement</label>
              <select class="form-control" id="frequence_paiement_modif" name="frequence_paiement">
                <option value="hebdomadaire">Hebdomadaire (samedi)</option>
                <option value="bihebdomadaire">Bihebdomadaire (mercredi & samedi)</option>
              </select>
            </div>
          </div>
          
          <!-- Section spécifique modification -->
          <div id="section_specifique_modif">
            <!-- Contenu dynamique via JS -->
          </div>
          
          <div class="form-group">
            <label for="notes_modif">Notes</label>
            <textarea class="form-control" id="notes_modif" name="notes" rows="2"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="actif_modif">Statut</label>
              <select class="form-control" id="actif_modif" name="actif">
                <option value="1">Actif</option>
                <option value="0">Inactif</option>
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

<!-- Modal : Enregistrer production (UNIVERSEL) -->
<div class="modal fade" id="production_prestataire" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" id="production_header">
        <h5 class="modal-title"><i class="fa fa-industry"></i> <span id="production_title">Enregistrer production</span></h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form action="../api/modules/enregistrer_production_prestataire.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="type_prestataire" id="production_type">
          
          <div class="form-group">
            <label id="prestataire_label">Prestataire *</label>
            <select class="form-control" name="id_prestataire" id="prestataire_select" required>
              <option value="">Sélectionnez un prestataire</option>
            </select>
          </div>
          
          <!-- Zone dynamique pour les champs spécifiques -->
          <div id="production_dynamic_fields">
            <!-- Sera rempli via JS -->
          </div>
          
          <div class="form-group">
            <label>Semaine début *</label>
            <input type="date" class="form-control" name="semaine_debut" required>
          </div>
          <div class="form-group">
            <label>Semaine fin *</label>
            <input type="date" class="form-control" name="semaine_fin" required>
          </div>
          <div class="form-group">
            <label>Remarques</label>
            <textarea class="form-control" name="remarques" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" id="production_submit_btn">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Valider paiement -->
<div class="modal fade" id="valider_paiement" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fa fa-check-circle"></i> Valider le paiement prestataire</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form action="../api/modules/valider_paiement_prestataire.php" method="POST">
        <div class="modal-body">
          <input type="hidden" id="paiement_id_prestataire" name="id_prestataire">
          <input type="hidden" id="paiement_type" name="type_prestataire">
          
          <div class="form-group">
            <label>Prestataire</label>
            <p class="form-control-static" id="paiement_nom" style="font-weight: bold;"></p>
          </div>
          
          <div class="form-group">
            <label>Montant à payer (FCFA) *</label>
            <input type="number" class="form-control" id="montant_a_payer_input" name="montant" required step="100" min="0" style="font-size: 18px; font-weight: bold;">
          </div>
          
          <div class="form-group">
            <label>Mode de paiement *</label>
            <select class="form-control" name="mode_paiement" id="mode_paiement" required>
              <option value="especes">Espèces</option>
              <option value="carte">Carte bancaire</option>
              <option value="mobile_money">Mobile Money</option>
              <option value="virement">Virement</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Référence transaction</label>
            <input type="text" class="form-control" name="reference_transaction" id="reference_transaction_paiement" placeholder="Optionnel">
          </div>
          
          <div class="form-group">
            <label>Remarques</label>
            <textarea class="form-control" name="remarques" id="remarques_paiement_text" rows="2" placeholder="Optionnel"></textarea>
          </div>
          
          <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> Attention : Cette action va créer une dépense automatique.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Valider le paiement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Gestion dynamique des champs spécifiques selon le type de prestataire
$(document).ready(function() {
    // Pour l'ajout
    $('#type_prestataire').on('change', function() {
        var type = $(this).val();
        chargerChampsSpecifiques('#section_specifique_ajout', type, 'ajout');
    });
    
    // Pour la modification
    $('#type_prestataire_modif').on('change', function() {
        var type = $(this).val();
        chargerChampsSpecifiques('#section_specifique_modif', type, 'modif');
    });
    
    // Pour la production
    $('#production_prestataire').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var type = button.data('type');
        $('#production_type').val(type);
        
        var title = '';
        var submitText = '';
        var headerClass = '';
        
        switch(type) {
            case 'couturier':
                title = 'Enregistrer production couturier - Tenues confectionnées';
                submitText = 'Enregistrer tenues';
                headerClass = 'bg-warning';
                break;
            case 'tisseuse':
                title = 'Enregistrer production tisseuse - Pagnes tissés';
                submitText = 'Enregistrer pagnes';
                headerClass = 'bg-info';
                break;
            case 'brodeur':
                title = 'Enregistrer production brodeur - Heures de travail';
                submitText = 'Enregistrer heures';
                headerClass = 'bg-success';
                break;
            case 'perleuse':
                title = 'Enregistrer production perleuse - Heures de travail';
                submitText = 'Enregistrer heures';
                headerClass = 'bg-danger';
                break;
            case 'mercerie':
                title = 'Enregistrer production mercerie - Heures de travail';
                submitText = 'Enregistrer heures';
                headerClass = 'bg-secondary';
                break;
            case 'vendeuse':
                title = 'Enregistrer commission vendeuse';
                submitText = 'Enregistrer commission';
                headerClass = 'bg-primary';
                break;
        }
        
        $('#production_title').text(title);
        $('#production_submit_btn').text(submitText);
        $('#production_header').removeClass().addClass('modal-header ' + headerClass + ' text-white');
        
        chargerChampsProduction(type);
        chargerPrestatairesParType(type);
    });
});

function chargerChampsSpecifiques(containerId, type, prefix) {
    var html = '';
    
    if (type === 'couturier') {
        html = `
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="tarif_par_tenue_${prefix}">Tarif par tenue (FCFA)</label>
                    <input type="number" step="100" class="form-control" id="tarif_par_tenue_${prefix}" name="tarif_par_tenue" value="0">
                </div>
                <div class="col-md-6">
                    <label for="specialites_${prefix}">Spécialités</label>
                    <input type="text" class="form-control" id="specialites_${prefix}" name="specialites" placeholder="Ex: Boubou, Robe, Ensemble">
                </div>
            </div>
        `;
    } else if (type === 'tisseuse') {
        html = `
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="tarif_par_pagne_${prefix}">Tarif par pagne (FCFA)</label>
                    <input type="number" step="100" class="form-control" id="tarif_par_pagne_${prefix}" name="tarif_par_pagne" value="0">
                </div>
                <div class="col-md-6">
                    <label for="specialites_${prefix}">Spécialités</label>
                    <input type="text" class="form-control" id="specialites_${prefix}" name="specialites" placeholder="Ex: Wax, Bazin, Tissage traditionnel">
                </div>
            </div>
        `;
    } else if (type === 'brodeur' || type === 'perleuse' || type === 'mercerie') {
        html = `
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="taux_horaire_${prefix}">Taux horaire (FCFA/heure)</label>
                    <input type="number" step="100" class="form-control" id="taux_horaire_${prefix}" name="taux_horaire" value="0">
                </div>
                <div class="col-md-6">
                    <label for="specialites_${prefix}">Spécialités</label>
                    <input type="text" class="form-control" id="specialites_${prefix}" name="specialites" placeholder="Spécialités">
                </div>
            </div>
        `;
    } else if (type === 'vendeuse') {
        html = `
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="commission_pourcentage_${prefix}">Commission (%)</label>
                    <input type="number" step="0.5" class="form-control" id="commission_pourcentage_${prefix}" name="commission_pourcentage" value="0">
                </div>
                <div class="col-md-6">
                    <label for="specialites_${prefix}">Spécialités</label>
                    <input type="text" class="form-control" id="specialites_${prefix}" name="specialites" placeholder="Ex: Vente en boutique, Vente en ligne">
                </div>
            </div>
        `;
    }
    
    $(containerId).html(html);
}

function chargerChampsProduction(type) {
    var html = '';
    
    if (type === 'couturier') {
        html = `
            <div class="form-group">
                <label>Commande confectionnée *</label>
                <select class="form-control" name="id_commande" id="commande_select" required>
                    <option value="">Sélectionnez d'abord un prestataire</option>
                </select>
                <small class="text-muted">Seules les commandes avec statut "Terminé" apparaissent.</small>
            </div>
            <div class="form-group">
                <label>Nombre de tenues</label>
                <input type="number" class="form-control" name="quantite" value="1" readonly>
            </div>
        `;
    } else if (type === 'tisseuse') {
        html = `
            <div class="form-group">
                <label>Type production *</label>
                <select class="form-control" name="type_production" required>
                    <option value="pagne">Pagne tissé</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nombre de pagnes *</label>
                <input type="number" class="form-control" name="quantite" min="1" required>
            </div>
        `;
    } else if (type === 'brodeur' || type === 'perleuse' || type === 'mercerie') {
        html = `
            <div class="form-group">
                <label>Type production *</label>
                <select class="form-control" name="type_production" required>
                    <option value="heure">Heures de travail</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nombre d'heures *</label>
                <input type="number" class="form-control" name="quantite" min="0.5" step="0.5" required>
            </div>
            <div class="form-group">
                <label>Description du travail</label>
                <textarea class="form-control" name="description_travail" rows="2" placeholder="Description des travaux effectués"></textarea>
            </div>
        `;
    } else if (type === 'vendeuse') {
        html = `
            <div class="form-group">
                <label>Type production *</label>
                <select class="form-control" name="type_production" required>
                    <option value="commission">Commission sur vente</option>
                </select>
            </div>
            <div class="form-group">
                <label>Chiffre d'affaires généré (FCFA) *</label>
                <input type="number" class="form-control" name="ca_genere" step="100" min="0" required>
            </div>
            <div class="form-group">
                <label>Taux commission (%)</label>
                <input type="number" class="form-control" name="taux_commission" step="0.5" min="0" max="100" required>
            </div>
            <div class="form-group">
                <label>Description vente</label>
                <textarea class="form-control" name="description_vente" rows="2" placeholder="Description des ventes"></textarea>
            </div>
        `;
    }
    
    $('#production_dynamic_fields').html(html);
}

function chargerPrestatairesParType(type) {
    $.ajax({
        url: '../api/modules/get_prestataires.php',
        type: 'GET',
        data: { type: type, actif: 1 },
        dataType: 'json',
        success: function(data) {
            var options = '<option value="">Sélectionnez un prestataire</option>';
            if (data && data.length) {
                $.each(data, function(i, p) {
                    options += '<option value="' + p.id_prestataire + '">' + p.nom + ' ' + p.prenom + '</option>';
                });
            }
            $('#prestataire_select').html(options);
        },
        error: function() {
            $('#prestataire_select').html('<option value="">Erreur chargement</option>');
        }
    });
}

// Pour couturier - charger commandes
$(document).on('change', '#prestataire_select', function() {
    var prestataireId = $(this).val();
    var productionType = $('#production_type').val();
    
    if (productionType === 'couturier' && prestataireId) {
        $('#commande_select').html('<option value="">Chargement...</option>');
        $.ajax({
            url: '../api/modules/get_commandes_terminees.php',
            type: 'GET',
            data: { id_prestataire: prestataireId },
            dataType: 'json',
            success: function(data) {
                var options = '<option value="">Sélectionnez une commande</option>';
                if (data && data.length && !data.error) {
                    $.each(data, function(i, cmd) {
                        options += '<option value="' + cmd.id_commande + '">' + cmd.numero_commande + ' - ' + cmd.client_nom + ' - ' + cmd.type_tenue + '</option>';
                    });
                } else {
                    options = '<option value="">Aucune commande terminée disponible</option>';
                }
                $('#commande_select').html(options);
            },
            error: function() {
                $('#commande_select').html('<option value="">Erreur de chargement</option>');
            }
        });
    }
});
</script>