<?php
// Fichier : api/modules/ajouter_vehicule.php

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
    header('Location: ../../pages/vehicules.php');
    exit;
}

try {
    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)

    // Récupération + nettoyage des données
    $idClientRaw       = trim($_POST['id_client'] ?? '');
    $immatriculation   = isset($_POST['immatriculation']) ? trim($_POST['immatriculation']) : '';

    $marque            = isset($_POST['marque']) ? trim($_POST['marque']) : null;
    $modele            = isset($_POST['modele']) ? trim($_POST['modele']) : null;
    $typeMoteur        = isset($_POST['type_moteur']) ? trim($_POST['type_moteur']) : null;
    $anneeRaw          = trim($_POST['annee'] ?? '');
    $kilometrageRaw    = trim($_POST['kilometrage'] ?? '');
    $couleur           = isset($_POST['couleur']) ? trim($_POST['couleur']) : null;
    $transmission      = isset($_POST['transmission']) ? trim($_POST['transmission']) : 'Manuelle';
    $statutVehicule    = isset($_POST['statut_vehicule']) ? trim($_POST['statut_vehicule']) : 'En service';

    // Champs additionnels (optionnels dans le formulaire)
    $categorie         = isset($_POST['categorie']) ? trim($_POST['categorie']) : null;
    $vin               = isset($_POST['vin']) ? trim($_POST['vin']) : null;
    $dateImmatriculation = isset($_POST['date_immatriculation']) ? trim($_POST['date_immatriculation']) : null;
    $dateDernEntretien   = isset($_POST['date_derniere_entretien']) ? trim($_POST['date_derniere_entretien']) : null;
    $kmDernEntretienRaw  = trim($_POST['kilometrage_derniere_entretien'] ?? '');
    $dateProchEntretien  = isset($_POST['date_prochain_entretien']) ? trim($_POST['date_prochain_entretien']) : null;
    $typeAssurance     = isset($_POST['type_assurance']) ? trim($_POST['type_assurance']) : null;
    $numeroAssurance   = isset($_POST['numero_assurance']) ? trim($_POST['numero_assurance']) : null;
    $dateExpAssurance  = isset($_POST['date_expiration_assurance']) ? trim($_POST['date_expiration_assurance']) : null;
    $capaciteMoteurRaw = trim($_POST['capacite_moteur'] ?? '');
    $puissanceCvRaw    = trim($_POST['puissance_cv'] ?? '');
    $consoUrbaineRaw   = trim($_POST['conso_urbaine'] ?? '');
    $consoExtraRaw     = trim($_POST['conso_extra_urbaine'] ?? '');
    $emissionCo2Raw    = trim($_POST['emission_co2'] ?? '');
    $garantieFin       = isset($_POST['garantie_fin']) ? trim($_POST['garantie_fin']) : null;
    $nbrPortesRaw      = trim($_POST['nbr_portes'] ?? '');
    $couleurInterieur  = isset($_POST['couleur_interieur']) ? trim($_POST['couleur_interieur']) : null;

    // Validation champs obligatoires
    $idClient = ($idClientRaw !== '' && ctype_digit($idClientRaw)) ? (int)$idClientRaw : 0;
    if ($idClient <= 0 || $immatriculation === '') {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/vehicules.php');
        exit;
    }

    // Normalisation de formats numériques
    $annee       = ($anneeRaw !== '' && ctype_digit($anneeRaw)) ? (int)$anneeRaw : null;
    $kilometrage = ($kilometrageRaw !== '' && ctype_digit($kilometrageRaw)) ? (int)$kilometrageRaw : null;
    $kmDernEntretien = ($kmDernEntretienRaw !== '' && ctype_digit($kmDernEntretienRaw)) ? (int)$kmDernEntretienRaw : null;

    // décimaux avec virgules -> points
    $capaciteMoteur = $capaciteMoteurRaw !== '' ? str_replace(',', '.', $capaciteMoteurRaw) : null;
    if (!is_null($capaciteMoteur) && !is_numeric($capaciteMoteur)) {
        $capaciteMoteur = null;
    }

    $puissanceCv = ($puissanceCvRaw !== '' && ctype_digit($puissanceCvRaw)) ? (int)$puissanceCvRaw : null;

    $consoUrbaine = $consoUrbaineRaw !== '' ? str_replace(',', '.', $consoUrbaineRaw) : null;
    if (!is_null($consoUrbaine) && !is_numeric($consoUrbaine)) {
        $consoUrbaine = null;
    }

    $consoExtra = $consoExtraRaw !== '' ? str_replace(',', '.', $consoExtraRaw) : null;
    if (!is_null($consoExtra) && !is_numeric($consoExtra)) {
        $consoExtra = null;
    }

    $emissionCo2 = ($emissionCo2Raw !== '' && ctype_digit($emissionCo2Raw)) ? (int)$emissionCo2Raw : null;

    $nbrPortes = ($nbrPortesRaw !== '' && ctype_digit($nbrPortesRaw)) ? (int)$nbrPortesRaw : null;

    // Contraintes de valeurs sur enums (sécurisation côté serveur)
    $transmissionAllowed = ['Manuelle', 'Automatique', 'Séquentielle'];
    if (!in_array($transmission, $transmissionAllowed, true)) {
        $transmission = 'Manuelle';
    }

    $statutAllowed = ['En service', 'En réparation', 'Hors service'];
    if (!in_array($statutVehicule, $statutAllowed, true)) {
        $statutVehicule = 'En service';
    }

    // Existence du client (non supprimé)
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM clients WHERE supprimer = 'Non' AND id_client = :idc");
    $stmt->execute([':idc' => $idClient]);
    if ((int)$stmt->fetchColumn() === 0) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/vehicules.php');
        exit;
    }

    // Doublon immatriculation sur vehicules non supprimés
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM vehicules WHERE supprimer = 'Non' AND immatriculation = :immat");
    $stmt->execute([':immat' => $immatriculation]);
    if ((int)$stmt->fetchColumn() > 0) {
        $_SESSION['ref_exist'] = 1; // réutilisation du flag de duplication (comme pièces)
        header('Location: ../../pages/vehicules.php');
        exit;
    }

    // Insertion
    $sql = "INSERT INTO vehicules (
                id_client,
                immatriculation,
                marque,
                modele,
                type_moteur,
                annee,
                kilometrage,
                couleur,
                transmission,
                statut_vehicule,
                categorie,
                vin,
                date_immatriculation,
                date_derniere_entretien,
                kilometrage_derniere_entretien,
                date_prochain_entretien,
                type_assurance,
                numero_assurance,
                date_expiration_assurance,
                capacite_moteur,
                puissance_cv,
                conso_urbaine,
                conso_extra_urbaine,
                emission_co2,
                garantie_fin,
                nbr_portes,
                couleur_interieur,
                supprimer
            ) VALUES (
                :id_client,
                :immatriculation,
                :marque,
                :modele,
                :type_moteur,
                :annee,
                :kilometrage,
                :couleur,
                :transmission,
                :statut_vehicule,
                :categorie,
                :vin,
                :date_immatriculation,
                :date_derniere_entretien,
                :kilometrage_derniere_entretien,
                :date_prochain_entretien,
                :type_assurance,
                :numero_assurance,
                :date_expiration_assurance,
                :capacite_moteur,
                :puissance_cv,
                :conso_urbaine,
                :conso_extra_urbaine,
                :emission_co2,
                :garantie_fin,
                :nbr_portes,
                :couleur_interieur,
                'Non'
            )";
    $stmt = $bdd->prepare($sql);

    $stmt->bindValue(':id_client', $idClient, PDO::PARAM_INT);
    $stmt->bindValue(':immatriculation', $immatriculation, PDO::PARAM_STR);
    $stmt->bindValue(':marque', $marque !== '' ? $marque : null, $marque === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':modele', $modele !== '' ? $modele : null, $modele === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':type_moteur', $typeMoteur !== '' ? $typeMoteur : null, $typeMoteur === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':annee', $annee, is_null($annee) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':kilometrage', $kilometrage, is_null($kilometrage) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':couleur', $couleur !== '' ? $couleur : null, $couleur === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':transmission', $transmission, PDO::PARAM_STR);
    $stmt->bindValue(':statut_vehicule', $statutVehicule, PDO::PARAM_STR);

    $stmt->bindValue(':categorie', $categorie !== '' ? $categorie : null, $categorie === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':vin', $vin !== '' ? $vin : null, $vin === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':date_immatriculation', $dateImmatriculation !== '' ? $dateImmatriculation : null, $dateImmatriculation === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':date_derniere_entretien', $dateDernEntretien !== '' ? $dateDernEntretien : null, $dateDernEntretien === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':kilometrage_derniere_entretien', $kmDernEntretien, is_null($kmDernEntretien) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':date_prochain_entretien', $dateProchEntretien !== '' ? $dateProchEntretien : null, $dateProchEntretien === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':type_assurance', $typeAssurance !== '' ? $typeAssurance : null, $typeAssurance === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':numero_assurance', $numeroAssurance !== '' ? $numeroAssurance : null, $numeroAssurance === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':date_expiration_assurance', $dateExpAssurance !== '' ? $dateExpAssurance : null, $dateExpAssurance === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':capacite_moteur', $capaciteMoteur, is_null($capaciteMoteur) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':puissance_cv', $puissanceCv, is_null($puissanceCv) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':conso_urbaine', $consoUrbaine, is_null($consoUrbaine) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':conso_extra_urbaine', $consoExtra, is_null($consoExtra) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':emission_co2', $emissionCo2, is_null($emissionCo2) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':garantie_fin', $garantieFin !== '' ? $garantieFin : null, $garantieFin === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':nbr_portes', $nbrPortes, is_null($nbrPortes) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':couleur_interieur', $couleurInterieur !== '' ? $couleurInterieur : null, $couleurInterieur === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $stmt->execute();

    $_SESSION['ajout_vehicule'] = 1;
    header('Location: ../../pages/vehicules.php');
    exit;

} catch (Throwable $e) {
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/vehicules.php');
    exit;
}
