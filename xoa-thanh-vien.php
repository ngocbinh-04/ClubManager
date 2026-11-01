<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

/* Chỉ cho phép POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Phương thức không hợp lệ.');
}

/* Xác thực CSRF */
$sessionToken = $_SESSION['csrf_token'] ?? '';
$postToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($sessionToken) ||
    !is_string($postToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $postToken)
) {
    http_response_code(403);
    exit('Phiên biểu mẫu không hợp lệ.');
}

$thamGiaId = (int) ($_POST['tham_gia_id'] ?? 0);
$clbId = (int) ($_POST['clb_id'] ?? 0);

if ($thamGiaId <= 0 || $clbId <= 0) {
    http_response_code(400);
    exit('Dữ liệu thành viên không hợp lệ.');
}

/* Xác định quyền sở hữu câu lạc bộ */
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
    exit('Bạn không có quyền quản lý CLB này.');
}

/* Xác định bản ghi thành viên */
$stmtTV = $pdo->prepare("
    SELECT id, nguoi_dung_id, trang_thai
    FROM tham_gia_clb
    WHERE id = :tham_gia_id
      AND cau_lac_bo_id = :clb_id
    LIMIT 1
");

$stmtTV->execute([
    'tham_gia_id' => $thamGiaId,
    'clb_id' => $clbId
]);

$thanhVien = $stmtTV->fetch();

if (!$thanhVien) {
    http_response_code(404);
    exit('Không tìm thấy thành viên.');
}

/* Không thể cho chính người tạo CLB rời CLB */
if ((int)$thanhVien['nguoi_dung_id'] === $userId) {
    http_response_code(403);
    exit('Không thể xóa người tạo câu lạc bộ.');
}

/* Đã rời rồi thì không cần cập nhật thêm */
if ($thanhVien['trang_thai'] === 'da_roi') {
    header(
        'Location: thanh-vien.php?clb_id=' . $clbId
    );
    exit;
}

try {
    /* Xóa mềm: giữ lại lịch sử */
    $stmtUpdate = $pdo->prepare("
        UPDATE tham_gia_clb
        SET trang_thai = 'da_roi'
        WHERE id = :tham_gia_id
          AND cau_lac_bo_id = :clb_id
          AND nguoi_dung_id <> :user_id
    ");

    $stmtUpdate->execute([
        'tham_gia_id' => $thamGiaId,
        'clb_id' => $clbId,
        'user_id' => $userId
    ]);

    header(
        'Location: thanh-vien.php?clb_id=' .
        $clbId .
        '&success=xoa'
    );
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    exit('Không thể cập nhật trạng thái thành viên.');
}
