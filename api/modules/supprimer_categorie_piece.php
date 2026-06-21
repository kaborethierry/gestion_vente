<?php
// api/modules/supprimer_categorie_piece.php

session_start();

// Vérification de l'accès : Admin uniquement
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

if (isset($_GET['id_categorie']) && is_numeric($_GET['id_categorie'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Connexion PDO

        $idCategorie = (int) $_GET['id_categorie'];

        // Récupération de l'ancienne valeur pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_categorie, libelle, description
            FROM categories_pieces
            WHERE id_categorie = :id AND supprimer = 'Non'
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idCategorie]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);

        // Suppression logique : passer "supprimer" à "Oui"
        $stmt = $bdd->prepare("UPDATE categories_pieces SET supprimer = 'Oui' WHERE id_categorie = :id");
        $stmt->bindValue(':id', $idCategorie, PDO::PARAM_INT);
        $stmt->execute();

        // Construction des données pour l'historique
        $ancienne = null;
        if ($oldRow) {
            $ancienne = json_encode([
                'id_categorie' => (int)$oldRow['id_categorie'],
                'libelle'      => $oldRow['libelle'],
                'description'  => $oldRow['description']
            ], JSON_UNESCAPED_UNICODE);
        }

        $nouvelle = json_encode([
            'id_categorie' => $idCategorie,
            'supprimer'    => 'Oui'
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
            ':action' => 'Suppression catégorie pièce',
            ':table'  => 'categories_pieces',
            ':idc'    => $idCategorie,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['suppr_categorie'] = 1;
        header('Location: ../../pages/categories_pieces.php');
        exit;

    } catch (Throwable $e) {
        error_log("Erreur lors de la suppression de la catégorie : " . $e->getMessage());
        $_SESSION['suppr_categorie'] = 0;
        header('Location: ../../pages/categories_pieces.php?error=delete');
        exit;
    }
} else {
    header('Location: ../../pages/categories_pieces.php');
    exit;
}
