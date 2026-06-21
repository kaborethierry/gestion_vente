<!-- Script affichant les SweetAlert pour produits -->
<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Produit modifié
if (isset($_SESSION['mod_produit']) && $_SESSION['mod_produit'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Produit modifié avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['mod_produit'] = 0;
}

// Produit supprimé
if (isset($_SESSION['supr_produit']) && $_SESSION['supr_produit'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Supprimé !',
  text: 'Produit supprimé avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['supr_produit'] = 0;
}

// Produit ajouté
if (isset($_SESSION['ajout_produit']) && $_SESSION['ajout_produit'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Ajouté !',
  text: 'Produit ajouté avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['ajout_produit'] = 0;
}

// Code produit déjà existant
if (isset($_SESSION['code_exist']) && $_SESSION['code_exist'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Ce code produit existe déjà !',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['code_exist'] = 0;
}

// Import CSV réussi
if (isset($_SESSION['import_csv']) && $_SESSION['import_csv'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Import CSV réussi !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['import_csv'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_produit']) && $_SESSION['err_produit'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_produit'] = 0;
}
?>