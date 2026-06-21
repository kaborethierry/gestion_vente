<?php
// api/modules/kpi_clients_actifs.php
// Objectif: retourner le nombre de clients actifs (statut='Actif' et supprimer='Non') au format JSON.
// Réponse attendue par le front: { "value": 123 }

declare(strict_types=1);
session_start();

// Contrôle d'accès: uniquement Admin
if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Accès refusé'], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    // Connexion PDO (doit définir $bdd)
    require_once __DIR__ . '/connect_db_pdo.php';

    if ($bdd instanceof PDO) {
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    $sql = "
        SELECT COUNT(*) AS nb
        FROM clients
        WHERE supprimer = 'Non'
          AND statut = 'Actif'
    ";

    $stmt = $bdd->query($sql);
    $nb = (int) ($stmt ? $stmt->fetchColumn() : 0);

    echo json_encode(['value' => $nb], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // Journaliser côté serveur
    // error_log('kpi_clients_actifs: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE);
} finally {
    $bdd = null;
}
