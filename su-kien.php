<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Sự kiện';
$activePage = 'su-kien';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* CLB tài khoản quản lý hoặc đang tham gia */
$stmtClb = $pdo->prepare("
    SELECT
        clb.id,
        clb.ten_clb,
        clb.nguoi_tao_id,
        CASE WHEN clb.nguoi_tao_id = :owner_check
             THEN 1 ELSE 0 END AS la_quan_ly
    FROM cau_lac_bo clb
    WHERE clb.nguoi_tao_id = :owner_id
       OR EXISTS (
           SELECT 1
           FROM tham_gia_clb tg
           WHERE tg.cau_lac_bo_id = clb.id
             AND tg.nguoi_dung_id = :member_id
             AND tg.trang_thai = 'hoat_dong'
       )
    ORDER BY clb.ten_clb ASC
");

$stmtClb->execute([
    'owner_check' => $userId,
    'owner_id' => $userId,
    'member_id' => $userId
]);

$danhSachClb = $stmtClb->fetchAll();

$clbId = (int) ($_GET['clb_id'] ?? 0);

if ($clbId <= 0 && $danhSachClb) {
    $clbId = (int) $danhSachClb[0]['id'];
}

$clbHienTai = null;
$laQuanLy = false;

foreach ($danhSachClb as $clb) {
    if ((int) $clb['id'] === $clbId) {
        $clbHienTai = $clb;
        $laQuanLy = (bool) $clb['la_quan_ly'];
        break;
    }
}

if ($clbId > 0 && !$clbHienTai) {
    http_response_code(403);
    exit('Bạn không có quyền xem sự kiện của CLB này.');
}

/* Danh sách sự kiện */
$danhSachSuKien = [];

if ($clbHienTai) {
    $stmtEvent = $pdo->prepare("
        SELECT
            id, ten_su_kien, ngay_to_chuc,
            gio_bat_dau, gio_ket_thuc,
            dia_diem, trang_thai
        FROM su_kien
        WHERE cau_lac_bo_id = :clb_id
        ORDER BY ngay_to_chuc DESC, id DESC
    ");

    $stmtEvent->execute(['clb_id' => $clbId]);
    $danhSachSuKien = $stmtEvent->fetchAll();
}

$successMessages = [
    'them' => 'Thêm sự kiện thành công.',
    'sua' => 'Cập nhật sự kiện thành công.',
    'xoa' => 'Xóa sự kiện thành công.'
];

$statusNames = [
    'sap_dien_ra' => 'Sắp diễn ra',
    'dang_dien_ra' => 'Đang diễn ra',
    'da_ket_thuc' => 'Đã kết thúc'
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <div>
                <h1>Sự kiện</h1>
                <p>Xem và quản lý sự kiện của các câu lạc bộ.</p>
            </div>

            <?php if ($laQuanLy): ?>
                <a
                    href="them-su-kien.php?clb_id=<?= $clbId ?>"
                    class="btn btn-primary"
                >
                    + Thêm sự kiện
                </a>
            <?php endif; ?>
        </section>

        <?php
        $success = $_GET['success'] ?? '';
        if (isset($successMessages[$success])):
        ?>
            <div class="alert-success">
                <?= e($successMessages[$success]) ?>
            </div>
        <?php endif; ?>

        <?php if (!$danhSachClb): ?>
            <div class="empty-state">
                <p>Bạn chưa quản lý hoặc tham gia câu lạc bộ nào.</p>
                <a href="index.php" class="btn">Về trang chủ</a>
            </div>

        <?php else: ?>

            <div class="card">
                <form method="GET" action="su-kien.php">
                    <div class="form-group">
                        <label for="clb_id">Chọn câu lạc bộ</label>

                        <select name="clb_id" id="clb_id">
                            <?php foreach ($danhSachClb as $clb): ?>
                                <option
                                    value="<?= (int)$clb['id'] ?>"
                                    <?= $clbId === (int)$clb['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= e($clb['ten_clb']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Xem sự kiện
                    </button>
                </form>
            </div>

            <?php if ($clbHienTai): ?>

                <section class="content-section">
                    <div class="section-header">
                        <div>
                            <h2><?= e($clbHienTai['ten_clb']) ?></h2>
                            <p>
                                Tổng số:
                                <?= count($danhSachSuKien) ?> sự kiện
                            </p>
                        </div>
                    </div>

                    <?php if ($danhSachSuKien): ?>

                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tên sự kiện</th>
                                        <th>Ngày</th>
                                        <th>Thời gian</th>
                                        <th>Địa điểm</th>
                                        <th>Trạng thái</th>
                                        <?php if ($laQuanLy): ?>
                                            <th>Thao tác</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($danhSachSuKien as $sk): ?>
                                        <tr>
                                            <td>
                                                <?= e($sk['ten_su_kien']) ?>
                                            </td>

                                            <td>
                                                <?= e($sk['ngay_to_chuc']) ?>
                                            </td>

                                            <td>
                                                <?php
                                                $batDau = $sk['gio_bat_dau']
                                                    ? substr($sk['gio_bat_dau'], 0, 5)
                                                    : '';
                                                $ketThuc = $sk['gio_ket_thuc']
                                                    ? substr($sk['gio_ket_thuc'], 0, 5)
                                                    : '';

                                                echo e(
                                                    ($batDau || $ketThuc)
                                                        ? ($batDau ?: '--:--')
                                                            . ' - '
                                                            . ($ketThuc ?: '--:--')
                                                        : 'Chưa cập nhật'
                                                );
                                                ?>
                                            </td>

                                            <td><?= e($sk['dia_diem']) ?></td>

                                            <td>
                                                <span class="badge">
                                                    <?= e(
                                                        $statusNames[$sk['trang_thai']]
                                                        ?? $sk['trang_thai']
                                                    ) ?>
                                                </span>
                                            </td>

                                            <?php if ($laQuanLy): ?>
                                                <td>
                                                    <a
                                                        href="sua-su-kien.php?id=<?= (int)$sk['id'] ?>&clb_id=<?= $clbId ?>"
                                                        class="btn"
                                                    >
                                                        Sửa
                                                    </a>

                                                    <form
                                                        method="POST"
                                                        action="xoa-su-kien.php"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Xóa sự kiện này sẽ xóa cả dữ liệu điểm danh liên quan. Bạn chắc chắn muốn tiếp tục?');"
                                                    >
                                                        <input
                                                            type="hidden"
                                                            name="csrf_token"
                                                            value="<?= e($_SESSION['csrf_token']) ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?= (int)$sk['id'] ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="clb_id"
                                                            value="<?= $clbId ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn"
                                                        >
                                                            Xóa
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <div class="empty-state">
                            <p>CLB chưa có sự kiện nào.</p>

                            <?php if ($laQuanLy): ?>
                                <a
                                    href="them-su-kien.php?clb_id=<?= $clbId ?>"
                                    class="btn btn-primary"
                                >
                                    + Tạo sự kiện đầu tiên
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </section>
            <?php endif; ?>

        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
