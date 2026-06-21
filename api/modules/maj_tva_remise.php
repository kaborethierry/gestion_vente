<?php
// api/modules/maj_tva_remise.php
session_start();
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset(); session_destroy();
    header('Location: ./../index.php?erreur=3'); exit;
}
try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $tva    = isset($_POST['tva']) ? str_replace(',', '.', trim($_POST['tva'])) : null;
    $remise = isset($_POST['remise']) ? str_replace(',', '.', trim($_POST['remise'])) : null;

    if (!is_numeric($tva) || (float)$tva < 0 || !is_numeric($remise) || (float)$remise < 0) {
        $_SESSION['param_error'] = "Valeurs invalides pour TVA/remise.";
        header('Location: ../../pages/parametres.php'); exit;
    }

    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lire anciennes valeurs
    $old = [];
    $stmt = $bdd->query("SELECT cle_param, valeur FROM parametres WHERE cle_param IN ('tva','remise') AND supprimer='Non'");
    foreach ($stmt->fetchAll() as $r) { $old[$r['cle_param']] = $r['valeur']; }

    // Upsert
    $up = $bdd->prepare("REPLACE INTO parametres (cle_param, valeur, supprimer) VALUES (:k, :v, 'Non')");
    $up->execute([':k' => 'tva', ':v' => $tva]);
    $up->execute([':k' => 'remise', ':v' => $remise]);

    // Historique
    $ancienne = json_encode($old, JSON_UNESCAPED_UNICODE);
    $nouvelle = json_encode(['tva'=>$tva,'remise'=>$remise], JSON_UNESCAPED_UNICODE);
    $h = $bdd->prepare("INSERT INTO historique_action (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
                        VALUES (:uid, :ip, 'Mise à jour paramètres TVA/Remise', 'parametres', NULL, :old, :new, 'Non')");
    $h->execute([
        ':uid' => $_SESSION['id'],
        ':ip'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ':old' => $ancienne,
        ':new' => $nouvelle
    ]);

    $_SESSION['param_success'] = "TVA et remise mises à jour.";
} catch (Throwable $e) {
    $_SESSION['param_error'] = "Erreur lors de la mise à jour.";
}
header('Location: ../../pages/parametres.php'); exit;
