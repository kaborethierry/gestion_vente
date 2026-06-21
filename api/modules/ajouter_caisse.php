<?php
// api/modules/ajouter_caisse.php
// DANFANIMENT POS - Ouverture d'une session de caisse

session_start();

// Seul un Admin peut ouvrir une session
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// Vérifier la présence des champs obligatoires
if (!isset($_POST['id_utilisateur'], $_POST['montant_initial'])) {
    header('Location: ../../pages/caisse.php');
    exit;
}

// Récupération et assainissement des données
$id_utilisateur   = (int) $_POST['id_utilisateur'];
$montant_initial  = floatval($_POST['montant_initial']);
$notes_ouverture  = trim($_POST['notes_ouverture'] ?? '');

// Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // 1) Vérifier si le caissier n'a pas déjà une session ouverte
    $stmt = $bdd->prepare('SELECT COUNT(*) FROM danfaniment_caisses WHERE id_utilisateur = :id_user AND statut = "ouverte"');
    $stmt->execute([':id_user' => $id_utilisateur]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/caisse.php');
        exit;
    }

    // 2) Insérer la nouvelle session avec tous les champs initialisés à 0
    $stmt = $bdd->prepare('
        INSERT INTO danfaniment_caisses 
        (id_session, id_utilisateur, montant_initial, notes_ouverture, statut, date_ouverture,
         nombre_ventes, nombre_annulations, nombre_tickets,
         total_especes, total_carte, total_mobile_money, total_virement, total_avance_confection,
         total_ventes_brut, total_remises, total_ventes_net)
        VALUES (NULL, :id_user, :montant_initial, :notes, "ouverte", NOW(),
                0, 0, 0,
                0, 0, 0, 0, 0,
                0, 0, 0)
    ');
    $stmt->execute([
        ':id_user'         => $id_utilisateur,
        ':montant_initial' => $montant_initial,
        ':notes'           => $notes_ouverture
    ]);

    $_SESSION['ajout'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['err_caisse'] = 1;
    header('Location: ../../pages/caisse.php');
    exit;
}
?>