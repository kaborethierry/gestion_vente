<?php
// api/modules/modifier_facture.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
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

$id_facture = (int)$_POST['id_facture'];
$id_client = !empty($_POST['id_client']) ? (int)$_POST['id_client'] : null;
$statut = $_POST['statut'] ?? 'brouillon';
$date_echeance = !empty($_POST['date_echeance']) ? $_POST['date_echeance'] : null;
$taux_tva = (float)($_POST['taux_tva'] ?? 0);
$notes = trim($_POST['notes'] ?? '');
$lignes = $_POST['lignes'] ?? [];

if (empty($lignes)) {
    $_SESSION['err_facture'] = 1;
    header('Location: ../../pages/facture.php');
    exit;
}

try {
    $bdd->beginTransaction();
    
    // Vérifier que la facture n'est pas payée
    $stmt = $bdd->prepare("SELECT statut FROM danfaniment_factures WHERE id_facture = :id");
    $stmt->execute([':id' => $id_facture]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($facture['statut'] === 'payee') {
        throw new Exception("Impossible de modifier une facture payée");
    }
    
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
    
    // Mise à jour de la facture
    $stmt = $bdd->prepare("
        UPDATE danfaniment_factures 
        SET id_client = :id_client,
            statut = :statut,
            date_echeance = :date_echeance,
            sous_total = :sous_total,
            remise_montant = :remise_montant,
            total_ht = :total_ht,
            taux_tva = :taux_tva,
            montant_tva = :montant_tva,
            total_ttc = :total_ttc,
            notes = :notes,
            updated_at = NOW()
        WHERE id_facture = :id_facture
    ");
    $stmt->execute([
        ':id_client' => $id_client,
        ':statut' => $statut,
        ':date_echeance' => $date_echeance,
        ':sous_total' => $sous_total,
        ':remise_montant' => $remise_totale,
        ':total_ht' => $total_ht,
        ':taux_tva' => $taux_tva,
        ':montant_tva' => $montant_tva,
        ':total_ttc' => $total_ttc,
        ':notes' => $notes,
        ':id_facture' => $id_facture
    ]);
    
    // Supprimer les anciennes lignes
    $stmt = $bdd->prepare("DELETE FROM danfaniment_facture_lignes WHERE id_facture = :id");
    $stmt->execute([':id' => $id_facture]);
    
    // Insérer les nouvelles lignes
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
    
    // Si le statut passe à "payee", enregistrer la date de paiement
    if ($statut === 'payee' && $facture['statut'] !== 'payee') {
        $stmt = $bdd->prepare("UPDATE danfaniment_factures SET date_paiement = NOW() WHERE id_facture = :id");
        $stmt->execute([':id' => $id_facture]);
    }
    
    $bdd->commit();
    $_SESSION['facture_modifiee'] = 1;
    header('Location: ../../pages/facture.php');
    exit;

} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur modification facture: " . $e->getMessage());
    $_SESSION['err_facture'] = 1;
    header('Location: ../../pages/facture.php');
    exit;
}
?>