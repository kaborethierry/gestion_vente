<!-- pages/modals/modal_employe.php -->

<!-- Modal : Ajouter un employé -->
<div class="modal fade" id="ajouter_employe" tabindex="-1" role="dialog" aria-labelledby="ajouterEmployeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterEmployeLabel">Ajout d'un nouvel employé</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/ajouter_employe.php" method="POST" novalidate>
        <div class="modal-body">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="emp_nom">Nom <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="emp_nom" name="nom" placeholder="Entrez le nom" maxlength="50" required>
            </div>
            <div class="col-md-6">
              <label for="emp_prenom">Prénom(s) <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="emp_prenom" name="prenom" placeholder="Entrez le(s) prénom(s)" maxlength="50" required>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="emp_poste">Poste <span class="text-danger">*</span></label>
              <select class="form-control" id="emp_poste" name="poste" required>
                <option value="" disabled selected>— Sélectionnez le poste —</option>
                <option value="Caissier">Caissier</option>
                <option value="Gestionnaire">Gestionnaire</option>
                <option value="Mécanicien polyvalent (généraliste)">Mécanicien polyvalent (généraliste)</option>
                <option value="Mécanicien spécialisé en moteur">Mécanicien spécialisé en moteur</option>
                <option value="Mécanicien en transmission">Mécanicien en transmission</option>
                <option value="Mécanicien en freinage et suspension">Mécanicien en freinage et suspension</option>
                <option value="Mécanicien en climatisation et chauffage">Mécanicien en climatisation et chauffage</option>
                <option value="Mécanicien en électricité et électronique automobile">Mécanicien en électricité et électronique automobile</option>
                <option value="Mécanicien diagnosticien">Mécanicien diagnosticien</option>
                <option value="Mécanicien en carrosserie et tôlerie">Mécanicien en carrosserie et tôlerie</option>
                <option value="Mécanicien en véhicules hybrides/électriques">Mécanicien en véhicules hybrides/électriques</option>
                <option value="Technicien en pneumatiques et géométrie">Technicien en pneumatiques et géométrie</option>
                <option value="Mécanicien poids lourds">Mécanicien poids lourds</option>
                <option value="Mécanicien en systèmes de dépollution">Mécanicien en systèmes de dépollution</option>
                <option value="Mécanicien préparateur (tuning/sport)">Mécanicien préparateur (tuning/sport)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="emp_email">Email</label>
              <input type="email" class="form-control" id="emp_email" name="email" placeholder="Entrez l'email" maxlength="100">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="emp_tel">Téléphone</label>
              <input type="text" class="form-control" id="emp_tel" name="telephone" placeholder="Entrez le téléphone" maxlength="20">
            </div>
            <div class="col-md-6">
              <label for="emp_date_embauche">Date d'embauche</label>
              <input type="date" class="form-control" id="emp_date_embauche" name="date_embauche">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="emp_salaire">Salaire de base</label>
              <input type="number" class="form-control" id="emp_salaire" name="salaire_base" placeholder="Ex: 250000" min="0" step="0.01">
            </div>
            <div class="col-md-6">
              <label for="emp_statut">Statut <span class="text-danger">*</span></label>
              <select class="form-control" id="emp_statut" name="statut" required>
                <option value="Actif" selected>Actif</option>
                <option value="Suspendu">Suspendu</option>
                <option value="Archivé">Archivé</option>
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

<!-- Modal : Modifier un employé -->
<div class="modal fade" id="modifier_employe" tabindex="-1" role="dialog" aria-labelledby="modifierEmployeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierEmployeLabel">Modification d'un employé</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/modifier_employe.php" method="POST" novalidate>
        <div class="modal-body">

          <!-- ID caché pour identification -->
          <input type="hidden" id="id_employe_modif" name="id_employe" value="">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="emp_nom_modif">Nom <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="emp_nom_modif" name="nom" placeholder="Entrez le nom" maxlength="50" required>
            </div>
            <div class="col-md-6">
              <label for="emp_prenom_modif">Prénom(s) <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="emp_prenom_modif" name="prenom" placeholder="Entrez le(s) prénom(s)" maxlength="50" required>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="emp_poste_modif">Poste <span class="text-danger">*</span></label>
              <select class="form-control" id="emp_poste_modif" name="poste" required>
                <option value="" disabled>— Sélectionnez le poste —</option>
                <option value="Caissier">Caissier</option>
                <option value="Gestionnaire">Gestionnaire</option>
                <option value="Mécanicien polyvalent (généraliste)">Mécanicien polyvalent (généraliste)</option>
                <option value="Mécanicien spécialisé en moteur">Mécanicien spécialisé en moteur</option>
                <option value="Mécanicien en transmission">Mécanicien en transmission</option>
                <option value="Mécanicien en freinage et suspension">Mécanicien en freinage et suspension</option>
                <option value="Mécanicien en climatisation et chauffage">Mécanicien en climatisation et chauffage</option>
                <option value="Mécanicien en électricité et électronique automobile">Mécanicien en électricité et électronique automobile</option>
                <option value="Mécanicien diagnosticien">Mécanicien diagnosticien</option>
                <option value="Mécanicien en carrosserie et tôlerie">Mécanicien en carrosserie et tôlerie</option>
                <option value="Mécanicien en véhicules hybrides/électriques">Mécanicien en véhicules hybrides/électriques</option>
                <option value="Technicien en pneumatiques et géométrie">Technicien en pneumatiques et géométrie</option>
                <option value="Mécanicien poids lourds">Mécanicien poids lourds</option>
                <option value="Mécanicien en systèmes de dépollution">Mécanicien en systèmes de dépollution</option>
                <option value="Mécanicien préparateur (tuning/sport)">Mécanicien préparateur (tuning/sport)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="emp_email_modif">Email</label>
              <input type="email" class="form-control" id="emp_email_modif" name="email" placeholder="Entrez l'email" maxlength="100">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="emp_tel_modif">Téléphone</label>
              <input type="text" class="form-control" id="emp_tel_modif" name="telephone" placeholder="Entrez le téléphone" maxlength="20">
            </div>
            <div class="col-md-6">
              <label for="emp_date_embauche_modif">Date d'embauche</label>
              <input type="date" class="form-control" id="emp_date_embauche_modif" name="date_embauche">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="emp_salaire_modif">Salaire de base</label>
              <input type="number" class="form-control" id="emp_salaire_modif" name="salaire_base" placeholder="Ex: 250000" min="0" step="0.01">
            </div>
            <div class="col-md-6">
              <label for="emp_statut_modif">Statut <span class="text-danger">*</span></label>
              <select class="form-control" id="emp_statut_modif" name="statut" required>
                <option value="Actif">Actif</option>
                <option value="Suspendu">Suspendu</option>
                <option value="Archivé">Archivé</option>
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
