<?php
session_start();

// Seul un Admin ou Caissier peut annuler une vente (tous les utilisateurs connectés)
if (empty($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'caissier')) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_vente = intval($_GET['id_vente'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id_vente <= 0) {
    $_SESSION['err_pos'] = 1;
    header('Location: ../../pages/pos.php');
    exit;
}

try {
    $bdd->beginTransaction();
    
    $stmt = $bdd->prepare("SELECT * FROM danfaniment_ventes WHERE id_vente = :id AND statut = 'valide'");
    $stmt->execute([':id' => $id_vente]);
    $vente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vente) {
        throw new Exception("Vente non trouvée ou déjà annulée");
    }
    
    $stmt = $bdd->prepare("SELECT * FROM danfaniment_lignes_ventes WHERE id_vente = :id");
    $stmt->execute([':id' => $id_vente]);
    $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($lignes as $ligne) {
        if ($ligne['id_produit'] && $ligne['id_produit'] != 4) {
            $stmt = $bdd->prepare("UPDATE danfaniment_produits SET stock_actuel = stock_actuel + :quantite WHERE id_produit = :id");
            $stmt->execute([':quantite' => $ligne['quantite'], ':id' => $ligne['id_produit']]);
        }
    }
    
    $stmt = $bdd->prepare("UPDATE danfaniment_ventes SET statut = 'annule', notes = :motif WHERE id_vente = :id");
    $stmt->execute([':motif' => 'Annulation par utilisateur', ':id' => $id_vente]);
    
    if ($vente['id_caisse']) {
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
        $stmt->execute([
            ':total' => $vente['total_ttc'],
            ':id_caisse' => $vente['id_caisse']
        ]);
    }
    
    $bdd->commit();
    
    $_SESSION['vente_annulee'] = 1;
    
} catch (Exception $e) {
    $bdd->rollBack();
    $_SESSION['err_pos'] = 1;
}

header('Location: ../../pages/pos.php');
exit;
?>