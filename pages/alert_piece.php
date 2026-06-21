<?php
// pages/alert_piece.php

session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Pièce modifiée
if (isset($_SESSION['mod_piece']) && $_SESSION['mod_piece'] === 1) {
    echo "<script>
Swal.fire(
  'Pièce modifiée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['mod_piece'] = 0;
}

// Pièce supprimée
if (isset($_SESSION['suppr_piece']) && $_SESSION['suppr_piece'] === 1) {
    echo "<script>
Swal.fire(
  'Pièce supprimée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['suppr_piece'] = 0;
}

// Pièce ajoutée
if (isset($_SESSION['ajout_piece']) && $_SESSION['ajout_piece'] === 1) {
    echo "<script>
Swal.fire(
  'Pièce ajoutée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['ajout_piece'] = 0;
}

// Référence déjà existante
if (isset($_SESSION['ref_exist']) && $_SESSION['ref_exist'] === 1) {
    echo "<script>
Swal.fire(
  'Cette référence existe déjà!',
  'Cliquez sur OK pour continuer',
  'error'
)
</script>";
    $_SESSION['ref_exist'] = 0;
}
?>
