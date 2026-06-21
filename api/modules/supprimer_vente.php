<?php
session_start();

// Seul un Admin ou Caissier peut supprimer une vente
if (empty($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'caissier')) {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$id_vente = intval($_GET['id_vente'] ?? 0);

if ($id_vente <= 0) {
    $_SESSION['err_pos'] = 1;
    header('Location: ../../pages/pos.php');
    exit;
}

try {
    $bdd->beginTransaction();
    
    $stmt = $bdd->prepare("SELECT statut, numero_vente, id_caisse, total_ttc, mode_paiement FROM danfaniment_ventes WHERE id_vente = :id");
    $stmt->execute([':id' => $id_vente]);
    $vente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vente) {
        throw new Exception("Vente non trouvée");
    }
    
    $stmt = $bdd->prepare("DELETE FROM danfaniment_lignes_ventes WHERE id_vente = :id");
    $stmt->execute([':id' => $id_vente]);
    
    if ($vente['id_caisse']) {
        $updateSql = "UPDATE danfaniment_caisses SET 
                      nombre_ventes = nombre_ventes - 1,
                      total_ventes_brut = total_ventes_brut - :total,
                      total_ventes_net = total_ventes_net - :total";
        
        if ($vente['mode_paiement'] === 'especes') {
            $updateSql .= ", total_especes = total_especes - :total";
        } elseif ($vente['mode_paiement'] === 'carte') {
            $updateSql .= ", total_carte = total_carte - :total";
        } elseif ($vente['mode_paiement'] === 'mobile_money') {
            $updateSql .= ", total_mobile_money = total_mobile_money - :total";
        } elseif ($vente['mode_paiement'] === 'virement') {
            $updateSql .= ", total_virement = total_virement - :total";
        }
        
        $updateSql .= " WHERE id_caisse = :id_caisse";
        
        $stmt = $bdd->prepare($updateSql);
        $stmt->execute([
            ':total' => $vente['total_ttc'],
            ':id_caisse' => $vente['id_caisse']
        ]);
    }
    
    $stmt = $bdd->prepare("DELETE FROM danfaniment_ventes WHERE id_vente = :id");
    $stmt->execute([':id' => $id_vente]);
    
    $bdd->commit();
    
    $_SESSION['vente_supprimee'] = 1;
    
} catch (Exception $e) {
    $bdd->rollBack();
    $_SESSION['err_pos'] = 1;
}

header('Location: ../../pages/pos.php');
exit;
?>