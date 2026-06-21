<?php
// Fichier : api/modules/historique_data.php

// ► Affichage des erreurs PHP (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ► Retour JSON UTF-8
header('Content-Type: application/json; charset=UTF-8');

// ► Connexion PDO
require __DIR__ . '/connect_db_pdo.php';

/*
  Requête :
  - Sélection dans l’ordre attendu par DataTables
  - Jointure avec la table `utilisateurs` (pluriel)
  - Utilisation de la colonne `nom_utilisateur`
  - Filtre sur `supprimer` insensible à la casse pour inclure 'Non', 'NON', NULL
*/

$sql = "
  SELECT
    h.id,
    h.adresse_ip,
    h.date_heure_ajout,
    u.nom_utilisateur,
    h.nom_action,
    h.nom_table,
    h.id_concerne,
    h.ancienne_valeur,
    h.nouvelle_valeur
  FROM historique_action AS h
  LEFT JOIN utilisateurs AS u
    ON h.id_utilisateur = u.id_utilisateur
  WHERE UPPER(COALESCE(h.supprimer, 'Non')) = 'NON'
  ORDER BY h.date_heure_ajout DESC
";

// ► Exécution
$stmt = $bdd->prepare($sql);
$stmt->execute();

// ► Récupération des résultats
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ► Sortie JSON pour DataTables
echo json_encode(
    ['data' => $data],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
