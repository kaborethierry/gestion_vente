<?php
// api/modules/kpi_interventions_encours.php
declare(strict_types=1);
session_start();

if (empty($_SESSION['id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
    session_unset();
    session_destroy();
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Accès refusé'], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/connect_db_pdo.php';

    if (!($bdd instanceof PDO)) { throw new RuntimeException('Connexion PDO invalide.'); }
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Statuts considérés "en cours"
    $sql = "
        SELECT COUNT(*) AS nb
        FROM interventions
        WHERE TRIM(LOWER(supprimer)) = 'non'
          AND TRIM(LOWER(statut)) IN ('en cours','en attente')
    ";
    $nb = (int) $bdd->query($sql)->fetchColumn();

    echo json_encode(['value' => $nb], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE);
} finally { $bdd = null; }
