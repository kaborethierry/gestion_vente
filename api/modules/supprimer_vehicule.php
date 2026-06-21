<?php
// Fichier : api/modules/supprimer_vehicule.php

session_start();

// 🔐 Autorisation: uniquement Admin
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit;
}

// ✅ Seulement si un id_vehicule numérique est fourni via GET
if (isset($_GET['id_vehicule']) && is_numeric($_GET['id_vehicule'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit fournir $bdd (PDO)

        $idVehicule = (int) $_GET['id_vehicule'];

        // Récupération des anciennes données pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_vehicule, immatriculation, marque, modele, annee, couleur, numero_serie, statut
            FROM vehicules
            WHERE id_vehicule = :id AND supprimer = 'Non'
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idVehicule]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldRow) {
            // Déjà supprimé ou inexistant → on pose quand même le flag pour feedback cohérent
            $_SESSION['suppr_vehicule'] = 1;
            header('Location: ../../pages/vehicules.php');
            exit;
        }

        // Suppression logique
        $stmt = $bdd->prepare("UPDATE vehicules SET supprimer = 'Oui' WHERE id_vehicule = :id");
        $stmt->bindValue(':id', $idVehicule, PDO::PARAM_INT);
        $stmt->execute();

        // Préparer ancienne et nouvelle valeurs pour historique
        $ancienne = json_encode([
            'id_vehicule'     => (int)$oldRow['id_vehicule'],
            'immatriculation' => $oldRow['immatriculation'],
            'marque'          => $oldRow['marque'],
            'modele'          => $oldRow['modele'],
            'annee'           => $oldRow['annee'],
            'couleur'         => $oldRow['couleur'],
            'numero_serie'    => $oldRow['numero_serie'],
            'statut'          => $oldRow['statut']
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id_vehicule' => $idVehicule,
            'supprimer'   => 'Oui'
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
            ':action' => 'Suppression véhicule',
            ':table'  => 'vehicules',
            ':idc'    => $idVehicule,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['suppr_vehicule'] = 1; // ✅ Flag de succès (clé alignée avec alert_vehicule.php)
        header('Location: ../../pages/vehicules.php');
        exit;

    } catch (Throwable $e) {
        error_log("Erreur lors de la suppression du véhicule (id={$idVehicule}) : " . $e->getMessage());
        $_SESSION['suppr_vehicule'] = 0;
        header('Location: ../../pages/vehicules.php?error=delete');
        exit;
    }
} else {
    header('Location: ../../pages/vehicules.php');
    exit;
}
