<!-- pages/modals/modal_utilisateur.php -->
<!-- DANFANIMENT POS - Modals pour la gestion des utilisateurs -->

<!-- Modal : Ajouter un utilisateur -->
<div class="modal fade" id="ajouter_utilisateur" tabindex="-1" role="dialog" aria-labelledby="ajouterUtilisateurLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterUtilisateurLabel">
          <i class="fas fa-user-plus"></i> Ajout d'un nouvel utilisateur
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/ajouter_utilisateur.php" method="POST">
        <div class="modal-body">
        
          <div class="form-group row">
            <div class="col-md-12">
              <label for="nom_complet">Nom complet *</label>
              <input type="text" class="form-control" id="nom_complet" name="nom_complet" placeholder="Entrez le nom complet" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="nom_utilisateur">Nom d'utilisateur *</label>
              <input type="text" class="form-control" id="nom_utilisateur" name="nom_utilisateur" placeholder="Entrez le nom d'utilisateur" required>
            </div>
            <div class="col-md-6">
              <label for="mot_de_passe">Mot de passe *</label>
              <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez le mot de passe" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="email">Email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Entrez l'email">
            </div>
            <div class="col-md-6">
              <label for="telephone">Téléphone</label>
              <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Entrez le téléphone">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="role">Rôle *</label>
              <select class="form-control" id="role" name="role" required>
                <option value="admin">Administrateur</option>
                <option value="caissier">Caissier</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="status">Statut *</label>
              <select class="form-control" id="status" name="status" required>
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
              </select>
            </div>
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

<!-- Modal : Modifier un utilisateur -->
<div class="modal fade" id="modifier_utilisateur" tabindex="-1" role="dialog" aria-labelledby="modifierUtilisateurLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierUtilisateurLabel">
          <i class="fas fa-user-edit"></i> Modification d'un utilisateur
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="../api/modules/modifier_utilisateur.php" method="POST">
        <div class="modal-body">
        
          <!-- ID caché pour identification -->
          <input type="hidden" id="id_utilisateur_modif" name="id_utilisateur" value="">
        
          <div class="form-group row">
            <div class="col-md-12">
              <label for="nom_complet_modif">Nom complet *</label>
              <input type="text" class="form-control" id="nom_complet_modif" name="nom_complet" placeholder="Entrez le nom complet" required>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="nom_utilisateur_modif">Nom d'utilisateur *</label>
              <input type="text" class="form-control" id="nom_utilisateur_modif" name="nom_utilisateur" placeholder="Entrez le nom d'utilisateur" required>
            </div>
            <div class="col-md-6">
              <label for="mot_de_passe_modif">Mot de passe</label>
              <input type="password" class="form-control" id="mot_de_passe_modif" name="mot_de_passe" placeholder="Laisser vide pour conserver l'actuel">
              <small class="text-muted">Laissez vide pour ne pas modifier le mot de passe</small>
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="email_modif">Email</label>
              <input type="email" class="form-control" id="email_modif" name="email" placeholder="Entrez l'email">
            </div>
            <div class="col-md-6">
              <label for="telephone_modif">Téléphone</label>
              <input type="text" class="form-control" id="telephone_modif" name="telephone" placeholder="Entrez le téléphone">
            </div>
          </div>
          
          <div class="form-group row">
            <div class="col-md-6">
              <label for="role_modif">Rôle *</label>
              <select class="form-control" id="role_modif" name="role" required>
                <option value="admin">Administrateur</option>
                <option value="caissier">Caissier</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="status_modif">Statut *</label>
              <select class="form-control" id="status_modif" name="status" required>
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <small class="text-muted">
              <span class="text-danger">*</span> Champs obligatoires.  
              Laisser <em>Mot de passe</em> vide pour ne pas le modifier.
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