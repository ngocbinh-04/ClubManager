
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/kiem-tra-thu-chi.php';

$userId = (int) $_SESSION['user_id'];
$clbId = (int) ($_GET['clb_id'] ?? 0);

taoCsrfThuChi();

/* Chỉ cho phép chủ CLB thêm giao dịch */
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
    exit('Bạn không có quyền quản lý tài chính CLB này.');
}

$data = [
    'loai' => 'thu',
    'noi_dung' => '',
    'so_tien' => '',
    'ngay_giao_dich' => date('Y-m-d'),
    'ghi_chu' => ''
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
            $stmt = $pdo->prepare("
                INSERT INTO thu_chi (
                    cau_lac_bo_id,
                    loai,
                    noi_dung,
                    so_tien,
                    ngay_giao_dich,
                    nguoi_thuc_hien_id,
                    ghi_chu
                )
                VALUES (
                    :clb_id,
                    :loai,
                    :noi_dung,
                    :so_tien,
                    :ngay_giao_dich,
                    :user_id,
                    :ghi_chu
                )
            ");

            $stmt->execute([
                'clb_id' => $clbId,
                'loai' => $data['loai'],
                'noi_dung' => $data['noi_dung'],
                'so_tien' => $data['so_tien'],
                'ngay_giao_dich' => $data['ngay_giao_dich'],
                'user_id' => $userId,
                'ghi_chu' => $data['ghi_chu']
            ]);

            header(
                'Location: thu-chi.php?clb_id='
                . $clbId
                . '&success=them'
            );
            exit;

        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors[] = 'Không thể lưu giao dịch.';
        }
    }
}

$pageTitle = 'Thêm giao dịch';
$activePage = 'thu-chi';
$nutLuu = 'Thêm giao dịch';

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <div>
                <h1>Thêm giao dịch</h1>
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
