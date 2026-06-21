<?php 
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
} else {
    if (isset($_POST['code_projet']) && isset($_POST['nom_projet']) && isset($_POST['date_debut']) && isset($_POST['statut'])) {
        include('connect_db.php');

        $code_projet = mysqli_real_escape_string($db, htmlspecialchars($_POST['code_projet'], ENT_QUOTES)); 
        $nom_projet  = mysqli_real_escape_string($db, htmlspecialchars($_POST['nom_projet'], ENT_QUOTES)); 
        $description = isset($_POST['description']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['description'], ENT_QUOTES)) : "";
        $date_debut  = mysqli_real_escape_string($db, htmlspecialchars($_POST['date_debut'], ENT_QUOTES)); 
        $date_fin    = isset($_POST['date_fin']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['date_fin'], ENT_QUOTES)) : "";
        $budget      = isset($_POST['budget']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['budget'], ENT_QUOTES)) : "";
        $responsable = isset($_POST['responsable']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['responsable'], ENT_QUOTES)) : "";
        $statut      = mysqli_real_escape_string($db, htmlspecialchars($_POST['statut'], ENT_QUOTES));

        // Transformation des retours à la ligne en <br> si besoin
        $code_projet = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $code_projet);
        $nom_projet  = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $nom_projet);
        $description = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $description);
        $responsable = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $responsable);

        $requete = "SELECT count(*) FROM projet WHERE code_projet = '".$code_projet."'";
        $exec_requete = mysqli_query($db, $requete);
        $reponse = mysqli_fetch_array($exec_requete);
        $count = $reponse['count(*)'];

        if ($count == 0) {
            include('connect_db_pdo.php');
            $stmt = $bdd->prepare('INSERT INTO projet (code_projet, nom_projet, description, date_debut, date_fin, budget, responsable, statut, date_heure_ajout) VALUES (?,?,?,?,?,?,?,? ,NOW())');
            $stmt->execute(array($code_projet, $nom_projet, $description, $date_debut, $date_fin, $budget, $responsable, $statut));
            mysqli_close($db);
            $bdd = null;
            $_SESSION['ajout'] = 1;
            header('Location:../../pages/projet.php');
        } else {
            mysqli_close($db);
            $_SESSION['imp'] = 1;
            header('Location:../../pages/projet.php');
        }
    } else {
        header('Location:../../pages/projet.php');
    }
}
?>
