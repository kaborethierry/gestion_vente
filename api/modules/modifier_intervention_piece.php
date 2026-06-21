<?php
// Fichier : api/modules/modifier_intervention_piece.php

session_start();

// Autorisation: uniquement Admin
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

// N'accepter que le POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/intervention_pieces.php');
    exit;
}

if (isset($_POST['id'], $_POST['id_intervention'], $_POST['id_piece'], $_POST['quantite'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)
        if (method_exists($bdd, 'setAttribute')) {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        // 1) Récupération + nettoyage
        $idRaw             = trim((string)$_POST['id']);
        $idInterventionRaw = trim((string)$_POST['id_intervention']);
        $idPieceRaw        = trim((string)$_POST['id_piece']);
        $quantiteRaw       = trim((string)$_POST['quantite']);
        $prixUnitRaw       = trim((string)($_POST['prix_unitaire'] ?? ''));

        $id             = (ctype_digit($idRaw) && (int)$idRaw > 0) ? (int)$idRaw : 0;
        $idIntervention = (ctype_digit($idInterventionRaw) && (int)$idInterventionRaw > 0) ? (int)$idInterventionRaw : 0;
        $idPiece        = (ctype_digit($idPieceRaw) && (int)$idPieceRaw > 0) ? (int)$idPieceRaw : 0;
        $quantite       = (ctype_digit($quantiteRaw) && (int)$quantiteRaw > 0) ? (int)$quantiteRaw : 0;

        $prixUnitaire = null;
        if ($prixUnitRaw !== '') {
            $p = str_replace(',', '.', $prixUnitRaw);
            if (is_numeric($p) && (float)$p >= 0) {
                $prixUnitaire = (float)$p;
            }
        }

        // 2) Validations de base
        if ($id <= 0 || $idIntervention <= 0 || $idPiece <= 0 || $quantite <= 0) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/intervention_pieces.php');
            exit;
        }

        // 3) Vérifier que la ligne existe et n'est pas supprimée logiquement + récupérer ancienne valeur
        $selOld = $bdd->prepare("
            SELECT id_intervention, id_piece, quantite, prix_unitaire, date_ajout
            FROM intervention_pieces
            WHERE id = :id AND (supprimer IS NULL OR supprimer = 'Non')
            LIMIT 1
        ");
        $selOld->execute([':id' => $id]);
        $oldRow = $selOld->fetch(PDO::FETCH_ASSOC);
        if (!$oldRow) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/intervention_pieces.php');
            exit;
        }

        // 4) Vérifier existence intervention et pièce (non supprimées)
        $chkInt = $bdd->prepare("SELECT 1 FROM interventions WHERE id_intervention = :id AND supprimer = 'Non' LIMIT 1");
        $chkInt->execute([':id' => $idIntervention]);
        if (!$chkInt->fetchColumn()) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/intervention_pieces.php');
            exit;
        }

        $chkPiece = $bdd->prepare("SELECT prix_vente FROM pieces WHERE id_piece = :id AND supprimer = 'Non' LIMIT 1");
        $chkPiece->execute([':id' => $idPiece]);
        $pieceRow = $chkPiece->fetch(PDO::FETCH_ASSOC);
        if (!$pieceRow) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/intervention_pieces.php');
            exit;
        }

        // 5) Fallback du prix unitaire si non fourni
        if (is_null($prixUnitaire)) {
            $prixUnitaire = is_null($pieceRow['prix_vente']) ? 0.00 : (float)$pieceRow['prix_vente'];
        }

        // 6) Empêcher doublon strict sur (intervention, pièce, prix_unitaire) pour une autre ligne active
        $dup = $bdd->prepare("
            SELECT COUNT(*) 
            FROM intervention_pieces
            WHERE id <> :id
              AND id_intervention = :iid
              AND id_piece = :pid
              AND (supprimer IS NULL OR supprimer = 'Non')
              AND ABS(prix_unitaire - :pu) < 0.0001
        ");
        $dup->execute([
            ':id'  => $id,
            ':iid' => $idIntervention,
            ':pid' => $idPiece,
            ':pu'  => $prixUnitaire
        ]);
        if ((int)$dup->fetchColumn() > 0) {
            $_SESSION['intervention_piece_exist'] = 1;
            header('Location: ../../pages/intervention_pieces.php');
            exit;
        }

        // 7) Mise à jour
        $sql = "
            UPDATE intervention_pieces
               SET id_intervention = :id_intervention,
                   id_piece        = :id_piece,
                   quantite        = :quantite,
                   prix_unitaire   = :prix_unitaire
             WHERE id = :id
        ";
        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':id_intervention', $idIntervention, PDO::PARAM_INT);
        $stmt->bindValue(':id_piece',        $idPiece,        PDO::PARAM_INT);
        $stmt->bindValue(':quantite',        $quantite,       PDO::PARAM_INT);
        $stmt->bindValue(':prix_unitaire',   number_format($prixUnitaire, 2, '.', ''), PDO::PARAM_STR);
        $stmt->bindValue(':id',              $id,             PDO::PARAM_INT);
        $stmt->execute();

        // 8) Historique action (ancienne vs nouvelle valeurs)
        $ancienne = json_encode([
            'id_intervention' => (int)$oldRow['id_intervention'],
            'id_piece'        => (int)$oldRow['id_piece'],
            'quantite'        => (int)$oldRow['quantite'],
            'prix_unitaire'   => isset($oldRow['prix_unitaire']) ? (float)$oldRow['prix_unitaire'] : null,
            'date_ajout'      => $oldRow['date_ajout']
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id'               => $id,
            'id_intervention'  => $idIntervention,
            'id_piece'         => $idPiece,
            'quantite'         => $quantite,
            'prix_unitaire'    => (float)$prixUnitaire,
            'date_modification'=> date('Y-m-d H:i:s')
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
            ':action' => 'Modification intervention_piece',
            ':table'  => 'intervention_pieces',
            ':idc'    => $id,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['mod_intervention_piece'] = 1;
        header('Location: ../../pages/intervention_pieces.php');
        exit;

    } catch (Throwable $e) {
        // error_log('modifier_intervention_piece: ' . $e->getMessage());
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/intervention_pieces.php');
        exit;
    }
} else {
    header('Location: ../../pages/intervention_pieces.php');
    exit;
}
