<?php
// Fichier : api/modules/supprimer_piece.php

session_start();

// 🔐 Autorisation : uniquement Admin
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

// ✅ Seulement si un id_piece numérique est fourni via GET
if (isset($_GET['id_piece']) && is_numeric($_GET['id_piece'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit fournir $bdd (PDO)

        $idPiece = (int) $_GET['id_piece'];

        // Récupération des anciennes données pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_piece, reference, designation, prix_achat, prix_vente, quantite_stock, seuil_minimal, fournisseur, id_categorie
            FROM pieces
            WHERE id_piece = :id AND supprimer = 'Non'
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idPiece]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldRow) {
            // Déjà supprimée ou inexistante → on pose quand même le flag pour feedback cohérent
            $_SESSION['suppr_piece'] = 1;
            header('Location: ../../pages/pieces.php');
            exit;
        }

        // Suppression logique
        $stmt = $bdd->prepare("UPDATE pieces SET supprimer = 'Oui' WHERE id_piece = :id");
        $stmt->bindValue(':id', $idPiece, PDO::PARAM_INT);
        $stmt->execute();

        // Construction des données pour l'historique
        $ancienne = json_encode([
            'id_piece'       => (int)$oldRow['id_piece'],
            'reference'      => $oldRow['reference'],
            'designation'    => $oldRow['designation'],
            'prix_achat'     => is_null($oldRow['prix_achat']) ? null : (float)$oldRow['prix_achat'],
            'prix_vente'     => is_null($oldRow['prix_vente']) ? null : (float)$oldRow['prix_vente'],
            'quantite_stock' => is_null($oldRow['quantite_stock']) ? null : (int)$oldRow['quantite_stock'],
            'seuil_minimal'  => is_null($oldRow['seuil_minimal']) ? null : (int)$oldRow['seuil_minimal'],
            'fournisseur'    => $oldRow['fournisseur'],
            'id_categorie'   => is_null($oldRow['id_categorie']) ? null : (int)$oldRow['id_categorie'],
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id_piece'  => $idPiece,
            'supprimer' => 'Oui'
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
            ':action' => 'Suppression pièce',
            ':table'  => 'pieces',
            ':idc'    => $idPiece,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        // ✅ Flag de succès (clé alignée avec alert_piece.php)
        $_SESSION['suppr_piece'] = 1;
        header('Location: ../../pages/pieces.php');
        exit;

    } catch (Throwable $e) {
        error_log("Erreur lors de la suppression de la pièce (id={$idPiece}) : " . $e->getMessage());
        $_SESSION['suppr_piece'] = 0;
        header('Location: ../../pages/pieces.php?error=delete');
        exit;
    }
} else {
    header('Location: ../../pages/pieces.php');
    exit;
}
