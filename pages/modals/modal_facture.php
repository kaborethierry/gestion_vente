<!-- pages/modals/modal_facture.php -->
<!-- DANFANIMENT POS - Modals pour la gestion des factures -->

<!-- Modal : Ajouter une facture -->
<div class="modal fade" id="ajouter_facture" tabindex="-1" role="dialog" aria-labelledby="ajouterFactureLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterFactureLabel">
          <i class="fas fa-file-invoice"></i> Nouvelle facture
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="form_ajout_facture" action="../api/modules/ajouter_facture.php" method="POST">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Client</label>
                <select class="form-control" id="client_id" name="id_client">
                  <option value="">Sélectionnez un client (optionnel)</option>
                  <?php
                  require_once __DIR__ . '/../../api/modules/connect_db_pdo.php';
                  try {
                      $stmt = $bdd->prepare("SELECT id_client, nom, prenom FROM danfaniment_clients WHERE supprimer = 'Non' OR supprimer IS NULL ORDER BY nom");
                      $stmt->execute();
                      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                          echo '<option value="' . $row['id_client'] . '">' . htmlspecialchars($row['nom'] . ' ' . $row['prenom']) . '</option>';
                      }
                  } catch (PDOException $e) {
                      echo '<option value="">Erreur de chargement</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Date d'échéance</label>
                <input type="date" class="form-control" name="date_echeance">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Référence externe</label>
                <input type="text" class="form-control" name="reference" placeholder="Devis, commande, etc.">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Taux TVA (%)</label>
                <input type="number" step="0.01" class="form-control" name="taux_tva" value="0" id="taux_tva_ajout">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Notes</label>
                <textarea class="form-control" name="notes" rows="2" placeholder="Notes supplémentaires..."></textarea>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Conditions de règlement</label>
                <textarea class="form-control" name="conditions_reglement" rows="2" placeholder="Paiement à réception, sous 30 jours, etc."></textarea>
              </div>
            </div>
          </div>
          
          <hr>
          <h6 class="font-weight-bold">Lignes de facture</h6>
          <div class="table-responsive">
            <table class="table table-bordered" id="lignes_facture_table">
              <thead>
                <tr>
                  <th>Désignation</th>
                  <th>Description</th>
                  <th>Quantité</th>
                  <th>Prix unitaire HT</th>
                  <th>Remise</th>
                  <th>Total HT</th>
                  <th>TVA</th>
                  <th>Total TTC</th>
                  <th style="width: 50px;">Action</th>
                </tr>
              </thead>
              <tbody id="lignes_facture_tbody">
                <tr>
                  <td><input type="text" class="form-control form-control-sm ligne-designation" name="lignes[0][designation]" required></td>
                  <td><input type="text" class="form-control form-control-sm" name="lignes[0][description]"></td>
                  <td><input type="number" step="0.01" class="form-control form-control-sm ligne-quantite" name="lignes[0][quantite]" value="1"></td>
                  <td><input type="number" step="0.01" class="form-control form-control-sm ligne-prix" name="lignes[0][prix_unitaire_ht]" value="0"></td>
                  <td><input type="number" step="0.01" class="form-control form-control-sm ligne-remise" name="lignes[0][remise_ligne]" value="0"></td>
                  <td class="ligne-total-ht">0</td>
                  <td class="ligne-tva">0</td>
                  <td class="ligne-total-ttc">0</td>
                  <td class="text-center"><button type="button" class="btn btn-danger btn-sm supprimer-ligne"><i class="fas fa-trash"></i></button></td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="8">
                    <button type="button" class="btn btn-success btn-sm" id="ajouter_ligne"><i class="fas fa-plus"></i> Ajouter une ligne</button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
          
          <hr>
          <div class="row">
            <div class="col-md-8 offset-md-4">
              <table class="table table-sm">
                <tr>
                  <td width="60%"><strong>Sous-total HT:</strong></td>
                  <td class="text-right"><span id="sous_total_ht">0</span> FCFA</td>
                </tr>
                <tr>
                  <td><strong>Remise totale:</strong></td>
                  <td class="text-right"><span id="remise_totale">0</span> FCFA</td>
                </tr>
                <tr>
                  <td><strong>Total HT:</strong></td>
                  <td class="text-right"><span id="total_ht">0</span> FCFA</td>
                </tr>
                <tr>
                  <td><strong>TVA:</strong></td>
                  <td class="text-right"><span id="montant_tva">0</span> FCFA</td>
                </tr>
                <tr class="font-weight-bold">
                  <td><strong>TOTAL TTC:</strong></td>
                  <td class="text-right"><span id="total_ttc">0</span> FCFA</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #DC2626; border-color: #DC2626;">Créer la facture</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Modifier une facture -->
<div class="modal fade" id="modifier_facture" tabindex="-1" role="dialog" aria-labelledby="modifierFactureLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierFactureLabel">
          <i class="fas fa-edit"></i> Modifier la facture
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="form_modif_facture" action="../api/modules/modifier_facture.php" method="POST">
        <div class="modal-body">
          <input type="hidden" id="modif_id_facture" name="id_facture">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Numéro facture</label>
                <input type="text" class="form-control" id="modif_numero_facture" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Date facture</label>
                <input type="text" class="form-control" id="modif_date_facture" readonly>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Client</label>
                <select class="form-control" id="modif_client_id" name="id_client">
                  <option value="">Sélectionnez un client</option>
                  <?php
                  try {
                      $stmt = $bdd->prepare("SELECT id_client, nom, prenom FROM danfaniment_clients WHERE supprimer = 'Non' OR supprimer IS NULL ORDER BY nom");
                      $stmt->execute();
                      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                          echo '<option value="' . $row['id_client'] . '">' . htmlspecialchars($row['nom'] . ' ' . $row['prenom']) . '</option>';
                      }
                  } catch (PDOException $e) {
                      echo '<option value="">Erreur de chargement</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Statut</label>
                <select class="form-control" id="modif_statut" name="statut">
                  <option value="brouillon">Brouillon</option>
                  <option value="envoyee">Envoyée</option>
                  <option value="payee">Payée</option>
                  <option value="annulee">Annulée</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Date d'échéance</label>
                <input type="date" class="form-control" id="modif_date_echeance" name="date_echeance">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Taux TVA (%)</label>
                <input type="number" step="0.01" class="form-control" id="modif_taux_tva" name="taux_tva" value="0">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Notes</label>
                <textarea class="form-control" id="modif_notes" name="notes" rows="2"></textarea>
              </div>
            </div>
          </div>
          
          <hr>
          <h6 class="font-weight-bold">Lignes de facture</h6>
          <div class="table-responsive">
            <table class="table table-bordered" id="modif_lignes_facture_table">
              <thead>
                <tr>
                  <th>Désignation</th>
                  <th>Description</th>
                  <th>Quantité</th>
                  <th>Prix unitaire HT</th>
                  <th>Remise</th>
                  <th>Total HT</th>
                  <th>TVA</th>
                  <th>Total TTC</th>
                  <th style="width: 50px;">Action</th>
                </tr>
              </thead>
              <tbody id="modif_lignes_facture_tbody"></tbody>
              <tfoot>
                <tr>
                  <td colspan="8">
                    <button type="button" class="btn btn-success btn-sm" id="modif_ajouter_ligne"><i class="fas fa-plus"></i> Ajouter une ligne</button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
          
          <hr>
          <div class="row">
            <div class="col-md-8 offset-md-4">
              <table class="table table-sm">
                <tr>
                  <td width="60%"><strong>Sous-total HT:</strong></td>
                  <td class="text-right"><span id="modif_sous_total_ht">0</span> FCFA</td>
                </tr>
                <tr>
                  <td><strong>Remise totale:</strong></td>
                  <td class="text-right"><span id="modif_remise_totale">0</span> FCFA</td>
                </tr>
                <tr>
                  <td><strong>Total HT:</strong></td>
                  <td class="text-right"><span id="modif_total_ht">0</span> FCFA</td>
                </tr>
                <tr>
                  <td><strong>TVA:</strong></td>
                  <td class="text-right"><span id="modif_montant_tva">0</span> FCFA</td>
                </tr>
                <tr class="font-weight-bold">
                  <td><strong>TOTAL TTC:</strong></td>
                  <td class="text-right"><span id="modif_total_ttc">0</span> FCFA</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #10B981; border-color: #10B981;">Enregistrer les modifications</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Voir facture -->
<div class="modal fade" id="voir_facture" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #3B82F6, #1E3A8A); color: white;">
        <h5 class="modal-title"><i class="fas fa-file-invoice"></i> Détails de la facture</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="voir_facture_content">
        <div class="text-center py-4">
          <i class="fas fa-spinner fa-spin fa-2x"></i> Chargement...
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" id="printFactureBtn"><i class="fas fa-print"></i> Imprimer</button>
      </div>
    </div>
  </div>
</div>

<script>
// Gestion des lignes de facture pour l'ajout
let ligneIndex = 1;

$(document).ready(function() {
    $('#ajouter_ligne').on('click', function() {
        const newRow = `
            <tr>
                <td><input type="text" class="form-control form-control-sm ligne-designation" name="lignes[${ligneIndex}][designation]" required></td>
                <td><input type="text" class="form-control form-control-sm" name="lignes[${ligneIndex}][description]"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm ligne-quantite" name="lignes[${ligneIndex}][quantite]" value="1"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm ligne-prix" name="lignes[${ligneIndex}][prix_unitaire_ht]" value="0"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm ligne-remise" name="lignes[${ligneIndex}][remise_ligne]" value="0"></td>
                <td class="ligne-total-ht">0</td>
                <td class="ligne-tva">0</td>
                <td class="ligne-total-ttc">0</td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm supprimer-ligne"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#lignes_facture_tbody').append(newRow);
        ligneIndex++;
        attacherEvenementsLignes();
        recalculerTotaux();
    });
    
    function attacherEvenementsLignes() {
        $('.ligne-quantite, .ligne-prix, .ligne-remise').off('input').on('input', function() {
            const row = $(this).closest('tr');
            calculerLigne(row);
            recalculerTotaux();
        });
        
        $('.supprimer-ligne').off('click').on('click', function() {
            if ($('#lignes_facture_tbody tr').length > 1) {
                $(this).closest('tr').remove();
                recalculerTotaux();
            } else {
                Swal.fire('Information', 'Vous devez conserver au moins une ligne', 'info');
            }
        });
    }
    
    function calculerLigne(row) {
        const quantite = parseFloat(row.find('.ligne-quantite').val()) || 0;
        const prixUnitaire = parseFloat(row.find('.ligne-prix').val()) || 0;
        const remiseLigne = parseFloat(row.find('.ligne-remise').val()) || 0;
        
        let totalHt = quantite * prixUnitaire;
        totalHt = totalHt - remiseLigne;
        if (totalHt < 0) totalHt = 0;
        
        const tauxTva = parseFloat($('#taux_tva_ajout').val()) || 0;
        const montantTva = totalHt * (tauxTva / 100);
        const totalTtc = totalHt + montantTva;
        
        row.find('.ligne-total-ht').text(totalHt.toLocaleString('fr-FR'));
        row.find('.ligne-tva').text(montantTva.toLocaleString('fr-FR'));
        row.find('.ligne-total-ttc').text(totalTtc.toLocaleString('fr-FR'));
    }
    
    function recalculerTotaux() {
        let sousTotalHt = 0;
        let remiseTotale = 0;
        
        $('#lignes_facture_tbody tr').each(function() {
            const quantite = parseFloat($(this).find('.ligne-quantite').val()) || 0;
            const prixUnitaire = parseFloat($(this).find('.ligne-prix').val()) || 0;
            const remiseLigne = parseFloat($(this).find('.ligne-remise').val()) || 0;
            const totalHtLigne = (quantite * prixUnitaire);
            sousTotalHt += totalHtLigne;
            remiseTotale += remiseLigne;
        });
        
        const totalHt = sousTotalHt - remiseTotale;
        const tauxTva = parseFloat($('#taux_tva_ajout').val()) || 0;
        const montantTva = totalHt * (tauxTva / 100);
        const totalTtc = totalHt + montantTva;
        
        $('#sous_total_ht').text(sousTotalHt.toLocaleString('fr-FR'));
        $('#remise_totale').text(remiseTotale.toLocaleString('fr-FR'));
        $('#total_ht').text(totalHt.toLocaleString('fr-FR'));
        $('#montant_tva').text(montantTva.toLocaleString('fr-FR'));
        $('#total_ttc').text(totalTtc.toLocaleString('fr-FR'));
    }
    
    $('#taux_tva_ajout').on('input', function() {
        $('#lignes_facture_tbody tr').each(function() { calculerLigne($(this)); });
        recalculerTotaux();
    });
    
    attacherEvenementsLignes();
});
</script>