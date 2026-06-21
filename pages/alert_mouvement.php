<?php
// Fichier : pages/alert_mouvement.php

session_start();

if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit;
}

// ✅ Mouvement ajouté
if (isset($_SESSION['ajout_mouvement']) && $_SESSION['ajout_mouvement'] === 1) {
    echo "<script>
Swal.fire(
  'Mouvement ajouté!',
  'Le stock a été mis à jour avec succès.',
  'success'
);
</script>";
    $_SESSION['ajout_mouvement'] = 0;
}

// ✅ Mouvement modifié
if (isset($_SESSION['mod_mouvement']) && $_SESSION['mod_mouvement'] === 1) {
    echo "<script>
Swal.fire(
  'Mouvement modifié!',
  'Les ajustements ont été enregistrés.',
  'success'
);
</script>";
    $_SESSION['mod_mouvement'] = 0;
}

// ✅ Mouvement supprimé
if (isset($_SESSION['suppr_mouvement']) && $_SESSION['suppr_mouvement'] === 1) {
    echo "<script>
Swal.fire(
  'Mouvement supprimé!',
  'Le stock a été corrigé en conséquence.',
  'success'
);
</script>";
    $_SESSION['suppr_mouvement'] = 0;
}

// ⚠️ Erreur générique
if (isset($_SESSION['imp']) && $_SESSION['imp'] === 1) {
    echo "<script>
Swal.fire(
  'Erreur inattendue!',
  'Impossible d\'effectuer l\'opération. Vérifiez les données.',
  'error'
);
</script>";
    $_SESSION['imp'] = 0;
}

// ❌ Pièce invalide ou supprimée
if (isset($_SESSION['piece_invalide']) && $_SESSION['piece_invalide'] === 1) {
    echo "<script>
Swal.fire(
  'Pièce introuvable!',
  'La pièce sélectionnée n\'existe pas ou a été supprimée.',
  'error'
);
</script>";
    $_SESSION['piece_invalide'] = 0;
}

// ❌ Quantité invalide
if (isset($_SESSION['quantite_invalide']) && $_SESSION['quantite_invalide'] === 1) {
    echo "<script>
Swal.fire(
  'Quantité invalide!',
  'Entrez une quantité strictement positive.',
  'error'
);
</script>";
    $_SESSION['quantite_invalide'] = 0;
}

// ❌ Type de mouvement invalide
if (isset($_SESSION['type_invalide']) && $_SESSION['type_invalide'] === 1) {
    echo "<script>
Swal.fire(
  'Type de mouvement invalide!',
  'Le type doit être Entrée, Sortie ou Ajustement.',
  'error'
);
</script>";
    $_SESSION['type_invalide'] = 0;
}
?>
