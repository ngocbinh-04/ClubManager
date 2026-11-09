
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Xác nhận xóa câu lạc bộ';
$activePage = 'clb-quan-tri';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
 * GET dùng để mở trang xác nhận.
 * POST dùng để gửi yêu cầu xóa.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clbId = (int) ($_POST['clb_id'] ?? 0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $clbId = (int) ($_GET['id'] ?? 0);
} else {
    http_response_code(405);
    exit('Phương thức không được hỗ trợ.');
}

if ($clbId <= 0) {
    http_response_code(400);
    exit('Mã câu lạc bộ không hợp lệ.');
}

/* Xác nhận người dùng sở hữu CLB */
$stmtClb = $pdo->prepare("
    SELECT id, ten_clb, linh_vuc
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
    exit('Bạn không có quyền xóa câu lạc bộ này.');
}

$error = '';

/* Thống kê số dữ liệu có thể bị mất */
$stmtCount = $pdo->prepare("
    SELECT
        (SELECT COUNT(*)
         FROM tham_gia_clb
         WHERE cau_lac_bo_id = :clb_members) AS thanh_vien,

        (SELECT COUNT(*)
         FROM su_kien
         WHERE cau_lac_bo_id = :clb_events) AS su_kien,

        (SELECT COUNT(*)
         FROM thu_chi
         WHERE cau_lac_bo_id = :clb_finance) AS giao_dich,

        (SELECT COUNT(*)
         FROM diem_danh dd
         INNER JOIN su_kien sk
             ON sk.id = dd.su_kien_id
         WHERE sk.cau_lac_bo_id = :clb_attendance) AS diem_danh
");

$stmtCount->execute([
    'clb_members' => $clbId,
    'clb_events' => $clbId,
    'clb_finance' => $clbId,
    'clb_attendance' => $clbId
]);

$soLieu = $stmtCount->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postToken = $_POST['csrf_token'] ?? '';
    $tenXacNhan = $_POST['ten_xac_nhan'] ?? '';

    if (
        !is_string($sessionToken) ||
        !is_string($postToken) ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $postToken)
    ) {
        $error = 'Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.';

    } elseif (
        !is_string($tenXacNhan) ||
        trim($tenXacNhan) !== $clb['ten_clb']
    ) {
        $error = 'Tên CLB xác nhận không chính xác.';

    } else {

        try {
            /*
             * Xóa trong transaction.
             * Nếu vi phạm khóa ngoại hoặc xảy ra lỗi,
             * rollback và giữ nguyên dữ liệu.
             */
            $pdo->beginTransaction();

            /* Khóa bản ghi CLB và kiểm tra quyền lại */
            $stmtLock = $pdo->prepare("
                SELECT id, ten_clb
                FROM cau_lac_bo
                WHERE id = :clb_id
                  AND nguoi_tao_id = :user_id
                FOR UPDATE
            ");

            $stmtLock->execute([
                'clb_id' => $clbId,
                'user_id' => $userId
            ]);

            $clbLocked = $stmtLock->fetch();

            if (
                !$clbLocked ||
                $clbLocked['ten_clb'] !== trim($tenXacNhan)
            ) {
                throw new RuntimeException(
                    'CLB đã thay đổi hoặc bạn không còn quyền xóa.'
                );
            }

            $stmtDelete = $pdo->prepare("
                DELETE FROM cau_lac_bo
                WHERE id = :clb_id
                  AND nguoi_tao_id = :user_id
            ");

            $stmtDelete->execute([
                'clb_id' => $clbId,
                'user_id' => $userId
            ]);

            if ($stmtDelete->rowCount() !== 1) {
                throw new RuntimeException(
                    'Không thể xác nhận việc xóa CLB.'
                );
            }

            $pdo->commit();

            header('Location: index.php?success=xoa');
            exit;

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($ex->getMessage());

            $error = 'Không thể xóa CLB. Có thể còn dữ liệu liên kết '
                   . 'bị ràng buộc hoặc xảy ra lỗi hệ thống.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Xác nhận xóa câu lạc bộ</h1>
                <p>Kiểm tra kỹ thông tin trước khi thực hiện.</p>
            </div>
        </section>

        <?php if ($error !== ''): ?>
            <div class="alert-error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">

            <h2><?= e($clb['ten_clb']) ?></h2>

            <p>
                <strong>Lĩnh vực:</strong>
                <?= e($clb['linh_vuc']) ?>
            </p>

            <div class="alert-error">
                <strong>Cảnh báo:</strong>
                Thao tác xóa CLB không thể hoàn tác bằng giao diện.
                Các dữ liệu liên quan có thể bị xóa theo ràng buộc
                khóa ngoại trong MySQL.
            </div>

            <h3>Dữ liệu hiện đang liên kết với CLB</h3>

            <p>
                Thành viên:
                <strong><?= (int)$soLieu['thanh_vien'] ?></strong>
            </p>

            <p>
                Sự kiện:
                <strong><?= (int)$soLieu['su_kien'] ?></strong>
            </p>

            <p>
                Lượt điểm danh:
                <strong><?= (int)$soLieu['diem_danh'] ?></strong>
            </p>

            <p>
                Giao dịch thu–chi:
                <strong><?= (int)$soLieu['giao_dich'] ?></strong>
            </p>

            <hr>

            <form method="POST" action="xoa-clb.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >

                <input
                    type="hidden"
                    name="clb_id"
                    value="<?= $clbId ?>"
                >

                <div class="form-group">
                    <label for="ten_xac_nhan">
                        Nhập chính xác tên câu lạc bộ để xác nhận:
                        <strong><?= e($clb['ten_clb']) ?></strong>
                    </label>

                    <input
                        type="text"
                        id="ten_xac_nhan"
                        name="ten_xac_nhan"
                        placeholder="Nhập tên CLB"
                        autocomplete="off"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-danger">
                    Xác nhận xóa vĩnh viễn
                </button>

                <a href="clb-quan-tri.php" class="btn">
                    Hủy và quay lại
                </a>

            </form>

        </div>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
