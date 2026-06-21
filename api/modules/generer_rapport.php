<?php
session_start();

if (empty($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ../../index.php?erreur=3');
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php'; // À installer

$type = $_GET['type'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$type_rapport = $_GET['type_rapport'] ?? 'ventes';

class PDF extends FPDF
{
    function Header()
    {
        // Logo (à placer dans assets/images/logo.png)
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/garagee/assets/images/logo.png')) {
            $this->Image($_SERVER['DOCUMENT_ROOT'] . '/garagee/assets/images/logo.png', 10, 6, 30);
        }
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'DANFANIMENT', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Tel: +225 XX XX XX XX | Email: contact@danfaniment.com', 0, 1, 'C');
        $this->Cell(0, 5, 'Date: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $this->Ln(10);
    }
    
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' - Généré par ' . $_SESSION['nom_complet'], 0, 0, 'C');
    }
    
    function Titre($titre)
    {
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(220, 38, 38);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, $titre, 0, 1, 'C', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(5);
    }
    
    function SousTitre($sous_titre)
    {
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(245, 158, 11);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 8, $sous_titre, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);
    }
}

$pdf = new PDF();
$pdf->AddPage();

switch($type) {
    case 'journalier':
        rapportJournalier($pdf, $bdd);
        break;
    case 'hebdomadaire':
        rapportHebdomadaire($pdf, $bdd);
        break;
    case 'mensuel':
        rapportMensuel($pdf, $bdd);
        break;
    case 'inventaire':
        rapportInventaire($pdf, $bdd);
        break;
    case 'mouvements_stock':
        rapportMouvementsStock($pdf, $bdd);
        break;
    case 'alertes_stock':
        rapportAlertesStock($pdf, $bdd);
        break;
    case 'prestataires':
        rapportPrestataires($pdf, $bdd);
        break;
    case 'paiements_prestataires':
        rapportPaiementsPrestataires($pdf, $bdd);
        break;
    case 'productions':
        rapportProductions($pdf, $bdd);
        break;
    case 'clients':
        rapportClients($pdf, $bdd);
        break;
    case 'clients_fideles':
        rapportClientsFideles($pdf, $bdd);
        break;
    case 'personnalise':
        rapportPersonnalise($pdf, $bdd, $date_debut, $date_fin, $type_rapport);
        break;
    default:
        rapportJournalier($pdf, $bdd);
}

$pdf->Output('I', 'rapport_' . date('Ymd_His') . '.pdf');

