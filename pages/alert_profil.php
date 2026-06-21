<!-- pages/alert_profil.php -->
<?php
if (empty($_SESSION['id'])) {
    header('Location:./../index.php?erreur=3');
    exit;
}

if (isset($_SESSION['modif_username']) && $_SESSION['modif_username'] == 1) {
?>
<script>
    Swal.fire({
        title: 'Profil modifié avec succès',
        text: 'Cliquez sur OK pour continuer',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK'
    });
</script>
<?php
    $_SESSION['modif_username'] = 0;
}

if (isset($_SESSION['anc_password']) && $_SESSION['anc_password'] == 1) {
?>
<script>
    Swal.fire({
        title: 'Ancien mot de passe incorrect',
        text: 'Cliquez sur OK pour continuer',
        icon: 'error',
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'OK'
    });
</script>
<?php
    $_SESSION['anc_password'] = 0;
}

if (isset($_SESSION['modif_password']) && $_SESSION['modif_password'] == 1) {
?>
<script>
    Swal.fire({
        title: 'Mot de passe modifié avec succès',
        text: 'Cliquez sur OK pour continuer',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK'
    });
</script>
<?php
    $_SESSION['modif_password'] = 0;
}

if (isset($_SESSION['imp']) && $_SESSION['imp'] == 1) {
?>
<script>
    Swal.fire({
        title: 'Erreur',
        text: 'Ce nom d\'utilisateur est déjà utilisé',
        icon: 'error',
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'OK'
    });
</script>
<?php
    $_SESSION['imp'] = 0;
}

if (isset($_SESSION['err_profil']) && $_SESSION['err_profil'] == 1) {
?>
<script>
    Swal.fire({
        title: 'Erreur technique',
        text: 'Une erreur est survenue. Veuillez réessayer.',
        icon: 'error',
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'OK'
    });
</script>
<?php
    $_SESSION['err_profil'] = 0;
}
?>