<?php
// Fichier : pages/alert_vehicule.php
// La session est déjà démarrée dans pages/vehicules.php

// Véhicule ajouté
if (!empty($_SESSION['ajout_vehicule'])) {
    echo "<script>
Swal.fire('Véhicule ajouté!', 'Cliquez sur OK pour continuer', 'success');
</script>";
    $_SESSION['ajout_vehicule'] = 0;
}

// Véhicule modifié
if (!empty($_SESSION['mod_vehicule'])) {
    echo "<script>
Swal.fire('Véhicule modifié!', 'Cliquez sur OK pour continuer', 'success');
</script>";
    $_SESSION['mod_vehicule'] = 0;
}

// Véhicule supprimé
if (!empty($_SESSION['suppr_vehicule'])) {
    echo "<script>
Swal.fire('Véhicule supprimé!', 'Cliquez sur OK pour continuer', 'success');
</script>";
    $_SESSION['suppr_vehicule'] = 0;
}

// Immatriculation déjà existante
if (!empty($_SESSION['vehicule_existe'])) {
    echo "<script>
Swal.fire('Un véhicule possède déjà cette immatriculation!', 'Veuillez vérifier les informations.', 'error');
</script>";
    $_SESSION['vehicule_existe'] = 0;
}

// Erreur technique générique
if (!empty($_SESSION['vehicule_err'])) {
    echo "<script>
Swal.fire('Erreur lors du traitement', 'Une erreur technique est survenue.', 'error');
</script>";
    $_SESSION['vehicule_err'] = 0;
}
