<?php
// api/modules/modifier_categorie_piece.php

session_start();

if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

if (isset($_POST['id_categorie'], $_POST['libelle'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)

        // Récupération + nettoyage
        $idCategorie = (int) $_POST['id_categorie'];
        $libelle     = trim((string) $_POST['libelle']);
        $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';

        // Validation basique
        if ($idCategorie <= 0 || $libelle === '' || mb_strlen($libelle) > 50) {
            $_SESSION['modif_categorie'] = 0;
            header('Location: ../../pages/categories_pieces.php');
            exit;
        }

        // Récupérer l'ancienne valeur pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_categorie, libelle, description
            FROM categories_pieces
            WHERE id_categorie = :id AND supprimer = 'Non'
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idCategorie]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);
        if (!$oldRow) {
            $_SESSION['modif_categorie'] = 0;
            header('Location: ../../pages/categories_pieces.php');
            exit;
        }

        // Vérifier doublon de libellé en excluant la ligne courante
        $dup = $bdd->prepare("
            SELECT COUNT(*)
              FROM categories_pieces
             WHERE supprimer = 'Non'
               AND libelle = :libelle
               AND id_categorie <> :id
        ");
        $dup->execute([':libelle' => $libelle, ':id' => $idCategorie]);
        if ((int) $dup->fetchColumn() > 0) {
            $_SESSION['doublon_categorie'] = 1;
            header('Location: ../../pages/categories_pieces.php');
            exit;
        }

        // Mise à jour
        $sql = "UPDATE categories_pieces
                   SET libelle = :libelle,
                       description = :description
                 WHERE id_categorie = :id AND supprimer = 'Non'";
        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':libelle', $libelle, PDO::PARAM_STR);
        if ($description !== '') {
            $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':description', null, PDO::PARAM_NULL);
        }
        $stmt->bindValue(':id', $idCategorie, PDO::PARAM_INT);
        $stmt->execute();

        // Historique action (avant / après)
        $ancienne = json_encode([
            'id_categorie' => (int)$oldRow['id_categorie'],
            'libelle'      => $oldRow['libelle'],
            'description'  => $oldRow['description'],
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id_categorie' => $idCategorie,
            'libelle'      => $libelle,
            'description'  => ($description !== '' ? $description : null),
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
            ':action' => 'Modification catégorie pièce',
            ':table'  => 'categories_pieces',
            ':idc'    => $idCategorie,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['modif_categorie'] = 1;
        header('Location: ../../pages/categories_pieces.php');
        exit;

    } catch (Throwable $e) {
        // error_log($e->getMessage());
        $_SESSION['modif_categorie'] = 0;
        header('Location: ../../pages/categories_pieces.php');
        exit;
    }
} else {
    header('Location: ../../pages/categories_pieces.php');
    exit;
}
