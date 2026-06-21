<?php
// api/modules/fermer_caisse.php
// DANFANIMENT POS - Fermeture d'une session de caisse

session_start();

// 1. Vérification du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation des champs POST
if (empty($_POST['id_caisse']) || !isset($_POST['montant_final_reel'])) {
    $_SESSION['err_caisse'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;
}

// 3. Récupération des données
$id_caisse         = (int) $_POST['id_caisse'];
$montant_final_reel = floatval($_POST['montant_final_reel']);
$notes_fermeture   = isset($_POST['notes_fermeture']) ? trim($_POST['notes_fermeture']) : '';

// 4. Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // 5. Vérifier que la session existe et est ouverte, et récupérer les totaux
    $stmt = $bdd->prepare("
        SELECT statut, montant_initial, total_ventes_net, total_avance_confection,
               (COALESCE(total_ventes_net, 0) + COALESCE(total_avance_confection, 0)) as total_ca
        FROM danfaniment_caisses 
        WHERE id_caisse = :id
    ");
    $stmt->execute([':id' => $id_caisse]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session || $session['statut'] !== 'ouverte') {
        $_SESSION['err_caisse'] = 1;
        header('Location: ../../pages/caisse.php');
        exit;
    }
    
    // 6. Calculer le montant final théorique
    $montant_theorique = $session['montant_initial'] + $session['total_ca'];
    $ecart_caisse = $montant_final_reel - $montant_theorique;
    
    // 7. Fermer la session
    $sql = "
        UPDATE danfaniment_caisses 
        SET statut = 'fermee',
            date_fermeture = NOW(),
            montant_final_reel = :montant_final_reel,
            montant_final_theorique = :montant_theorique,
            ecart_caisse = :ecart_caisse,
            notes_fermeture = :notes_fermeture
        WHERE id_caisse = :id
    ";
    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':montant_final_reel' => $montant_final_reel,
        ':montant_theorique'  => $montant_theorique,
        ':ecart_caisse'       => $ecart_caisse,
        ':notes_fermeture'    => $notes_fermeture,
        ':id'                 => $id_caisse
    ]);

    $_SESSION['ferme'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;

} catch (Exception $e) {
    $_SESSION['err_caisse'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;
}
?>