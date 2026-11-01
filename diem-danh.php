
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Điểm danh';
$activePage = 'diem-danh';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function hienThiVaiTro($role) {
    $roles = [
        'quan_tri' => 'Quản trị',
        'chu_nhiem' => 'Chủ nhiệm',
        'pho_chu_nhiem' => 'Phó chủ nhiệm',
        'thanh_vien' => 'Thành viên'
    ];

    return $roles[$role] ?? 'Thành viên';
}

/* Lấy các CLB do tài khoản hiện tại tạo */
$stmtClb = $pdo->prepare("
    SELECT id, ten_clb
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
    ORDER BY ten_clb ASC
");

$stmtClb->execute([
    'user_id' => $userId
]);

$danhSachClb = $stmtClb->fetchAll();

$clbId = (int) ($_GET['clb_id'] ?? 0);

if ($clbId <= 0 && !empty($danhSachClb)) {
    $clbId = (int) $danhSachClb[0]['id'];
}

/* Kiểm tra quyền trên CLB đang chọn */
$clbHienTai = null;

foreach ($danhSachClb as $clb) {
    if ((int)$clb['id'] === $clbId) {
        $clbHienTai = $clb;
        break;
    }
}

if ($clbId > 0 && !$clbHienTai) {
    http_response_code(403);
    exit('Bạn không có quyền điểm danh CLB này.');
}

/* Danh sách sự kiện */
$danhSachSuKien = [];

if ($clbHienTai) {
    $stmtSuKien = $pdo->prepare("
        SELECT id, ten_su_kien, ngay_to_chuc
        FROM su_kien
        WHERE cau_lac_bo_id = :clb_id
        ORDER BY ngay_to_chuc DESC, id DESC
    ");

    $stmtSuKien->execute([
        'clb_id' => $clbId
    ]);

    $danhSachSuKien = $stmtSuKien->fetchAll();
}

/* Xác định sự kiện */
$suKienId = (int) ($_GET['su_kien_id'] ?? 0);

if ($suKienId <= 0 && !empty($danhSachSuKien)) {
    $suKienId = (int) $danhSachSuKien[0]['id'];
}

$suKienHienTai = null;

foreach ($danhSachSuKien as $sk) {
    if ((int)$sk['id'] === $suKienId) {
        $suKienHienTai = $sk;
        break;
    }
}

if ($suKienId > 0 && !$suKienHienTai) {
    http_response_code(400);
    exit('Sự kiện không thuộc câu lạc bộ đã chọn.');
}

/* Lấy thành viên hoạt động và kết quả điểm danh */
$danhSachThanhVien = [];

if ($clbHienTai && $suKienHienTai) {
    $stmtTV = $pdo->prepare("
        SELECT
            nd.id AS nguoi_dung_id,
            nd.ho_ten,
            tg.vai_tro,
            dd.trang_thai AS diem_danh_trang_thai,
            dd.ghi_chu

        FROM tham_gia_clb tg

        INNER JOIN nguoi_dung nd
            ON nd.id = tg.nguoi_dung_id

        LEFT JOIN diem_danh dd
            ON dd.nguoi_dung_id = nd.id
           AND dd.su_kien_id = :su_kien_id

        WHERE tg.cau_lac_bo_id = :clb_id
          AND tg.trang_thai = 'hoat_dong'

        ORDER BY nd.ho_ten ASC
    ");

    $stmtTV->execute([
        'su_kien_id' => $suKienId,
        'clb_id' => $clbId
    ]);

    $danhSachThanhVien = $stmtTV->fetchAll();
}

/* Tổng kết điểm danh */
$tongThanhVien = count($danhSachThanhVien);
$soCoMat = 0;
$soVang = 0;
$soChuaDiemDanh = 0;

foreach ($danhSachThanhVien as $tv) {
    if ($tv['diem_danh_trang_thai'] === 'co_mat') {
        $soCoMat++;
    } elseif ($tv['diem_danh_trang_thai'] === 'vang') {
        $soVang++;
    } else {
        $soChuaDiemDanh++;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Điểm danh sự kiện</h1>
                <p>
                    Theo dõi tình trạng tham gia của thành viên
                    trong từng sự kiện.
                </p>
            </div>
        </section>

        <?php if (($_GET['success'] ?? '') === '1'): ?>
            <div class="alert-success">
                Đã lưu kết quả điểm danh thành công.
            </div>
        <?php endif; ?>

        <?php if (empty($danhSachClb)): ?>

            <div class="empty-state">
                <p>Bạn chưa quản lý câu lạc bộ nào.</p>

                <a href="them-clb.php" class="btn btn-primary">
                    + Tạo câu lạc bộ
                </a>
            </div>

        <?php else: ?>

            <!-- CHỌN CÂU LẠC BỘ -->
            <div class="card">

                <form
                    action="diem-danh.php"
                    method="GET"
                >
                    <div class="form-group">
                        <label for="clb_id">
                            Chọn câu lạc bộ
                        </label>

                        <select id="clb_id" name="clb_id">
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

                <?php if (!empty($danhSachSuKien)): ?>

                    <form
                        action="diem-danh.php"
                        method="GET"
                        style="margin-top:20px;"
                    >
                        <input
                            type="hidden"
                            name="clb_id"
                            value="<?= $clbId ?>"
                        >

                        <div class="form-group">
                            <label for="su_kien_id">
                                Chọn sự kiện
                            </label>

                            <select
                                id="su_kien_id"
                                name="su_kien_id"
                            >
                                <?php foreach ($danhSachSuKien as $sk): ?>
                                    <option
                                        value="<?= (int)$sk['id'] ?>"
                                        <?= $suKienId === (int)$sk['id']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= e($sk['ten_su_kien']) ?>
                                        -
                                        <?= e($sk['ngay_to_chuc']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Xem điểm danh
                        </button>
                    </form>

                <?php endif; ?>
            </div>

            <?php if (!$suKienHienTai): ?>

                <div class="empty-state" style="margin-top:20px;">
                    <p>Câu lạc bộ chưa có sự kiện nào.</p>

                    <a
                        href="them-su-kien.php?clb_id=<?= $clbId ?>"
                        class="btn btn-primary"
                    >
                        + Tạo sự kiện
                    </a>
                </div>

            <?php else: ?>

                <section class="content-section">

                    <div class="section-header">
                        <div>
                            <h2><?= e($suKienHienTai['ten_su_kien']) ?></h2>

                            <p>
                                Ngày tổ chức:
                                <?= e($suKienHienTai['ngay_to_chuc']) ?>
                            </p>
                        </div>
                    </div>

                    <div class="dashboard-cards">

                        <div class="stat-card">
                            <h3><?= $tongThanhVien ?></h3>
                            <p>Tổng thành viên hoạt động</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $soCoMat ?></h3>
                            <p>Có mặt</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $soVang ?></h3>
                            <p>Vắng</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $soChuaDiemDanh ?></h3>
                            <p>Chưa điểm danh</p>
                        </div>

                    </div>

                    <?php if (!empty($danhSachThanhVien)): ?>

                        <form
                            method="POST"
                            action="luu-diem-danh.php"
                        >
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

                            <input
                                type="hidden"
                                name="su_kien_id"
                                value="<?= $suKienId ?>"
                            >

                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Họ tên</th>
                                            <th>Vai trò</th>
                                            <th>Trạng thái điểm danh</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php foreach ($danhSachThanhVien as $tv): ?>

                                            <?php
                                            $id = (int)$tv['nguoi_dung_id'];

                                            $trangThai = (
                                                $tv['diem_danh_trang_thai']
                                                ?? ''
                                            );
                                            ?>

                                            <tr>
                                                <td>
                                                    <?= e($tv['ho_ten']) ?>
                                                </td>

                                                <td>
                                                    <?= e(hienThiVaiTro($tv['vai_tro'])) ?>
                                                </td>

                                                <td>
                                                    <select
                                                        name="trang_thai[<?= $id ?>]"
                                                    >
                                                        <option
                                                            value=""
                                                            <?= $trangThai === ''
                                                                ? 'selected'
                                                                : '' ?>
                                                        >
                                                            -- Chưa điểm danh --
                                                        </option>

                                                        <option
                                                            value="co_mat"
                                                            <?= $trangThai === 'co_mat'
                                                                ? 'selected'
                                                                : '' ?>
                                                        >
                                                            Có mặt
                                                        </option>

                                                        <option
                                                            value="vang"
                                                            <?= $trangThai === 'vang'
                                                                ? 'selected'
                                                                : '' ?>
                                                        >
                                                            Vắng
                                                        </option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <input
                                                        type="text"
                                                        name="ghi_chu[<?= $id ?>]"
                                                        value="<?= e($tv['ghi_chu']) ?>"
                                                        maxlength="255"
                                                        placeholder="Ghi chú..."
                                                    >
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>

                            <p style="margin-top:12px;color:#6d7f79;">
                                Chỉ những thành viên được chọn
                                Có mặt hoặc Vắng mới được lưu.
                                Chưa điểm danh sẽ không tính là Có mặt.
                            </p>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                style="margin-top:15px;"
                            >
                                Lưu điểm danh
                            </button>
                        </form>

                    <?php else: ?>

                        <div class="empty-state">
                            <p>
                                Chưa có thành viên hoạt động
                                để điểm danh.
                            </p>
                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        <?php endif; ?>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
