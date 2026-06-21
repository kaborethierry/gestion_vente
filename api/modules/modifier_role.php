<?php
// api/modules/modifier_role.php
session_start();
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset(); session_destroy();
    header('Location: ./../index.php?erreur=3'); exit;
}
try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $old = isset($_POST['old_role']) ? trim($_POST['old_role']) : '';
    $new = isset($_POST['new_role']) ? trim($_POST['new_role']) : '';

    if ($old === '' || $new === '') { $_SESSION['param_error']="Données invalides."; header('Location: ../../pages/parametres.php'); exit; }
    if ($old === 'Admin') { $_SESSION['param_error']="Le rôle Admin ne peut pas être renommé."; header('Location: ../../pages/parametres.php'); exit; }

    // Mettre à jour les utilisateurs existants
    $upd = $bdd->prepare("UPDATE utilisateurs SET role = :new WHERE role = :old AND supprimer='Non'");
    $upd->execute([':new'=>$new, ':old'=>$old]);

    // Mettre à jour le paramètre roles_dynamiques s'il existe
    $val = $bdd->query("SELECT valeur FROM parametres WHERE cle_param='roles_dynamiques' AND supprimer='Non'")->fetchColumn();
    $arr = $val ? json_decode($val, true) : [];
    if (is_array($arr)) {
        $idx = array_search($old, $arr, true);
        if ($idx !== false) {
            $arr[$idx] = $new;
            $stmt = $bdd->prepare("REPLACE INTO parametres (cle_param, valeur, supprimer) VALUES ('roles_dynamiques', :v, 'Non')");
            $stmt->execute([':v' => json_encode(array_values($arr), JSON_UNESCAPED_UNICODE)]);
        }
    }

    // Historique
    $ancienne = json_encode(['old'=>$old], JSON_UNESCAPED_UNICODE);
    $nouvelle = json_encode(['new'=>$new], JSON_UNESCAPED_UNICODE);
    $h = $bdd->prepare("INSERT INTO historique_action (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
                        VALUES (:uid, :ip, 'Renommer rôle', 'utilisateurs', NULL, :old, :new, 'Non')");
    $h->execute([
        ':uid' => $_SESSION['id'],
        ':ip'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ':old' => $ancienne,
        ':new' => $nouvelle
    ]);

    $_SESSION['param_success'] = "Rôle renommé.";
} catch (Throwable $e) {
    $_SESSION['param_error'] = "Erreur lors de la modification du rôle.";
}
header('Location: ../../pages/parametres.php'); exit;
