
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Phương thức không hợp lệ.');
}

/* Kiểm tra CSRF */
$sessionToken = $_SESSION['csrf_token'] ?? '';
$postToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($sessionToken) ||
    !is_string($postToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $postToken)
) {
    http_response_code(403);
    exit('Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.');
}

$clbId = (int) ($_POST['clb_id'] ?? 0);
$suKienId = (int) ($_POST['su_kien_id'] ?? 0);

$trangThai = $_POST['trang_thai'] ?? [];
$ghiChu = $_POST['ghi_chu'] ?? [];

if (
    $clbId <= 0 ||
    $suKienId <= 0 ||
    !is_array($trangThai) ||
    !is_array($ghiChu)
) {
    http_response_code(400);
    exit('Dữ liệu điểm danh không hợp lệ.');
}

/* Kiểm tra người đăng nhập sở hữu CLB */
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
    exit('Bạn không có quyền điểm danh câu lạc bộ này.');
}

/* Kiểm tra sự kiện thuộc đúng CLB */
$stmtSuKien = $pdo->prepare("
    SELECT id
    FROM su_kien
    WHERE id = :su_kien_id
      AND cau_lac_bo_id = :clb_id
    LIMIT 1
");

$stmtSuKien->execute([
    'su_kien_id' => $suKienId,
    'clb_id' => $clbId
]);

if (!$stmtSuKien->fetch()) {
    http_response_code(400);
    exit('Sự kiện không hợp lệ.');
}

/* Lấy danh sách người được phép điểm danh */
$stmtThanhVien = $pdo->prepare("
    SELECT nguoi_dung_id
    FROM tham_gia_clb
    WHERE cau_lac_bo_id = :clb_id
      AND trang_thai = 'hoat_dong'
");

$stmtThanhVien->execute([
    'clb_id' => $clbId
]);

$thanhVienHopLe = array_fill_keys(
    array_map('intval', $stmtThanhVien->fetchAll(PDO::FETCH_COLUMN)),
    true
);

/* Lệnh thêm hoặc cập nhật điểm danh */
$stmtLuu = $pdo->prepare("
    INSERT INTO diem_danh
    (
        su_kien_id,
        nguoi_dung_id,
        trang_thai,
        ghi_chu
    )
    VALUES
    (
        :su_kien_id,
        :nguoi_dung_id,
        :trang_thai,
        :ghi_chu
    )
    ON DUPLICATE KEY UPDATE
        trang_thai = VALUES(trang_thai),
        ghi_chu = VALUES(ghi_chu)
");

try {
    $pdo->beginTransaction();

    foreach ($trangThai as $id => $tt) {
        if (!ctype_digit((string)$id) || (int)$id <= 0) {
            throw new RuntimeException(
                'Mã thành viên không hợp lệ.'
            );
        }

        $nguoiDungId = (int) $id;

        if (!isset($thanhVienHopLe[$nguoiDungId])) {
            throw new RuntimeException(
                'Có thành viên không thuộc câu lạc bộ.'
            );
        }

        if (!is_string($tt)) {
            throw new RuntimeException(
                'Trạng thái điểm danh không hợp lệ.'
            );
        }

        /* Để trống nghĩa là chưa điểm danh */
        if ($tt === '') {
            continue;
        }

        if (!in_array($tt, ['co_mat', 'vang'], true)) {
            throw new RuntimeException(
                'Giá trị điểm danh không hợp lệ.'
            );
        }

        $note = $ghiChu[$id] ?? '';

        if (!is_string($note)) {
            throw new RuntimeException(
                'Ghi chú không hợp lệ.'
            );
        }

        $note = trim($note);

        if (mb_strlen($note) > 255) {
            throw new RuntimeException(
                'Ghi chú không được vượt quá 255 ký tự.'
            );
        }

        $stmtLuu->execute([
            'su_kien_id' => $suKienId,
            'nguoi_dung_id' => $nguoiDungId,
            'trang_thai' => $tt,
            'ghi_chu' => $note
        ]);
    }

    $pdo->commit();

    header(
        'Location: diem-danh.php?clb_id=' .
        $clbId .
        '&su_kien_id=' .
        $suKienId .
        '&success=1'
    );
    exit;

} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    exit($e->getMessage());

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);
    exit('Không thể lưu điểm danh. Vui lòng thử lại.');
}
