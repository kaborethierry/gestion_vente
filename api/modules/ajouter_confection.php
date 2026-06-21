<?php
// api/modules/ajouter_confection.php
// DANFANIMENT POS - Ajout d'une commande confection avec prestataires multiples

session_start();

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/confections.php');
    exit;
}

try {
    require_once __DIR__ . '/connect_db_pdo.php';
    $bdd->beginTransaction();

    $id_client = (int)($_POST['id_client'] ?? 0);
    $type_tenue = trim($_POST['type_tenue'] ?? 'autre');
    $description_tenue = trim($_POST['description_tenue'] ?? '');
    $tissu_fourni_par = trim($_POST['tissu_fourni_par'] ?? 'client');
    $quantite_tissu = !empty($_POST['quantite_tissu']) ? (float)$_POST['quantite_tissu'] : null;
    $reference_tissu = trim($_POST['reference_tissu'] ?? '');
    $date_livraison_prevue = trim($_POST['date_livraison_prevue'] ?? '');
    $montant_total = (float)($_POST['montant_total'] ?? 0);
    $montant_avance = (float)($_POST['montant_avance'] ?? 0);
    $instructions_couturier = trim($_POST['instructions_couturier'] ?? '');
    $remarques = trim($_POST['remarques'] ?? '');
    $id_utilisateur = $_SESSION['id'];
    
    $prestataires = $_POST['prestataires'] ?? [];
    
    if ($id_client <= 0 || empty($type_tenue) || $date_livraison_prevue === '' || $montant_total <= 0 || empty($prestataires)) {
        throw new Exception("Champs obligatoires manquants");
    }
    
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_clients WHERE (supprimer = 'Non' OR supprimer IS NULL) AND id_client = :idc");
    $stmt->execute([':idc' => $id_client]);
    if ((int)$stmt->fetchColumn() === 0) {
        throw new Exception("Client non trouvé");
    }
    
    $annee = date('Y');
    $mois = date('m');
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_commandes_confection WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
    $stmt->execute();
    $count = (int)$stmt->fetchColumn();
    $numero = sprintf("CMD-%s%s-%04d", $annee, $mois, $count + 1);
    
    $total_prestataires = 0;
    foreach ($prestataires as $p) {
        $total_prestataires += (float)($p['montant'] ?? 0);
    }
    
    $sql = "INSERT INTO danfaniment_commandes_confection (
                numero_commande, id_client, id_utilisateur, type_tenue, description_tenue,
                tissu_fourni_par, quantite_tissu, reference_tissu, date_livraison_prevue,
                montant_total, montant_avance, solde_restant, cout_couturier, remarques, instructions_couturier,
                statut, created_at, synced
            ) VALUES (
                :numero_commande, :id_client, :id_utilisateur, :type_tenue, :description_tenue,
                :tissu_fourni_par, :quantite_tissu, :reference_tissu, :date_livraison_prevue,
                :montant_total, :montant_avance, :solde_restant, :cout_couturier, :remarques, :instructions_couturier,
                'en_attente', NOW(), 0
            )";
    
    $stmt = $bdd->prepare($sql);
    $solde_restant = $montant_total - $montant_avance;
    
    $stmt->bindValue(':numero_commande', $numero, PDO::PARAM_STR);
    $stmt->bindValue(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->bindValue(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->bindValue(':type_tenue', $type_tenue, PDO::PARAM_STR);
    $stmt->bindValue(':description_tenue', $description_tenue, PDO::PARAM_STR);
    $stmt->bindValue(':tissu_fourni_par', $tissu_fourni_par, PDO::PARAM_STR);
    $stmt->bindValue(':quantite_tissu', $quantite_tissu, is_null($quantite_tissu) ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':reference_tissu', $reference_tissu, PDO::PARAM_STR);
    $stmt->bindValue(':date_livraison_prevue', $date_livraison_prevue, PDO::PARAM_STR);
    $stmt->bindValue(':montant_total', $montant_total, PDO::PARAM_STR);
    $stmt->bindValue(':montant_avance', $montant_avance, PDO::PARAM_STR);
    $stmt->bindValue(':solde_restant', $solde_restant, PDO::PARAM_STR);
    $stmt->bindValue(':cout_couturier', $total_prestataires, PDO::PARAM_STR);
    $stmt->bindValue(':remarques', $remarques, PDO::PARAM_STR);
    $stmt->bindValue(':instructions_couturier', $instructions_couturier, PDO::PARAM_STR);
    $stmt->execute();
    
    $id_commande = $bdd->lastInsertId();
    
    // Insertion des prestataires
    foreach ($prestataires as $p) {
        $id_prestataire = (int)($p['id_prestataire'] ?? 0);
        $montant = (float)($p['montant'] ?? 0);
        $type_production = trim($p['type_production'] ?? 'tenue');
        
        if ($id_prestataire > 0) {
            $stmt = $bdd->prepare("
                INSERT INTO danfaniment_commande_prestataires (
                    id_commande, id_prestataire, type_production,
                    montant_unitaire, montant_total, statut_paiement, created_at
                ) VALUES (
                    :id_commande, :id_prestataire, :type_production,
                    :montant, :montant, 'en_attente', NOW()
                )
            ");
            $stmt->execute([
                ':id_commande' => $id_commande,
                ':id_prestataire' => $id_prestataire,
                ':type_production' => $type_production,
                ':montant' => $montant
            ]);
        }
    }
    
    // ✅ AJOUT IMPORTANT : Mise à jour de la caisse pour l'avance
    if ($montant_avance > 0) {
        // Enregistrer le paiement dans la table des paiements
        $stmt = $bdd->prepare("
            INSERT INTO danfaniment_paiements_confection (
                id_commande, id_utilisateur, montant, type_paiement, mode_paiement, created_at
            ) VALUES (
                :id_commande, :id_utilisateur, :montant, 'avance', 'especes', NOW()
            )
        ");
        $stmt->execute([
            ':id_commande' => $id_commande,
            ':id_utilisateur' => $id_utilisateur,
            ':montant' => $montant_avance
        ]);
        
        // Récupérer la session de caisse active
        $stmt = $bdd->prepare("
            SELECT id_caisse FROM danfaniment_caisses 
            WHERE statut = 'ouverte' 
            ORDER BY id_caisse DESC LIMIT 1
        ");
        $stmt->execute();
        $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si aucune caisse ouverte trouvée, essayer avec l'utilisateur connecté
        if (!$caisse) {
            $stmt = $bdd->prepare("
                SELECT id_caisse FROM danfaniment_caisses 
                WHERE id_utilisateur = :id_user AND statut = 'ouverte' 
                ORDER BY id_caisse DESC LIMIT 1
            ");
            $stmt->execute([':id_user' => $_SESSION['id']]);
            $caisse = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($caisse) {
            // Récupérer les valeurs avant modification
            $stmt = $bdd->prepare("SELECT total_avance_confection FROM danfaniment_caisses WHERE id_caisse = :id_caisse");
            $stmt->execute([':id_caisse' => $caisse['id_caisse']]);
            $ancien_total_avance = (float)$stmt->fetchColumn();
            $nouveau_total_avance = $ancien_total_avance + $montant_avance;
            
            // Mettre à jour la caisse
            $stmt = $bdd->prepare("
                UPDATE danfaniment_caisses 
                SET total_avance_confection = COALESCE(total_avance_confection, 0) + :montant,
                    total_ventes_brut = COALESCE(total_ventes_brut, 0) + :montant,
                    total_ventes_net = COALESCE(total_ventes_net, 0) + :montant
                WHERE id_caisse = :id_caisse
            ");
            $stmt->execute([
                ':montant' => $montant_avance,
                ':id_caisse' => $caisse['id_caisse']
            ]);
            
            // Mettre à jour total_especes
            $stmt = $bdd->prepare("UPDATE danfaniment_caisses SET total_especes = COALESCE(total_especes, 0) + :montant WHERE id_caisse = :id_caisse");
            $stmt->execute([
                ':montant' => $montant_avance,
                ':id_caisse' => $caisse['id_caisse']
            ]);
            
            // Enregistrer l'opération dans le journal de caisse
            $stmt = $bdd->prepare("
                INSERT INTO danfaniment_caisse_operations 
                (id_caisse, id_utilisateur, type_operation, reference, montant, montant_avant, montant_apres, description, created_at)
                VALUES 
                (:id_caisse, :id_user, 'depot', :reference, :montant, :montant_avant, :montant_apres, :description, NOW())
            ");
            $stmt->execute([
                ':id_caisse' => $caisse['id_caisse'],
                ':id_user' => $_SESSION['id'],
                ':reference' => $numero,
                ':montant' => $montant_avance,
                ':montant_avant' => $ancien_total_avance,
                ':montant_apres' => $nouveau_total_avance,
                ':description' => "Avance sur commande confection #{$numero} - Client ID: {$id_client} - Montant: " . number_format($montant_avance, 0, ',', ' ') . " FCFA"
            ]);
        } else {
            error_log("Aucune session de caisse active trouvée pour l'avance de confection");
        }
    }
    
    $bdd->commit();
    $_SESSION['ajout_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;

} catch (Exception $e) {
    if (isset($bdd)) $bdd->rollBack();
    error_log("Erreur ajout confection: " . $e->getMessage());
    $_SESSION['err_conf'] = 1;
    header('Location: ../../pages/confections.php');
    exit;
}
?>