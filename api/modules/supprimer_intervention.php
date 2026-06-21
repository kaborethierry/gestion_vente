<?php
// Fichier : api/modules/supprimer_intervention.php

session_start();

// 🔐 Autorisation: uniquement Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

// ✅ Suppression uniquement si un id_intervention numérique est fourni via GET
if (isset($_GET['id_intervention']) && is_numeric($_GET['id_intervention'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit fournir $bdd (PDO)

        $idInterv = (int) $_GET['id_intervention'];

        // Récupérer les données actuelles pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_intervention, id_vehicule, id_employe, type_intervention, date_debut, date_fin, kilometrage, statut, priorite, temps_estime, temps_reel, main_oeuvre_ht, description, remarques
            FROM interventions
            WHERE id_intervention = :id AND supprimer = 'Non'
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idInterv]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldRow) {
            // Déjà supprimée ou inexistante → feedback cohérent
            $_SESSION['suppr_intervention'] = 1;
            header('Location: ../../pages/interventions.php');
            exit;
        }

        // Suppression logique
        $stmt = $bdd->prepare("UPDATE interventions SET supprimer = 'Oui' WHERE id_intervention = :id");
        $stmt->bindValue(':id', $idInterv, PDO::PARAM_INT);
        $stmt->execute();

        // Construction des données pour l'historique
        $ancienne = json_encode([
            'id_intervention' => (int)$oldRow['id_intervention'],
            'id_vehicule'     => (int)$oldRow['id_vehicule'],
            'id_employe'      => (int)$oldRow['id_employe'],
            'type_intervention'=> $oldRow['type_intervention'],
            'date_debut'      => $oldRow['date_debut'],
            'date_fin'        => $oldRow['date_fin'],
            'kilometrage'     => is_null($oldRow['kilometrage']) ? null : (int)$oldRow['kilometrage'],
            'statut'          => $oldRow['statut'],
            'priorite'        => $oldRow['priorite'],
            'temps_estime'    => is_null($oldRow['temps_estime']) ? null : (float)$oldRow['temps_estime'],
            'temps_reel'      => is_null($oldRow['temps_reel']) ? null : (float)$oldRow['temps_reel'],
            'main_oeuvre_ht'  => is_null($oldRow['main_oeuvre_ht']) ? null : (float)$oldRow['main_oeuvre_ht'],
            'description'     => $oldRow['description'],
            'remarques'       => $oldRow['remarques'],
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id_intervention' => $idInterv,
            'supprimer'       => 'Oui'
        ], JSON_UNESCAPED_UNICODE);

        // Insertion dans l'historique_action
        $h = $bdd->prepare("
            INSERT INTO historique_action
                (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
            VALUES
                (:uid, :ip, :action, :table, :idc, :old, :new, 'Non')
        ");
        $h->execute([
            ':uid'    => $_SESSION['id'],
            ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
            ':action' => 'Suppression intervention',
            ':table'  => 'interventions',
            ':idc'    => $idInterv,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['suppr_intervention'] = 1; // ✅ Flag de succès
        header('Location: ../../pages/interventions.php');
        exit;

    } catch (Throwable $e) {
        error_log("Erreur suppression intervention (id={$idInterv}) : " . $e->getMessage());
        $_SESSION['suppr_intervention'] = 0;
        header('Location: ../../pages/interventions.php?error=delete');
        exit;
    }
} else {
    header('Location: ../../pages/interventions.php');
    exit;
}
