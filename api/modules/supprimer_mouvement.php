<?php
// Fichier : api/modules/supprimer_mouvement.php

session_start();

// 🔐 Autorisation: uniquement Admin
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

// ✅ Seulement si un id_mouvement numérique est fourni via GET
if (isset($_GET['id_mouvement']) && is_numeric($_GET['id_mouvement'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit fournir $bdd (PDO)
        if (method_exists($bdd, 'setAttribute')) {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        $idMouvement = (int) $_GET['id_mouvement'];

        // Récupérer le mouvement pour rollback & historique
        $stmt = $bdd->prepare("
            SELECT id_piece, type_mouvement, quantite, date_mouvement, motif
            FROM mouvements_stock
            WHERE id_mouvement = :id AND supprimer = 'Non'
            LIMIT 1
        ");
        $stmt->execute([':id' => $idMouvement]);
        $mvt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mvt) {
            $_SESSION['suppr_mouvement'] = 1;
            header('Location: ../../pages/mouvements_stock.php');
            exit;
        }

        $idPiece  = (int)$mvt['id_piece'];
        $quantite = (int)$mvt['quantite'];
        $type     = (string)$mvt['type_mouvement'];

        // Calcul du rollback
        $rollback = 0;
        if ($type === 'Entrée') {
            $rollback = -$quantite;
        } elseif ($type === 'Sortie') {
            $rollback = +$quantite;
        } else { // Ajustement interprété comme delta
            $rollback = -$quantite;
        }

        // Récupérer le stock actuel
        $stmtStock = $bdd->prepare("SELECT quantite_stock FROM pieces WHERE id_piece = :id AND supprimer = 'Non' LIMIT 1");
        $stmtStock->execute([':id' => $idPiece]);
        $stock = $stmtStock->fetchColumn();
        if ($stock === false) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/mouvements_stock.php');
            exit;
        }
        $stock = (int)$stock;

        $newStock = $stock + $rollback;

        // Données pour historique
        $ancienne = json_encode([
            'id_mouvement'   => $idMouvement,
            'id_piece'       => $idPiece,
            'type_mouvement' => $type,
            'quantite'       => $quantite,
            'date_mouvement' => $mvt['date_mouvement'],
            'motif'          => $mvt['motif'],
            'stock_avant'    => $stock,
            'stock_apres'    => $newStock
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id_mouvement' => $idMouvement,
            'supprimer'    => 'Oui'
        ], JSON_UNESCAPED_UNICODE);

        // Transaction: MAJ stock + suppression logique + historique
        $bdd->beginTransaction();

        // Mise à jour du stock
        $upd = $bdd->prepare("UPDATE pieces SET quantite_stock = :stock WHERE id_piece = :id");
        $upd->execute([':stock' => $newStock, ':id' => $idPiece]);

        // Suppression logique du mouvement
        $stmtSuppr = $bdd->prepare("UPDATE mouvements_stock SET supprimer = 'Oui' WHERE id_mouvement = :id");
        $stmtSuppr->execute([':id' => $idMouvement]);

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
            ':action' => 'Suppression mouvement stock',
            ':table'  => 'mouvements_stock',
            ':idc'    => $idMouvement,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $bdd->commit();

        // ✅ Flag de succès
        $_SESSION['suppr_mouvement'] = 1;
        header('Location: ../../pages/mouvements_stock.php');
        exit;

    } catch (Throwable $e) {
        if (isset($bdd) && $bdd->inTransaction()) {
            $bdd->rollBack();
        }
        error_log("Erreur suppression mouvement (id={$idMouvement}) : " . $e->getMessage());
        $_SESSION['suppr_mouvement'] = 0;
        header('Location: ../../pages/mouvements_stock.php?error=delete');
        exit;
    }
} else {
    header('Location: ../../pages/mouvements_stock.php');
    exit;
}
