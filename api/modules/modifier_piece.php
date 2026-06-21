<?php
// Fichier : api/modules/modifier_piece.php

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
    header('Location: ../../pages/pieces.php');
    exit;
}

if (isset($_POST['id_piece'], $_POST['reference'], $_POST['designation'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)

        // Récupération + nettoyage
        $idPiece     = (int) $_POST['id_piece'];
        $reference   = trim((string) $_POST['reference']);
        $designation = trim((string) $_POST['designation']);

        $prixAchatRaw = isset($_POST['prix_achat']) ? trim((string) $_POST['prix_achat']) : '';
        $prixVenteRaw = isset($_POST['prix_vente']) ? trim((string) $_POST['prix_vente']) : '';
        $quantiteRaw  = isset($_POST['quantite_stock']) ? trim((string) $_POST['quantite_stock']) : '';
        $seuilRaw     = isset($_POST['seuil_minimal']) ? trim((string) $_POST['seuil_minimal']) : '';
        $fournisseur  = isset($_POST['fournisseur']) ? trim((string) $_POST['fournisseur']) : '';
        $idCatRaw     = isset($_POST['id_categorie']) ? trim((string) $_POST['id_categorie']) : '';

        // Validation basique
        if ($idPiece <= 0 || $reference === '' || $designation === '') {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/pieces.php');
            exit;
        }

        // Récupération de l'ancienne valeur pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_piece, reference, designation, prix_achat, prix_vente, quantite_stock, seuil_minimal, fournisseur, id_categorie
            FROM pieces
            WHERE id_piece = :id
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idPiece]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);

        // Normalisation
        $prixAchat = ($prixAchatRaw !== '') ? str_replace(',', '.', $prixAchatRaw) : null;
        if (!is_null($prixAchat) && !is_numeric($prixAchat)) $prixAchat = null;

        $prixVente = ($prixVenteRaw !== '') ? str_replace(',', '.', $prixVenteRaw) : null;
        if (!is_null($prixVente) && !is_numeric($prixVente)) $prixVente = null;

        $quantite  = ($quantiteRaw !== '' && ctype_digit($quantiteRaw)) ? (int)$quantiteRaw : null;
        $seuil     = ($seuilRaw !== '' && ctype_digit($seuilRaw)) ? (int)$seuilRaw : null;
        $fournisseur = ($fournisseur !== '') ? $fournisseur : null;
        $idCategorie = ($idCatRaw !== '' && ctype_digit($idCatRaw)) ? (int)$idCatRaw : null;

        // Vérification doublon de référence (parmi non supprimés, en excluant l'élément courant)
        $stmt = $bdd->prepare("SELECT COUNT(*) FROM pieces WHERE supprimer = 'Non' AND reference = :ref AND id_piece <> :id");
        $stmt->execute([':ref' => $reference, ':id' => $idPiece]);
        if ((int)$stmt->fetchColumn() > 0) {
            $_SESSION['ref_exist'] = 1;
            header('Location: ../../pages/pieces.php');
            exit;
        }

        // Mise à jour
        $sql = "UPDATE pieces
                   SET reference = :reference,
                       designation = :designation,
                       prix_achat = :prix_achat,
                       prix_vente = :prix_vente,
                       quantite_stock = :quantite_stock,
                       seuil_minimal = :seuil_minimal,
                       fournisseur = :fournisseur,
                       id_categorie = :id_categorie
                 WHERE id_piece = :id_piece";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':reference',      $reference,   PDO::PARAM_STR);
        $stmt->bindValue(':designation',    $designation, PDO::PARAM_STR);
        $stmt->bindValue(':prix_achat',     $prixAchat,   is_null($prixAchat) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':prix_vente',     $prixVente,   is_null($prixVente) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':quantite_stock', $quantite,    is_null($quantite) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':seuil_minimal',  $seuil,       is_null($seuil) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':fournisseur',    $fournisseur, is_null($fournisseur) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':id_categorie',   $idCategorie, is_null($idCategorie) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id_piece',       $idPiece,     PDO::PARAM_INT);
        $stmt->execute();

        // Historique action (avant / après)
        $ancienne = $oldRow ? json_encode([
            'id_piece'       => (int)$oldRow['id_piece'],
            'reference'      => $oldRow['reference'],
            'designation'    => $oldRow['designation'],
            'prix_achat'     => is_null($oldRow['prix_achat']) ? null : (float)$oldRow['prix_achat'],
            'prix_vente'     => is_null($oldRow['prix_vente']) ? null : (float)$oldRow['prix_vente'],
            'quantite_stock' => is_null($oldRow['quantite_stock']) ? null : (int)$oldRow['quantite_stock'],
            'seuil_minimal'  => is_null($oldRow['seuil_minimal']) ? null : (int)$oldRow['seuil_minimal'],
            'fournisseur'    => $oldRow['fournisseur'],
            'id_categorie'   => is_null($oldRow['id_categorie']) ? null : (int)$oldRow['id_categorie'],
        ], JSON_UNESCAPED_UNICODE) : null;

        $nouvelle = json_encode([
            'id_piece'       => $idPiece,
            'reference'      => $reference,
            'designation'    => $designation,
            'prix_achat'     => is_null($prixAchat) ? null : (float)$prixAchat,
            'prix_vente'     => is_null($prixVente) ? null : (float)$prixVente,
            'quantite_stock' => $quantite,
            'seuil_minimal'  => $seuil,
            'fournisseur'    => $fournisseur,
            'id_categorie'   => $idCategorie,
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
            ':action' => 'Modification pièce',
            ':table'  => 'pieces',
            ':idc'    => $idPiece,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['mod_piece'] = 1;
        header('Location: ../../pages/pieces.php');
        exit;

    } catch (Throwable $e) {
        // error_log($e->getMessage());
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/pieces.php');
        exit;
    }
} else {
    header('Location: ../../pages/pieces.php');
    exit;
}
