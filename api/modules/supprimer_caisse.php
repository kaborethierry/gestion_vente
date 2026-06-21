<?php
// api/modules/supprimer_caisse.php
// DANFANIMENT POS - Suppression d'une session de caisse

session_start();

// 1. Vérification de la session et du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation du paramètre GET
if (empty($_GET['id_session']) || !ctype_digit($_GET['id_session'])) {
    $_SESSION['err_caisse'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;
}

// 3. Cast sécurisé de l'ID
$idSession = (int) $_GET['id_session'];

try {
    // 4. Connexion PDO
    require_once __DIR__ . '/connect_db_pdo.php';

    // 5. Vérifier que la session est fermée avant suppression
    $stmt = $bdd->prepare("SELECT statut FROM danfaniment_caissier_sessions WHERE id_session = :id");
    $stmt->execute([':id' => $idSession]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session && $session['statut'] === 'ouverte') {
        $_SESSION['err_caisse'] = 1;
        header('Location: ../../pages/caisse.php');
        exit;
    }
    
    // 6. Supprimer la session (suppression définitive car table sans champ supprimer)
    $stmt = $bdd->prepare('DELETE FROM danfaniment_caissier_sessions WHERE id_session = :id');
    $stmt->execute([':id' => $idSession]);

    $_SESSION['supr'] = 1;

} catch (Exception $e) {
    $_SESSION['err_caisse'] = 1;
}

header('Location: ../../pages/caisse.php');
exit;
?>