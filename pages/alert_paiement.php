<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Paiement ajouté avec succès
if (isset($_SESSION['paiement_success']) && $_SESSION['paiement_success'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Succès !',
        text: 'Le paiement a été enregistré avec succès !',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK',
        timer: 3000
    }).then(() => {
        if (typeof printRecu === 'function' && '" . ($_SESSION['last_recu_num'] ?? '') . "') {
            printRecu('" . ($_SESSION['last_recu_num'] ?? '') . "');
        }
    });
    </script>";
    $_SESSION['paiement_success'] = 0;
    unset($_SESSION['last_recu_num']);
}

// Paiement modifié avec succès
if (isset($_SESSION['paiement_modifie']) && $_SESSION['paiement_modifie'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Succès !',
        text: 'Le paiement a été modifié avec succès !',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK'
    });
    </script>";
    $_SESSION['paiement_modifie'] = 0;
}

// Paiement supprimé avec succès
if (isset($_SESSION['paiement_supprime']) && $_SESSION['paiement_supprime'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Supprimé !',
        text: 'Le paiement a été supprimé avec succès.',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK'
    });
    </script>";
    $_SESSION['paiement_supprime'] = 0;
}

// Erreur
if (isset($_SESSION['err_paiement']) && $_SESSION['err_paiement'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Erreur !',
        text: 'Une erreur est survenue. Veuillez réessayer.',
        icon: 'error',
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'OK'
    });
    </script>";
    $_SESSION['err_paiement'] = 0;
}

// Montant trop élevé
if (isset($_SESSION['montant_trop_eleve']) && $_SESSION['montant_trop_eleve'] === 1) {
    echo "<script>
    Swal.fire({
        title: 'Montant trop élevé !',
        text: 'Le montant saisi dépasse le solde restant à payer.',
        icon: 'warning',
        confirmButtonColor: '#F59E0B',
        confirmButtonText: 'OK'
    });
    </script>";
    $_SESSION['montant_trop_eleve'] = 0;
}
?>