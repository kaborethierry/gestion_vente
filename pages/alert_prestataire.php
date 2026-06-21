<!-- Script affichant les SweetAlert pour prestataires -->
<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Prestataire modifié
if (isset($_SESSION['mod_presta']) && $_SESSION['mod_presta'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Prestataire modifié avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['mod_presta'] = 0;
}

// Prestataire supprimé
if (isset($_SESSION['supr_presta']) && $_SESSION['supr_presta'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Supprimé !',
  text: 'Prestataire supprimé avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['supr_presta'] = 0;
}

// Prestataire ajouté
if (isset($_SESSION['ajout_presta']) && $_SESSION['ajout_presta'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Ajouté !',
  text: 'Prestataire ajouté avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ajout_presta'] = 0;
}

// Production enregistrée
if (isset($_SESSION['prod_presta']) && $_SESSION['prod_presta'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Production enregistrée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['prod_presta'] = 0;
}

// Paiement généré
if (isset($_SESSION['paiement_presta']) && $_SESSION['paiement_presta'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Paiement généré avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['paiement_presta'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_presta']) && $_SESSION['err_presta'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_presta'] = 0;
}
?>