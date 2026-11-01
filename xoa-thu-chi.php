<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/kiem-tra-thu-chi.php';

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Chỉ cho phép phương thức POST.');
}

if (!csrfThuChiHopLe($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Phiên biểu mẫu không hợp lệ.');
}

$clbId = (int) ($_POST['clb_id'] ?? 0);
$giaoDichId = (int) ($_POST['id'] ?? 0);

if ($clbId <= 0 || $giaoDichId <= 0) {
    http_response_code(400);
    exit('Thông tin giao dịch không hợp lệ.');
}

/* Người đăng nhập phải là người tạo CLB */
$stmtClb = $pdo->prepare("
    SELECT id
    FROM cau_lac_bo
    WHERE id = :clb_id
      AND nguoi_tao_id = :user_id
    LIMIT 1
");

$stmtClb->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);

if (!$stmtClb->fetch()) {
    http_response_code(403);
    exit('Bạn không có quyền xóa giao dịch này.');
}

/* Kiểm tra giao dịch có thuộc CLB không */
$stmtCheck = $pdo->prepare("
    SELECT id
    FROM thu_chi
    WHERE id = :id
      AND cau_lac_bo_id = :clb_id
    LIMIT 1
");

$stmtCheck->execute([
    'id' => $giaoDichId,
    'clb_id' => $clbId
]);

if (!$stmtCheck->fetch()) {
    http_response_code(404);
    exit('Không tìm thấy giao dịch.');
}

try {
    $stmtDelete = $pdo->prepare("
        DELETE FROM thu_chi
        WHERE id = :id
          AND cau_lac_bo_id = :clb_id
    ");

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

} catch (PDOException $ex) {
    error_log($ex->getMessage());

    http_response_code(500);
    exit('Không thể xóa giao dịch.');
}
