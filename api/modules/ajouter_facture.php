<?php
// api/modules/ajouter_facture.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin' && ($_SESSION['role'] ?? '') !== 'caissier') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/facture.php');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_client = !empty($_POST['id_client']) ? (int)$_POST['id_client'] : null;
$date_echeance = !empty($_POST['date_echeance']) ? $_POST['date_echeance'] : null;
$reference = trim($_POST['reference'] ?? '');
$taux_tva = (float)($_POST['taux_tva'] ?? 0);
$notes = trim($_POST['notes'] ?? '');
$conditions_reglement = trim($_POST['conditions_reglement'] ?? '');
$lignes = $_POST['lignes'] ?? [];

if (empty($lignes)) {
    $_SESSION['err_facture'] = 1;
    header('Location: ../../pages/facture.php');
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Génération du numéro de facture
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_factures WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
    $stmt->execute();
    $count = (int)$stmt->fetchColumn();
    $numero_facture = 'FAC-' . date('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    
    // Calcul des totaux
    $sous_total = 0;
    $remise_totale = 0;
    
    foreach ($lignes as $ligne) {
        $quantite = (float)($ligne['quantite'] ?? 1);
        $prix = (float)($ligne['prix_unitaire_ht'] ?? 0);
        $remise_ligne = (float)($ligne['remise_ligne'] ?? 0);
        $sous_total += $quantite * $prix;
        $remise_totale += $remise_ligne;
    }
    
    $total_ht = $sous_total - $remise_totale;
    $montant_tva = $total_ht * ($taux_tva / 100);
    $total_ttc = $total_ht + $montant_tva;
    
    // Insertion de la facture
    $stmt = $bdd->prepare("
        INSERT INTO danfaniment_factures 
        (numero_facture, id_client, id_utilisateur, date_echeance, reference,
         sous_total, remise_montant, total_ht, taux_tva, montant_tva, total_ttc,
         notes, conditions_reglement, statut, created_at)
        VALUES 
        (:numero, :id_client, :id_user, :date_echeance, :reference,
         :sous_total, :remise_montant, :total_ht, :taux_tva, :montant_tva, :total_ttc,
         :notes, :conditions, 'brouillon', NOW())
    ");
    $stmt->execute([
        ':numero' => $numero_facture,
        ':id_client' => $id_client,
        ':id_user' => $_SESSION['id'],
        ':date_echeance' => $date_echeance,
        ':reference' => $reference,
        ':sous_total' => $sous_total,
        ':remise_montant' => $remise_totale,
        ':total_ht' => $total_ht,
        ':taux_tva' => $taux_tva,
        ':montant_tva' => $montant_tva,
        ':total_ttc' => $total_ttc,
        ':notes' => $notes,
        ':conditions' => $conditions_reglement
    ]);
    
    $id_facture = $bdd->lastInsertId();
    
    // Insertion des lignes
    foreach ($lignes as $ligne) {
        $designation = trim($ligne['designation']);
        $description = trim($ligne['description'] ?? '');
        $quantite = (float)($ligne['quantite'] ?? 1);
        $prix = (float)($ligne['prix_unitaire_ht'] ?? 0);
        $remise_ligne = (float)($ligne['remise_ligne'] ?? 0);
        
        $total_ht_ligne = ($quantite * $prix) - $remise_ligne;
        if ($total_ht_ligne < 0) $total_ht_ligne = 0;
        
        $tva_ligne = $total_ht_ligne * ($taux_tva / 100);
        $total_ttc_ligne = $total_ht_ligne + $tva_ligne;
        
        $stmt = $bdd->prepare("
            INSERT INTO danfaniment_facture_lignes 
            (id_facture, designation, description, quantite, prix_unitaire_ht, remise_ligne, total_ht, taux_tva, total_ttc)
            VALUES 
            (:id_facture, :designation, :description, :quantite, :prix, :remise, :total_ht, :taux_tva, :total_ttc)
        ");
        $stmt->execute([
            ':id_facture' => $id_facture,
            ':designation' => $designation,
            ':description' => $description,
            ':quantite' => $quantite,
            ':prix' => $prix,
            ':remise' => $remise_ligne,
            ':total_ht' => $total_ht_ligne,
            ':taux_tva' => $taux_tva,
            ':total_ttc' => $total_ttc_ligne
        ]);
    }
    
    $bdd->commit();
    $_SESSION['facture_ajoutee'] = 1;
    header('Location: ../../pages/facture.php');
    exit;

} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur ajout facture: " . $e->getMessage());
    $_SESSION['err_facture'] = 1;
    header('Location: ../../pages/facture.php');
    exit;
}
?>