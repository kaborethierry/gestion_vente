<?php
// api/modules/enregistrer_production_prestataire.php
session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/prestataires.php');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_prestataire = (int)($_POST['id_prestataire'] ?? 0);
$type_prestataire = $_POST['type_prestataire'] ?? '';
$quantite = (float)($_POST['quantite'] ?? 1);
$semaine_debut = $_POST['semaine_debut'] ?? date('Y-m-d');
$semaine_fin = $_POST['semaine_fin'] ?? date('Y-m-d');
$remarques = trim($_POST['remarques'] ?? '');

if ($id_prestataire <= 0 || empty($type_prestataire)) {
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}

try {
    // Récupérer les tarifs du prestataire
    $stmt = $bdd->prepare("
        SELECT tarif_par_tenue, tarif_par_pagne, taux_horaire, commission_pourcentage 
        FROM danfaniment_prestataires 
        WHERE id_prestataire = :id
    ");
    $stmt->execute([':id' => $id_prestataire]);
    $prestataire = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $montant_unitaire = 0;
    $type_production = '';
    $id_commande = null;
    
    switch($type_prestataire) {
        case 'couturier':
            $id_commande = (int)($_POST['id_commande'] ?? 0);
            if ($id_commande <= 0) {
                throw new Exception("Commande requise");
            }
            $type_production = 'tenue';
            $montant_unitaire = $prestataire['tarif_par_tenue'] ?? 0;
            
            // Mettre à jour la commande
            $stmt = $bdd->prepare("UPDATE danfaniment_commandes SET status = 'livrée' WHERE id_commande = :id");
            $stmt->execute([':id' => $id_commande]);
            break;
            
        case 'tisseuse':
            $type_production = 'pagne';
            $montant_unitaire = $prestataire['tarif_par_pagne'] ?? 0;
            break;
            
        case 'brodeur':
        case 'perleuse':
        case 'mercerie':
            $type_production = 'heure';
            $montant_unitaire = $prestataire['taux_horaire'] ?? 0;
            break;
            
        case 'vendeuse':
            $type_production = 'commission';
            $ca_genere = (float)($_POST['ca_genere'] ?? 0);
            $taux_commission = (float)($_POST['taux_commission'] ?? $prestataire['commission_pourcentage']);
            $montant_unitaire = ($ca_genere * $taux_commission) / 100;
            $remarques .= " CA: " . number_format($ca_genere, 0, ',', ' ') . " FCFA, Taux: " . $taux_commission . "%";
            
            // Mettre à jour le CA du prestataire
            $stmt = $bdd->prepare("
                UPDATE danfaniment_prestataires 
                SET ca_genere = ca_genere + :ca 
                WHERE id_prestataire = :id
            ");
            $stmt->execute([':ca' => $ca_genere, ':id' => $id_prestataire]);
            break;
    }
    
    // Insérer la production
    $sql = "INSERT INTO danfaniment_productions_prestataires 
            (id_prestataire, id_commande, type_production, quantite, semaine_debut, semaine_fin, montant_unitaire, statut_paiement, remarques)
            VALUES 
            (:id_prestataire, :id_commande, :type_production, :quantite, :semaine_debut, :semaine_fin, :montant_unitaire, 'en_attente', :remarques)";
    
    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':id_prestataire' => $id_prestataire,
        ':id_commande' => $id_commande ?: null,
        ':type_production' => $type_production,
        ':quantite' => $quantite,
        ':semaine_debut' => $semaine_debut,
        ':semaine_fin' => $semaine_fin,
        ':montant_unitaire' => $montant_unitaire,
        ':remarques' => $remarques
    ]);
    
    // Mettre à jour les totaux du prestataire
    $total_a_payer = $montant_unitaire * $quantite;
    $stmt = $bdd->prepare("
        UPDATE danfaniment_prestataires 
        SET total_a_payer = total_a_payer + :total,
            total_productions = total_productions + :quantite
        WHERE id_prestataire = :id
    ");
    $stmt->execute([
        ':total' => $total_a_payer,
        ':quantite' => $quantite,
        ':id' => $id_prestataire
    ]);
    
    $_SESSION['prod_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
    
} catch (Exception $e) {
    error_log("Erreur enregistrement production: " . $e->getMessage());
    $_SESSION['err_presta'] = 1;
    header('Location: ../../pages/prestataires.php');
    exit;
}
?>