function rapportJournalier($pdf, $bdd)
{
    $pdf->Titre('Rapport journalier');
    $pdf->SousTitre('Ventes du ' . date('d/m/Y'));
    
    // Ventes du jour
    $stmt = $bdd->prepare("
        SELECT v.*, u.nom_complet as caissier,
               COALESCE(SUM(l.quantite), 0) as nb_articles
        FROM danfaniment_ventes v
        LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
        LEFT JOIN danfaniment_lignes_ventes l ON v.id_vente = l.id_vente
        WHERE DATE(v.created_at) = CURDATE() AND v.statut = 'valide'
        GROUP BY v.id_vente
    ");
    $stmt->execute();
    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_ventes = array_sum(array_column($ventes, 'total_ttc'));
    $nb_ventes = count($ventes);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 8, 'Nombre de ventes:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $nb_ventes, 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 8, 'Total encaissé:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($total_ventes, 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->Ln(5);
    $pdf->SousTitre('Détail des ventes');
    
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(30, 8, 'N° Vente', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Client', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Montant', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Mode paiement', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Caissier', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    foreach ($ventes as $vente) {
        $client = $vente['id_client'] ? 'Client' : 'Non fidèle';
        $pdf->Cell(30, 6, $vente['numero_vente'], 1, 0);
        $pdf->Cell(40, 6, $client, 1, 0);
        $pdf->Cell(30, 6, number_format($vente['total_ttc'], 0, ',', ' ') . ' CFA', 1, 0);
        $pdf->Cell(30, 6, $vente['mode_paiement'], 1, 0);
        $pdf->Cell(40, 6, $vente['caissier'], 1, 1);
    }
    
    // Dépenses du jour
    $stmt = $bdd->prepare("SELECT * FROM danfaniment_depenses WHERE date_depense = CURDATE()");
    $stmt->execute();
    $depenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_depenses = array_sum(array_column($depenses, 'montant'));
    
    $pdf->Ln(5);
    $pdf->SousTitre('Dépenses du jour');
    
    if (count($depenses) > 0) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(60, 8, 'Libellé', 1, 0, 'C');
        $pdf->Cell(60, 8, 'Catégorie', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Montant', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 8);
        foreach ($depenses as $depense) {
            $pdf->Cell(60, 6, $depense['libelle'], 1, 0);
            $pdf->Cell(60, 6, $depense['categorie'], 1, 0);
            $pdf->Cell(50, 6, number_format($depense['montant'], 0, ',', ' ') . ' CFA', 1, 1);
        }
    } else {
        $pdf->Cell(0, 6, 'Aucune dépense enregistrée aujourd\'hui', 1, 1, 'C');
    }
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, 'Solde de caisse: ' . number_format($total_ventes - $total_depenses, 0, ',', ' ') . ' CFA', 0, 1, 'R');
    
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, 'Signature de l\'administrateur: _________________________', 0, 1, 'R');
    $pdf->Cell(0, 5, date('d/m/Y'), 0, 1, 'R');
}

function rapportHebdomadaire($pdf, $bdd)
{
    $debut_semaine = date('Y-m-d', strtotime('monday this week'));
    $fin_semaine = date('Y-m-d', strtotime('sunday this week'));
    
    $pdf->Titre('Rapport hebdomadaire');
    $pdf->SousTitre('Semaine du ' . date('d/m/Y', strtotime($debut_semaine)) . ' au ' . date('d/m/Y', strtotime($fin_semaine)));
    
    // Ventes semaine
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(total_ttc), 0) as total_ventes,
               COUNT(*) as nb_ventes
        FROM danfaniment_ventes
        WHERE DATE(created_at) BETWEEN :debut AND :fin AND statut = 'valide'
    ");
    $stmt->execute([':debut' => $debut_semaine, ':fin' => $fin_semaine]);
    $ventes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Dépenses semaine
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(montant), 0) as total_depenses,
               COUNT(*) as nb_depenses,
               categorie, SUM(montant) as total_par_categorie
        FROM danfaniment_depenses
        WHERE date_depense BETWEEN :debut AND :fin
        GROUP BY categorie
    ");
    $stmt->execute([':debut' => $debut_semaine, ':fin' => $fin_semaine]);
    $depenses_par_categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_depenses = array_sum(array_column($depenses_par_categorie, 'total_par_categorie'));
    
    // Paiements prestataires semaine
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(montant), 0) as total_paiements,
               COUNT(*) as nb_paiements
        FROM danfaniment_depenses
        WHERE categorie IN ('salaire_prestataire_couturier', 'salaire_prestataire_tisseuse')
        AND date_depense BETWEEN :debut AND :fin
    ");
    $stmt->execute([':debut' => $debut_semaine, ':fin' => $fin_semaine]);
    $paiements = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(80, 8, 'Total ventes de la semaine:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($ventes['total_ventes'], 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(80, 8, 'Nombre de ventes:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $ventes['nb_ventes'], 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(80, 8, 'Total dépenses:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($total_depenses, 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(80, 8, 'Paiements prestataires:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($paiements['total_paiements'], 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, 'Bénéfice brut: ' . number_format($ventes['total_ventes'] - $total_depenses, 0, ',', ' ') . ' CFA', 0, 1, 'R');
    
    // Confections en cours
    $stmt = $bdd->prepare("
        SELECT COUNT(*) as nb_encours, COALESCE(SUM(solde_restant), 0) as total_restant
        FROM danfaniment_commandes_confection
        WHERE statut IN ('en_attente', 'en_cours')
    ");
    $stmt->execute();
    $encours = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $pdf->Ln(5);
    $pdf->SousTitre('Confections en cours');
    $pdf->Cell(80, 8, 'Commandes en cours:', 0, 0);
    $pdf->Cell(0, 8, $encours['nb_encours'], 0, 1);
    $pdf->Cell(80, 8, 'Solde total restant à payer:', 0, 0);
    $pdf->Cell(0, 8, number_format($encours['total_restant'], 0, ',', ' ') . ' CFA', 0, 1);
}

function rapportMensuel($pdf, $bdd)
{
    $mois_courant = date('Y-m');
    $pdf->Titre('Rapport mensuel - ' . date('F Y'));
    
    // Bilan des ventes
    $stmt = $bdd->prepare("
        SELECT COALESCE(SUM(total_ttc), 0) as total_ventes,
               COUNT(*) as nb_ventes,
               mode_paiement, SUM(total_ttc) as total_par_mode
        FROM danfaniment_ventes
        WHERE DATE_FORMAT(created_at, '%Y-%m') = :mois AND statut = 'valide'
        GROUP BY mode_paiement
    ");
    $stmt->execute([':mois' => $mois_courant]);
    $ventes_par_mode = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_ventes = array_sum(array_column($ventes_par_mode, 'total_par_mode'));
    $nb_ventes = array_sum(array_column($ventes_par_mode, 'nb_ventes'));
    
    $pdf->SousTitre('Chiffre d\'affaires');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Total ventes du mois:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($total_ventes, 0, ',', ' ') . ' CFA', 0, 1);
    $pdf->Cell(60, 8, 'Nombre de ventes:', 0, 0);
    $pdf->Cell(0, 8, $nb_ventes, 0, 1);
    
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(80, 8, 'Mode de paiement', 1, 0, 'C');
    $pdf->Cell(80, 8, 'Montant', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
    foreach ($ventes_par_mode as $vente) {
        $pdf->Cell(80, 6, $vente['mode_paiement'], 1, 0);
        $pdf->Cell(80, 6, number_format($vente['total_par_mode'], 0, ',', ' ') . ' CFA', 1, 1);
    }
    
    // Dépenses par catégorie
    $stmt = $bdd->prepare("
        SELECT categorie, SUM(montant) as total
        FROM danfaniment_depenses
        WHERE DATE_FORMAT(date_depense, '%Y-%m') = :mois
        GROUP BY categorie
        ORDER BY total DESC
    ");
    $stmt->execute([':mois' => $mois_courant]);
    $depenses_par_cat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_depenses = array_sum(array_column($depenses_par_cat, 'total'));
    
    $pdf->Ln(5);
    $pdf->SousTitre('Dépenses par catégorie');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(100, 8, 'Catégorie', 1, 0, 'C');
    $pdf->Cell(60, 8, 'Montant', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
    foreach ($depenses_par_cat as $depense) {
        $pdf->Cell(100, 6, $depense['categorie'], 1, 0);
        $pdf->Cell(60, 6, number_format($depense['total'], 0, ',', ' ') . ' CFA', 1, 1);
    }
    
    // Bénéfice
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'BILAN DU MOIS', 0, 1, 'C', true);
    
    $benefice = $total_ventes - $total_depenses;
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(80, 8, 'Chiffre d\'affaires:', 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, number_format($total_ventes, 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(80, 8, 'Total dépenses:', 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, number_format($total_depenses, 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(34, 197, 94);
    $pdf->Cell(80, 10, 'BÉNÉFICE:', 0, 0);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, number_format($benefice, 0, ',', ' ') . ' CFA', 0, 1);
}

function rapportInventaire($pdf, $bdd)
{
    $pdf->Titre('Rapport d\'inventaire');
    $pdf->SousTitre('État du stock au ' . date('d/m/Y H:i:s'));
    
    $stmt = $bdd->prepare("
        SELECT p.*,
               CASE 
                   WHEN p.stock_actuel <= 0 THEN 'Rupture'
                   WHEN p.stock_actuel <= p.stock_minimum THEN 'Alerte'
                   ELSE 'Normal'
               END as statut_stock,
               (p.stock_actuel * p.prix_achat) as valeur_stock
        FROM danfaniment_produits p
        WHERE p.statut = 'actif'
        ORDER BY p.nom
    ");
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $valeur_totale = array_sum(array_column($produits, 'valeur_stock'));
    $nb_produits = count($produits);
    $nb_ruptures = count(array_filter($produits, fn($p) => $p['statut_stock'] === 'Rupture'));
    $nb_alertes = count(array_filter($produits, fn($p) => $p['statut_stock'] === 'Alerte'));
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Nombre total de produits:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $nb_produits, 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Valeur totale du stock:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($valeur_totale, 0, ',', ' ') . ' CFA', 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Produits en rupture:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $nb_ruptures, 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Produits en alerte:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $nb_alertes, 0, 1);
    
    $pdf->Ln(5);
    $pdf->SousTitre('Détail des produits');
    
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(50, 8, 'Produit', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Stock actuel', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Stock mini', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Valeur unitaire', 1, 0, 'C');
    $pdf->Cell(50, 8, 'Valeur totale', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Statut', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 7);
    foreach ($produits as $produit) {
        $couleur = match($produit['statut_stock']) {
            'Rupture' => [255, 200, 200],
            'Alerte' => [255, 235, 200],
            default => [255, 255, 255]
        };
        $pdf->SetFillColor($couleur[0], $couleur[1], $couleur[2]);
        
        $pdf->Cell(50, 6, substr($produit['nom'], 0, 25), 1, 0, 'L', true);
        $pdf->Cell(30, 6, $produit['stock_actuel'], 1, 0, 'C', true);
        $pdf->Cell(30, 6, $produit['stock_minimum'], 1, 0, 'C', true);
        $pdf->Cell(40, 6, number_format($produit['prix_achat'], 0, ',', ' ') . ' CFA', 1, 0, 'R', true);
        $pdf->Cell(50, 6, number_format($produit['valeur_stock'], 0, ',', ' ') . ' CFA', 1, 0, 'R', true);
        
        $statut_text = match($produit['statut_stock']) {
            'Rupture' => 'RUPTURE',
            'Alerte' => 'ALERTE',
            default => 'Normal'
        };
        $pdf->Cell(30, 6, $statut_text, 1, 1, 'C', true);
    }
}

function rapportMouvementsStock($pdf, $bdd)
{
    $pdf->Titre('Rapport des mouvements de stock');
    $pdf->SousTitre('Historique des 30 derniers jours');
    
    $stmt = $bdd->prepare("
        SELECT m.*, p.nom as produit_nom, u.nom_complet as utilisateur_nom
        FROM danfaniment_stock_mouvements m
        INNER JOIN danfaniment_produits p ON m.id_produit = p.id_produit
        INNER JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
        WHERE m.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY m.created_at DESC
        LIMIT 100
    ");
    $stmt->execute();
    $mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_entrees = array_sum(array_filter(array_column($mouvements, 'quantite'), fn($q) => $q > 0));
    $total_sorties = abs(array_sum(array_filter(array_column($mouvements, 'quantite'), fn($q) => $q < 0)));
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Total entrées (30j):', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $total_entrees . ' unités', 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Total sorties (30j):', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $total_sorties . ' unités', 0, 1);
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(25, 8, 'Date', 1, 0, 'C');
    $pdf->Cell(50, 8, 'Produit', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Type', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Qté', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Stock avant', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Stock après', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Utilisateur', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 7);
    foreach ($mouvements as $mvt) {
        $pdf->Cell(25, 5, date('d/m/Y', strtotime($mvt['created_at'])), 1, 0);
        $pdf->Cell(50, 5, substr($mvt['produit_nom'], 0, 20), 1, 0);
        $pdf->Cell(25, 5, $mvt['type_mouvement'], 1, 0);
        $pdf->Cell(20, 5, $mvt['quantite'], 1, 0);
        $pdf->Cell(25, 5, $mvt['stock_avant'], 1, 0);
        $pdf->Cell(25, 5, $mvt['stock_apres'], 1, 0);
        $pdf->Cell(40, 5, substr($mvt['utilisateur_nom'], 0, 15), 1, 1);
    }
}

function rapportAlertesStock($pdf, $bdd)
{
    $pdf->Titre('Rapport des alertes stock');
    
    $stmt = $bdd->prepare("
        SELECT p.id_produit, p.nom, p.stock_actuel, p.stock_minimum,
               CASE 
                   WHEN p.stock_actuel <= 0 THEN 'Rupture de stock'
                   WHEN p.stock_actuel <= p.stock_minimum THEN 'Stock minimum atteint'
                   ELSE 'Normal'
               END as niveau_alerte
        FROM danfaniment_produits p
        WHERE p.stock_actuel <= p.stock_minimum AND p.statut = 'actif'
        ORDER BY p.stock_actuel ASC
    ");
    $stmt->execute();
    $alertes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($alertes) > 0) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(60, 8, 'Produit', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Stock actuel', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Stock minimum', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Action requise', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 8);
        foreach ($alertes as $alerte) {
            $pdf->SetFillColor(255, 200, 200);
            $pdf->Cell(60, 8, $alerte['nom'], 1, 0, 'L', true);
            $pdf->Cell(30, 8, $alerte['stock_actuel'], 1, 0, 'C', true);
            $pdf->Cell(30, 8, $alerte['stock_minimum'], 1, 0, 'C', true);
            $pdf->Cell(50, 8, $alerte['niveau_alerte'], 1, 1, 'C', true);
        }
        
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(220, 38, 38);
        $pdf->Cell(0, 8, 'URGENT: ' . count($alertes) . ' produit(s) nécessite(nt) un réapprovisionnement immédiat !', 0, 1);
        $pdf->SetTextColor(0, 0, 0);
    } else {
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(34, 197, 94);
        $pdf->Cell(0, 10, 'Aucune alerte de stock ! Tous les produits sont bien approvisionnés.', 0, 1, 'C');
    }
}

function rapportPrestataires($pdf, $bdd)
{
    $pdf->Titre('Rapport des prestataires');
    
    $stmt = $bdd->prepare("
        SELECT p.*,
               COALESCE(SUM(prod.quantite), 0) as total_productions,
               (SELECT COALESCE(SUM(montant), 0) FROM danfaniment_depenses WHERE id_prestataire = p.id_prestataire) as total_paye
        FROM danfaniment_prestataires p
        LEFT JOIN danfaniment_productions_prestataires prod ON p.id_prestataire = prod.id_prestataire
        WHERE p.actif = 1
        GROUP BY p.id_prestataire
        ORDER BY p.type_prestataire, p.nom
    ");
    $stmt->execute();
    $prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $couturiers = array_filter($prestataires, fn($p) => $p['type_prestataire'] === 'couturier');
    $tisseuses = array_filter($prestataires, fn($p) => $p['type_prestataire'] === 'tisseuse');
    
    $pdf->SousTitre('Couturiers (' . count($couturiers) . ')');
    if (count($couturiers) > 0) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(50, 8, 'Nom', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Téléphone', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Tarif/tenue', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Productions', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Total payé', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 8);
        foreach ($couturiers as $c) {
            $pdf->Cell(50, 6, $c['prenom'] . ' ' . $c['nom'], 1, 0);
            $pdf->Cell(40, 6, $c['telephone'], 1, 0);
            $pdf->Cell(30, 6, number_format($c['tarif_par_tenue'], 0, ',', ' ') . ' CFA', 1, 0);
            $pdf->Cell(30, 6, $c['total_productions'], 1, 0);
            $pdf->Cell(40, 6, number_format($c['total_paye'], 0, ',', ' ') . ' CFA', 1, 1);
        }
    }
    
    $pdf->Ln(5);
    $pdf->SousTitre('Tisseuses (' . count($tisseuses) . ')');
    if (count($tisseuses) > 0) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(50, 8, 'Nom', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Téléphone', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Tarif/pagne', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Productions', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Total payé', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 8);
        foreach ($tisseuses as $t) {
            $pdf->Cell(50, 6, $t['prenom'] . ' ' . $t['nom'], 1, 0);
            $pdf->Cell(40, 6, $t['telephone'], 1, 0);
            $pdf->Cell(30, 6, number_format($t['tarif_par_pagne'], 0, ',', ' ') . ' CFA', 1, 0);
            $pdf->Cell(30, 6, $t['total_productions'], 1, 0);
            $pdf->Cell(40, 6, number_format($t['total_paye'], 0, ',', ' ') . ' CFA', 1, 1);
        }
    }
}

function rapportPaiementsPrestataires($pdf, $bdd)
{
    $pdf->Titre('Rapport des paiements prestataires');
    $pdf->SousTitre('Historique des 30 derniers jours');
    
    $stmt = $bdd->prepare("
        SELECT d.*, p.nom as prestataire_nom, p.prenom as prestataire_prenom, p.type_prestataire
        FROM danfaniment_depenses d
        INNER JOIN danfaniment_prestataires p ON d.id_prestataire = p.id_prestataire
        WHERE d.categorie IN ('salaire_prestataire_couturier', 'salaire_prestataire_tisseuse')
        AND d.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY d.created_at DESC
    ");
    $stmt->execute();
    $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_paiements = array_sum(array_column($paiements, 'montant'));
    $total_couturiers = array_sum(array_column(array_filter($paiements, fn($p) => $p['type_prestataire'] === 'couturier'), 'montant'));
    $total_tisseuses = array_sum(array_column(array_filter($paiements, fn($p) => $p['type_prestataire'] === 'tisseuse'), 'montant'));
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Total paiements (30j):', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($total_paiements, 0, ',', ' ') . ' CFA', 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Dont couturiers:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($total_couturiers, 0, ',', ' ') . ' CFA', 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Dont tisseuses:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($total_tisseuses, 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(25, 8, 'Date', 1, 0, 'C');
    $pdf->Cell(50, 8, 'Prestataire', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Type', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Montant', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Mode paiement', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    foreach ($paiements as $paiement) {
        $pdf->Cell(25, 6, date('d/m/Y', strtotime($paiement['date_depense'])), 1, 0);
        $pdf->Cell(50, 6, $paiement['prestataire_prenom'] . ' ' . $paiement['prestataire_nom'], 1, 0);
        $pdf->Cell(30, 6, $paiement['type_prestataire'], 1, 0);
        $pdf->Cell(40, 6, number_format($paiement['montant'], 0, ',', ' ') . ' CFA', 1, 0);
        $pdf->Cell(40, 6, $paiement['mode_paiement'], 1, 1);
    }
}

function rapportProductions($pdf, $bdd)
{
    $pdf->Titre('Rapport des productions');
    
    $stmt = $bdd->prepare("
        SELECT prod.*, 
               p.nom as prestataire_nom, p.prenom as prestataire_prenom, p.type_prestataire,
               c.numero_commande
        FROM danfaniment_productions_prestataires prod
        INNER JOIN danfaniment_prestataires p ON prod.id_prestataire = p.id_prestataire
        LEFT JOIN danfaniment_commandes_confection c ON prod.id_commande = c.id_commande
        ORDER BY prod.date_production DESC
        LIMIT 50
    ");
    $stmt->execute();
    $productions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_tenues = array_sum(array_column(array_filter($productions, fn($pr) => $pr['type_production'] === 'tenue'), 'quantite'));
    $total_pagnes = array_sum(array_column(array_filter($productions, fn($pr) => $pr['type_production'] === 'pagne'), 'quantite'));
    $total_montant = array_sum(array_column($productions, 'quantite') * array_column($productions, 'montant_unitaire'));
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Total tenues produites:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $total_tenues, 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Total pagnes produits:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $total_pagnes, 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Montant total à payer:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($total_montant, 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(25, 8, 'Date', 1, 0, 'C');
    $pdf->Cell(50, 8, 'Prestataire', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Type', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Qté', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Tarif unitaire', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Total', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Payé', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 7);
    foreach ($productions as $prod) {
        $pdf->Cell(25, 6, date('d/m/Y', strtotime($prod['date_production'])), 1, 0);
        $pdf->Cell(50, 6, $prod['prestataire_prenom'] . ' ' . $prod['prestataire_nom'], 1, 0);
        $pdf->Cell(25, 6, $prod['type_production'], 1, 0);
        $pdf->Cell(20, 6, $prod['quantite'], 1, 0);
        $pdf->Cell(35, 6, number_format($prod['montant_unitaire'], 0, ',', ' ') . ' CFA', 1, 0);
        $pdf->Cell(35, 6, number_format($prod['quantite'] * $prod['montant_unitaire'], 0, ',', ' ') . ' CFA', 1, 0);
        $pdf->Cell(20, 6, $prod['statut_paiement'] === 'paye' ? 'Oui' : 'Non', 1, 1);
    }
}

function rapportClients($pdf, $bdd)
{
    $pdf->Titre('Rapport des clients');
    
    $stmt = $bdd->prepare("
        SELECT c.*,
               COUNT(v.id_vente) as nb_achats,
               COALESCE(SUM(v.total_ttc), 0) as total_achats
        FROM danfaniment_clients c
        LEFT JOIN danfaniment_ventes v ON c.id_client = v.id_client AND v.statut = 'valide'
        WHERE c.supprimer = 'Non'
        GROUP BY c.id_client
        ORDER BY total_achats DESC
        LIMIT 50
    ");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_clients = count($clients);
    $total_achats = array_sum(array_column($clients, 'total_achats'));
    $moyenne_achat = $total_clients > 0 ? $total_achats / $total_clients : 0;
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Nombre total de clients:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, $total_clients, 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Total des achats:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($total_achats, 0, ',', ' ') . ' CFA', 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Moyenne d\'achat/client:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, number_format($moyenne_achat, 0, ',', ' ') . ' CFA', 0, 1);
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(40, 8, 'Client', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Téléphone', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Visites', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Total achats', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Points', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Dernière visite', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 7);
    foreach ($clients as $client) {
        $pdf->Cell(40, 6, $client['prenom'] . ' ' . $client['nom'], 1, 0);
        $pdf->Cell(30, 6, $client['telephone'], 1, 0);
        $pdf->Cell(25, 6, $client['nombre_visites'], 1, 0);
        $pdf->Cell(35, 6, number_format($client['total_achats'], 0, ',', ' ') . ' CFA', 1, 0);
        $pdf->Cell(25, 6, $client['points_fidelite'], 1, 0);
        $pdf->Cell(35, 6, $client['date_derniere_visite'] ? date('d/m/Y', strtotime($client['date_derniere_visite'])) : '-', 1, 1);
    }
}

function rapportClientsFideles($pdf, $bdd)
{
    $pdf->Titre('Top clients fidèles');
    
    $stmt = $bdd->prepare("
        SELECT c.*,
               COUNT(v.id_vente) as nb_achats,
               COALESCE(SUM(v.total_ttc), 0) as total_achats
        FROM danfaniment_clients c
        LEFT JOIN danfaniment_ventes v ON c.id_client = v.id_client AND v.statut = 'valide'
        WHERE c.supprimer = 'Non' AND c.points_fidelite > 0
        GROUP BY c.id_client
        ORDER BY c.points_fidelite DESC
        LIMIT 20
    ");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($clients) > 0) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(50, 8, 'Client', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Téléphone', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Points fidélité', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Total achats', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Visites', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 8);
        foreach ($clients as $client) {
            $pdf->Cell(50, 8, $client['prenom'] . ' ' . $client['nom'], 1, 0);
            $pdf->Cell(30, 8, $client['telephone'], 1, 0);
            $pdf->SetFillColor(245, 158, 11);
            $pdf->Cell(25, 8, $client['points_fidelite'], 1, 0, 'C', true);
            $pdf->Cell(35, 8, number_format($client['total_achats'], 0, ',', ' ') . ' CFA', 1, 0);
            $pdf->Cell(30, 8, $client['nombre_visites'], 1, 1);
        }
    } else {
        $pdf->Cell(0, 10, 'Aucun client fidèle avec points enregistrés.', 0, 1, 'C');
    }
}

function rapportPersonnalise($pdf, $bdd, $date_debut, $date_fin, $type_rapport)
{
    $pdf->Titre('Rapport personnalisé');
    $pdf->SousTitre('Période du ' . date('d/m/Y', strtotime($date_debut)) . ' au ' . date('d/m/Y', strtotime($date_fin)));
    
    switch($type_rapport) {
        case 'ventes':
            $stmt = $bdd->prepare("
                SELECT v.*, u.nom_complet as caissier
                FROM danfaniment_ventes v
                LEFT JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
                WHERE DATE(v.created_at) BETWEEN :debut AND :fin AND v.statut = 'valide'
                ORDER BY v.created_at DESC
            ");
            $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
            $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = array_sum(array_column($donnees, 'total_ttc'));
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 8, 'Total des ventes: ' . number_format($total, 0, ',', ' ') . ' CFA', 0, 1);
            $pdf->Ln(5);
            
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(35, 8, 'N° Vente', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Date', 1, 0, 'C');
            $pdf->Cell(50, 8, 'Client', 1, 0, 'C');
            $pdf->Cell(35, 8, 'Montant', 1, 0, 'C');
            $pdf->Cell(35, 8, 'Mode', 1, 1, 'C');
            
            $pdf->SetFont('Arial', '', 7);
            foreach ($donnees as $row) {
                $pdf->Cell(35, 6, $row['numero_vente'], 1, 0);
                $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['created_at'])), 1, 0);
                $pdf->Cell(50, 6, $row['id_client'] ? 'Client fidèle' : 'Client non fidèle', 1, 0);
                $pdf->Cell(35, 6, number_format($row['total_ttc'], 0, ',', ' ') . ' CFA', 1, 0);
                $pdf->Cell(35, 6, $row['mode_paiement'], 1, 1);
            }
            break;
            
        case 'depenses':
            $stmt = $bdd->prepare("
                SELECT * FROM danfaniment_depenses
                WHERE date_depense BETWEEN :debut AND :fin
                ORDER BY date_depense DESC
            ");
            $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
            $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = array_sum(array_column($donnees, 'montant'));
            $total_par_categorie = [];
            foreach ($donnees as $d) {
                $total_par_categorie[$d['categorie']] = ($total_par_categorie[$d['categorie']] ?? 0) + $d['montant'];
            }
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 8, 'Total des dépenses: ' . number_format($total, 0, ',', ' ') . ' CFA', 0, 1);
            $pdf->Ln(3);
            
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 8, 'Répartition par catégorie:', 0, 1);
            foreach ($total_par_categorie as $cat => $montant) {
                $pdf->Cell(80, 6, $cat, 0, 0);
                $pdf->Cell(0, 6, number_format($montant, 0, ',', ' ') . ' CFA', 0, 1);
            }
            
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(25, 8, 'Date', 1, 0, 'C');
            $pdf->Cell(50, 8, 'Libellé', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Catégorie', 1, 0, 'C');
            $pdf->Cell(35, 8, 'Montant', 1, 0, 'C');
            $pdf->Cell(30, 8, 'Mode', 1, 1, 'C');
            
            $pdf->SetFont('Arial', '', 7);
            foreach ($donnees as $row) {
                $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['date_depense'])), 1, 0);
                $pdf->Cell(50, 6, substr($row['libelle'], 0, 25), 1, 0);
                $pdf->Cell(40, 6, $row['categorie'], 1, 0);
                $pdf->Cell(35, 6, number_format($row['montant'], 0, ',', ' ') . ' CFA', 1, 0);
                $pdf->Cell(30, 6, $row['mode_paiement'], 1, 1);
            }
            break;
            
        case 'commandes':
            $stmt = $bdd->prepare("
                SELECT c.*, CONCAT(cl.nom, ' ', cl.prenom) as client_nom, p.nom as prestataire_nom, p.prenom as prestataire_prenom
                FROM danfaniment_commandes_confection c
                INNER JOIN danfaniment_clients cl ON c.id_client = cl.id_client
                INNER JOIN danfaniment_prestataires p ON c.id_prestataire = p.id_prestataire
                WHERE DATE(c.created_at) BETWEEN :debut AND :fin
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
            $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = array_sum(array_column($donnees, 'montant_total'));
            $total_encaisse = array_sum(array_column($donnees, 'montant_avance'));
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(80, 8, 'Total commandes:', 0, 0);
            $pdf->Cell(0, 8, count($donnees), 0, 1);
            $pdf->Cell(80, 8, 'Montant total des commandes:', 0, 0);
            $pdf->Cell(0, 8, number_format($total, 0, ',', ' ') . ' CFA', 0, 1);
            $pdf->Cell(80, 8, 'Total encaissé:', 0, 0);
            $pdf->Cell(0, 8, number_format($total_encaisse, 0, ',', ' ') . ' CFA', 0, 1);
            $pdf->Cell(80, 8, 'Solde restant:', 0, 0);
            $pdf->Cell(0, 8, number_format($total - $total_encaisse, 0, ',', ' ') . ' CFA', 0, 1);
            break;
            
        case 'paiements':
            $stmt = $bdd->prepare("
                SELECT d.*, p.nom as prestataire_nom, p.prenom as prestataire_prenom
                FROM danfaniment_depenses d
                INNER JOIN danfaniment_prestataires p ON d.id_prestataire = p.id_prestataire
                WHERE d.categorie IN ('salaire_prestataire_couturier', 'salaire_prestataire_tisseuse')
                AND d.date_depense BETWEEN :debut AND :fin
                ORDER BY d.date_depense DESC
            ");
            $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
            $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = array_sum(array_column($donnees, 'montant'));
            $total_couturiers = array_sum(array_column(array_filter($donnees, fn($d) => strpos($d['categorie'], 'couturier') !== false), 'montant'));
            $total_tisseuses = array_sum(array_column(array_filter($donnees, fn($d) => strpos($d['categorie'], 'tisseuse') !== false), 'montant'));
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(80, 8, 'Total paiements prestataires:', 0, 0);
            $pdf->Cell(0, 8, number_format($total, 0, ',', ' ') . ' CFA', 0, 1);
            $pdf->Cell(80, 8, 'Dont couturiers:', 0, 0);
            $pdf->Cell(0, 8, number_format($total_couturiers, 0, ',', ' ') . ' CFA', 0, 1);
            $pdf->Cell(80, 8, 'Dont tisseuses:', 0, 0);
            $pdf->Cell(0, 8, number_format($total_tisseuses, 0, ',', ' ') . ' CFA', 0, 1);
            
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(25, 8, 'Date', 1, 0, 'C');
            $pdf->Cell(50, 8, 'Prestataire', 1, 0, 'C');
            $pdf->Cell(35, 8, 'Montant', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Mode', 1, 1, 'C');
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($donnees as $row) {
                $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['date_depense'])), 1, 0);
                $pdf->Cell(50, 6, $row['prestataire_prenom'] . ' ' . $row['prestataire_nom'], 1, 0);
                $pdf->Cell(35, 6, number_format($row['montant'], 0, ',', ' ') . ' CFA', 1, 0);
                $pdf->Cell(40, 6, $row['mode_paiement'], 1, 1);
            }
            break;
    }
}
?>