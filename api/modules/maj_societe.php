<?php
// api/modules/maj_societe.php
session_start();
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset(); session_destroy();
    header('Location: ./../index.php?erreur=3'); exit;
}
try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nom  = isset($_POST['nom_societe']) ? trim($_POST['nom_societe']) : '';
    $pied = isset($_POST['pied_page']) ? trim($_POST['pied_page']) : '';

    if ($nom === '') { $_SESSION['param_error']="Le nom de la société est requis."; header('Location: ../../pages/parametres.php'); exit; }

    // Récup ancienne valeur
    $rowOld = $bdd->query("SELECT id_societe, nom, logo, pied_page FROM societe WHERE supprimer='Non' ORDER BY id_societe ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    // Upload logo si fourni
    $logoPath = $rowOld['logo'] ?? null;
    if (!empty($_FILES['logo']['name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png','jpg','jpeg'], true)) {
            $_SESSION['param_error'] = "Format de logo invalide (PNG/JPG uniquement).";
            header('Location: ../../pages/parametres.php'); exit;
        }
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['param_error'] = "Logo trop volumineux (max 2 Mo).";
            header('Location: ../../pages/parametres.php'); exit;
        }
        $destDir = __DIR__ . '/../../uploads';
        if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
        $fileName = 'logo_' . date('Ymd_His') . '.' . $ext;
        $destPath = $destDir . '/' . $fileName;
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $destPath)) {
            $_SESSION['param_error'] = "Échec de l'upload du logo.";
            header('Location: ../../pages/parametres.php'); exit;
        }
        $logoPath = 'uploads/' . $fileName;
    }

    if ($rowOld) {
        $stmt = $bdd->prepare("UPDATE societe SET nom=:n, logo=:l, pied_page=:p WHERE id_societe=:id");
        $stmt->execute([':n'=>$nom, ':l'=>$logoPath, ':p'=>$pied, ':id'=>$rowOld['id_societe']]);
        $idSoc = (int)$rowOld['id_societe'];
    } else {
        $stmt = $bdd->prepare("INSERT INTO societe (nom, logo, pied_page, supprimer) VALUES (:n, :l, :p, 'Non')");
        $stmt->execute([':n'=>$nom, ':l'=>$logoPath, ':p'=>$pied]);
        $idSoc = (int)$bdd->lastInsertId();
    }

    // Historique
    $ancienne = json_encode($rowOld, JSON_UNESCAPED_UNICODE);
    $nouvelle = json_encode(['nom'=>$nom,'logo'=>$logoPath,'pied_page'=>$pied], JSON_UNESCAPED_UNICODE);
    $h = $bdd->prepare("INSERT INTO historique_action (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
                        VALUES (:uid, :ip, 'Mise à jour société', 'societe', :idc, :old, :new, 'Non')");
    $h->execute([
        ':uid' => $_SESSION['id'],
        ':ip'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ':idc' => $idSoc,
        ':old' => $ancienne,
        ':new' => $nouvelle
    ]);

    $_SESSION['param_success'] = "Informations de la société mises à jour.";
} catch (Throwable $e) {
    $_SESSION['param_error'] = "Erreur lors de la mise à jour des informations société.";
}
header('Location: ../../pages/parametres.php'); exit;
