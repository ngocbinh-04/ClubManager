<?php

require_once 'auth.php';
require_once __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'] ?? 0;

/* Kiểm tra user có quản lý ít nhất 1 CLB hay không */
$sql = "
    SELECT COUNT(*)
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'user_id' => $userId
]);

$soClbQuanLy = (int) $stmt->fetchColumn();

if ($soClbQuanLy <= 0) {
    header('Location: index.php');
    exit;
}