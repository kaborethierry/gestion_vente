<?php
// api/modules/depense_data.php
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // Vérification Admin
    if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    require_once __DIR__ . '/connect_db_pdo.php';

    // Paramètres DataTables
    $draw        = intval($_REQUEST['draw'] ?? 0);
    $start       = intval($_REQUEST['start'] ?? 0);
    $length      = intval($_REQUEST['length'] ?? 10);
    $searchValue = trim($_REQUEST['search']['value'] ?? '');
    
    // Filtres supplémentaires
    $filtre_categorie = trim($_REQUEST['filtre_categorie'] ?? '');
    $filtre_date_debut = trim($_REQUEST['filtre_date_debut'] ?? '');
    $filtre_date_fin = trim($_REQUEST['filtre_date_fin'] ?? '');
    $filtre_montant_min = trim($_REQUEST['filtre_montant_min'] ?? '');
    $filtre_montant_max = trim($_REQUEST['filtre_montant_max'] ?? '');
    $filtre_statut = trim($_REQUEST['filtre_statut'] ?? '');
    $filtre_date_unique = trim($_REQUEST['filtre_date_unique'] ?? '');

    // Total sans filtre
    $totalStmt = $bdd->query("SELECT COUNT(*) FROM danfaniment_depenses");
    $recordsTotal = (int) $totalStmt->fetchColumn();

    // Construction du filtre WHERE
    $where = "WHERE 1=1";
    $bindings = [];
    
    if ($searchValue !== '') {
        $where .= " AND (d.libelle LIKE :search OR d.beneficiaire LIKE :search OR d.justification LIKE :search OR d.reference LIKE :search)";
        $bindings[':search'] = "%{$searchValue}%";
    }
    
    if ($filtre_categorie !== '') {
        $where .= " AND d.categorie = :categorie";
        $bindings[':categorie'] = $filtre_categorie;
    }
    
    if ($filtre_date_unique !== '') {
        $where .= " AND d.date_depense = :date_unique";
        $bindings[':date_unique'] = $filtre_date_unique;
    } else {
        if ($filtre_date_debut !== '') {
            $where .= " AND d.date_depense >= :date_debut";
            $bindings[':date_debut'] = $filtre_date_debut;
        }
        
        if ($filtre_date_fin !== '') {
            $where .= " AND d.date_depense <= :date_fin";
            $bindings[':date_fin'] = $filtre_date_fin;
        }
    }
    
    if ($filtre_montant_min !== '') {
        $where .= " AND d.montant >= :montant_min";
        $bindings[':montant_min'] = (float)$filtre_montant_min;
    }
    
    if ($filtre_montant_max !== '') {
        $where .= " AND d.montant <= :montant_max";
        $bindings[':montant_max'] = (float)$filtre_montant_max;
    }
    
    if ($filtre_statut !== '') {
        $where .= " AND d.statut = :statut";
        $bindings[':statut'] = $filtre_statut;
    }

    // Total après filtre
    $countSql = "SELECT COUNT(*) FROM danfaniment_depenses d $where";
    $countStmt = $bdd->prepare($countSql);
    foreach ($bindings as $k => $v) {
        $countStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $recordsFiltered = (int) $countStmt->fetchColumn();

    // Tri
    $orderColIndex = intval($_REQUEST['order'][0]['column'] ?? 0);
    $orderDirInput = strtolower($_REQUEST['order'][0]['dir'] ?? 'desc');
    $orderDir      = $orderDirInput === 'asc' ? 'ASC' : 'DESC';
    $columnMap = [
        0 => 'd.id_depense',
        1 => 'd.date_depense',
        2 => 'd.reference',
        3 => 'd.libelle',
        4 => 'd.categorie',
        5 => 'd.beneficiaire',
        6 => 'd.justification',
        7 => 'd.montant',
        8 => 'd.origine',
        9 => 'u.nom_utilisateur',
        10 => 'd.statut'
    ];
    $orderBy = ($columnMap[$orderColIndex] ?? 'd.id_depense') . " $orderDir";

    // Requête de données
    $dataSql = "
        SELECT 
            d.id_depense,
            d.reference,
            d.libelle,
            d.categorie,
            d.beneficiaire,
            d.justification,
            d.montant,
            DATE_FORMAT(d.date_depense, '%d/%m/%Y') AS date_depense_formatee,
            d.date_depense AS date_depense_originale,
            d.reference_piece,
            d.origine,
            d.mode_paiement,
            d.reference_transaction,
            d.statut,
            d.created_at,
            u.nom_utilisateur AS saisi_par,
            LEFT(d.justification, 50) AS justification_courte,
            CASE 
                WHEN d.categorie = 'salaire_prestataire_couturier' THEN '💰 Salaire couturier'
                WHEN d.categorie = 'salaire_prestataire_tisseuse' THEN '🪢 Salaire tisseuse'
                WHEN d.categorie = 'salaire_prestataire_brodeur' THEN '🪡 Salaire brodeur'
                WHEN d.categorie = 'salaire_prestataire_perleuse' THEN '💎 Salaire perleuse'
                WHEN d.categorie = 'salaire_prestataire_mercerie' THEN '📿 Salaire mercerie'
                WHEN d.categorie = 'commission_prestataire_vendeuse' THEN '🛍️ Commission vendeuse'
                WHEN d.categorie = 'livraison' THEN '🚚 Livraison'
                WHEN d.categorie = 'loyer' THEN '🏠 Loyer'
                WHEN d.categorie = 'fournitures' THEN '✂️ Fournitures'
                WHEN d.categorie = 'fournisseur_tissu' THEN '🧵 Fournisseur tissu'
                WHEN d.categorie = 'charges_diverses' THEN '📋 Charges diverses'
                WHEN d.categorie = 'tontines_entreprise' THEN '🤝 Tontines entreprise'
                ELSE d.categorie
            END AS categorie_libelle,
            CASE 
                WHEN d.statut = 'valide' THEN '<span class=\"badge badge-success\">✅ Validé</span>'
                WHEN d.statut = 'en_attente' THEN '<span class=\"badge badge-warning\">⏳ En attente</span>'
                ELSE '<span class=\"badge badge-secondary\">' + d.statut + '</span>'
            END AS statut_badge,
            FORMAT(d.montant, 0) AS montant_formate
        FROM danfaniment_depenses d
        LEFT JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
        $where
        ORDER BY $orderBy
        LIMIT :start, :length
    ";
    
    $dataStmt = $bdd->prepare($dataSql);
    foreach ($bindings as $k => $v) {
        $dataStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $dataStmt->bindValue(':start', $start, PDO::PARAM_INT);
    $dataStmt->bindValue(':length', $length, PDO::PARAM_INT);
    $dataStmt->execute();

    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ajouter la valeur numérique du montant pour les calculs JS
    foreach ($rows as &$row) {
        $row['montant_valeur'] = (float)str_replace(',', '', $row['montant_formate']);
    }

    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur interne : ' . $e->getMessage()]);
}
?>