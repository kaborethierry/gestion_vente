<?php
// Fichier : api/modules/ajouter_intervention_piece.php

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

try {
    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)
    if (method_exists($bdd, 'setAttribute')) {
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // 1) Récupération + nettoyage
    $idInterventionRaw = trim($_POST['id_intervention'] ?? '');
    $idPieceRaw        = trim($_POST['id_piece'] ?? '');
    $quantiteRaw       = trim($_POST['quantite'] ?? '');
    $prixUnitRaw       = trim($_POST['prix_unitaire'] ?? '');

    $idIntervention = ($idInterventionRaw !== '' && ctype_digit($idInterventionRaw)) ? (int)$idInterventionRaw : null;
    $idPiece        = ($idPieceRaw !== '' && ctype_digit($idPieceRaw)) ? (int)$idPieceRaw : null;

    $quantite = ($quantiteRaw !== '' && ctype_digit($quantiteRaw) && (int)$quantiteRaw > 0) ? (int)$quantiteRaw : null;

    $prixUnitaire = null;
    if ($prixUnitRaw !== '') {
        $p = str_replace(',', '.', $prixUnitRaw);
        if (is_numeric($p) && (float)$p >= 0) {
            $prixUnitaire = (float)$p;
        }
    }

    // 2) Validations obligatoires
    if (is_null($idIntervention) || is_null($idPiece) || is_null($quantite)) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/intervention_pieces.php');
        exit;
    }

    // 3) Vérifier existence intervention non supprimée
    $chkInt = $bdd->prepare("SELECT 1 FROM interventions WHERE id_intervention = :id AND supprimer = 'Non' LIMIT 1");
    $chkInt->execute([':id' => $idIntervention]);
    if (!$chkInt->fetchColumn()) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/intervention_pieces.php');
        exit;
    }

    // 4) Vérifier existence pièce non supprimée
    $chkPiece = $bdd->prepare("SELECT prix_vente, quantite_stock FROM pieces WHERE id_piece = :id AND supprimer = 'Non' LIMIT 1");
    $chkPiece->execute([':id' => $idPiece]);
    $pieceRow = $chkPiece->fetch(PDO::FETCH_ASSOC);
    if (!$pieceRow) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/intervention_pieces.php');
        exit;
    }

    // 5) Prix unitaire fallback = prix_vente si non fourni
    if (is_null($prixUnitaire)) {
        if (!is_null($pieceRow['prix_vente'])) {
            $prixUnitaire = (float)$pieceRow['prix_vente'];
        } else {
            $prixUnitaire = 0.00; // fallback minimal
        }
    }

    // 6) Optionnel: contrôle simple de stock (sans le décrémenter ici)
    // if (!is_null($pieceRow['quantite_stock']) && $pieceRow['quantite_stock'] < $quantite) {
    //     $_SESSION['imp'] = 1;
    //     header('Location: ../../pages/intervention_pieces.php');
    //     exit;
    // }

    // 7) Empêcher doublon exact (même intervention + même pièce + même prix_unitaire non supprimé)
    $dup = $bdd->prepare("
        SELECT COUNT(*) FROM intervention_pieces 
        WHERE id_intervention = :iid 
          AND id_piece = :pid
          AND (supprimer IS NULL OR supprimer = 'Non')
          AND ABS(prix_unitaire - :pu) < 0.0001
    ");
    $dup->execute([
        ':iid' => $idIntervention,
        ':pid' => $idPiece,
        ':pu'  => $prixUnitaire
    ]);
    if ((int)$dup->fetchColumn() > 0) {
        $_SESSION['intervention_piece_exist'] = 1;
        header('Location: ../../pages/intervention_pieces.php');
        exit;
    }

    // 8) Insertion
    $ins = $bdd->prepare("
        INSERT INTO intervention_pieces
            (id_intervention, id_piece, quantite, prix_unitaire, date_ajout, supprimer)
        VALUES
            (:id_intervention, :id_piece, :quantite, :prix_unitaire, NOW(), 'Non')
    ");

    $ins->bindValue(':id_intervention', $idIntervention, PDO::PARAM_INT);
    $ins->bindValue(':id_piece',        $idPiece,        PDO::PARAM_INT);
    $ins->bindValue(':quantite',        $quantite,       PDO::PARAM_INT);
    $ins->bindValue(':prix_unitaire',   number_format($prixUnitaire, 2, '.', ''), PDO::PARAM_STR);

    $ins->execute();

    $idNew = (int)$bdd->lastInsertId();

    // 9) Historique action
    $nouvelle = json_encode([
        'id_intervention_piece' => $idNew,
        'id_intervention'       => $idIntervention,
        'id_piece'              => $idPiece,
        'quantite'              => $quantite,
        'prix_unitaire'         => (float)$prixUnitaire,
        'date_ajout'            => date('Y-m-d H:i:s')
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
        ':action' => 'Ajout intervention_piece',
        ':table'  => 'intervention_pieces',
        ':idc'    => $idNew,
        ':old'    => null,
        ':new'    => $nouvelle
    ]);

    $_SESSION['ajout_intervention_piece'] = 1;
    header('Location: ../../pages/intervention_pieces.php');
    exit;

} catch (Throwable $e) {
    // error_log('ajouter_intervention_piece: ' . $e->getMessage());
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/intervention_pieces.php');
    exit;
}
