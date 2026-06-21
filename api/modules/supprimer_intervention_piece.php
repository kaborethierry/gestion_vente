<?php
// Fichier : api/modules/supprimer_intervention_piece.php

session_start();

// 🔐 Autorisation: uniquement Admin
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

// ✅ Seulement si un id numérique est fourni via GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit fournir $bdd (PDO)
        if (method_exists($bdd, 'setAttribute')) {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        $idLigne = (int) $_GET['id'];

        // Récupérer l'état actuel pour l'historique et vérifier l'existence
        $sel = $bdd->prepare("
            SELECT id, id_intervention, id_piece, quantite, prix_unitaire, date_ajout
            FROM intervention_pieces
            WHERE id = :id AND (supprimer IS NULL OR supprimer = 'Non')
            LIMIT 1
        ");
        $sel->execute([':id' => $idLigne]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Déjà supprimée ou inexistante → poser quand même le flag pour feedback cohérent
            $_SESSION['suppr_intervention_piece'] = 1;
            header('Location: ../../pages/intervention_pieces.php');
            exit;
        }

        // Préparer données historique
        $ancienne = json_encode([
            'id'              => (int)$row['id'],
            'id_intervention' => (int)$row['id_intervention'],
            'id_piece'        => (int)$row['id_piece'],
            'quantite'        => (int)$row['quantite'],
            'prix_unitaire'   => isset($row['prix_unitaire']) ? (float)$row['prix_unitaire'] : null,
            'date_ajout'      => $row['date_ajout']
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id'        => $idLigne,
            'supprimer' => 'Oui'
        ], JSON_UNESCAPED_UNICODE);

        // Suppression logique + historique dans une transaction
        $bdd->beginTransaction();

        $stmt = $bdd->prepare("
            UPDATE intervention_pieces 
               SET supprimer = 'Oui' 
             WHERE id = :id
        ");
        $stmt->bindValue(':id', $idLigne, PDO::PARAM_INT);
        $stmt->execute();

        // Historique action
        $h = $bdd->prepare("
            INSERT INTO historique_action
                (id_utilisateur, adresse_ip, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, supprimer)
            VALUES
                (:uid, :ip, :action, :table, :idc, :old, :new, 'Non')
        ");
        $h->execute([
            ':uid'    => $_SESSION['id'],
            ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
            ':action' => 'Suppression intervention_piece',
            ':table'  => 'intervention_pieces',
            ':idc'    => $idLigne,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $bdd->commit();

        // ✅ Flag de succès (clé alignée avec alert_intervention_piece.php)
        $_SESSION['suppr_intervention_piece'] = 1;
        header('Location: ../../pages/intervention_pieces.php');
        exit;

    } catch (Throwable $e) {
        if (isset($bdd) && $bdd->inTransaction()) {
            $bdd->rollBack();
        }
        error_log("Erreur lors de la suppression intervention_piece (id={$idLigne}) : " . $e->getMessage());
        $_SESSION['suppr_intervention_piece'] = 0;
        header('Location: ../../pages/intervention_pieces.php?error=delete');
        exit;
    }
} else {
    header('Location: ../../pages/intervention_pieces.php');
    exit;
}
