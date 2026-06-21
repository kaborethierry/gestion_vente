<!-- pages/modals/modal_client.php -->
<!-- DANFANIMENT POS - Modals pour la gestion des clients -->

<!-- Modal : Ajouter un client -->
<div class="modal fade" id="ajouter_client" tabindex="-1" role="dialog" aria-labelledby="ajouterClientLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterClientLabel">
          <i class="fas fa-user-plus"></i> Ajout d'un nouveau client
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_client.php" method="POST">
        <div class="modal-body">
          <div class="form-group row">
            <div class="col-md-6">
              <label for="nom">Nom *</label>
              <input type="text" class="form-control" id="nom" name="nom" placeholder="Entrez le nom" required>
            </div>
            <div class="col-md-6">
              <label for="prenom">Prénom *</label>
              <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Entrez le prénom" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="telephone">Téléphone *</label>
              <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="Entrez le téléphone" required>
            </div>
            <div class="col-md-6">
              <label for="email">Email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Entrez l'email">
            </div>
          </div>
          
          <div class="form-group">
            <label for="adresse">Adresse</label>
            <textarea class="form-control" id="adresse" name="adresse" rows="2" placeholder="Entrez l'adresse complète"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="ville">Ville</label>
              <input type="text" class="form-control" id="ville" name="ville" placeholder="Entrez la ville">
            </div>
          </div>
          
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Informations complémentaires..."></textarea>
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

<!-- Modal : Modifier un client -->
<div class="modal fade" id="modifier_client" tabindex="-1" role="dialog" aria-labelledby="modifierClientLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierClientLabel">
          <i class="fas fa-user-edit"></i> Modification d'un client
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_client.php" method="POST">
        <div class="modal-body">
          <input type="hidden" id="id_client_modif" name="id_client" value="">
        
          <div class="form-group row">
            <div class="col-md-6">
              <label for="nom_modif">Nom *</label>
              <input type="text" class="form-control" id="nom_modif" name="nom" placeholder="Entrez le nom" required>
            </div>
            <div class="col-md-6">
              <label for="prenom_modif">Prénom *</label>
              <input type="text" class="form-control" id="prenom_modif" name="prenom" placeholder="Entrez le prénom" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="telephone_modif">Téléphone *</label>
              <input type="tel" class="form-control" id="telephone_modif" name="telephone" placeholder="Entrez le téléphone" required>
            </div>
            <div class="col-md-6">
              <label for="email_modif">Email</label>
              <input type="email" class="form-control" id="email_modif" name="email" placeholder="Entrez l'email">
            </div>
          </div>
          
          <div class="form-group">
            <label for="adresse_modif">Adresse</label>
            <textarea class="form-control" id="adresse_modif" name="adresse" rows="2" placeholder="Entrez l'adresse complète"></textarea>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="ville_modif">Ville</label>
              <input type="text" class="form-control" id="ville_modif" name="ville" placeholder="Entrez la ville">
            </div>
          </div>
          
          <div class="form-group">
            <label for="notes_modif">Notes</label>
            <textarea class="form-control" id="notes_modif" name="notes" rows="2" placeholder="Informations complémentaires..."></textarea>
          </div>
          
          <div class="form-group">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
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

