<?php
session_start();

// Seul un Admin peut modifier les ventes
if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: /garagee/index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'annuler_vente':
        annulerVente($bdd);
        break;
    case 'rembourser_vente':
        rembourserVente($bdd);
        break;
    default:
        $_SESSION['err_pos'] = 1;
        header('Location: /garagee/pages/pos.php');
}

function annulerVente($bdd) {
    $id_vente = intval($_GET['id_vente'] ?? 0);
    $motif = trim($_GET['motif'] ?? 'Annulation sans motif');
    
    if ($id_vente <= 0) {
        $_SESSION['err_pos'] = 1;
        header('Location: /garagee/pages/pos.php');
        return;
    }
    
    try {
        $bdd->beginTransaction();
        
        // Récupérer la vente
        $stmt = $bdd->prepare("SELECT * FROM danfaniment_ventes WHERE id_vente = :id AND statut = 'complete'");
        $stmt->execute([':id' => $id_vente]);
        $vente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vente) {
            throw new Exception("Vente non trouvée ou déjà annulée");
        }
        
        // Récupérer les lignes de vente
        $stmt = $bdd->prepare("SELECT * FROM danfaniment_ventes_lignes WHERE id_vente = :id");
        $stmt->execute([':id' => $id_vente]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restaurer les stocks
        foreach ($lignes as $ligne) {
            $stmt = $bdd->prepare("UPDATE danfaniment_produits SET stock_actuel = stock_actuel + :quantite WHERE id_produit = :id");
            $stmt->execute([':quantite' => $ligne['quantite'], ':id' => $ligne['id_produit']]);
        }
        
        // Mettre à jour la vente
        $stmt = $bdd->prepare("UPDATE danfaniment_ventes SET statut = 'annulee', motif_annulation = :motif, date_annulation = NOW() WHERE id_vente = :id");
        $stmt->execute([':motif' => $motif, ':id' => $id_vente]);
        
        // Mettre à jour la caisse
        $stmt = $bdd->prepare("UPDATE danfaniment_caisses SET 
                              nombre_ventes = nombre_ventes - 1,
                              nombre_annulations = nombre_annulations + 1,
                              total_ventes_brut = total_ventes_brut - :total,
                              total_ventes_net = total_ventes_net - :total,
                              " . ($vente['mode_paiement'] === 'especes' ? 'total_especes = total_especes - :total' : 
                                  ($vente['mode_paiement'] === 'carte' ? 'total_carte = total_carte - :total' :
                                  ($vente['mode_paiement'] === 'mobile_money' ? 'total_mobile_money = total_mobile_money - :total' :
                                  'total_virement = total_virement - :total'))) . "
                              WHERE id_caisse = :id_caisse");
        $stmt->execute([':total' => $vente['total_ttc'], ':id_caisse' => $vente['id_caisse']]);
        
        // Journalisation
        $stmt = $bdd->prepare("INSERT INTO danfaniment_caisse_operations 
                              (id_caisse, id_utilisateur, type_operation, reference, montant, description, ip_address) 
                              VALUES (:id_caisse, :id_utilisateur, 'annulation_vente', :reference, :montant, :description, :ip)");
        $stmt->execute([
            ':id_caisse' => $vente['id_caisse'],
            ':id_utilisateur' => $_SESSION['id'],
            ':reference' => $vente['numero_vente'],
            ':montant' => $vente['total_ttc'],
            ':description' => "Annulation vente #{$vente['numero_vente']} - Motif: {$motif}",
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        
        $bdd->commit();
        
        $_SESSION['vente_annulee'] = 1;
        header('Location: /garagee/pages/ventes.php');
        
    } catch (Exception $e) {
        $bdd->rollBack();
        $_SESSION['err_pos'] = 1;
        header('Location: /garagee/pages/pos.php');
    }
}

function rembourserVente($bdd) {
    $id_vente = intval($_GET['id_vente'] ?? 0);
    $motif = trim($_GET['motif'] ?? 'Remboursement client');
    
    if ($id_vente <= 0) {
        $_SESSION['err_pos'] = 1;
        header('Location: /garagee/pages/pos.php');
        return;
    }
    
    try {
        $bdd->beginTransaction();
        
        // Même logique que l'annulation mais avec un statut différent
        $stmt = $bdd->prepare("SELECT * FROM danfaniment_ventes WHERE id_vente = :id AND statut = 'complete'");
        $stmt->execute([':id' => $id_vente]);
        $vente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vente) {
            throw new Exception("Vente non trouvée");
        }
        
        $stmt = $bdd->prepare("UPDATE danfaniment_ventes SET statut = 'remboursee', motif_annulation = :motif, date_annulation = NOW() WHERE id_vente = :id");
        $stmt->execute([':motif' => $motif, ':id' => $id_vente]);
        
        $bdd->commit();
        
        $_SESSION['vente_remboursee'] = 1;
        header('Location: /garagee/pages/ventes.php');
        
    } catch (Exception $e) {
        $bdd->rollBack();
        $_SESSION['err_pos'] = 1;
        header('Location: /garagee/pages/pos.php');
    }
}
?>