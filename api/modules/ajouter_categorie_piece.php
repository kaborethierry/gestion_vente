<?php
// api/modules/ajouter_categorie_piece.php

session_start();

// Accès réservé à l'Admin
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

if (isset($_POST['libelle'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)

        // Récupération + nettoyage des données
        $libelle     = trim((string) $_POST['libelle']);
        $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';

        // Validation basique
        if ($libelle === '' || mb_strlen($libelle) > 50) {
            $_SESSION['doublon_categorie'] = 0; // pas un doublon, mais on signale un échec
            header('Location: ../../pages/categories_pieces.php');
            exit;
        }

        // Contrôle doublon sur libellé (uniquement non supprimées)
        $stmt = $bdd->prepare("SELECT COUNT(*) FROM categories_pieces WHERE supprimer = 'Non' AND libelle = :libelle");
        $stmt->execute([':libelle' => $libelle]);
        $count = (int) $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['doublon_categorie'] = 1; // Libellé déjà existant
            header('Location: ../../pages/categories_pieces.php');
            exit;
        }

        // Insertion
        $sql = "INSERT INTO categories_pieces (libelle, description, supprimer)
                VALUES (:libelle, :description, 'Non')";
        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':libelle', $libelle, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description !== '' ? $description : null, $description !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();

        // Historique action
        $id_new = (int)$bdd->lastInsertId();
        $nouvelle = json_encode([
            'id_categorie' => $id_new,
            'libelle'      => $libelle,
            'description'  => ($description !== '' ? $description : null)
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
            ':action' => 'Ajout catégorie pièce',
            ':table'  => 'categories_pieces',
            ':idc'    => $id_new,
            ':old'    => null,
            ':new'    => $nouvelle
        ]);

        $_SESSION['ajout_categorie'] = 1;
        header('Location: ../../pages/categories_pieces.php');
        exit;

    } catch (Throwable $e) {
        // error_log($e->getMessage());
        $_SESSION['ajout_categorie'] = 0;
        header('Location: ../../pages/categories_pieces.php');
        exit;
    }
} else {
    header('Location: ../../pages/categories_pieces.php');
    exit;
}
