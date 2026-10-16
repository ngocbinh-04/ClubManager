<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: thu-chi.php');
    exit;
}


$giaoDichId = (int) ($_POST['id'] ?? 0);
$clbId = (int) ($_POST['clb_id'] ?? 0);


/* Kiểm tra CLB thuộc user */
$sqlClb = "
    SELECT id
    FROM cau_lac_bo
    WHERE id = :clb_id
      AND nguoi_tao_id = :user_id
";

$stmtClb = $pdo->prepare($sqlClb);

$stmtClb->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);

if (!$stmtClb->fetch()) {

    header('Location: thu-chi.php');
    exit;
}


/* Xóa giao dịch */
$sqlDelete = "
    DELETE FROM thu_chi
    WHERE id = :id
      AND cau_lac_bo_id = :clb_id
";

$stmtDelete = $pdo->prepare($sqlDelete);

$stmtDelete->execute([
    'id' => $giaoDichId,
    'clb_id' => $clbId
]);


header(
    'Location: thu-chi.php?clb_id='
    . $clbId
    . '&success=xoa'
);

exit;