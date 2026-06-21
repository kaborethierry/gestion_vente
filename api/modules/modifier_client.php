<?php
// api/modules/modifier_client.php
// DANFANIMENT POS - Modification d'un client

session_start();

// Vérification de l'authentification (admin ou caissier peuvent modifier)
if (empty($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// Validation des champs POST obligatoires
$required = ['id_client', 'nom', 'prenom', 'telephone'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['err_client'] = 1;
        header('Location: ../../pages/clients.php');
        exit;
    }
}

// Récupération et assainissement des données
$id_client   = (int) $_POST['id_client'];
$nom         = trim($_POST['nom']);
$prenom      = trim($_POST['prenom']);
$telephone   = trim($_POST['telephone']);
$email       = trim($_POST['email'] ?? '');
$adresse     = trim($_POST['adresse'] ?? '');
$ville       = trim($_POST['ville'] ?? '');
$notes       = trim($_POST['notes'] ?? '');

// Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // Vérifier l'unicité du téléphone (exclure le client actuel)
    $stmt = $bdd->prepare("
        SELECT COUNT(*) 
        FROM danfaniment_clients 
        WHERE telephone = :telephone 
        AND id_client <> :id 
        AND supprimer = 'Non'
    ");
    $stmt->execute([
        ':telephone' => $telephone,
        ':id'        => $id_client
    ]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['imp_client'] = 1;
        header('Location: ../../pages/clients.php');
        exit;
    }

    // Mettre à jour le client
    $sql = "
        UPDATE danfaniment_clients 
        SET nom = :nom,
            prenom = :prenom,
            telephone = :telephone,
            email = :email,
            adresse = :adresse,
            ville = :ville,
            notes = :notes,
            updated_at = NOW()
        WHERE id_client = :id
    ";
    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':nom'       => $nom,
        ':prenom'    => $prenom,
        ':telephone' => $telephone,
        ':email'     => $email,
        ':adresse'   => $adresse,
        ':ville'     => $ville,
        ':notes'     => $notes,
        ':id'        => $id_client
    ]);

    $_SESSION['mod_client'] = 1;
    header('Location: ../../pages/clients.php');
    exit;

} catch (Exception $e) {
    $_SESSION['err_client'] = 1;
    header('Location: ../../pages/clients.php');
    exit;
}
?>