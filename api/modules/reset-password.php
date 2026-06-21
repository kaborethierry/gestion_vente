<?php
// api/modules/reset-password.php
// DANFANIMENT POS - Réinitialisation du mot de passe

session_start();

// Connexion à la base de données
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // 1. Vérifier que les données nécessaires existent
    if (
        !isset($_POST['token'], $_POST['password'], $_POST['confirm_password']) ||
        empty(trim($_POST['token'])) ||
        empty(trim($_POST['password'])) ||
        empty(trim($_POST['confirm_password']))
    ) {
        $_SESSION['reset_err'] = 3;
        header('Location: ../../pages/reset-password.php?token=' . urlencode($_POST['token'] ?? ''));
        exit;
    }

    $token     = trim($_POST['token']);
    $password  = trim($_POST['password']);
    $confirm   = trim($_POST['confirm_password']);

    // 2. Vérifier que les mots de passe correspondent
    if ($password !== $confirm) {
        $_SESSION['reset_err'] = 2;
        header('Location: ../../pages/reset-password.php?token=' . urlencode($token));
        exit;
    }

    // 3. Vérifier complexité minimale (6 caractères pour correspondre à la table)
    if (strlen($password) < 6) {
        $_SESSION['reset_err'] = 3;
        header('Location: ../../pages/reset-password.php?token=' . urlencode($token));
        exit;
    }

    // 4. Vérifier si la table reset_password_tokens existe
    try {
        $checkTable = $bdd->query("SHOW TABLES LIKE 'reset_password_tokens'");
        if ($checkTable->rowCount() == 0) {
            // Créer la table si elle n'existe pas
            $bdd->exec("
                CREATE TABLE IF NOT EXISTS `reset_password_tokens` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `token` varchar(255) NOT NULL,
                    `expires_at` datetime NOT NULL,
                    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `token` (`token`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    } catch (Exception $e) {
        // Table existe déjà ou erreur ignorée
    }

    // 5. Chercher le token en base et vérifier sa validité
    $stmt = $bdd->prepare("
        SELECT user_id, expires_at 
        FROM reset_password_tokens 
        WHERE token = :token 
        LIMIT 1
    ");
    $stmt->execute(['token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['reset_err'] = 1;
        header('Location: ../../pages/reset-password.php?token=' . urlencode($token));
        exit;
    }

    // 6. Vérifier expiration
    if (strtotime($row['expires_at']) < time()) {
        $_SESSION['reset_err'] = 1;
        header('Location: ../../pages/reset-password.php?token=' . urlencode($token));
        exit;
    }

    $userId = $row['user_id'];

    // 7. Hasher le nouveau mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 8. Mettre à jour le mot de passe de l'utilisateur
    $bdd->beginTransaction();

    $update = $bdd->prepare("
        UPDATE utilisateurs 
        SET mot_de_passe = :password 
        WHERE id_utilisateur = :id
    ");
    $update->execute([
        'password' => $hashedPassword,
        'id'       => $userId
    ]);

    // 9. Supprimer le token pour éviter réutilisation
    $delete = $bdd->prepare("DELETE FROM reset_password_tokens WHERE token = :token");
    $delete->execute(['token' => $token]);

    // 10. Mettre à jour la date de modification
    $updateDate = $bdd->prepare("
        UPDATE utilisateurs 
        SET updated_at = NOW() 
        WHERE id_utilisateur = :id
    ");
    $updateDate->execute(['id' => $userId]);

    $bdd->commit();

    // 11. Message succès
    $_SESSION['reset'] = 1;
    header('Location: ../../pages/reset-password.php');
    exit;

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if (isset($bdd) && method_exists($bdd, 'inTransaction') && $bdd->inTransaction()) {
        $bdd->rollBack();
    }
    error_log("Erreur reset-password : " . $e->getMessage());
    $_SESSION['reset_err'] = 1;
    header('Location: ../../pages/reset-password.php?token=' . urlencode($_POST['token'] ?? ''));
    exit;
}
?>