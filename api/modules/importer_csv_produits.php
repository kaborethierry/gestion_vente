<?php
// api/modules/importer_csv_produits.php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu']);
    exit;
}

require_once __DIR__ . '/connect_db_pdo.php';

$file = $_FILES['csv_file']['tmp_name'];
if (($handle = fopen($file, 'r')) !== false) {
    $row = 0;
    $imported = 0;
    $errors = [];
    
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
        $row++;
        if ($row == 1) continue; // Skip header row
        
        if (count($data) < 7) {
            $errors[] = "Ligne $row: Données incomplètes";
            continue;
        }
        
        $code_produit = $data[0];
        $nom = $data[1];
        $categorie = $data[2];
        $prix_achat = (float)$data[3];
        $prix_vente = (float)$data[4];
        $stock_initial = (int)$data[5];
        $description = $data[6] ?? '';
        
        if (empty($code_produit)) {
            $annee = date('Y');
            $mois = date('m');
            $stmt = $bdd->prepare("SELECT COUNT(*) FROM danfaniment_produits WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
            $stmt->execute();
            $count = (int)$stmt->fetchColumn();
            $code_produit = sprintf("PRD-%s%s-%04d", $annee, $mois, $count + $imported + 1);
        }
        
        try {
            $stmt = $bdd->prepare("
                INSERT INTO danfaniment_produits 
                (code_produit, nom, description, categorie, prix_achat, prix_vente, stock_initial, stock_actuel, created_by, created_at) 
                VALUES 
                (:code, :nom, :desc, :cat, :pa, :pv, :stock, :stock, :user, NOW())
            ");
            $stmt->execute([
                ':code' => $code_produit,
                ':nom' => $nom,
                ':desc' => $description,
                ':cat' => $categorie,
                ':pa' => $prix_achat,
                ':pv' => $prix_vente,
                ':stock' => $stock_initial,
                ':user' => $_SESSION['id']
            ]);
            $imported++;
        } catch (PDOException $e) {
            $errors[] = "Ligne $row: " . $e->getMessage();
        }
    }
    fclose($handle);
    
    echo json_encode([
        'success' => true,
        'message' => "$imported produits importés. " . count($errors) . " erreurs.",
        'errors' => $errors
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Impossible de lire le fichier CSV']);
}
?>