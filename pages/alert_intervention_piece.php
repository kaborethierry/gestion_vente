<?php
// pages/alert_intervention_piece.php

session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Intervention/pièce modifiée
if (isset($_SESSION['mod_intervention_piece']) && $_SESSION['mod_intervention_piece'] === 1) {
    echo "<script>
Swal.fire(
  'Ligne intervention/pièce modifiée !',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['mod_intervention_piece'] = 0;
}

// Intervention/pièce supprimée
if (isset($_SESSION['suppr_intervention_piece']) && $_SESSION['suppr_intervention_piece'] === 1) {
    echo "<script>
Swal.fire(
  'Ligne intervention/pièce supprimée !',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['suppr_intervention_piece'] = 0;
}

// Intervention/pièce ajoutée
if (isset($_SESSION['ajout_intervention_piece']) && $_SESSION['ajout_intervention_piece'] === 1) {
    echo "<script>
Swal.fire(
  'Ligne intervention/pièce ajoutée !',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['ajout_intervention_piece'] = 0;
}

// Référence intervention/pièce déjà existante (si applicable)
if (isset($_SESSION['intervention_piece_exist']) && $_SESSION['intervention_piece_exist'] === 1) {
    echo "<script>
Swal.fire(
  'Cette combinaison intervention/pièce existe déjà !',
  'Cliquez sur OK pour continuer',
  'error'
)
</script>";
    $_SESSION['intervention_piece_exist'] = 0;
}
?>
