<?php
// Fichier : api/modules/modifier_mouvement.php

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

if (isset($_POST['id_mouvement'], $_POST['id_piece'], $_POST['type_mouvement'], $_POST['quantite'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)
        if (method_exists($bdd, 'setAttribute')) {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        // Récupération + nettoyage
        $idMvtRaw    = trim((string)$_POST['id_mouvement']);
        $idPieceRaw  = trim((string)$_POST['id_piece']);
        $typeRaw     = trim((string)$_POST['type_mouvement']); // attend ENTREE/SORTIE/AJUSTEMENT (depuis modal)
        $qteRaw      = trim((string)$_POST['quantite']);
        $dateRaw     = trim((string)($_POST['date_mouvement'] ?? '')); // datetime-local optionnel
        $motif       = isset($_POST['motif']) ? trim((string)$_POST['motif']) : '';

        $idMouvement = (ctype_digit($idMvtRaw) ? (int)$idMvtRaw : 0);
        $idPiece     = (ctype_digit($idPieceRaw) ? (int)$idPieceRaw : 0);
        $quantite    = (ctype_digit($qteRaw) ? (int)$qteRaw : 0);

        // Map du type pour correspondre à l'ENUM en base (Entrée/Sortie/Ajustement)
        $mapType = [
            'ENTREE' => 'Entrée',
            'SORTIE' => 'Sortie',
            'AJUSTEMENT' => 'Ajustement',
            // tolère déjà les libellés si jamais envoyés
            'Entrée' => 'Entrée',
            'Sortie' => 'Sortie',
            'Ajustement' => 'Ajustement',
        ];
        $type = $mapType[$typeRaw] ?? '';

        // Validations
        if ($idMouvement <= 0 || $idPiece <= 0 || !in_array($type, ['Entrée','Sortie','Ajustement'], true) || $quantite <= 0) {
            $_SESSION['imp'] = 1;
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
        $dateMouvement = $normalizeDateTime($dateRaw); // peut rester null (on laissera la valeur existante si null)

        // Récupérer l'ancien mouvement pour calculer le différentiel de stock
        $oldStmt = $bdd->prepare("
            SELECT id_piece, type_mouvement, quantite, date_mouvement, motif
            FROM mouvements_stock
            WHERE id_mouvement = :id AND supprimer = 'Non'
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idMouvement]);
        $old = $oldStmt->fetch(PDO::FETCH_ASSOC);

        if (!$old) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/mouvements_stock.php');
            exit;
        }

        $oldIdPiece = (int)$old['id_piece'];
        $oldType    = (string)$old['type_mouvement']; // 'Entrée' | 'Sortie' | 'Ajustement'
        $oldQte     = (int)$old['quantite'];

        // Vérifier que la pièce cible existe et non supprimée
        $pieceStmt = $bdd->prepare("SELECT quantite_stock FROM pieces WHERE id_piece = :id AND supprimer = 'Non' LIMIT 1");
        $pieceStmt->execute([':id' => $idPiece]);
        $stockCible = $pieceStmt->fetchColumn();
        if ($stockCible === false) {
            $_SESSION['piece_invalide'] = 1;
            header('Location: ../../pages/mouvements_stock.php');
            exit;
        }
        $stockCible = (int)$stockCible;

        // Si la pièce change, récupérer l'ancien stock de l'ancienne pièce
        $stockOldPiece = $stockCible;
        if ($idPiece !== $oldIdPiece) {
            $oldPieceStmt = $bdd->prepare("SELECT quantite_stock FROM pieces WHERE id_piece = :id AND supprimer = 'Non' LIMIT 1");
            $oldPieceStmt->execute([':id' => $oldIdPiece]);
            $stockOldPiece = (int)$oldPieceStmt->fetchColumn();
        }

        // Calcul du rollback de l'ancien mouvement sur l'ancienne pièce
        $rollbackOld = 0;
        if ($oldType === 'Entrée') {
            $rollbackOld = -$oldQte;
        } elseif ($oldType === 'Sortie') {
            $rollbackOld = +$oldQte;
        } else { // Ajustement (interprété comme delta appliqué)
            $rollbackOld = -$oldQte;
        }

        // Calcul de l'application du nouveau mouvement sur la nouvelle pièce
        $applyNew = 0;
        if ($type === 'Entrée') {
            $applyNew = +$quantite;
        } elseif ($type === 'Sortie') {
            $applyNew = -$quantite;
        } else { // Ajustement (delta)
            $applyNew = +$quantite;
        }

        // Transaction: rollback ancien effet + appliquer nouveau, puis UPDATE du mouvement
        $bdd->beginTransaction();

        // 1) Corriger l'ancienne pièce (si nécessaire)
        if ($idPiece === $oldIdPiece) {
            // Même pièce: on applique net = rollbackOld + applyNew sur le même stock
            $newStock = $stockCible + $rollbackOld + $applyNew;
            $updPiece = $bdd->prepare("UPDATE pieces SET quantite_stock = :q WHERE id_piece = :id");
            $updPiece->execute([':q' => $newStock, ':id' => $idPiece]);
        } else {
            // Pièce différente:
            // a) rollback sur l'ancienne pièce
            $newStockOld = $stockOldPiece + $rollbackOld;
            $updOld = $bdd->prepare("UPDATE pieces SET quantite_stock = :q WHERE id_piece = :id");
            $updOld->execute([':q' => $newStockOld, ':id' => $oldIdPiece]);

            // b) apply sur la nouvelle pièce
            $newStockNew = $stockCible + $applyNew;
            $updNew = $bdd->prepare("UPDATE pieces SET quantite_stock = :q WHERE id_piece = :id");
            $updNew->execute([':q' => $newStockNew, ':id' => $idPiece]);
        }

        // 2) Mettre à jour le mouvement
        // Si dateMouvement est null, on conserve la valeur existante (ne pas l'écraser à NULL)
        $sql = "UPDATE mouvements_stock
                   SET id_piece = :id_piece,
                       type_mouvement = :type_mouvement,
                       quantite = :quantite,
                       motif = :motif";

        if ($dateMouvement !== null) {
            $sql .= ", date_mouvement = :date_mouvement";
        }

        $sql .= " WHERE id_mouvement = :id_mouvement AND supprimer = 'Non'";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':id_piece',       $idPiece, PDO::PARAM_INT);
        $stmt->bindValue(':type_mouvement', $type,    PDO::PARAM_STR);
        $stmt->bindValue(':quantite',       $quantite, PDO::PARAM_INT);
        $stmt->bindValue(':motif',          $motif !== '' ? $motif : null, $motif !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        if ($dateMouvement !== null) {
            $stmt->bindValue(':date_mouvement', $dateMouvement, PDO::PARAM_STR);
        }
        $stmt->bindValue(':id_mouvement',   $idMouvement, PDO::PARAM_INT);
        $stmt->execute();

        // Historique action (avant / après)
        $ancienne = json_encode([
            'id_mouvement'   => $idMouvement,
            'id_piece'       => $oldIdPiece,
            'type_mouvement' => $oldType,
            'quantite'       => $oldQte,
            'date_mouvement' => $old['date_mouvement'],
            'motif'          => $old['motif']
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id_mouvement'   => $idMouvement,
            'id_piece'       => $idPiece,
            'type_mouvement' => $type,
            'quantite'       => $quantite,
            'date_mouvement' => $dateMouvement, // peut être null si non modifiée
            'motif'          => ($motif !== '' ? $motif : null)
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
            ':action' => 'Modification mouvement stock',
            ':table'  => 'mouvements_stock',
            ':idc'    => $idMouvement,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $bdd->commit();

        $_SESSION['mod_mouvement'] = 1;
        header('Location: ../../pages/mouvements_stock.php');
        exit;

    } catch (Throwable $e) {
        if (isset($bdd) && $bdd->inTransaction()) {
            $bdd->rollBack();
        }
        // error_log('modifier_mouvement: ' . $e->getMessage());
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/mouvements_stock.php');
        exit;
    }
} else {
    header('Location: ../../pages/mouvements_stock.php');
    exit;
}
