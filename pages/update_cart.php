<?php
session_start();
require_once '../includes/db_connect.php';

// 基础安全检查
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = $_GET['id'] ?? null;
$action  = $_GET['action'] ?? 'update'; // 默认为更新数量
$new_qty = $_GET['quantity'] ?? null;

if (!$cart_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing item ID']);
    exit();
}

// 逻辑分流
if ($action === 'delete') {
    // 执行删除
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$cart_id, $user_id])) {
        echo json_encode(['status' => 'success', 'message' => 'Item removed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
    }
} else {
    // 执行更新数量
    if ($new_qty > 0) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$new_qty, $cart_id, $user_id])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
        }
    }
}