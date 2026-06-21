<?php
// Fichier : api/modules/vehicule_data.php

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // 🔐 Sécurité : accès réservé aux administrateurs
    if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd

    // 📊 Paramètres DataTables
    $draw        = intval($_GET['draw'] ?? $_POST['draw'] ?? 0);
    $start       = max(0, intval($_GET['start'] ?? $_POST['start'] ?? 0));
    $length      = intval($_GET['length'] ?? $_POST['length'] ?? 10);
    $searchValue = trim($_GET['search']['value'] ?? $_POST['search']['value'] ?? '');

    // 📌 Total brut (non filtré)
    $totalStmt = $bdd->query("SELECT COUNT(*) FROM vehicules WHERE supprimer = 'Non'");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // 🔍 Filtrage
    $where    = "WHERE v.supprimer = 'Non'";
    $bindings = [];

    if ($searchValue !== '') {
        $where .= " AND (
            v.immatriculation LIKE :search
         OR v.marque LIKE :search
         OR v.modele LIKE :search
         OR v.type_moteur LIKE :search
         OR CAST(v.annee AS CHAR) LIKE :search
         OR CAST(v.kilometrage AS CHAR) LIKE :search
         OR v.couleur LIKE :search
         OR v.transmission LIKE :search
         OR v.statut_vehicule LIKE :search
         OR CASE
             WHEN c.type_client = 'Entreprise' THEN c.raison_sociale
             ELSE CONCAT(c.nom, ' ', c.prenom)
           END LIKE :search
        )";
        $bindings[':search'] = "%{$searchValue}%";
    }

    // 📌 Total filtré
    $countSql = "
        SELECT COUNT(*)
        FROM vehicules v
        LEFT JOIN clients c ON c.id_client = v.id_client
        $where
    ";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $key => $val) {
        $countStmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    // 🔀 Tri
    $orderColIndex = intval($_GET['order'][0]['column'] ?? $_POST['order'][0]['column'] ?? 0);
    $orderDirInput = strtolower($_GET['order'][0]['dir'] ?? $_POST['order'][0]['dir'] ?? 'desc');
    $orderDir      = $orderDirInput === 'asc' ? 'ASC' : 'DESC';

    $columnMap = [
        0 => 'v.id_vehicule',
        1 => 'v.id_client',
        2 => 'v.immatriculation',
        3 => 'v.marque',
        4 => 'v.modele',
        5 => 'v.type_moteur',
        6 => 'v.annee',
        7 => 'v.kilometrage',
        8 => 'v.couleur',
        9 => 'v.transmission',
        10 => 'v.statut_vehicule',
        11 => 'v.date_ajout'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'v.id_vehicule') . " $orderDir";

    // 📦 Requête enrichie
    $useLimit = $length !== -1;
    $limitSql = $useLimit ? "LIMIT :start, :length" : "";

    $dataSql = "
        SELECT
            v.id_vehicule,
            v.id_client,
            v.immatriculation,
            v.marque,
            v.modele,
            v.categorie,
            v.type_moteur,
            v.capacite_moteur,
            v.puissance_cv,
            v.annee,
            v.kilometrage,
            v.couleur,
            v.vin,
            v.transmission,
            v.nbr_portes,
            v.conso_urbaine,
            v.conso_extra_urbaine,
            v.emission_co2,
            v.type_assurance,
            v.numero_assurance,
            DATE_FORMAT(v.date_expiration_assurance, '%Y-%m-%d') AS date_expiration_assurance,
            DATE_FORMAT(v.date_immatriculation, '%Y-%m-%d') AS date_immatriculation,
            DATE_FORMAT(v.date_derniere_entretien, '%Y-%m-%d %H:%i') AS date_derniere_entretien,
            v.kilometrage_derniere_entretien,
            DATE_FORMAT(v.date_prochain_entretien, '%Y-%m-%d %H:%i') AS date_prochain_entretien,
            DATE_FORMAT(v.garantie_fin, '%Y-%m-%d') AS garantie_fin,
            v.couleur_interieur,
            v.statut_vehicule,
            DATE_FORMAT(v.date_ajout, '%Y-%m-%d %H:%i:%s') AS date_ajout,

            CASE
                WHEN c.type_client = 'Entreprise' AND c.raison_sociale IS NOT NULL THEN c.raison_sociale
                ELSE CONCAT(c.nom, ' ', c.prenom)
            END AS client_label
        FROM vehicules v
        LEFT JOIN clients c ON c.id_client = v.id_client AND c.supprimer = 'Non'
        $where
        ORDER BY $orderBy
        $limitSql
    ";
    $dataStmt = $bdd->prepare($dataSql);

    foreach ($bindings as $key => $val) {
        $dataStmt->bindValue($key, $val, PDO::PARAM_STR);
    }

    if ($useLimit) {
        $dataStmt->bindValue(':start', $start, PDO::PARAM_INT);
        $dataStmt->bindValue(':length', $length, PDO::PARAM_INT);
    }

    $dataStmt->execute();
    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    // 🧾 Envoi JSON
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
