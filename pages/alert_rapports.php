<?php
// pages/alert_rapports.php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

if (isset($_SESSION['rapport_genere']) && $_SESSION['rapport_genere'] === 1) {
    echo "<script>
Swal.fire({
    title: 'Succès!',
    text: 'Rapport généré avec succès!',
    icon: 'success',
    confirmButtonColor: '#10B981',
    confirmButtonText: 'OK'
});
</script>";
    $_SESSION['rapport_genere'] = 0;
}

if (isset($_SESSION['err_rapport']) && $_SESSION['err_rapport'] === 1) {
    echo "<script>
Swal.fire({
    title: 'Erreur!',
    text: 'Une erreur est survenue lors de la génération du rapport.',
    icon: 'error',
    confirmButtonColor: '#DC2626',
    confirmButtonText: 'OK'
});
</script>";
    $_SESSION['err_rapport'] = 0;
}
?>