
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Chỉ chấp nhận phương thức POST.');
}

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

$suKienId = (int) ($_POST['id'] ?? 0);
$clbId = (int) ($_POST['clb_id'] ?? 0);

if ($suKienId <= 0 || $clbId <= 0) {
    http_response_code(400);
    exit('Thông tin sự kiện không hợp lệ.');
}

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
    exit('Bạn không có quyền xóa sự kiện này.');
}

$stmtEvent = $pdo->prepare("
    SELECT id
    FROM su_kien
    WHERE id = :id
      AND cau_lac_bo_id = :clb_id
    LIMIT 1
");

$stmtEvent->execute([
    'id' => $suKienId,
    'clb_id' => $clbId
]);

if (!$stmtEvent->fetch()) {
    http_response_code(404);
    exit('Sự kiện không tồn tại.');
}

try {
    $stmtDelete = $pdo->prepare("
        DELETE FROM su_kien
        WHERE id = :id
          AND cau_lac_bo_id = :clb_id
    ");

    $stmtDelete->execute([
        'id' => $suKienId,
        'clb_id' => $clbId
    ]);

    header('Location: su-kien.php?clb_id=' . $clbId . '&success=xoa');
    exit;

} catch (PDOException $ex) {
    error_log($ex->getMessage());
    http_response_code(500);
    exit('Không thể xóa sự kiện.');
}
