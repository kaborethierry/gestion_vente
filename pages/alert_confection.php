<!-- Script affichant les SweetAlert pour confection -->
<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Commande modifiée
if (isset($_SESSION['mod_conf']) && $_SESSION['mod_conf'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Commande modifiée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['mod_conf'] = 0;
}

// Commande supprimée
if (isset($_SESSION['supr_conf']) && $_SESSION['supr_conf'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Supprimé !',
  text: 'Commande supprimée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['supr_conf'] = 0;
}

// Commande ajoutée
if (isset($_SESSION['ajout_conf']) && $_SESSION['ajout_conf'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Ajouté !',
  text: 'Commande ajoutée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ajout_conf'] = 0;
}

// Statut modifié
if (isset($_SESSION['statut_conf']) && $_SESSION['statut_conf'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Statut de la commande mis à jour !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['statut_conf'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_conf']) && $_SESSION['err_conf'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_conf'] = 0;
}
?>