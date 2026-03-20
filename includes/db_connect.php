<?php
// includes/db_connect.php - Database connection using PDO

$host = 'localhost';
$dbname = 'grokcamping';  // Change to your actual database name
$db_user = 'root';  // 更改变量名，避免与页面变量冲突
$db_password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $db_user,
        $db_password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // 在生产环境中不要显示详细的错误信息
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>