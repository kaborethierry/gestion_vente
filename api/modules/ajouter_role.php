<?php
// api/modules/ajouter_role.php
session_start();
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset(); session_destroy();
    header('Location: ./../index.php?erreur=3'); exit;
}
try {
    $role = isset($_POST['role_nom']) ? trim($_POST['role_nom']) : '';
    if ($role === '') { $_SESSION['param_error'] = "Nom de rôle requis."; header('Location: ../../pages/parametres.php'); exit; }

    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ici, selon ton modèle actuel, on n’a pas de table roles.
    // On peut autoriser une “création logique” en stockant la liste dans parametres.
    // Stockage sous cle_param=roles_dynamiques (JSON)
    $old = $bdd->query("SELECT valeur FROM parametres WHERE cle_param='roles_dynamiques' AND supprimer='Non'")->fetchColumn();
    $arr = $old ? json_decode($old, true) : [];
    if (!is_array($arr)) $arr = [];

    if (!in_array($role, $arr, true)) {
        $arr[] = $role;
        $stmt = $bdd->prepare("REPLACE INTO parametres (cle_param, valeur, supprimer) VALUES ('roles_dynamiques', :v, 'Non')");
        $stmt->execute([':v' => json_encode($arr, JSON_UNESCAPED_UNICODE)]);
    }

    // Historique
    $h = $bdd->prepare("INSERT INTO historique_action (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
                        VALUES (:uid, :ip, 'Ajout rôle', 'parametres', NULL, :old, :new, 'Non')");
    $h->execute([
        ':uid' => $_SESSION['id'],
        ':ip'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ':old' => $old,
        ':new' => json_encode($arr, JSON_UNESCAPED_UNICODE)
    ]);

    $_SESSION['param_success'] = "Rôle ajouté.";
} catch (Throwable $e) {
    $_SESSION['param_error'] = "Erreur lors de l'ajout du rôle.";
}
header('Location: ../../pages/parametres.php'); exit;
