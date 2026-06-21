<!-- Script affichant les SweetAlert pour dépenses -->
<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Dépense modifiée
if (isset($_SESSION['mod_depense']) && $_SESSION['mod_depense'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Dépense modifiée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['mod_depense'] = 0;
}

// Dépense supprimée
if (isset($_SESSION['supr_depense']) && $_SESSION['supr_depense'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Supprimé !',
  text: 'Dépense supprimée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['supr_depense'] = 0;
}

// Dépense ajoutée
if (isset($_SESSION['ajout_depense']) && $_SESSION['ajout_depense'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Ajouté !',
  text: 'Dépense ajoutée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ajout_depense'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_depense']) && $_SESSION['err_depense'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_depense'] = 0;
}
?>