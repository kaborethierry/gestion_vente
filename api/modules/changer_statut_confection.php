<?php
// api/modules/changer_statut_confection.php
// DANFANIMENT POS - Changement de statut d'une commande confection

session_start();

// 1. Vérification du rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// 2. Validation des champs POST obligatoires
if (empty($_POST['id_commande']) || empty($_POST['statut'])) {
    $_SESSION['err_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;
}

// 3. Récupération et assainissement des données
$id_commande = (int)$_POST['id_commande'];
$nouveau_statut = trim($_POST['statut']);
$remarques = trim($_POST['statut_remarques'] ?? '');
$id_utilisateur = $_SESSION['id'];

// 4. Liste des statuts valides
$statuts_valides = ['en_attente', 'en_cours', 'termine', 'livre', 'annule'];
if (!in_array($nouveau_statut, $statuts_valides)) {
    $_SESSION['err_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;
}

// 5. Connexion PDO
require_once __DIR__ . '/connect_db_pdo.php';

try {
    // 6. Récupérer l'ancien statut
    $stmt = $bdd->prepare("SELECT statut FROM danfaniment_commandes_confection WHERE id_commande = :id");
    $stmt->execute([':id' => $id_commande]);
    $ancien_statut = $stmt->fetchColumn();
    
    if ($ancien_statut === false) {
        $_SESSION['err_conf'] = 1;
        header('Location: ../../pages/confections.php');
        exit;
    }

    // 7. Mettre à jour le statut
    $sql = "UPDATE danfaniment_commandes_confection 
            SET statut = :statut,
                statut_changed_by = :user_id,
                statut_changed_at = NOW(),
                statut_remarques = :remarques,
                updated_at = NOW()";
    
    // Si le statut est "livre", enregistrer la date de livraison réelle
    if ($nouveau_statut === 'livre') {
        $sql .= ", date_livraison_reelle = NOW()";
    }
    
    $sql .= " WHERE id_commande = :id_commande";
    
    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':statut', $nouveau_statut, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $id_utilisateur, PDO::PARAM_INT);
    $stmt->bindValue(':remarques', $remarques, PDO::PARAM_STR);
    $stmt->bindValue(':id_commande', $id_commande, PDO::PARAM_INT);
    $stmt->execute();

    // 8. Enregistrer dans l'historique
    $sqlHistorique = "INSERT INTO danfaniment_historique_statuts (
                        id_commande,
                        id_utilisateur,
                        ancien_statut,
                        nouveau_statut,
                        remarques,
                        created_at
                    ) VALUES (
                        :id_commande,
                        :id_utilisateur,
                        :ancien_statut,
                        :nouveau_statut,
                        :remarques,
                        NOW()
                    )";
    
    $stmtHisto = $bdd->prepare($sqlHistorique);
    $stmtHisto->bindValue(':id_commande', $id_commande, PDO::PARAM_INT);
    $stmtHisto->bindValue(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmtHisto->bindValue(':ancien_statut', $ancien_statut, PDO::PARAM_STR);
    $stmtHisto->bindValue(':nouveau_statut', $nouveau_statut, PDO::PARAM_STR);
    $stmtHisto->bindValue(':remarques', $remarques, PDO::PARAM_STR);
    $stmtHisto->execute();

    $_SESSION['statut_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;

} catch (Exception $e) {
    error_log("Erreur changement statut confection: " . $e->getMessage());
    $_SESSION['err_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;
}
?>