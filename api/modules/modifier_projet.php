<?php
session_start();
if (empty($_SESSION['id']) || $_SESSION['type_compte'] != "Super Administrateur") {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
    exit;
} 

if (isset($_POST['id_projet']) && isset($_POST['code_projet']) && isset($_POST['nom_projet']) && isset($_POST['date_debut']) && isset($_POST['statut'])) {
    include('connect_db.php');  // Connexion via MySQLi pour les vérifications

    // Sécurisation des données reçues
    $id_projet   = mysqli_real_escape_string($db, htmlspecialchars($_POST['id_projet']));
    $code_projet = mysqli_real_escape_string($db, htmlspecialchars($_POST['code_projet'], ENT_QUOTES));
    $nom_projet  = mysqli_real_escape_string($db, htmlspecialchars($_POST['nom_projet'], ENT_QUOTES));
    $description = isset($_POST['description']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['description'], ENT_QUOTES)) : "";
    $date_debut  = mysqli_real_escape_string($db, htmlspecialchars($_POST['date_debut'], ENT_QUOTES));
    $date_fin    = isset($_POST['date_fin']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['date_fin'], ENT_QUOTES)) : "";
    $budget      = isset($_POST['budget']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['budget'], ENT_QUOTES)) : "";
    $responsable = isset($_POST['responsable']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['responsable'], ENT_QUOTES)) : "";
    $statut      = mysqli_real_escape_string($db, htmlspecialchars($_POST['statut'], ENT_QUOTES));

    // Facultatif : transformer les retours à la ligne en <br> pour certains champs
    $code_projet = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $code_projet);
    $nom_projet  = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $nom_projet);
    $description = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $description);
    $responsable = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $responsable);

    // Vérification de l'unicité du code projet (on ignore le projet actuel)
    $query = "SELECT count(*) FROM projet WHERE code_projet = '".$code_projet."' AND id != '".$id_projet."'";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_array($result);
    $count = $row['count(*)'];

    if ($count == 0) {  // Si le code est unique
        include('connect_db_pdo.php'); // Connexion PDO pour la mise à jour
        try {
            $stmt = $bdd->prepare("UPDATE projet SET code_projet = ?, nom_projet = ?, description = ?, date_debut = ?, date_fin = ?, budget = ?, responsable = ?, statut = ?, date_heure_modification = NOW() WHERE id = ?");
            $stmt->execute(array($code_projet, $nom_projet, $description, $date_debut, $date_fin, $budget, $responsable, $statut, $id_projet));
            mysqli_close($db);
            $bdd = null;
            $_SESSION['mod'] = 1;
            header('Location:../../pages/projet.php');
            exit;
        } catch (Exception $e) {
            error_log("Erreur lors de la modification du projet : " . $e->getMessage());
            mysqli_close($db);
            header('Location:../../pages/projet.php?error=update');
            exit;
        }
    } else { // Le code du projet existe déjà pour un autre projet
        mysqli_close($db);
        $_SESSION['imp'] = 1;
        header('Location:../../pages/projet.php');
        exit;
    }
} else {
    header('Location:../../pages/projet.php');
    exit;
}
?>
