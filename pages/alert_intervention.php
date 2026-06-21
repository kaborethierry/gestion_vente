<?php
// pages/alert_intervention.php

session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Intervention ajoutée
if (isset($_SESSION['ajout_intervention']) && $_SESSION['ajout_intervention'] === 1) {
    echo "<script>
Swal.fire(
  'Intervention ajoutée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['ajout_intervention'] = 0;
}

// Intervention modifiée
if (isset($_SESSION['mod_intervention']) && $_SESSION['mod_intervention'] === 1) {
    echo "<script>
Swal.fire(
  'Intervention modifiée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['mod_intervention'] = 0;
}

// Intervention supprimée
if (isset($_SESSION['suppr_intervention']) && $_SESSION['suppr_intervention'] === 1) {
    echo "<script>
Swal.fire(
  'Intervention supprimée!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['suppr_intervention'] = 0;
}

// Intervention déjà en conflit (ex. chevauchement horaire)
if (isset($_SESSION['intervention_conflit']) && $_SESSION['intervention_conflit'] === 1) {
    echo "<script>
Swal.fire(
  'Conflit détecté!',
  'Une autre intervention chevauche cette période pour le véhicule ou l’employé',
  'error'
)
</script>";
    $_SESSION['intervention_conflit'] = 0;
}

// Véhicule ou employé invalide
if (isset($_SESSION['intervention_fk_invalide']) && $_SESSION['intervention_fk_invalide'] === 1) {
    echo "<script>
Swal.fire(
  'Données invalides!',
  'Le véhicule ou l’employé sélectionné est introuvable',
  'error'
)
</script>";
    $_SESSION['intervention_fk_invalide'] = 0;
}

// Erreur générale
if (isset($_SESSION['intervention_erreur']) && $_SESSION['intervention_erreur'] === 1) {
    echo "<script>
Swal.fire(
  'Erreur technique!',
  'Impossible d’enregistrer l’intervention, veuillez réessayer',
  'error'
)
</script>";
    $_SESSION['intervention_erreur'] = 0;
}
?>
