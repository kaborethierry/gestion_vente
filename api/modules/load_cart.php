<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}

$cart = $_SESSION['pos_cart'] ?? [];

header('Content-Type: application/json');
echo json_encode(['success' => true, 'cart' => $cart]);
?>