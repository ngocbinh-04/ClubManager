
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];
$thamGiaId = (int) ($_GET['id'] ?? 0);
$clbId = (int) ($_GET['clb_id'] ?? 0);

function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($thamGiaId <= 0 || $clbId <= 0) {
    http_response_code(400);
    exit('Thông tin không hợp lệ.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* Kiểm tra chủ sở hữu CLB */
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
    exit('Bạn không có quyền quản lý câu lạc bộ này.');
}

/* Lấy thành viên thuộc đúng CLB */
$stmtTV = $pdo->prepare("
    SELECT
        tg.id,
        tg.nguoi_dung_id,
        tg.vai_tro,
        tg.trang_thai,
        nd.ho_ten,
        nd.ten_dang_nhap,
        nd.email
    FROM tham_gia_clb tg
    INNER JOIN nguoi_dung nd
        ON nd.id = tg.nguoi_dung_id
    WHERE tg.id = :tham_gia_id
      AND tg.cau_lac_bo_id = :clb_id
    LIMIT 1
");

$stmtTV->execute([
    'tham_gia_id' => $thamGiaId,
    'clb_id' => $clbId
]);

$thanhVien = $stmtTV->fetch();

if (!$thanhVien) {
    http_response_code(404);
    exit('Không tìm thấy thành viên trong CLB này.');
}

$laChuClb = (int)$thanhVien['nguoi_dung_id'] === $userId;

$error = '';
$vaiTro = $thanhVien['vai_tro'];
$trangThai = $thanhVien['trang_thai'];

$vaiTroHopLe = [
    'chu_nhiem',
    'pho_chu_nhiem',
    'thanh_vien'
];

$trangThaiHopLe = [
    'hoat_dong',
    'tam_dung',
    'da_roi'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $error = 'Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.';
    } elseif ($laChuClb) {
        /* Người tạo không được thay đổi quyền hoặc trạng thái của mình */
        $error = 'Không thể chỉnh sửa quyền của người tạo CLB.';
    } else {
        $vaiTro = $_POST['vai_tro'] ?? '';
        $trangThai = $_POST['trang_thai'] ?? '';

        if (
            !in_array($vaiTro, $vaiTroHopLe, true) ||
            !in_array($trangThai, $trangThaiHopLe, true)
        ) {
            $error = 'Vai trò hoặc trạng thái không hợp lệ.';
        } else {
            try {
                $stmtUpdate = $pdo->prepare("
                    UPDATE tham_gia_clb
                    SET vai_tro = :vai_tro,
                        trang_thai = :trang_thai
                    WHERE id = :tham_gia_id
                      AND cau_lac_bo_id = :clb_id
                      AND nguoi_dung_id <> :user_id
                ");

                $stmtUpdate->execute([
                    'vai_tro' => $vaiTro,
                    'trang_thai' => $trangThai,
                    'tham_gia_id' => $thamGiaId,
                    'clb_id' => $clbId,
                    'user_id' => $userId
                ]);

                header(
                    'Location: thanh-vien.php?clb_id=' .
                    $clbId .
                    '&success=sua'
                );
                exit;

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Không thể cập nhật. Vui lòng thử lại.';
            }
        }
    }
}

$pageTitle = 'Cập nhật thành viên';
$activePage = 'thanh-vien';

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <div>
                <h1>Cập nhật thành viên</h1>
                <p>
                    CLB: <?= e($clb['ten_clb']) ?> —
                    <?= e($thanhVien['ho_ten']) ?>
                </p>
            </div>
        </section>

        <?php if ($error !== ''): ?>
            <div class="alert-error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <p>
                <strong>Họ tên:</strong>
                <?= e($thanhVien['ho_ten']) ?>
            </p>

            <p>
                <strong>Tên đăng nhập:</strong>
                <?= e($thanhVien['ten_dang_nhap']) ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= e($thanhVien['email']) ?>
            </p>

            <?php if ($laChuClb): ?>
                <div class="alert-success">
                    Đây là người tạo câu lạc bộ.
                    Quyền quản trị và trạng thái hoạt động
                    được bảo vệ, không thể sửa tại đây.
                </div>

                <a
                    class="btn"
                    href="thanh-vien.php?clb_id=<?= $clbId ?>"
                >
                    Quay lại
                </a>
            <?php else: ?>
                <form method="POST"
                      action="sua-thanh-vien.php?id=<?= $thamGiaId ?>&clb_id=<?= $clbId ?>">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['csrf_token']) ?>"
                    >

                    <div class="form-group">
                        <label for="vai_tro">Vai trò</label>

                        <select
                            name="vai_tro"
                            id="vai_tro"
                            required
                        >
                            <option
                                value="thanh_vien"
                                <?= $vaiTro === 'thanh_vien' ? 'selected' : '' ?>
                            >
                                Thành viên
                            </option>

                            <option
                                value="pho_chu_nhiem"
                                <?= $vaiTro === 'pho_chu_nhiem' ? 'selected' : '' ?>
                            >
                                Phó chủ nhiệm
                            </option>

                            <option
                                value="chu_nhiem"
                                <?= $vaiTro === 'chu_nhiem' ? 'selected' : '' ?>
                            >
                                Chủ nhiệm
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="trang_thai">Trạng thái</label>

                        <select
                            name="trang_thai"
                            id="trang_thai"
                            required
                        >
                            <option
                                value="hoat_dong"
                                <?= $trangThai === 'hoat_dong' ? 'selected' : '' ?>
                            >
                                Hoạt động
                            </option>

                            <option
                                value="tam_dung"
                                <?= $trangThai === 'tam_dung' ? 'selected' : '' ?>
                            >
                                Tạm dừng
                            </option>

                            <option
                                value="da_roi"
                                <?= $trangThai === 'da_roi' ? 'selected' : '' ?>
                            >
                                Đã rời
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Lưu thay đổi
                    </button>

                    <a
                        class="btn"
                        href="thanh-vien.php?clb_id=<?= $clbId ?>"
                    >
                        Hủy
                    </a>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
