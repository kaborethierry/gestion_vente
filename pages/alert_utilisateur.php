<!-- Script affichant les SweetAlert -->
<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Utilisateur modifié
if (isset($_SESSION['mod']) && $_SESSION['mod'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Utilisateur modifié avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['mod'] = 0;
}

// Utilisateur supprimé
if (isset($_SESSION['supr']) && $_SESSION['supr'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Supprimé !',
  text: 'Utilisateur supprimé avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['supr'] = 0;
}

// Utilisateur ajouté
if (isset($_SESSION['ajout']) && $_SESSION['ajout'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Ajouté !',
  text: 'Utilisateur ajouté avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ajout'] = 0;
}

// Nom d'utilisateur déjà existant
if (isset($_SESSION['imp']) && $_SESSION['imp'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Un utilisateur possède déjà ce nom d\'utilisateur !',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['imp'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_util']) && $_SESSION['err_util'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_util'] = 0;
}
?>