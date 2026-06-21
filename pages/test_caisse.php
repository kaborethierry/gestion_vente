<?php
session_start();

// Forcer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Session Caisse</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .btn { display: inline-block; padding: 10px 15px; background: #DC2626; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
        .btn:hover { background: #B91C1C; }
    </style>
</head>
<body>
    <h1>🔧 Diagnostic Session Caisse</h1>";

// Vérifier si l'utilisateur est connecté
if (empty($_SESSION['id'])) {
    echo "<div class='error'>";
    echo "<h2>⚠️ VOUS N'ÊTES PAS CONNECTÉ !</h2>";
    echo "<p>Session ID utilisateur: NON DEFINI</p>";
    echo "<p>Pour utiliser le POS, vous devez d'abord vous connecter.</p>";
    echo "<a href='../index.php' class='btn'>🔐 Aller à la page de connexion</a>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

echo "<h2>📌 Informations de session PHP</h2>";
echo "<pre>";
echo "Session ID utilisateur: " . $_SESSION['id'] . "\n";
echo "Rôle: " . ($_SESSION['role'] ?? 'NON DEFINI') . "\n";
echo "Nom complet: " . ($_SESSION['nom_complet'] ?? $_SESSION['nom_utilisateur'] ?? 'NON DEFINI') . "\n";
echo "</pre>";

// Test de connexion BDD
require_once __DIR__ . '/../api/modules/connect_db_pdo.php';

echo "<h2>📌 Test de connexion à la base de données</h2>";
try {
    $stmt = $bdd->query("SELECT DATABASE() as db");
    $db = $stmt->fetch();
    echo "<p class='success'>✓ Connexion BDD réussie - Base: " . $db['db'] . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Erreur connexion BDD: " . $e->getMessage() . "</p>";
}

echo "<h2>📌 Session caisse - même requête que pos.php</h2>";
try {
    $stmt = $bdd->prepare("SELECT * FROM danfaniment_caisses WHERE id_utilisateur = ? AND statut = 'ouverte' ORDER BY id_caisse DESC LIMIT 1");
    $stmt->execute([$_SESSION['id']]);
    $session_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session_data) {
        echo "<p class='success'>✓ SESSION TROUVÉE !</p>";
        echo "<table border='1'>";
        foreach ($session_data as $key => $value) {
            echo "<tr><th>" . htmlspecialchars($key) . "</th><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        echo "</table>";
        echo "<p class='success'>✅ Votre POS va fonctionner normalement !</p>";
    } else {
        echo "<p class='error'>✗ AUCUNE session ouverte trouvée pour l'utilisateur ID: " . $_SESSION['id'] . "</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Erreur requête: " . $e->getMessage() . "</p>";
}

echo "<h2>📌 Toutes les sessions de l'utilisateur (ID " . $_SESSION['id'] . ")</h2>";
try {
    $stmt = $bdd->prepare("SELECT id_caisse, id_session, statut, date_ouverture, date_fermeture, montant_initial FROM danfaniment_caisses WHERE id_utilisateur = ? ORDER BY id_caisse DESC");
    $stmt->execute([$_SESSION['id']]);
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($all) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Session</th><th>Statut</th><th>Date ouverture</th><th>Date fermeture</th><th>Montant initial</th></tr>";
        foreach ($all as $row) {
            $statusClass = $row['statut'] == 'ouverte' ? 'style=\"background:#d4edda;\"' : '';
            echo "<tr $statusClass>";
            echo "<td>" . $row['id_caisse'] . "</td>";
            echo "<td>" . htmlspecialchars($row['id_session']) . "</td>";
            echo "<td>" . htmlspecialchars($row['statut']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date_ouverture']) . "</td>";
            echo "<td>" . ($row['date_fermeture'] ?? 'NULL') . "</td>";
            echo "<td>" . number_format($row['montant_initial'], 0, ',', ' ') . " CFA</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>Aucune session trouvée</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
}

echo "<h2>📌 Comptage des sessions ouvertes</h2>";
try {
    $stmt = $bdd->prepare("SELECT COUNT(*) as total FROM danfaniment_caisses WHERE id_utilisateur = ? AND statut = 'ouverte'");
    $stmt->execute([$_SESSION['id']]);
    $count = $stmt->fetch();
    if ($count['total'] > 0) {
        echo "<p class='success'>✓ Nombre de sessions ouvertes: " . $count['total'] . "</p>";
        echo "<p class='success'>✅ Tout est bon ! Rafraîchissez <a href='pos.php'>pos.php</a></p>";
    } else {
        echo "<p class='error'>✗ Nombre de sessions ouvertes: 0</p>";
        echo "<p class='info'>💡 Solution: Cliquez ci-dessous pour ouvrir une session de caisse:</p>";
        echo "<a href='caisse.php' class='btn'>💰 Ouvrir une session de caisse</a>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>