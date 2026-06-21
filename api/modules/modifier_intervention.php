<?php
// Fichier : api/modules/modifier_intervention.php

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
    header('Location: ../../pages/interventions.php');
    exit;
}

if (
    isset($_POST['id_intervention'], $_POST['id_vehicule'], $_POST['id_employe'], $_POST['type_intervention'], $_POST['statut'])
) {
    try {
        require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)
        if (method_exists($bdd, 'setAttribute')) {
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        // Récupération + nettoyage
        $idInterv    = (int)($_POST['id_intervention'] ?? 0);
        $idVehRaw    = trim((string)($_POST['id_vehicule'] ?? ''));
        $idEmpRaw    = trim((string)($_POST['id_employe'] ?? ''));
        $type        = trim((string)($_POST['type_intervention'] ?? ''));
        $statut      = trim((string)($_POST['statut'] ?? ''));
        $priorite    = trim((string)($_POST['priorite'] ?? ''));

        $dateDebutRaw = trim((string)($_POST['date_debut'] ?? '')); // input datetime-local
        $dateFinRaw   = trim((string)($_POST['date_fin'] ?? ''));

        $kilomRaw    = trim((string)($_POST['kilometrage'] ?? ''));
        $tempsEstRaw = trim((string)($_POST['temps_estime'] ?? ''));
        $tempsReelRaw= trim((string)($_POST['temps_reel'] ?? ''));
        $moHtRaw     = trim((string)($_POST['main_oeuvre_ht'] ?? ''));

        $description = isset($_POST['description']) ? trim((string)$_POST['description']) : '';
        $remarques   = isset($_POST['remarques'])   ? trim((string)$_POST['remarques'])   : '';

        // Validations minimales
        $idVehicule = ($idVehRaw !== '' && ctype_digit($idVehRaw)) ? (int)$idVehRaw : null;
        $idEmploye  = ($idEmpRaw !== '' && ctype_digit($idEmpRaw)) ? (int)$idEmpRaw : null;

        if ($idInterv <= 0 || is_null($idVehicule) || is_null($idEmploye) || $type === '' || $statut === '') {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/interventions.php');
            exit;
        }

        // Récupération de l'ancienne valeur pour l'historique
        $oldStmt = $bdd->prepare("
            SELECT id_intervention, id_vehicule, id_employe, type_intervention, date_debut, date_fin, kilometrage, statut, priorite, temps_estime, temps_reel, main_oeuvre_ht, description, remarques
            FROM interventions
            WHERE id_intervention = :id AND supprimer = 'Non'
            LIMIT 1
        ");
        $oldStmt->execute([':id' => $idInterv]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);
        if (!$oldRow) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/interventions.php');
            exit;
        }

        // Normalisation dates: 'YYYY-MM-DDTHH:mm' -> 'YYYY-MM-DD HH:mm:ss'
        $normalizeDate = static function (?string $raw): ?string {
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

        $dateDebut = $normalizeDate($dateDebutRaw);
        $dateFin   = $normalizeDate($dateFinRaw);

        // Numériques: '' -> NULL
        $kilometrage = ($kilomRaw !== '' && ctype_digit($kilomRaw)) ? (int)$kilomRaw : null;

        $toDecimal = static function (?string $raw): ?string {
            if ($raw === null) return null;
            $s = str_replace(',', '.', trim($raw));
            if ($s === '') return null;
            if (!preg_match('/^\d+(?:\.\d+)?$/', $s)) return null;
            return $s;
        };
        $tempsEstime  = $toDecimal($tempsEstRaw);
        $tempsReel    = $toDecimal($tempsReelRaw);
        $mainOeuvreHt = $toDecimal($moHtRaw);

        if ($priorite === '') $priorite = 'Normale';

        // Intégrité référentielle (existe et non supprimé)
        $stmt = $bdd->prepare("SELECT 1 FROM vehicules WHERE id_vehicule = :id AND supprimer = 'Non' LIMIT 1");
        $stmt->execute([':id' => $idVehicule]);
        $vehOk = (bool)$stmt->fetchColumn();

        $stmt2 = $bdd->prepare("SELECT 1 FROM employes WHERE id_employe = :id AND supprimer = 'Non' LIMIT 1");
        $stmt2->execute([':id' => $idEmploye]);
        $empOk = (bool)$stmt2->fetchColumn();

        if (!$vehOk || !$empOk) {
            $_SESSION['imp'] = 1;
            header('Location: ../../pages/interventions.php');
            exit;
        }

        // Mise à jour
        $sql = "UPDATE interventions SET
                    id_vehicule       = :id_vehicule,
                    id_employe        = :id_employe,
                    type_intervention = :type_intervention,
                    date_debut        = :date_debut,
                    date_fin          = :date_fin,
                    kilometrage       = :kilometrage,
                    statut            = :statut,
                    priorite          = :priorite,
                    temps_estime      = :temps_estime,
                    temps_reel        = :temps_reel,
                    main_oeuvre_ht    = :main_oeuvre_ht,
                    description       = :description,
                    remarques         = :remarques
                WHERE id_intervention = :id_intervention
                  AND supprimer = 'Non'";

        $upd = $bdd->prepare($sql);
        $upd->bindValue(':id_vehicule',        $idVehicule,   PDO::PARAM_INT);
        $upd->bindValue(':id_employe',         $idEmploye,    PDO::PARAM_INT);
        $upd->bindValue(':type_intervention',  $type,         PDO::PARAM_STR);
        $upd->bindValue(':date_debut',         $dateDebut,    is_null($dateDebut) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $upd->bindValue(':date_fin',           $dateFin,      is_null($dateFin) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $upd->bindValue(':kilometrage',        $kilometrage,  is_null($kilometrage) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $upd->bindValue(':statut',             $statut,       PDO::PARAM_STR);
        $upd->bindValue(':priorite',           $priorite,     PDO::PARAM_STR);
        $upd->bindValue(':temps_estime',       $tempsEstime,  is_null($tempsEstime) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $upd->bindValue(':temps_reel',         $tempsReel,    is_null($tempsReel) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $upd->bindValue(':main_oeuvre_ht',     $mainOeuvreHt, is_null($mainOeuvreHt) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $upd->bindValue(':description',        $description !== '' ? $description : null, $description !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $upd->bindValue(':remarques',          $remarques   !== '' ? $remarques   : null, $remarques   !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $upd->bindValue(':id_intervention',    $idInterv,     PDO::PARAM_INT);

        $upd->execute();

        // Historique action (avant / après)
        $ancienne = json_encode([
            'id_intervention' => (int)$oldRow['id_intervention'],
            'id_vehicule'     => (int)$oldRow['id_vehicule'],
            'id_employe'      => (int)$oldRow['id_employe'],
            'type_intervention'=> $oldRow['type_intervention'],
            'date_debut'      => $oldRow['date_debut'],
            'date_fin'        => $oldRow['date_fin'],
            'kilometrage'     => is_null($oldRow['kilometrage']) ? null : (int)$oldRow['kilometrage'],
            'statut'          => $oldRow['statut'],
            'priorite'        => $oldRow['priorite'],
            'temps_estime'    => is_null($oldRow['temps_estime']) ? null : (float)$oldRow['temps_estime'],
            'temps_reel'      => is_null($oldRow['temps_reel']) ? null : (float)$oldRow['temps_reel'],
            'main_oeuvre_ht'  => is_null($oldRow['main_oeuvre_ht']) ? null : (float)$oldRow['main_oeuvre_ht'],
            'description'     => $oldRow['description'],
            'remarques'       => $oldRow['remarques'],
        ], JSON_UNESCAPED_UNICODE);

        $nouvelle = json_encode([
            'id_intervention' => $idInterv,
            'id_vehicule'     => $idVehicule,
            'id_employe'      => $idEmploye,
            'type_intervention'=> $type,
            'date_debut'      => $dateDebut,
            'date_fin'        => $dateFin,
            'kilometrage'     => $kilometrage,
            'statut'          => $statut,
            'priorite'        => $priorite,
            'temps_estime'    => is_null($tempsEstime) ? null : (float)$tempsEstime,
            'temps_reel'      => is_null($tempsReel) ? null : (float)$tempsReel,
            'main_oeuvre_ht'  => is_null($mainOeuvreHt) ? null : (float)$mainOeuvreHt,
            'description'     => ($description !== '' ? $description : null),
            'remarques'       => ($remarques   !== '' ? $remarques   : null),
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
            ':action' => 'Modification intervention',
            ':table'  => 'interventions',
            ':idc'    => $idInterv,
            ':old'    => $ancienne,
            ':new'    => $nouvelle
        ]);

        $_SESSION['mod_intervention'] = 1;
        header('Location: ../../pages/interventions.php');
        exit;

    } catch (Throwable $e) {
        // error_log('modifier_intervention: ' . $e->getMessage());
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/interventions.php');
        exit;
    }
} else {
    header('Location: ../../pages/interventions.php');
    exit;
}
