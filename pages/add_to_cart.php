<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

if (isset($_GET['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_GET['product_id'];

    // 检查购物车里是否已经有这个商品
    $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $check->execute([$user_id, $product_id]);
    $item = $check->fetch();

    if ($item) {
        // 如果有，数量 +1
        $update = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $update->execute([$item['id']]);
    } else {
        // 如果没有，新增一行
        $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->execute([$user_id, $product_id]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Added to cart!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
}