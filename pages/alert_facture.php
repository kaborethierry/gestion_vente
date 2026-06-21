<?php
// pages/alert_facture.php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Facture ajoutée
if (isset($_SESSION['facture_ajoutee']) && $_SESSION['facture_ajoutee'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'La facture a été créée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['facture_ajoutee'] = 0;
}

// Facture modifiée
if (isset($_SESSION['facture_modifiee']) && $_SESSION['facture_modifiee'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'La facture a été modifiée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['facture_modifiee'] = 0;
}

// Facture supprimée
if (isset($_SESSION['facture_supprimee']) && $_SESSION['facture_supprimee'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Supprimée !',
  text: 'La facture a été supprimée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['facture_supprimee'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_facture']) && $_SESSION['err_facture'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_facture'] = 0;
}
?>