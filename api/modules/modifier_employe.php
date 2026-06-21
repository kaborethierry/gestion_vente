<?php
// api/modules/modifier_employe.php

session_start();

if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

if (
    isset($_POST['id_employe'], $_POST['nom'], $_POST['prenom'], $_POST['poste'], $_POST['statut'])
) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)

        // Récupération + nettoyage
        $idEmploye   = (int) $_POST['id_employe'];
        $nom         = trim((string) $_POST['nom']);
        $prenom      = trim((string) $_POST['prenom']);
        $poste       = trim((string) $_POST['poste']);
        $statut      = trim((string) $_POST['statut']);

        $email       = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
        $telephone   = isset($_POST['telephone']) ? trim((string) $_POST['telephone']) : '';
        $dateEmb     = isset($_POST['date_embauche']) ? trim((string) $_POST['date_embauche']) : '';
        $salaireBase = isset($_POST['salaire_base']) ? trim((string) $_POST['salaire_base']) : '';

        // Validation basique
        if ($idEmploye <= 0 || $nom === '' || $prenom === '' || $poste === '') {
            header('Location: ../../pages/employes.php');
            exit;
        }

        $statutsAutorises = ['Actif', 'Suspendu', 'Archivé'];
        if (!in_array($statut, $statutsAutorises, true)) {
            $statut = 'Actif';
        }

        // Normalisation valeurs vides -> NULL
        $email       = $email !== '' ? $email : null;
        $telephone   = $telephone !== '' ? $telephone : null;
        $dateEmb     = $dateEmb !== '' ? $dateEmb : null;
        $salaireBase = $salaireBase !== '' ? str_replace(',', '.', $salaireBase) : null;

        // Récupération des anciennes valeurs pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_employe, nom, prenom, poste, email, telephone, date_embauche, salaire_base, statut
            FROM employes
            WHERE id_employe = :id
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idEmploye]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);

        // Vérifications de doublons (email / téléphone) en excluant l'employé courant
        if (!is_null($email)) {
            $stmt = $bdd->prepare("SELECT COUNT(*) FROM employes WHERE supprimer = 'Non' AND email = :email AND id_employe <> :id");
            $stmt->execute([':email' => $email, ':id' => $idEmploye]);
            if ((int) $stmt->fetchColumn() > 0) {
                $_SESSION['imp'] = 1;
                header('Location: ../../pages/employes.php');
                exit;
            }
        }

        if (!is_null($telephone)) {
            $stmt = $bdd->prepare("SELECT COUNT(*) FROM employes WHERE supprimer = 'Non' AND telephone = :tel AND id_employe <> :id");
            $stmt->execute([':tel' => $telephone, ':id' => $idEmploye]);
            if ((int) $stmt->fetchColumn() > 0) {
                $_SESSION['imp'] = 1;
                header('Location: ../../pages/employes.php');
                exit;
            }
        }

        // Mise à jour
        $sql = "UPDATE employes
                   SET nom = :nom,
                       prenom = :prenom,
                       poste = :poste,
                       email = :email,
                       telephone = :telephone,
                       date_embauche = :date_embauche,
                       salaire_base = :salaire_base,
                       statut = :statut
                 WHERE id_employe = :id_employe";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':nom',            $nom,         PDO::PARAM_STR);
        $stmt->bindValue(':prenom',         $prenom,      PDO::PARAM_STR);
        $stmt->bindValue(':poste',          $poste,       PDO::PARAM_STR);
        $stmt->bindValue(':email',          $email,       is_null($email) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':telephone',      $telephone,   is_null($telephone) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':date_embauche',  $dateEmb,     is_null($dateEmb) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':salaire_base',   $salaireBase, is_null($salaireBase) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':statut',         $statut,      PDO::PARAM_STR);
        $stmt->bindValue(':id_employe',     $idEmploye,   PDO::PARAM_INT);
        $stmt->execute();

        // Historique action (ancienne et nouvelle valeurs)
        // Construction de l'ancienne valeur (si trouvée)
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

        // Nouvelle valeur
        $nouvelle = json_encode([
            'id_employe'    => $idEmploye,
            'nom'           => $nom,
            'prenom'        => $prenom,
            'poste'         => $poste,
            'email'         => $email,
            'telephone'     => $telephone,
            'date_embauche' => $dateEmb,
            'salaire_base'  => is_null($salaireBase) ? null : (float)$salaireBase,
            'statut'        => $statut
        ], JSON_UNESCAPED_UNICODE);

        $h = $bdd->prepare("
            INSERT INTO historique_action
                (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
            VALUES
                (:uid, :ip, :action, :table, :idc, :old, :new, 'Non')
        ");
        $h->execute([
            ':uid'    => $_SESSION['id'],
            ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
            ':action' => 'Modification employé',
            ':table'  => 'employes',
            ':idc'    => $idEmploye,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['mod'] = 1;
        header('Location: ../../pages/employes.php');
        exit;

    } catch (Exception $e) {
        // Journalisation possible: error_log($e->getMessage());
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/employes.php');
        exit;
    }
} else {
    header('Location: ../../pages/employes.php');
    exit;
}
