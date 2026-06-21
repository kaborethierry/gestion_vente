<?php
// pages/alert_categorie_piece.php

session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Catégorie ajoutée
if (isset($_SESSION['ajout_categorie']) && $_SESSION['ajout_categorie'] === 1) {
    echo "<script>
Swal.fire(
  'Catégorie ajoutée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['ajout_categorie'] = 0;
}

// Catégorie modifiée
if (isset($_SESSION['modif_categorie']) && $_SESSION['modif_categorie'] === 1) {
    echo "<script>
Swal.fire(
  'Catégorie modifiée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['modif_categorie'] = 0;
}

// Catégorie supprimée
if (isset($_SESSION['suppr_categorie']) && $_SESSION['suppr_categorie'] === 1) {
    echo "<script>
Swal.fire(
  'Catégorie supprimée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['suppr_categorie'] = 0;
}

// Libellé déjà existant
if (isset($_SESSION['doublon_categorie']) && $_SESSION['doublon_categorie'] === 1) {
    echo "<script>
Swal.fire(
  'Une catégorie possède déjà ce libellé!',
  'Cliquez sur OK pour continuer',
  'error'
)
</script>";
    $_SESSION['doublon_categorie'] = 0;
}
?>
