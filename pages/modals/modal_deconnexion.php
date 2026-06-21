<!-- modals/modal_deconnexion.php -->
<!-- DANFANIMENT POS - Modal de déconnexion -->

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt"></i> Confirmation de déconnexion
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-question-circle fa-3x mb-3" style="color: #F59E0B;"></i>
                <p>Souhaitez-vous vraiment vous déconnecter ?</p>
                <p class="text-muted small">Vous devrez vous reconnecter pour accéder à votre compte.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <a class="btn btn-danger" href="../api/modules/deconnexion.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</div>