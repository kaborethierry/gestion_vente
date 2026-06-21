<?php
// Fichier : api/modules/modifier_vehicule.php

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

if (isset($_POST['id_vehicule'], $_POST['id_client'], $_POST['immatriculation'], $_POST['transmission'], $_POST['statut_vehicule'])) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)

        // Récupération + nettoyage (obligatoires)
        $idVehicule      = (int) $_POST['id_vehicule'];
        $idClientRaw     = trim((string) $_POST['id_client']);
        $immatriculation = trim((string) $_POST['immatriculation']);
        $transmission    = trim((string) $_POST['transmission']);
        $statutVehicule  = trim((string) $_POST['statut_vehicule']);

        // Validation basique
        $idClient = ($idClientRaw !== '' && ctype_digit($idClientRaw)) ? (int)$idClientRaw : 0;
        if ($idVehicule <= 0 || $idClient <= 0 || $immatriculation === '' || $transmission === '' || $statutVehicule === '') {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/vehicules.php');
            exit;
        }

        // Récupération + nettoyage (optionnels)
        $marque            = isset($_POST['marque']) ? trim((string) $_POST['marque']) : '';
        $modele            = isset($_POST['modele']) ? trim((string) $_POST['modele']) : '';
        $categorie         = isset($_POST['categorie']) ? trim((string) $_POST['categorie']) : '';
        $typeMoteur        = isset($_POST['type_moteur']) ? trim((string) $_POST['type_moteur']) : '';
        $anneeRaw          = isset($_POST['annee']) ? trim((string) $_POST['annee']) : '';
        $kilometrageRaw    = isset($_POST['kilometrage']) ? trim((string) $_POST['kilometrage']) : '';
        $couleur           = isset($_POST['couleur']) ? trim((string) $_POST['couleur']) : '';
        $vin               = isset($_POST['vin']) ? trim((string) $_POST['vin']) : '';
        $nbrPortesRaw      = isset($_POST['nbr_portes']) ? trim((string) $_POST['nbr_portes']) : '';

        $consoUrbaineRaw   = isset($_POST['conso_urbaine']) ? trim((string) $_POST['conso_urbaine']) : '';
        $consoExtraRaw     = isset($_POST['conso_extra_urbaine']) ? trim((string) $_POST['conso_extra_urbaine']) : '';
        $emissionCo2Raw    = isset($_POST['emission_co2']) ? trim((string) $_POST['emission_co2']) : '';
        $capaciteMoteurRaw = isset($_POST['capacite_moteur']) ? trim((string) $_POST['capacite_moteur']) : '';
        $puissanceCvRaw    = isset($_POST['puissance_cv']) ? trim((string) $_POST['puissance_cv']) : '';

        $dateImmat         = isset($_POST['date_immatriculation']) ? trim((string) $_POST['date_immatriculation']) : '';
        $dateDernEntRaw    = isset($_POST['date_derniere_entretien']) ? trim((string) $_POST['date_derniere_entretien']) : '';
        $kmDernEntRaw      = isset($_POST['kilometrage_derniere_entretien']) ? trim((string) $_POST['kilometrage_derniere_entretien']) : '';
        $dateProchEntRaw   = isset($_POST['date_prochain_entretien']) ? trim((string) $_POST['date_prochain_entretien']) : '';
        $garantieFin       = isset($_POST['garantie_fin']) ? trim((string) $_POST['garantie_fin']) : '';

        $typeAssurance     = isset($_POST['type_assurance']) ? trim((string) $_POST['type_assurance']) : '';
        $numeroAssurance   = isset($_POST['numero_assurance']) ? trim((string) $_POST['numero_assurance']) : '';
        $dateExpAssurance  = isset($_POST['date_expiration_assurance']) ? trim((string) $_POST['date_expiration_assurance']) : '';
        $couleurInterieur  = isset($_POST['couleur_interieur']) ? trim((string) $_POST['couleur_interieur']) : '';

        // Normalisation numériques
        $annee       = ($anneeRaw !== '' && ctype_digit($anneeRaw)) ? (int)$anneeRaw : null;
        $kilometrage = ($kilometrageRaw !== '' && ctype_digit($kilometrageRaw)) ? (int)$kilometrageRaw : null;
        $nbrPortes   = ($nbrPortesRaw !== '' && ctype_digit($nbrPortesRaw)) ? (int)$nbrPortesRaw : null;
        $kmDernEnt   = ($kmDernEntRaw !== '' && ctype_digit($kmDernEntRaw)) ? (int)$kmDernEntRaw : null;
        $puissanceCv = ($puissanceCvRaw !== '' && ctype_digit($puissanceCvRaw)) ? (int)$puissanceCvRaw : null;
        $emissionCo2 = ($emissionCo2Raw !== '' && ctype_digit($emissionCo2Raw)) ? (int)$emissionCo2Raw : null;

        // Décimaux: remplacer virgule par point
        $consoUrbaine = ($consoUrbaineRaw !== '') ? str_replace(',', '.', $consoUrbaineRaw) : null;
        if (!is_null($consoUrbaine) && !is_numeric($consoUrbaine)) $consoUrbaine = null;

        $consoExtra = ($consoExtraRaw !== '') ? str_replace(',', '.', $consoExtraRaw) : null;
        if (!is_null($consoExtra) && !is_numeric($consoExtra)) $consoExtra = null;

        $capaciteMoteur = ($capaciteMoteurRaw !== '') ? str_replace(',', '.', $capaciteMoteurRaw) : null;
        if (!is_null($capaciteMoteur) && !is_numeric($capaciteMoteur)) $capaciteMoteur = null;

        // Validation enums
        $transmissionAllowed = ['Manuelle', 'Automatique', 'Séquentielle'];
        if (!in_array($transmission, $transmissionAllowed, true)) {
            $transmission = 'Manuelle';
        }
        $statutAllowed = ['En service', 'En réparation', 'Hors service'];
        if (!in_array($statutVehicule, $statutAllowed, true)) {
            $statutVehicule = 'En service';
        }

        // Existence client (non supprimé)
        $stmt = $bdd->prepare("SELECT COUNT(*) FROM clients WHERE supprimer = 'Non' AND id_client = :idc");
        $stmt->execute([':idc' => $idClient]);
        if ((int)$stmt->fetchColumn() === 0) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/vehicules.php');
            exit;
        }

        // Doublon immatriculation (hors véhicule courant)
        $stmt = $bdd->prepare("
            SELECT COUNT(*) FROM vehicules 
            WHERE supprimer = 'Non' AND immatriculation = :immat AND id_vehicule <> :idv
        ");
        $stmt->execute([':immat' => $immatriculation, ':idv' => $idVehicule]);
        if ((int)$stmt->fetchColumn() > 0) {
            $_SESSION['ref_exist'] = 1;
            header('Location: ../../pages/vehicules.php');
            exit;
        }

        // Mise à jour
        $sql = "UPDATE vehicules
                   SET id_client = :id_client,
                       immatriculation = :immatriculation,
                       marque = :marque,
                       modele = :modele,
                       categorie = :categorie,
                       type_moteur = :type_moteur,
                       annee = :annee,
                       kilometrage = :kilometrage,
                       couleur = :couleur,
                       vin = :vin,
                       transmission = :transmission,
                       nbr_portes = :nbr_portes,
                       conso_urbaine = :conso_urbaine,
                       conso_extra_urbaine = :conso_extra_urbaine,
                       emission_co2 = :emission_co2,
                       capacite_moteur = :capacite_moteur,
                       puissance_cv = :puissance_cv,
                       date_immatriculation = :date_immatriculation,
                       date_derniere_entretien = :date_derniere_entretien,
                       kilometrage_derniere_entretien = :kilometrage_derniere_entretien,
                       date_prochain_entretien = :date_prochain_entretien,
                       type_assurance = :type_assurance,
                       numero_assurance = :numero_assurance,
                       date_expiration_assurance = :date_expiration_assurance,
                       garantie_fin = :garantie_fin,
                       couleur_interieur = :couleur_interieur,
                       statut_vehicule = :statut_vehicule
                 WHERE id_vehicule = :id_vehicule";

        $stmt = $bdd->prepare($sql);

        $stmt->bindValue(':id_client', $idClient, PDO::PARAM_INT);
        $stmt->bindValue(':immatriculation', $immatriculation, PDO::PARAM_STR);
        $stmt->bindValue(':marque', $marque !== '' ? $marque : null, $marque === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':modele', $modele !== '' ? $modele : null, $modele === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':categorie', $categorie !== '' ? $categorie : null, $categorie === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':type_moteur', $typeMoteur !== '' ? $typeMoteur : null, $typeMoteur === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':annee', $annee, is_null($annee) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':kilometrage', $kilometrage, is_null($kilometrage) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':couleur', $couleur !== '' ? $couleur : null, $couleur === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':vin', $vin !== '' ? $vin : null, $vin === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':transmission', $transmission, PDO::PARAM_STR);
        $stmt->bindValue(':nbr_portes', $nbrPortes, is_null($nbrPortes) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':conso_urbaine', $consoUrbaine, is_null($consoUrbaine) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':conso_extra_urbaine', $consoExtra, is_null($consoExtra) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':emission_co2', $emissionCo2, is_null($emissionCo2) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':capacite_moteur', $capaciteMoteur, is_null($capaciteMoteur) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':puissance_cv', $puissanceCv, is_null($puissanceCv) ? PDO::PARAM_NULL : PDO::PARAM_INT);

        $stmt->bindValue(':date_immatriculation', $dateImmat !== '' ? $dateImmat : null, $dateImmat === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':date_derniere_entretien', $dateDernEntRaw !== '' ? $dateDernEntRaw : null, $dateDernEntRaw === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':kilometrage_derniere_entretien', $kmDernEnt, is_null($kmDernEnt) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':date_prochain_entretien', $dateProchEntRaw !== '' ? $dateProchEntRaw : null, $dateProchEntRaw === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':type_assurance', $typeAssurance !== '' ? $typeAssurance : null, $typeAssurance === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':numero_assurance', $numeroAssurance !== '' ? $numeroAssurance : null, $numeroAssurance === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':date_expiration_assurance', $dateExpAssurance !== '' ? $dateExpAssurance : null, $dateExpAssurance === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':garantie_fin', $garantieFin !== '' ? $garantieFin : null, $garantieFin === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->bindValue(':couleur_interieur', $couleurInterieur !== '' ? $couleurInterieur : null, $couleurInterieur === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':statut_vehicule', $statutVehicule, PDO::PARAM_STR);

        $stmt->bindValue(':id_vehicule', $idVehicule, PDO::PARAM_INT);

        $stmt->execute();

        $_SESSION['mod_vehicule'] = 1;
        header('Location: ../../pages/vehicules.php');
        exit;

    } catch (Throwable $e) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/vehicules.php');
        exit;
    }
} else {
    header('Location: ../../pages/vehicules.php');
    exit;
}
