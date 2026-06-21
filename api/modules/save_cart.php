<?php
session_start();

if (empty($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}

$cart = json_decode($_POST['cart'] ?? '[]', true);
$_SESSION['pos_cart'] = $cart;

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>