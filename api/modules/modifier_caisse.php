<?php
// api/modules/modifier_caisse.php
// DANFANIMENT POS - Modification d'une session de caisse

session_start();

// 1. Vérification du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation des champs POST obligatoires
if (empty($_POST['id_caisse']) || empty($_POST['statut'])) {
    $_SESSION['err_caisse'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;
}

// 3. Récupération et assainissement des données
$id_caisse          = (int) $_POST['id_caisse'];
$statut             = trim($_POST['statut']);
$montant_initial    = isset($_POST['montant_initial']) ? floatval($_POST['montant_initial']) : null;
$montant_final_reel = isset($_POST['montant_final_reel']) && !empty($_POST['montant_final_reel']) ? floatval($_POST['montant_final_reel']) : null;
$notes              = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// 4. Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Vérifier si la session est permanente
    $stmt = $bdd->prepare("SELECT id_session FROM danfaniment_caisses WHERE id_caisse = :id");
    $stmt->execute([':id' => $id_caisse]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_permanente = ($session && strpos($session['id_session'], 'SES-PERMANENTE') === 0);
    
    // 5. Construction de la requête UPDATE
    $updateFields = [];
    $params = [':id' => $id_caisse];
    
    // Ajouter les champs à modifier s'ils sont présents
    if ($montant_initial !== null) {
        $updateFields[] = "montant_initial = :montant_initial";
        $params[':montant_initial'] = $montant_initial;
    }
    
    if ($statut !== null) {
        $updateFields[] = "statut = :statut";
        $params[':statut'] = $statut;
    }
    
    if ($montant_final_reel !== null) {
        $updateFields[] = "montant_final_reel = :montant_final_reel";
        $updateFields[] = "ecart_caisse = :montant_final_reel - COALESCE(montant_final_theorique, 0)";
        $params[':montant_final_reel'] = $montant_final_reel;
    }
    
    if (!empty($notes)) {
        $updateFields[] = "notes_ouverture = CONCAT(IFNULL(notes_ouverture, ''), '\n---\nModification: ', :notes)";
        $params[':notes'] = $notes;
    }
    
    // Si on ferme la session, ajouter date_fermeture
    if ($statut === 'fermee' && !$is_permanente) {
        $updateFields[] = "date_fermeture = NOW()";
    } elseif ($statut === 'fermee' && $is_permanente) {
        // Empêcher la fermeture des sessions permanentes
        $_SESSION['err_caisse'] = 2;
        $_SESSION['message_erreur'] = "Les sessions permanentes ne peuvent pas être fermées !";
        header('Location: ../../pages/caisse.php');
        exit;
    }
    
    if (empty($updateFields)) {
        $_SESSION['mod'] = 1;
        header('Location: ../../pages/caisse.php');
        exit;
    }
    
    $sql = "UPDATE danfaniment_caisses SET " . implode(", ", $updateFields) . " WHERE id_caisse = :id";
    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);
    
    $_SESSION['mod'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;

} catch (Exception $e) {
    $_SESSION['err_caisse'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;
}
?>