<?php
// pages/alert_employe.php

session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Employé modifié
if (isset($_SESSION['mod']) && $_SESSION['mod'] === 1) {
    echo "<script>
Swal.fire(
  'Employé modifié!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['mod'] = 0;
}

// Employé supprimé
if (isset($_SESSION['supr']) && $_SESSION['supr'] === 1) {
    echo "<script>
Swal.fire(
  'Employé supprimé!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['supr'] = 0;
}

// Employé ajouté
if (isset($_SESSION['ajout']) && $_SESSION['ajout'] === 1) {
    echo "<script>
Swal.fire(
  'Employé ajouté!',
  'Cliquez sur OK pour continuer',
  'success'
)
</script>";
    $_SESSION['ajout'] = 0;
}

// Email déjà existant
if (isset($_SESSION['imp']) && $_SESSION['imp'] === 1) {
    echo "<script>
Swal.fire(
  'Un employé possède déjà cet email!',
  'Cliquez sur OK pour continuer',
  'error'
)
</script>";
    $_SESSION['imp'] = 0;
}
?>
