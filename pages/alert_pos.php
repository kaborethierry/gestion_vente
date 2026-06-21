<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// Vente effectuée avec succès
if (isset($_SESSION['vente_success']) && $_SESSION['vente_success'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Succès !',
  text: 'Vente effectuée avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  confirmButtonText: 'OK',
  timer: 3000
}).then(() => {
    if (typeof printTicket === 'function' && '" . ($_SESSION['last_ticket_num'] ?? '') . "') {
        printTicket('" . ($_SESSION['last_ticket_num'] ?? '') . "');
    }
});
</script>";
    $_SESSION['vente_success'] = 0;
    unset($_SESSION['last_ticket_num']);
}

// Stock insuffisant
if (isset($_SESSION['stock_error']) && $_SESSION['stock_error'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Stock insuffisant !',
  text: 'La quantité demandée dépasse le stock disponible !',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['stock_error'] = 0;
}

// Session fermée
if (isset($_SESSION['session_fermee']) && $_SESSION['session_fermee'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Session fermée',
  text: 'Votre session de caisse a été fermée. Veuillez en ouvrir une nouvelle.',
  icon: 'warning',
  confirmButtonColor: '#F59E0B'
})
</script>";
    $_SESSION['session_fermee'] = 0;
}

// Client ajouté
if (isset($_SESSION['client_ajoute']) && $_SESSION['client_ajoute'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Client ajouté',
  text: 'Le client a été ajouté avec succès !',
  icon: 'success',
  confirmButtonColor: '#10B981'
})
</script>";
    $_SESSION['client_ajoute'] = 0;
}

// Client trouvé
if (isset($_SESSION['client_trouve']) && $_SESSION['client_trouve'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Client trouvé',
  text: 'Le client a été associé à la vente !',
  icon: 'success',
  confirmButtonColor: '#10B981',
  timer: 2000
})
</script>";
    $_SESSION['client_trouve'] = 0;
}

// Erreur technique
if (isset($_SESSION['err_pos']) && $_SESSION['err_pos'] === 1) {
    echo "<script>
Swal.fire({
  title: 'Erreur technique !',
  text: 'Une erreur technique est survenue. Veuillez réessayer.',
  icon: 'error',
  confirmButtonColor: '#DC2626',
  confirmButtonText: 'OK'
})
</script>";
    $_SESSION['err_pos'] = 0;
}
?>