<!-- pages/modals/modal_caisse.php -->
<!-- DANFANIMENT POS - Modals pour la gestion des caisses -->

<!-- Modal : Ouvrir une session de caisse -->
<div class="modal fade" id="ajouter_caisse" tabindex="-1" role="dialog" aria-labelledby="ajouterCaisseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterCaisseLabel">
          <i class="fas fa-cash-register"></i> Ouverture d'une session de caisse
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_caisse.php" method="POST">
        <div class="modal-body">
        
          <div class="form-group row">
            <div class="col-md-12">
              <label for="id_utilisateur">Caissier *</label>
              <select class="form-control" id="id_utilisateur" name="id_utilisateur" required>
                <option value="">Sélectionnez un caissier</option>
                <?php
                require_once __DIR__ . '/../../api/modules/connect_db_pdo.php';
                try {
                    $stmt = $bdd->prepare("SELECT id_utilisateur, nom_complet FROM utilisateurs WHERE role = 'caissier' AND actif = 1 AND supprimer = 'Non' ORDER BY nom_complet");
                    $stmt->execute();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $row['id_utilisateur'] . '">' . htmlspecialchars($row['nom_complet']) . '</option>';
                    }
                } catch (PDOException $e) {
                    echo '<option value="">Erreur de chargement</option>';
                }
                ?>
              </select>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-12">
              <label for="montant_initial">Montant initial (FCFA) *</label>
              <input type="number" step="0.01" class="form-control" id="montant_initial" name="montant_initial" placeholder="Entrez le montant initial de la caisse" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-12">
              <label for="notes_ouverture">Notes d'ouverture</label>
              <textarea class="form-control" id="notes_ouverture" name="notes_ouverture" rows="3" placeholder="Notes optionnelles sur l'ouverture de session"></textarea>
            </div>
          </div>
          
          <div class="form-group">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
          </div>
        
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" style="background-color: #DC2626; border-color: #DC2626;">Ouvrir la session</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Modifier une session de caisse -->
<div class="modal fade" id="modifier_caisse" tabindex="-1" role="dialog" aria-labelledby="modifierCaisseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierCaisseLabel">
          <i class="fas fa-edit"></i> Modification d'une session de caisse
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_caisse.php" method="POST">
        <div class="modal-body">
        
          <input type="hidden" id="id_caisse_modif" name="id_caisse" value="">
        
          <div class="form-group row">
            <div class="col-md-12">
              <label for="id_session_modif">Session ID</label>
              <input type="text" class="form-control" id="id_session_modif" name="id_session" readonly>
              <small class="text-muted">Identifiant unique de session</small>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-12">
              <label for="id_utilisateur_modif">Caissier *</label>
              <select class="form-control" id="id_utilisateur_modif" name="id_utilisateur" required disabled>
                <option value="">Sélectionnez un caissier</option>
                <?php
                try {
                    $stmt = $bdd->prepare("SELECT id_utilisateur, nom_complet FROM utilisateurs WHERE role = 'caissier' AND supprimer = 'Non' ORDER BY nom_complet");
                    $stmt->execute();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $row['id_utilisateur'] . '">' . htmlspecialchars($row['nom_complet']) . '</option>';
                    }
                } catch (PDOException $e) {
                    echo '<option value="">Erreur de chargement</option>';
                }
                ?>
              </select>
              <small class="text-muted">Le caissier ne peut pas être modifié</small>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="montant_initial_modif">Montant initial (FCFA) *</label>
              <input type="number" step="0.01" class="form-control" id="montant_initial_modif" name="montant_initial" required>
            </div>
            <div class="col-md-6">
              <label for="montant_final_reel_modif">Montant final réel (FCFA)</label>
              <input type="number" step="0.01" class="form-control" id="montant_final_reel_modif" name="montant_final_reel">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="statut_modif">Statut *</label>
              <select class="form-control" id="statut_modif" name="statut" required>
                <option value="ouverte">Ouverte</option>
                <option value="suspendue">Suspendue</option>
                <option value="fermee">Fermée</option>
                <option value="cloturee">Clôturée</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="chiffre_affaires">Chiffre d'affaires (FCFA)</label>
              <input type="text" class="form-control" id="chiffre_affaires" readonly>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-12">
              <label for="notes_modif">Notes</label>
              <textarea class="form-control" id="notes_modif" name="notes" rows="3"></textarea>
            </div>
          </div>
          
          <div class="alert alert-warning mt-2">
            <i class="fas fa-exclamation-triangle"></i> Attention : La modification du montant initial affectera les calculs de la caisse.
          </div>
          
          <div class="form-group">
            <small class="text-muted">
              <span class="text-danger">*</span> Champs obligatoires.
            </small>
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

<!-- Modal : Fermer une session de caisse -->
<div class="modal fade" id="fermer_caisse" tabindex="-1" role="dialog" aria-labelledby="fermerCaisseLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #F59E0B, #DC2626); color: white;">
        <h5 class="modal-title" id="fermerCaisseLabel">
          <i class="fas fa-lock"></i> Fermeture de session
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/fermer_caisse.php" method="POST">
        <div class="modal-body">
          <input type="hidden" id="id_caisse_fermer" name="id_caisse" value="">
          
          <div class="form-group">
            <label>Montant final réel en caisse (FCFA) *</label>
            <input type="number" step="0.01" class="form-control" id="montant_final_reel_fermer" name="montant_final_reel" required>
          </div>
          
          <div class="form-group">
            <label>Notes de fermeture</label>
            <textarea class="form-control" name="notes_fermeture" rows="3" placeholder="Notes sur la fermeture de session"></textarea>
          </div>
          
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Attention : La fermeture d'une session est irréversible.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Fermer la session</button>
        </div>
      </form>
    </div>
  </div>
</div>