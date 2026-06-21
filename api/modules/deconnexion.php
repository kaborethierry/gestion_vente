<?php
// api/modules/deconnexion.php
// DANFANIMENT POS - Déconnexion utilisateur

declare(strict_types=1);
session_start();

// Vérification que l'utilisateur est connecté
if (empty($_SESSION['id'])) {
    // Déjà déconnecté, rediriger simplement
    header('Location: ../../index.php');
    exit;
}

try {
    // Connexion à la base de données
    require_once __DIR__ . '/connect_db_pdo.php';

    if ($bdd instanceof PDO) {
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Mettre à jour la date du dernier accès
        $stmt = $bdd->prepare('
            UPDATE utilisateurs 
            SET dernier_acces = NOW() 
            WHERE id_utilisateur = :id
        ');
        $stmt->execute([':id' => (int) $_SESSION['id']]);
    }
} catch (Exception $e) {
    // Journaliser l'erreur silencieusement
    error_log('[deconnexion] ' . $e->getMessage());
}

// Terminer la session
session_unset();
session_destroy();

// Rediriger vers la page de connexion
header('Location: ../../index.php');
exit;
?>