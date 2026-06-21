<?php
// api/modules/supprimer_employe.php

session_start();

if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

if (isset($_GET['id_employe']) && is_numeric($_GET['id_employe'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Connexion PDO à la base

        $idEmploye = (int) $_GET['id_employe'];

        // Récupération des anciennes données pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_employe, nom, prenom, poste, email, telephone, date_embauche, salaire_base, statut
            FROM employes
            WHERE id_employe = :id
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idEmploye]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);

        // Suppression logique : mise à jour du champ "supprimer" à 'Oui'
        $stmt = $bdd->prepare("UPDATE employes SET supprimer = 'Oui' WHERE id_employe = :id");
        $stmt->bindValue(':id', $idEmploye, PDO::PARAM_INT);
        $stmt->execute();

        // Préparer ancienne et nouvelle valeur pour l'historique
        $ancienne = null;
        if ($oldRow) {
            $ancienne = json_encode([
                'id_employe'    => (int)$oldRow['id_employe'],
                'nom'           => $oldRow['nom'],
                'prenom'        => $oldRow['prenom'],
                'poste'         => $oldRow['poste'],
                'email'         => $oldRow['email'],
                'telephone'     => $oldRow['telephone'],
                'date_embauche' => $oldRow['date_embauche'],
                'salaire_base'  => is_null($oldRow['salaire_base']) ? null : (float)$oldRow['salaire_base'],
                'statut'        => $oldRow['statut']
            ], JSON_UNESCAPED_UNICODE);
        }

        $nouvelle = json_encode([
            'id_employe' => $idEmploye,
            'supprimer'  => 'Oui'
        ], JSON_UNESCAPED_UNICODE);

        // Insertion dans l'historique
        $h = $bdd->prepare("
            INSERT INTO historique_action
                (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
            VALUES
                (:uid, :ip, :action, :table, :idc, :old, :new, 'Non')
        ");
        $h->execute([
            ':uid'    => $_SESSION['id'],
            ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
            ':action' => 'Suppression employé',
            ':table'  => 'employes',
            ':idc'    => $idEmploye,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['supr'] = 1;
        header('Location: ../../pages/employes.php');
        exit;

    } catch (Exception $e) {
        error_log("Erreur lors de la suppression de l'employé : " . $e->getMessage());
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/employes.php?error=delete');
        exit;
    }
} else {
    header('Location: ../../pages/employes.php');
    exit;
}
