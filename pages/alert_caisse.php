<!-- Script affichant les SweetAlert pour la caisse -->
<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Session modifiée
if (isset($_SESSION['mod']) && $_SESSION['mod'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Session de caisse modifiée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['mod'] = 0;
}

// Session fermée
if (isset($_SESSION['ferme']) && $_SESSION['ferme'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Fermeture !',
  text: 'Session de caisse fermée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ferme'] = 0;
}

// Session ouverte
if (isset($_SESSION['ajout']) && $_SESSION['ajout'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Ouverture !',
  text: 'Session de caisse ouverte avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ajout'] = 0;
}

// Session déjà ouverte pour ce caissier
if (isset($_SESSION['imp']) && $_SESSION['imp'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Ce caissier a déjà une session ouverte !',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['imp'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_caisse']) && $_SESSION['err_caisse'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_caisse'] = 0;
}
?>