<!-- Modal : Voir profil client -->
<div class="modal fade" id="voir_profil_client" tabindex="-1" role="dialog" aria-labelledby="voirProfilClientLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #4F46E5, #3730A3); color: white;">
        <h5 class="modal-title" id="voirProfilClientLabel">
          <i class="fas fa-user-circle"></i> Profil client
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
        <!-- Informations générales -->
        <div class="row mb-4">
          <div class="col-md-12">
            <div class="card shadow-sm">
              <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle"></i> Informations personnelles</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-user"></i> Nom complet :</strong> <span id="profil_nom_complet">-</span></p>
                  </div>
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-phone"></i> Téléphone :</strong> <span id="profil_telephone">-</span></p>
                  </div>
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-envelope"></i> Email :</strong> <span id="profil_email">-</span></p>
                  </div>
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-map-marker-alt"></i> Adresse :</strong> <span id="profil_adresse">-</span></p>
                  </div>
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-city"></i> Ville :</strong> <span id="profil_ville">-</span></p>
                  </div>
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-chart-line"></i> Total dépensé :</strong> <span id="profil_total_depense">-</span></p>
                  </div>
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-calendar-check"></i> Nombre de visites :</strong> <span id="profil_nb_visites">-</span></p>
                  </div>
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-calendar-alt"></i> Dernière visite :</strong> <span id="profil_date_derniere_visite">-</span></p>
                  </div>
                  <div class="col-md-4">
                    <p><strong><i class="fas fa-star"></i> Points fidélité :</strong> <span id="profil_points_fidelite">-</span></p>
                  </div>
                  <div class="col-md-12">
                    <p><strong><i class="fas fa-sticky-note"></i> Notes :</strong> <span id="profil_notes">-</span></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation par onglets -->
        <ul class="nav nav-tabs nav-tabs-custom" id="profilTab" role="tablist">
 
          <li class="nav-item">
            <a class="nav-link" id="confections-tab" data-toggle="tab" href="#confections" role="tab">
              <i class="fas fa-cut"></i> Commandes confection
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="achats-tab" data-toggle="tab" href="#achats" role="tab">
              <i class="fas fa-shopping-cart"></i> Historique achats
            </a>
          </li>
        </ul>

        <div class="tab-content mt-3" id="profilTabContent">
          

          <!-- Onglet Commandes confection - SANS bouton Actions -->
          <div class="tab-pane fade" id="confections" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="confections-table">
                <thead class="thead-light">
                  <tr>
                    <th>N° Commande</th>
                    <th>Type tenue</th>
                    <th>Montant total</th>
                    <th>Avance</th>
                    <th>Payé</th>
                    <th>Reste</th>
                    <th>Statut</th>
                    <th>Prestataire</th>
                    <th>Date livraison</th>
                  </tr>
                </thead>
                <tbody id="confections-tbody">
                  <tr>
                    <td colspan="9" class="text-center text-muted">Chargement des commandes...</td>
                  </tr>
                </tbody>
              <tr>
            </div>
          </div>

          <!-- Onglet Historique achats -->
          <div class="tab-pane fade" id="achats" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="achats-table">
                <thead class="thead-light">
                  <tr>
                    <th>N° Vente</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Mode paiement</th>
                    <th>Caissier</th>
                  </tr>
                </thead>
                <tbody id="achats-tbody">
                  <tr>
                    <td colspan="5" class="text-center text-muted">Chargement des achats...</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-2">
              <strong>Total des achats :</strong> <span id="total_achats">0 FCFA</span>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal : Ajouter/Modifier mesure -->
