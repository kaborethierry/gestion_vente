<?php
// api/modules/supprimer_role.php
session_start();
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset(); session_destroy();
    header('Location: ./../index.php?erreur=3'); exit;
}
try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    if ($role === '' || $role === 'Admin') {
        $_SESSION['param_error'] = "Rôle invalide.";
        header('Location: ../../pages/parametres.php'); exit;
    }

    // Empêcher suppression si des utilisateurs l'ont
    $nb = (int)$bdd->prepare("SELECT COUNT(*) FROM utilisateurs WHERE supprimer='Non' AND role=:r");
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM utilisateurs WHERE supprimer='Non' AND role=:r");
    $stmt->execute([':r' => $role]);
    if ((int)$stmt->fetchColumn() > 0) {
        $_SESSION['param_error'] = "Impossible: des utilisateurs ont encore ce rôle.";
        header('Location: ../../pages/parametres.php'); exit;
    }

    // Mettre à jour roles_dynamiques
    $val = $bdd->query("SELECT valeur FROM parametres WHERE cle_param='roles_dynamiques' AND supprimer='Non'")->fetchColumn();
    $arr = $val ? json_decode($val, true) : [];
    if (is_array($arr)) {
        $arr = array_values(array_filter($arr, fn($x) => $x !== $role));
        $stmt = $bdd->prepare("REPLACE INTO parametres (cle_param, valeur, supprimer) VALUES ('roles_dynamiques', :v, 'Non')");
        $stmt->execute([':v' => json_encode($arr, JSON_UNESCAPED_UNICODE)]);
    }

    // Historique
    $h = $bdd->prepare("INSERT INTO historique_action (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
                        VALUES (:uid, :ip, 'Suppression rôle', 'parametres', NULL, :old, :new, 'Non')");
    $h->execute([
        ':uid' => $_SESSION['id'],
        ':ip'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ':old' => $val,
        ':new' => json_encode($arr ?? [], JSON_UNESCAPED_UNICODE)
    ]);

    $_SESSION['param_success'] = "Rôle supprimé.";
} catch (Throwable $e) {
    $_SESSION['param_error'] = "Erreur lors de la suppression du rôle.";
}
header('Location: ../../pages/parametres.php'); exit;
