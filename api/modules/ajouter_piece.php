<?php
// Fichier : api/modules/ajouter_piece.php

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

try {
    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)

    // Récupération + nettoyage des données
    $reference   = isset($_POST['reference'])   ? trim($_POST['reference'])   : '';
    $designation = isset($_POST['designation']) ? trim($_POST['designation']) : '';

    $prixAchatRaw = trim($_POST['prix_achat'] ?? '');
    $prixVenteRaw = trim($_POST['prix_vente'] ?? '');
    $quantiteRaw  = trim($_POST['quantite_stock'] ?? '');
    $seuilRaw     = trim($_POST['seuil_minimal'] ?? '');
    $fournisseur  = isset($_POST['fournisseur']) ? trim($_POST['fournisseur']) : '';
    $idCatRaw     = trim($_POST['id_categorie'] ?? '');

    // Validation champs obligatoires
    if ($reference === '' || $designation === '') {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/pieces.php');
        exit;
    }

    // Normalisation formats numériques
    $prixAchat = $prixAchatRaw !== '' ? str_replace(',', '.', $prixAchatRaw) : null;
    if (!is_null($prixAchat) && !is_numeric($prixAchat)) {
        $prixAchat = null;
    }

    $prixVente = $prixVenteRaw !== '' ? str_replace(',', '.', $prixVenteRaw) : null;
    if (!is_null($prixVente) && !is_numeric($prixVente)) {
        $prixVente = null;
    }

    $quantite = ($quantiteRaw !== '' && ctype_digit($quantiteRaw)) ? (int)$quantiteRaw : null;
    $seuil    = ($seuilRaw !== '' && ctype_digit($seuilRaw)) ? (int)$seuilRaw : null;

    $fournisseur = ($fournisseur !== '') ? $fournisseur : null;
    $idCategorie = ($idCatRaw !== '' && ctype_digit($idCatRaw)) ? (int)$idCatRaw : null;

    // Contrôle de doublon sur référence parmi les non supprimés
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM pieces WHERE supprimer = 'Non' AND reference = :ref");
    $stmt->execute([':ref' => $reference]);
    $count = (int)$stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['ref_exist'] = 1; // référence déjà existante
        header('Location: ../../pages/pieces.php');
        exit;
    }

    // Insertion
    $sql = "INSERT INTO pieces
            (reference, designation, prix_achat, prix_vente, quantite_stock, seuil_minimal, fournisseur, id_categorie, supprimer)
            VALUES
            (:reference, :designation, :prix_achat, :prix_vente, :quantite_stock, :seuil_minimal, :fournisseur, :id_categorie, 'Non')";
    $stmt = $bdd->prepare($sql);

    $stmt->bindValue(':reference',      $reference,   PDO::PARAM_STR);
    $stmt->bindValue(':designation',    $designation, PDO::PARAM_STR);
    $stmt->bindValue(':prix_achat',     $prixAchat,   is_null($prixAchat) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':prix_vente',     $prixVente,   is_null($prixVente) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':quantite_stock', $quantite,    is_null($quantite) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':seuil_minimal',  $seuil,       is_null($seuil) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':fournisseur',    $fournisseur, is_null($fournisseur) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':id_categorie',   $idCategorie, is_null($idCategorie) ? PDO::PARAM_NULL : PDO::PARAM_INT);

    $stmt->execute();

    // Historique action
    $id_new = (int)$bdd->lastInsertId();
    $nouvelle = json_encode([
        'id_piece'       => $id_new,
        'reference'      => $reference,
        'designation'    => $designation,
        'prix_achat'     => is_null($prixAchat) ? null : (float)$prixAchat,
        'prix_vente'     => is_null($prixVente) ? null : (float)$prixVente,
        'quantite_stock' => $quantite,
        'seuil_minimal'  => $seuil,
        'fournisseur'    => $fournisseur,
        'id_categorie'   => $idCategorie
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
        ':action' => 'Ajout pièce',
        ':table'  => 'pieces',
        ':idc'    => $id_new,
        ':old'    => null,
        ':new'    => $nouvelle
    ]);

    $_SESSION['ajout_piece'] = 1;
    header('Location: ../../pages/pieces.php');
    exit;

} catch (Throwable $e) {
    // error_log($e->getMessage());
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/pieces.php');
    exit;
}
