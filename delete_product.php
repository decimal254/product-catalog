<?php
session_start();

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=shop_db;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$_GET['id']]);
}

header('Location: index.php');
exit;
