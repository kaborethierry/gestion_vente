<!-- Script affichant les SweetAlert pour les clients -->
<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Client modifié
if (isset($_SESSION['mod_client']) && $_SESSION['mod_client'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Client modifié avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['mod_client'] = 0;
}

// Client supprimé
if (isset($_SESSION['supr_client']) && $_SESSION['supr_client'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Supprimé !',
  text: 'Client supprimé avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['supr_client'] = 0;
}

// Client ajouté
if (isset($_SESSION['ajout_client']) && $_SESSION['ajout_client'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Ajouté !',
  text: 'Client ajouté avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ajout_client'] = 0;
}

// Téléphone déjà existant
if (isset($_SESSION['imp_client']) && $_SESSION['imp_client'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Un client possède déjà ce numéro de téléphone !',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['imp_client'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_client']) && $_SESSION['err_client'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_client'] = 0;
}
?>