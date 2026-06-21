<!-- Script affichant les SweetAlert pour les mesures -->
<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Mesure ajoutée
if (isset($_SESSION['ajout_mesure']) && $_SESSION['ajout_mesure'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Mesures ajoutées avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ajout_mesure'] = 0;
}

// Mesure modifiée
if (isset($_SESSION['mod_mesure']) && $_SESSION['mod_mesure'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Mesures modifiées avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['mod_mesure'] = 0;
}

// Mesure supprimée
if (isset($_SESSION['suppr_mesure']) && $_SESSION['suppr_mesure'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Supprimé !',
  text: 'Mesures supprimées avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['suppr_mesure'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_mesure']) && $_SESSION['err_mesure'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_mesure'] = 0;
}
?>