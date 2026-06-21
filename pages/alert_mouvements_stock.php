<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Mouvement ajouté avec succès
if (isset($_SESSION['mouvement_success']) && $_SESSION['mouvement_success'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Succès !',
        text: 'Le mouvement de stock a été enregistré avec succès !',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK',
        timer: 3000
    });
    </script>";
    $_SESSION['mouvement_success'] = 0;
}

// Mouvement modifié avec succès
if (isset($_SESSION['mouvement_modifie']) && $_SESSION['mouvement_modifie'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Succès !',
        text: 'Le mouvement a été modifié avec succès !',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK'
    });
    </script>";
    $_SESSION['mouvement_modifie'] = 0;
}

// Mouvement supprimé avec succès
if (isset($_SESSION['mouvement_supprime']) && $_SESSION['mouvement_supprime'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Supprimé !',
        text: 'Le mouvement a été supprimé avec succès.',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK'
    });
    </script>";
    $_SESSION['mouvement_supprime'] = 0;
}

// Erreur
if (isset($_SESSION['err_mouvement']) && $_SESSION['err_mouvement'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Erreur !',
        text: 'Une erreur est survenue. Veuillez réessayer.',
        icon: 'error',
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'OK'
    });
    </script>";
    $_SESSION['err_mouvement'] = 0;
}

// Stock négatif
if (isset($_SESSION['stock_negatif']) && $_SESSION['stock_negatif'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Stock insuffisant !',
        text: 'Cette opération rendrait le stock négatif. Vérifiez la quantité.',
        icon: 'warning',
        confirmButtonColor: '#F59E0B',
        confirmButtonText: 'OK'
    });
    </script>";
    $_SESSION['stock_negatif'] = 0;
}
?>