<div class="modal fade" id="modal_mesure" tabindex="-1" role="dialog" aria-labelledby="mesureLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #8B5CF6, #6D28D9); color: white;">
        <h5 class="modal-title" id="mesureLabel">
          <i class="fas fa-ruler-combined"></i> Mesures du client
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="form_mesure">
        <div class="modal-body">
          <input type="hidden" id="mesure_id_client" name="id_client">
          <input type="hidden" id="mesure_id_mesure" name="id_mesure">
          
          <div class="row">
            <div class="col-md-3">
              <label>Tour de cou (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_cou" id="tour_cou">
            </div>
            <div class="col-md-3">
              <label>Largeur épaule (cm)</label>
              <input type="number" step="0.1" class="form-control" name="largeur_epaule" id="largeur_epaule">
            </div>
            <div class="col-md-3">
              <label>Tour de poitrine (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_poitrine" id="tour_poitrine">
            </div>
            <div class="col-md-3">
              <label>Tour sous poitrine (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_sous_poitrine" id="tour_sous_poitrine">
            </div>
          </div>
          
          <div class="row mt-2">
            <div class="col-md-3">
              <label>Hauteur poitrine (cm)</label>
              <input type="number" step="0.1" class="form-control" name="hauteur_poitrine" id="hauteur_poitrine">
            </div>
            <div class="col-md-3">
              <label>Écart poitrine (cm)</label>
              <input type="number" step="0.1" class="form-control" name="ecart_poitrine" id="ecart_poitrine">
            </div>
            <div class="col-md-3">
              <label>Tour de taille (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_taille" id="tour_taille">
            </div>
            <div class="col-md-3">
              <label>Hauteur taille (cm)</label>
              <input type="number" step="0.1" class="form-control" name="hauteur_taille" id="hauteur_taille">
            </div>
          </div>
          
          <div class="row mt-2">
            <div class="col-md-3">
              <label>Tour de hanches (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_hanches" id="tour_hanches">
            </div>
            <div class="col-md-3">
              <label>Hauteur hanches (cm)</label>
              <input type="number" step="0.1" class="form-control" name="hauteur_hanches" id="hauteur_hanches">
            </div>
            <div class="col-md-3">
              <label>Longueur dos (cm)</label>
              <input type="number" step="0.1" class="form-control" name="longueur_dos" id="longueur_dos">
            </div>
            <div class="col-md-3">
              <label>Largeur dos (cm)</label>
              <input type="number" step="0.1" class="form-control" name="largeur_dos" id="largeur_dos">
            </div>
          </div>
          
          <div class="row mt-2">
            <div class="col-md-3">
              <label>Longueur devant (cm)</label>
              <input type="number" step="0.1" class="form-control" name="longueur_devant" id="longueur_devant">
            </div>
            <div class="col-md-3">
              <label>Tour de bras (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_bras" id="tour_bras">
            </div>
            <div class="col-md-3">
              <label>Longueur bras (cm)</label>
              <input type="number" step="0.1" class="form-control" name="longueur_bras" id="longueur_bras">
            </div>
            <div class="col-md-3">
              <label>Longueur manche (cm)</label>
              <input type="number" step="0.1" class="form-control" name="longueur_manche" id="longueur_manche">
            </div>
          </div>
          
          <div class="row mt-2">
            <div class="col-md-3">
              <label>Tour de poignet (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_poignet" id="tour_poignet">
            </div>
            <div class="col-md-3">
              <label>Longueur totale tenue (cm)</label>
              <input type="number" step="0.1" class="form-control" name="longueur_totale_tenue" id="longueur_totale_tenue">
            </div>
            <div class="col-md-3">
              <label>Longueur jupe (cm)</label>
              <input type="number" step="0.1" class="form-control" name="longueur_jupe" id="longueur_jupe">
            </div>
            <div class="col-md-3">
              <label>Longueur pantalon (cm)</label>
              <input type="number" step="0.1" class="form-control" name="longueur_pantalon" id="longueur_pantalon">
            </div>
          </div>
          
          <div class="row mt-2">
            <div class="col-md-3">
              <label>Tour de cuisse (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_cuisse" id="tour_cuisse">
            </div>
            <div class="col-md-3">
              <label>Tour de mollet (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_mollet" id="tour_mollet">
            </div>
            <div class="col-md-3">
              <label>Tour de cheville (cm)</label>
              <input type="number" step="0.1" class="form-control" name="tour_cheville" id="tour_cheville">
            </div>
            <div class="col-md-3">
              <label>Hauteur totale (cm)</label>
              <input type="number" step="0.01" class="form-control" name="hauteur_totale" id="hauteur_totale">
            </div>
          </div>
          
          <div class="row mt-2">
            <div class="col-md-3">
              <label>Poids (kg)</label>
              <input type="number" step="0.01" class="form-control" name="poids" id="poids">
            </div>
            <div class="col-md-3">
              <label>Pointure chaussure</label>
              <input type="number" step="0.1" class="form-control" name="pointure_chaussure" id="pointure_chaussure">
            </div>
            <div class="col-md-3">
              <label>Taille ceinture</label>
              <input type="text" class="form-control" name="taille_ceinture" id="taille_ceinture">
            </div>
          </div>
          
          <div class="row mt-2">
            <div class="col-md-12">
              <label>Notes</label>
              <textarea class="form-control" name="notes_mesure" id="notes_mesure" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #8B5CF6; border-color: #8B5CF6;">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Variable globale pour l'ID client courant
var currentClientId = null;

// Fonction pour charger et afficher les mesures
function chargerMesures(idClient) {
    $.ajax({
        url: '../api/modules/get_mesures_client.php',
        type: 'GET',
        data: { id_client: idClient },
        dataType: 'json',
        success: function(data) {
            if (data.success && data.mesures && data.mesures.length > 0) {
                var html = '';
                data.mesures.forEach(function(m) {
                    var dateMesure = '-';
                    if (m.date_mesure && m.date_mesure !== '0000-00-00') {
                        var d = new Date(m.date_mesure);
                        if (!isNaN(d.getTime())) {
                            dateMesure = d.toLocaleDateString('fr-FR');
                        }
                    }
                    html += '<tr>';
                    html += '<td>' + dateMesure + '</td>';
                    html += '<td>' + (m.tour_cou || '-') + '</td>';
                    html += '<td>' + (m.largeur_epaule || '-') + '</td>';
                    html += '<td>' + (m.tour_poitrine || '-') + '</td>';
                    html += '<td>' + (m.tour_taille || '-') + '</td>';
                    html += '<td>' + (m.tour_hanches || '-') + '</td>';
                    html += '<td>' + (m.longueur_dos || '-') + '</td>';
                    html += '<td>' + (m.longueur_bras || '-') + '</td>';
                    html += '<td>' + (m.longueur_manche || '-') + '</td>';
                    html += '<td>' + (m.longueur_totale_tenue || '-') + '</td>';
                    html += '<td>' + (m.hauteur_totale || '-') + '</td>';
                    html += '<td>' + (m.poids || '-') + '</td>';
                    html += '</tr>';
                });
                $('#mesures-tbody').html(html);
            } else {
                $('#mesures-tbody').html('<tr><td colspan="12" class="text-center text-muted">Aucune mesure enregistrée</td></tr>');
            }
        },
        error: function() {
            $('#mesures-tbody').html('<tr><td colspan="12" class="text-center text-danger">Erreur de chargement</td></tr>');
        }
    });
}

// Fonction pour charger les commandes de confection (sans bouton)
function chargerConfections(idClient) {
    $.ajax({
        url: '../api/modules/get_client.php',
        type: 'GET',
        data: { id: idClient },
        dataType: 'json',
        success: function(data) {
            if (data.success && data.client.commandes_confection && data.client.commandes_confection.length > 0) {
                var html = '';
                data.client.commandes_confection.forEach(function(c) {
                    var statutClass = '';
                    switch(c.statut) {
                        case 'en_attente': statutClass = 'badge-warning'; break;
                        case 'en_cours': statutClass = 'badge-info'; break;
                        case 'termine': statutClass = 'badge-success'; break;
                        case 'livre': statutClass = 'badge-primary'; break;
                        case 'annule': statutClass = 'badge-danger'; break;
                        default: statutClass = 'badge-secondary';
                    }
                    
                    var dateLivraison = '-';
                    if (c.date_livraison_prevue && c.date_livraison_prevue !== '0000-00-00') {
                        var d = new Date(c.date_livraison_prevue);
                        if (!isNaN(d.getTime())) {
                            dateLivraison = d.toLocaleDateString('fr-FR');
                        }
                    }
                    
                    html += '<tr>';
                    html += '<td>' + (c.numero_commande || '-') + '</td>';
                    html += '<td>' + (c.type_tenue || '-') + '</td>';
                    html += '<td>' + (c.montant_total || 0).toLocaleString() + ' FCFA</td>';
                    html += '<td>' + (c.montant_avance || 0).toLocaleString() + ' FCFA</td>';
                    html += '<td>' + ((c.total_paye || 0)).toLocaleString() + ' FCFA</td>';
                    html += '<td>' + ((c.solde_restant_calcule || 0)).toLocaleString() + ' FCFA</td>';
                    html += '<td><span class="badge ' + statutClass + '">' + c.statut + '</span></td>';
                    html += '<td>' + (c.prestataire_nom ? c.prestataire_nom + ' ' + (c.prestataire_prenom || '') : '-') + '</td>';
                    html += '<td>' + dateLivraison + '</td>';
                    html += '</tr>';
                });
                $('#confections-tbody').html(html);
            } else {
                $('#confections-tbody').html('<tr><td colspan="9" class="text-center text-muted">Aucune commande de confection</td></tr>');
            }
        },
        error: function() {
            $('#confections-tbody').html('<tr><td colspan="9" class="text-center text-danger">Erreur de chargement</td></tr>');
        }
    });
}

// Fonction pour charger l'historique achats
function chargerAchats(idClient) {
    $.ajax({
        url: '../api/modules/get_client.php',
        type: 'GET',
        data: { id: idClient },
        dataType: 'json',
        success: function(data) {
            if (data.success && data.client.historique_achats && data.client.historique_achats.length > 0) {
                var html = '';
                data.client.historique_achats.forEach(function(a) {
                    var dateVente = '-';
                    if (a.date_vente && a.date_vente !== '0000-00-00') {
                        var d = new Date(a.date_vente);
                        if (!isNaN(d.getTime())) {
                            dateVente = d.toLocaleDateString('fr-FR');
                        }
                    }
                    html += '<tr>';
                    html += '<td>' + (a.numero_vente || '-') + '</td>';
                    html += '<td>' + dateVente + '</td>';
                    html += '<td>' + (a.total_ttc || 0).toLocaleString() + ' FCFA</td>';
                    html += '<td>' + (a.mode_paiement || '-') + '</td>';
                    html += '<td>' + (a.caissier || '-') + '</td>';
                    html += '</tr>';
                });
                $('#achats-tbody').html(html);
                $('#total_achats').text((data.client.total_achats || 0).toLocaleString() + ' FCFA');
            } else {
                $('#achats-tbody').html('<tr><td colspan="5" class="text-center text-muted">Aucun achat enregistré</td></tr>');
                $('#total_achats').text('0 FCFA');
            }
        },
        error: function() {
            $('#achats-tbody').html('<tr><td colspan="5" class="text-center text-danger">Erreur de chargement</td></tr>');
        }
    });
}

// Fonction pour charger le profil complet
function chargerProfilComplet(idClient) {
    currentClientId = idClient;
    chargerMesures(idClient);
    chargerConfections(idClient);
    chargerAchats(idClient);
}

// Fonction pour ajouter une mesure
function ajouterMesure() {
    if (currentClientId) {
        $('#mesure_id_client').val(currentClientId);
        $('#mesure_id_mesure').val('');
        $('#form_mesure')[0].reset();
        $('#modal_mesure').modal('show');
    }
}

// Soumission du formulaire de mesure
$('#form_mesure').on('submit', function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    
    $.ajax({
        url: '../api/modules/save_mesure_client.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Succès', 'Mesures enregistrées avec succès', 'success');
                $('#modal_mesure').modal('hide');
                chargerMesures(currentClientId);
            } else {
                Swal.fire('Erreur', response.error || 'Erreur lors de l\'enregistrement', 'error');
            }
        },
        error: function() {
            Swal.fire('Erreur', 'Erreur réseau', 'error');
        }
    });
});
</script>