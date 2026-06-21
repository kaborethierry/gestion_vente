<?php
// Fichier : api/modules/historique_data.php

// ✅ Afficher toutes les erreurs PHP (utile en développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Retour JSON UTF-8
header('Content-Type: application/json; charset=UTF-8');

// ✅ Connexion PDO
require __DIR__ . '/connect_db_pdo.php';

/*
  Sélection dans l’ordre attendu par DataTables :
  id, adresse_ip, date_heure_ajout, nom_utilisateur, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur
  On joint la table `utilisateurs` (ou `utilisateur` selon ton schéma) pour récupérer le nom.
*/

$sql = "
  SELECT
    h.id,
    h.adresse_ip,
    h.date_heure_ajout,
    u.nom_utilisateur AS nom_utilisateur, -- adapte si ta colonne s'appelle différemment
    h.nom_action,
    h.nom_table,
    h.id_concerne,
    h.ancienne_valeur,
    h.nouvelle_valeur
  FROM historique_action AS h
  LEFT JOIN utilisateurs AS u
    ON h.id_utilisateur = u.id_utilisateur
  WHERE h.supprimer = 'Non'
  ORDER BY h.date_heure_ajout DESC
";

// ✅ Exécution
$stmt = $bdd->prepare($sql);
$stmt->execute();

// ✅ Récupération des lignes
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Sortie JSON pour DataTables
echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
