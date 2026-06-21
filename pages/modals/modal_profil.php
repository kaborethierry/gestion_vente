<!-- pages/modals/modal_profil.php -->
<!-- DANFANIMENT POS - Modals pour la gestion du profil -->

<!-- Modal : Modifier le profil -->
<div class="modal fade" id="modifier_profil" tabindex="-1" role="dialog" aria-labelledby="modifierProfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
                <h5 class="modal-title" id="modifierProfilLabel">
                    <i class="fas fa-user-edit"></i> Modification du profil
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="../api/modules/modifier_profil.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="id_user" name="id_user" value="<?= htmlspecialchars($_SESSION['id'] ?? '') ?>">

                    <div class="form-group">
                        <label for="nom_complet">Nom complet *</label>
                        <input type="text" class="form-control" id="nom_complet" name="nom_complet" 
                               value="<?= htmlspecialchars($_SESSION['nom_complet'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Nom d'utilisateur *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" 
                               value="<?= htmlspecialchars($_SESSION['telephone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="role">Rôle *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="admin" <?= ($_SESSION['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Administrateur</option>
                            <option value="caissier" <?= ($_SESSION['role'] ?? '') == 'caissier' ? 'selected' : '' ?>>Caissier</option>
                        </select>
                    </div>

                    <small class="text-muted">NB : Les champs avec (*) sont obligatoires.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #DC2626; border-color: #DC2626;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal : Modifier le mot de passe -->
<div class="modal fade" id="modifier_password" tabindex="-1" role="dialog" aria-labelledby="modifierPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #F59E0B, #DC2626); color: white;">
                <h5 class="modal-title" id="modifierPasswordLabel">
                    <i class="fas fa-key"></i> Modification du mot de passe
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="RegForm" onsubmit="return verificationPassword()" action="../api/modules/modifier_password.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="id_user_pw" name="id_user" value="<?= htmlspecialchars($_SESSION['id'] ?? '') ?>">

                    <div class="form-group">
                        <label for="old_pass">Ancien mot de passe *</label>
                        <input type="password" class="form-control" id="old_pass" name="old_pass" placeholder="Entrer l'ancien mot de passe" required>
                    </div>

                    <div class="form-group">
                        <label for="new_pass">Nouveau mot de passe *</label>
                        <input type="password" class="form-control" id="new_pass" name="new_pass" placeholder="Entrer le nouveau mot de passe" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_pass">Confirmation du mot de passe *</label>
                        <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" placeholder="Confirmer le mot de passe" required>
                    </div>

                    <small class="text-muted">NB : Les champs avec (*) sont obligatoires.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #F59E0B; border-color: #F59E0B;">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function verificationPassword() {
    var new_pass = document.getElementById('new_pass').value;
    var confirm_pass = document.getElementById('confirm_pass').value;
    
    if (new_pass !== confirm_pass) {
        Swal.fire({
            title: 'Erreur',
            text: 'Les mots de passe ne correspondent pas',
            icon: 'error',
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    if (new_pass.length < 6) {
        Swal.fire({
            title: 'Erreur',
            text: 'Le mot de passe doit contenir au moins 6 caractères',
            icon: 'error',
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    return true;
}
</script>