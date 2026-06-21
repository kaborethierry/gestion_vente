<?php
// Fichier : api/modules/ajouter_mouvement.php

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
    header('Location: ../../pages/mouvements_stock.php');
    exit;
}

try {
    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)
    if (method_exists($bdd, 'setAttribute')) {
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Récupération + nettoyage
    $idPieceRaw   = trim($_POST['id_piece'] ?? '');
    $typeRaw      = trim($_POST['type_mouvement'] ?? '');
    $quantiteRaw  = trim($_POST['quantite'] ?? '');
    $motif        = isset($_POST['motif']) ? trim($_POST['motif']) : '';
    $dateRaw      = trim($_POST['date_mouvement'] ?? '');

    // Optionnels (si tu veux stocker des méta complémentaires)
    $prixUnitRaw  = trim($_POST['prix_unitaire'] ?? '');
    $refDoc       = isset($_POST['reference_document']) ? trim($_POST['reference_document']) : '';

    // Validation de base
    $idPiece  = ($idPieceRaw !== '' && ctype_digit($idPieceRaw)) ? (int)$idPieceRaw : 0;
    $quantite = ($quantiteRaw !== '' && ctype_digit($quantiteRaw)) ? (int)$quantiteRaw : 0;

    // Normalisation du type: le modal envoie ENTREE/SORTIE/AJUSTEMENT
    $mapType = [
        'ENTREE'     => "Entrée",
        'SORTIE'     => "Sortie",
        'AJUSTEMENT' => "Ajustement",
        'Entrée'     => "Entrée",
        'Sortie'     => "Sortie",
        'Ajustement' => "Ajustement",
    ];
    $type = $mapType[$typeRaw] ?? '';

    if ($idPiece <= 0) {
        $_SESSION['piece_invalide'] = 1;
        header('Location: ../../pages/mouvements_stock.php');
        exit;
    }
    if (!in_array($type, ['Entrée','Sortie','Ajustement'], true)) {
        $_SESSION['type_invalide'] = 1;
        header('Location: ../../pages/mouvements_stock.php');
        exit;
    }
    if ($quantite <= 0) {
        $_SESSION['quantite_invalide'] = 1;
        header('Location: ../../pages/mouvements_stock.php');
        exit;
    }

    // Normalisation date 'YYYY-MM-DDTHH:mm' -> 'YYYY-MM-DD HH:mm:ss'
    $normalizeDateTime = static function (?string $raw): ?string {
        if (!$raw) return null;
        $s = str_replace('T', ' ', $raw);
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $s)) {
            $s .= ':00';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/', $s)) {
            return null;
        }
        return $s;
    };
    $dateMvt = $normalizeDateTime($dateRaw); // peut rester null => DEFAULT CURRENT_TIMESTAMP()

    // Optionnels numériques
    $prixUnitaire = null;
    if ($prixUnitRaw !== '') {
        $prixUnitNorm = str_replace(',', '.', $prixUnitRaw);
        if (preg_match('/^\d+(?:\.\d+)?$/', $prixUnitNorm)) {
            $prixUnitaire = $prixUnitNorm;
        }
    }
    $refDoc = $refDoc !== '' ? $refDoc : null;

    // Vérifier existence pièce (et non supprimée)
    $st = $bdd->prepare("SELECT quantite_stock FROM pieces WHERE id_piece = :id AND supprimer = 'Non' LIMIT 1");
    $st->execute([':id' => $idPiece]);
    $stockActuel = $st->fetchColumn();
    if ($stockActuel === false) {
        $_SESSION['piece_invalide'] = 1;
        header('Location: ../../pages/mouvements_stock.php');
        exit;
    }
    $stockActuel = (int)$stockActuel;

    // Transaction: insertion mouvement + MAJ stock atomiques
    $bdd->beginTransaction();

    // Insertion mouvement
    // On n'insère pas date_mouvement si null pour laisser DEFAULT CURRENT_TIMESTAMP
    if ($dateMvt !== null) {
        $sqlM = "INSERT INTO mouvements_stock
                    (id_piece, type_mouvement, quantite, date_mouvement, motif, supprimer)
                 VALUES
                    (:id_piece, :type_mouvement, :quantite, :date_mouvement, :motif, 'Non')";
    } else {
        $sqlM = "INSERT INTO mouvements_stock
                    (id_piece, type_mouvement, quantite, motif, supprimer)
                 VALUES
                    (:id_piece, :type_mouvement, :quantite, :motif, 'Non')";
    }

    $ins = $bdd->prepare($sqlM);
    $ins->bindValue(':id_piece',       $idPiece, PDO::PARAM_INT);
    $ins->bindValue(':type_mouvement', $type,    PDO::PARAM_STR);
    $ins->bindValue(':quantite',       $quantite, PDO::PARAM_INT);
    if ($dateMvt !== null) {
        $ins->bindValue(':date_mouvement', $dateMvt, PDO::PARAM_STR);
    }
    $ins->bindValue(':motif', $motif !== '' ? $motif : null, $motif !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $ins->execute();

    $idMouvement = (int)$bdd->lastInsertId();

    // Calcul nouveau stock
    $nouveauStock = $stockActuel;
    if ($type === 'Entrée') {
        $nouveauStock += $quantite;
    } elseif ($type === 'Sortie') {
        // Si tu veux empêcher un stock négatif, décommente le bloc suivant:
        // if ($stockActuel - $quantite < 0) {
        //     throw new RuntimeException('Stock insuffisant pour la sortie.');
        // }
        $nouveauStock -= $quantite;
    } else { // Ajustement (delta)
        $nouveauStock += $quantite;
    }

    // Mise à jour du stock de la pièce
    $up = $bdd->prepare("UPDATE pieces SET quantite_stock = :qte WHERE id_piece = :id");
    $up->bindValue(':qte', $nouveauStock, PDO::PARAM_INT);
    $up->bindValue(':id',  $idPiece, PDO::PARAM_INT);
    $up->execute();

    // Historique action
    $nouvelle = json_encode([
        'id_mouvement'   => $idMouvement,
        'id_piece'       => $idPiece,
        'type_mouvement' => $type,
        'quantite'       => $quantite,
        'date_mouvement' => $dateMvt, // peut être null (DEFAULT côté DB)
        'motif'          => ($motif !== '' ? $motif : null),
        'prix_unitaire'  => is_null($prixUnitaire) ? null : (float)$prixUnitaire,
        'reference_document' => $refDoc ?: null,
        'stock_avant'    => $stockActuel,
        'stock_apres'    => $nouveauStock
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
        ':action' => 'Ajout mouvement stock',
        ':table'  => 'mouvements_stock',
        ':idc'    => $idMouvement,
        ':old'    => null,
        ':new'    => $nouvelle
    ]);

    $bdd->commit();

    $_SESSION['ajout_mouvement'] = 1;
    header('Location: ../../pages/mouvements_stock.php');
    exit;

} catch (Throwable $e) {
    if (isset($bdd) && $bdd->inTransaction()) {
        $bdd->rollBack();
    }
    // error_log('ajouter_mouvement: ' . $e->getMessage());
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/mouvements_stock.php');
    exit;
}
