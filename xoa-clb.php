<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$clbId = (int) ($_GET['id'] ?? 0);

if ($clbId <= 0) {
    header('Location: clb-quan-tri.php');
    exit;
}


/* Kiểm tra CLB có thuộc quyền user không */
$sql = "
    SELECT id
    FROM cau_lac_bo
    WHERE id = :id
      AND nguoi_tao_id = :user_id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'id' => $clbId,
    'user_id' => $userId
]);

$clb = $stmt->fetch();

if (!$clb) {
    header('Location: clb-quan-tri.php');
    exit;
}


/*
 * Do schema của mình đã đặt foreign key CASCADE
 * cho các bảng liên quan phù hợp,
 * nên xóa CLB sẽ xóa dữ liệu liên quan tương ứng.
 */

$sqlDelete = "
    DELETE FROM cau_lac_bo
    WHERE id = :id
      AND nguoi_tao_id = :user_id
";

$stmtDelete = $pdo->prepare($sqlDelete);

$stmtDelete->execute([
    'id' => $clbId,
    'user_id' => $userId
]);

header('Location: clb-quan-tri.php?success=xoa');
exit;