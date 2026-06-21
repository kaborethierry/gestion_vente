<?php
session_start();
if (empty($_SESSION['id']) || $_SESSION['type_compte'] != "Super Administrateur") {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
    exit;
} 

if (isset($_GET['id_projet']) && !empty($_GET['id_projet'])) {
    try {
        include('connect_db_pdo.php');  // Connexion PDO à la base de données
        
        // Préparation et exécution de la suppression en utilisant "id" (colonne primaire)
        $stmt = $bdd->prepare("DELETE FROM projet WHERE id = ?");
        $stmt->execute(array($_GET['id_projet']));

        // Fermeture de la connexion PDO
        $bdd = null;

        $_SESSION['supr'] = 1;
        header('Location:../../pages/projet.php');
        exit;
    } catch (Exception $e) {
        error_log("Erreur lors de la suppression du projet : " . $e->getMessage());
        header('Location:../../pages/projet.php?error=delete');
        exit;
    }
} else {
    header('Location:../../pages/projet.php');
    exit;
}
?>
