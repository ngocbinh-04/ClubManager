<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/kiem-tra-thu-chi.php';

$userId = (int) $_SESSION['user_id'];
$clbId = (int) ($_GET['clb_id'] ?? 0);
$giaoDichId = (int) ($_GET['id'] ?? 0);

taoCsrfThuChi();

if ($clbId <= 0 || $giaoDichId <= 0) {
    http_response_code(400);
    exit('Thông tin giao dịch không hợp lệ.');
}

/* Kiểm tra quyền quản lý */
$stmtClb = $pdo->prepare("
    SELECT id, ten_clb
    FROM cau_lac_bo
    WHERE id = :clb_id
      AND nguoi_tao_id = :user_id
    LIMIT 1
");

$stmtClb->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);

$clb = $stmtClb->fetch();

if (!$clb) {
    http_response_code(403);
    exit('Bạn không có quyền quản lý CLB này.');
}

/* Giao dịch phải thuộc đúng CLB */
$stmtGD = $pdo->prepare("
    SELECT *
    FROM thu_chi
    WHERE id = :id
      AND cau_lac_bo_id = :clb_id
    LIMIT 1
");

$stmtGD->execute([
    'id' => $giaoDichId,
    'clb_id' => $clbId
]);

$giaoDich = $stmtGD->fetch();

if (!$giaoDich) {
    http_response_code(404);
    exit('Không tìm thấy giao dịch.');
}

$data = [
    'loai' => $giaoDich['loai'],
    'noi_dung' => $giaoDich['noi_dung'],
    'so_tien' => $giaoDich['so_tien'],
    'ngay_giao_dich' => $giaoDich['ngay_giao_dich'],
    'ghi_chu' => $giaoDich['ghi_chu'] ?? ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrfThuChiHopLe($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Phiên biểu mẫu không hợp lệ.';
    }

    $ketQua = kiemTraGiaoDich($_POST);
    $data = $ketQua['data'];
    $errors = array_merge($errors, $ketQua['errors']);

    if (!$errors) {
        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE thu_chi
                SET
                    loai = :loai,
                    noi_dung = :noi_dung,
                    so_tien = :so_tien,
                    ngay_giao_dich = :ngay_giao_dich,
                    ghi_chu = :ghi_chu
                WHERE id = :id
                  AND cau_lac_bo_id = :clb_id
            ");

            $stmtUpdate->execute([
                'loai' => $data['loai'],
                'noi_dung' => $data['noi_dung'],
                'so_tien' => $data['so_tien'],
                'ngay_giao_dich' => $data['ngay_giao_dich'],
                'ghi_chu' => $data['ghi_chu'],
                'id' => $giaoDichId,
                'clb_id' => $clbId
            ]);

            header(
                'Location: thu-chi.php?clb_id='
                . $clbId
                . '&success=sua'
            );
            exit;

        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors[] = 'Không thể cập nhật giao dịch.';
        }
    }
}

$pageTitle = 'Sửa giao dịch';
$activePage = 'thu-chi';
$nutLuu = 'Lưu thay đổi';

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <div>
                <h1>Sửa giao dịch</h1>
                <p>
                    Câu lạc bộ:
                    <strong><?= hienThiHTML($clb['ten_clb']) ?></strong>
                </p>
            </div>
        </section>

        <?php foreach ($errors as $error): ?>
            <div class="alert-error">
                <?= hienThiHTML($error) ?>
            </div>
        <?php endforeach; ?>

        <?php require __DIR__ . '/includes/form-thu-chi.php'; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
