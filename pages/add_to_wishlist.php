<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['product_id'] ?? 0;

// 检查是否已存在
$check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$check->execute([$user_id, $product_id]);

if ($check->fetch()) {
    echo json_encode(['status' => 'info', 'message' => 'Already in wishlist']);
} else {
    $ins = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $ins->execute([$user_id, $product_id]);
    echo json_encode(['status' => 'success', 'message' => 'Added to wishlist!']);
}