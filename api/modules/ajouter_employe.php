<?php
// api/modules/ajouter_employe.php

session_start();

// Autorisation: uniquement Admin
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

if (isset($_POST['nom'], $_POST['prenom'], $_POST['poste'], $_POST['statut'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Définit $bdd (PDO)

        // Récupération + nettoyage des données
        $nom         = trim((string)$_POST['nom']);
        $prenom      = trim((string)$_POST['prenom']);
        $poste       = trim((string)$_POST['poste']);
        $email       = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
        $telephone   = isset($_POST['telephone']) ? trim((string)$_POST['telephone']) : '';
        $dateEmb     = isset($_POST['date_embauche']) ? trim((string)$_POST['date_embauche']) : '';
        $salaireBase = isset($_POST['salaire_base']) ? trim((string)$_POST['salaire_base']) : '';
        $statut      = trim((string)$_POST['statut']);

        // Normalisation valeurs vides -> NULL
        $email       = ($email !== '') ? $email : null;
        $telephone   = ($telephone !== '') ? $telephone : null;
        $dateEmb     = ($dateEmb !== '') ? $dateEmb : null;
        $salaireBase = ($salaireBase !== '') ? str_replace(',', '.', $salaireBase) : null;

        // Validation basique
        $statutsAutorises = ['Actif', 'Suspendu', 'Archivé'];
        if (!in_array($statut, $statutsAutorises, true)) {
            $statut = 'Actif';
        }

        if ($nom === '' || $prenom === '' || $poste === '') {
            header('Location: ../../pages/employes.php');
            exit;
        }

        // Contrôle doublon (priorité email, sinon téléphone)
        $count = 0;
        if (!is_null($email)) {
            $stmt = $bdd->prepare("SELECT COUNT(*) FROM employes WHERE supprimer = 'Non' AND email = :email");
            $stmt->execute([':email' => $email]);
            $count = (int)$stmt->fetchColumn();
        } elseif (!is_null($telephone)) {
            $stmt = $bdd->prepare("SELECT COUNT(*) FROM employes WHERE supprimer = 'Non' AND telephone = :tel");
            $stmt->execute([':tel' => $telephone]);
            $count = (int)$stmt->fetchColumn();
        }

        if ($count > 0) {
            $_SESSION['imp'] = 1; // Déjà existant
            header('Location: ../../pages/employes.php');
            exit;
        }

        // Insertion employé
        $sql = "INSERT INTO employes
                (nom, prenom, poste, email, telephone, date_embauche, salaire_base, statut, supprimer)
                VALUES
                (:nom, :prenom, :poste, :email, :telephone, :date_embauche, :salaire_base, :statut, 'Non')";
        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':nom',           $nom,         PDO::PARAM_STR);
        $stmt->bindValue(':prenom',        $prenom,      PDO::PARAM_STR);
        $stmt->bindValue(':poste',         $poste,       PDO::PARAM_STR);
        $stmt->bindValue(':email',         $email,       is_null($email) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':telephone',     $telephone,   is_null($telephone) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':date_embauche', $dateEmb,     is_null($dateEmb) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':salaire_base',  $salaireBase, is_null($salaireBase) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':statut',        $statut,      PDO::PARAM_STR);
        $stmt->execute();

        // Historique action
        $id_new = (int)$bdd->lastInsertId();
        $nouvelle = json_encode([
            'id_employe'    => $id_new,
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
            ':action' => 'Ajout employé',
            ':table'  => 'employes',
            ':idc'    => $id_new,
            ':old'    => null,
            ':new'    => $nouvelle
        ]);

        $_SESSION['ajout'] = 1;
        header('Location: ../../pages/employes.php');
        exit;

    } catch (Throwable $e) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/employes.php');
        exit;
    }
} else {
    header('Location: ../../pages/employes.php');
    exit;
}
