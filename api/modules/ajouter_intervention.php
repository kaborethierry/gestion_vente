<?php
// Fichier : api/modules/ajouter_intervention.php

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

try {
    require_once __DIR__ . '/connect_db_pdo.php'; // Doit définir $bdd (PDO)
    // Active les erreurs PDO pour remonter proprement dans le catch
    if (method_exists($bdd, 'setAttribute')) {
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Récupération + nettoyage des données
    $idVehRaw     = trim($_POST['id_vehicule'] ?? '');
    $idEmpRaw     = trim($_POST['id_employe'] ?? '');
    $type         = isset($_POST['type_intervention']) ? trim($_POST['type_intervention']) : '';
    $statut       = isset($_POST['statut']) ? trim($_POST['statut']) : '';
    $priorite     = isset($_POST['priorite']) ? trim($_POST['priorite']) : '';
    $dateDebutRaw = trim($_POST['date_debut'] ?? '');
    $dateFinRaw   = trim($_POST['date_fin'] ?? '');
    $kilomRaw     = trim($_POST['kilometrage'] ?? '');
    $tempsEstRaw  = trim($_POST['temps_estime'] ?? '');
    $tempsReelRaw = trim($_POST['temps_reel'] ?? '');
    $moHtRaw      = trim($_POST['main_oeuvre_ht'] ?? '');
    $description  = isset($_POST['description']) ? trim($_POST['description']) : '';
    $remarques    = isset($_POST['remarques']) ? trim($_POST['remarques']) : '';

    // Validation champs obligatoires minimaux (schéma: id_employe NOT NULL, type_intervention ENUM, statut ENUM)
    $idVehicule = ($idVehRaw !== '' && ctype_digit($idVehRaw)) ? (int)$idVehRaw : null;
    $idEmploye  = ($idEmpRaw !== '' && ctype_digit($idEmpRaw)) ? (int)$idEmpRaw : null;

    // Règles: vehicule, employe, type, statut sont obligatoires côté serveur (alignés avec schéma)
    if (is_null($idVehicule) || is_null($idEmploye) || $type === '' || $statut === '') {
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

    // IMPORTANT: schéma: date_debut NOT NULL DEFAULT current_timestamp()
    // Ne jamais binder NULL si vide; laisse MySQL appliquer le DEFAULT.
    $sendDateDebut = $dateDebut !== null; // si false, on ne met pas la colonne dans l'INSERT

    // Numériques ('' -> NULL)
    $kilometrage = ($kilomRaw !== '' && ctype_digit($kilomRaw)) ? (int)$kilomRaw : null;

    $toDecimal = static function (?string $raw): ?string {
        if ($raw === null) return null;
        $s = str_replace(',', '.', trim($raw));
        if ($s === '') return null;
        if (!preg_match('/^\d+(?:\.\d+)?$/', $s)) return null;
        return $s;
    };
    $tempsEstime   = $toDecimal($tempsEstRaw);
    $tempsReel     = $toDecimal($tempsReelRaw);
    $mainOeuvreHt  = $toDecimal($moHtRaw);

    // Valeurs métiers par défaut (cohérentes avec l'ENUM)
    if ($priorite === '') $priorite = 'Normale';

    // Intégrité référentielle
    $stmt = $bdd->prepare("SELECT 1 FROM vehicules WHERE id_vehicule = :id AND supprimer = 'Non' LIMIT 1");
    $stmt->execute([':id' => $idVehicule]);
    if (!(bool)$stmt->fetchColumn()) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/interventions.php');
        exit;
    }

    $stmt2 = $bdd->prepare("SELECT 1 FROM employes WHERE id_employe = :id AND supprimer = 'Non' LIMIT 1");
    $stmt2->execute([':id' => $idEmploye]);
    if (!(bool)$stmt2->fetchColumn()) {
        $_SESSION['imp'] = 1;
        header('Location: ../../pages/interventions.php');
        exit;
    }

    // Construction de la requête INSERT en respectant les contraintes (date_debut)
    if ($sendDateDebut) {
        $sql = "INSERT INTO interventions
                (id_vehicule, id_employe, type_intervention, date_debut, date_fin, kilometrage, statut, priorite, temps_estime, temps_reel, main_oeuvre_ht, description, remarques, supprimer)
                VALUES
                (:id_vehicule, :id_employe, :type_intervention, :date_debut, :date_fin, :kilometrage, :statut, :priorite, :temps_estime, :temps_reel, :main_oeuvre_ht, :description, :remarques, 'Non')";
    } else {
        // On omet date_debut pour laisser le DEFAULT CURRENT_TIMESTAMP() s'appliquer
        $sql = "INSERT INTO interventions
                (id_vehicule, id_employe, type_intervention, date_fin, kilometrage, statut, priorite, temps_estime, temps_reel, main_oeuvre_ht, description, remarques, supprimer)
                VALUES
                (:id_vehicule, :id_employe, :type_intervention, :date_fin, :kilometrage, :statut, :priorite, :temps_estime, :temps_reel, :main_oeuvre_ht, :description, :remarques, 'Non')";
    }

    $ins = $bdd->prepare($sql);

    // Bind communs
    $ins->bindValue(':id_vehicule',       $idVehicule,   PDO::PARAM_INT);
    $ins->bindValue(':id_employe',        $idEmploye,    PDO::PARAM_INT);
    $ins->bindValue(':type_intervention', $type,         PDO::PARAM_STR);
    if ($sendDateDebut) {
        $ins->bindValue(':date_debut',    $dateDebut,    PDO::PARAM_STR);
    }
    $ins->bindValue(':date_fin',          $dateFin,      is_null($dateFin) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $ins->bindValue(':kilometrage',       $kilometrage,  is_null($kilometrage) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $ins->bindValue(':statut',            $statut,       PDO::PARAM_STR);
    $ins->bindValue(':priorite',          $priorite,     PDO::PARAM_STR);
    $ins->bindValue(':temps_estime',      $tempsEstime,  is_null($tempsEstime) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $ins->bindValue(':temps_reel',        $tempsReel,    is_null($tempsReel) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $ins->bindValue(':main_oeuvre_ht',    $mainOeuvreHt, is_null($mainOeuvreHt) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $ins->bindValue(':description',       $description !== '' ? $description : null, $description !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $ins->bindValue(':remarques',         $remarques   !== '' ? $remarques   : null, $remarques   !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);

    $ins->execute();

    // Historique action
    $id_new = (int)$bdd->lastInsertId();
    $nouvelle = json_encode([
        'id_intervention' => $id_new,
        'id_vehicule'     => $idVehicule,
        'id_employe'      => $idEmploye,
        'type_intervention'=> $type,
        'date_debut'      => $sendDateDebut ? $dateDebut : null,
        'date_fin'        => $dateFin,
        'kilometrage'     => $kilometrage,
        'statut'          => $statut,
        'priorite'        => $priorite,
        'temps_estime'    => is_null($tempsEstime) ? null : (float)$tempsEstime,
        'temps_reel'      => is_null($tempsReel) ? null : (float)$tempsReel,
        'main_oeuvre_ht'  => is_null($mainOeuvreHt) ? null : (float)$mainOeuvreHt,
        'description'     => ($description !== '' ? $description : null),
        'remarques'       => ($remarques   !== '' ? $remarques   : null)
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
        ':action' => 'Ajout intervention',
        ':table'  => 'interventions',
        ':idc'    => $id_new,
        ':old'    => null,
        ':new'    => $nouvelle
    ]);

    $_SESSION['ajout_intervention'] = 1;
    header('Location: ../../pages/interventions.php');
    exit;

} catch (Throwable $e) {
    // Journalisation technique (optionnelle)
    // error_log('ajouter_intervention: ' . $e->getMessage());
    $_SESSION['imp'] = 1;
    header('Location: ../../pages/interventions.php');
    exit;
}
