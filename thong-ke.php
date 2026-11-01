<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Thống kê và báo cáo';
$activePage = 'thong-ke';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function tien($value) {
    return number_format((float)$value, 0, ',', '.') . ' đ';
}

/* Lấy các CLB người dùng quản lý */
$stmtClb = $pdo->prepare("
    SELECT id, ten_clb
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
    ORDER BY ten_clb ASC
");

$stmtClb->execute(['user_id' => $userId]);
$danhSachClb = $stmtClb->fetchAll();

$clbId = (int) ($_GET['clb_id'] ?? 0);

if ($clbId <= 0 && $danhSachClb) {
    $clbId = (int) $danhSachClb[0]['id'];
}

$clbHienTai = null;

foreach ($danhSachClb as $clb) {
    if ((int)$clb['id'] === $clbId) {
        $clbHienTai = $clb;
        break;
    }
}

if ($clbId > 0 && !$clbHienTai) {
    http_response_code(403);
    exit('Bạn không có quyền xem thống kê câu lạc bộ này.');
}

/* Giá trị ban đầu */
$tongThanhVien = 0;
$tongSuKien = 0;
$tongThu = 0;
$tongChi = 0;
$soDu = 0;
$tongLuotDiemDanh = 0;
$coMat = 0;
$vang = 0;
$tyLeCoMat = 0;
$thongKeThang = [];

if ($clbHienTai) {

    /* Thống kê thành viên */
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM tham_gia_clb
        WHERE cau_lac_bo_id = :clb_id
          AND trang_thai = 'hoat_dong'
    ");
    $stmt->execute(['clb_id' => $clbId]);
    $tongThanhVien = (int)$stmt->fetchColumn();

    /* Thống kê sự kiện */
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM su_kien
        WHERE cau_lac_bo_id = :clb_id
    ");
    $stmt->execute(['clb_id' => $clbId]);
    $tongSuKien = (int)$stmt->fetchColumn();

    /* Thống kê tài chính */
    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(
                CASE WHEN loai = 'thu'
                    THEN so_tien ELSE 0 END
            ), 0) AS tong_thu,

            COALESCE(SUM(
                CASE WHEN loai = 'chi'
                    THEN so_tien ELSE 0 END
            ), 0) AS tong_chi

        FROM thu_chi
        WHERE cau_lac_bo_id = :clb_id
    ");
    $stmt->execute(['clb_id' => $clbId]);
    $taiChinh = $stmt->fetch();

    $tongThu = (float)$taiChinh['tong_thu'];
    $tongChi = (float)$taiChinh['tong_chi'];
    $soDu = $tongThu - $tongChi;

    /* Thống kê điểm danh */
    $stmt = $pdo->prepare("
        SELECT
            COUNT(dd.id) AS tong,
            COALESCE(SUM(
                CASE WHEN dd.trang_thai = 'co_mat'
                    THEN 1 ELSE 0 END
            ), 0) AS co_mat,
            COALESCE(SUM(
                CASE WHEN dd.trang_thai = 'vang'
                    THEN 1 ELSE 0 END
            ), 0) AS vang

        FROM diem_danh dd
        INNER JOIN su_kien sk
            ON sk.id = dd.su_kien_id

        WHERE sk.cau_lac_bo_id = :clb_id
    ");
    $stmt->execute(['clb_id' => $clbId]);
    $diemDanh = $stmt->fetch();

    $tongLuotDiemDanh = (int)$diemDanh['tong'];
    $coMat = (int)$diemDanh['co_mat'];
    $vang = (int)$diemDanh['vang'];

    if ($tongLuotDiemDanh > 0) {
        $tyLeCoMat = round(
            $coMat / $tongLuotDiemDanh * 100,
            1
        );
    }

    /* Thống kê thu chi theo tháng */
    $stmt = $pdo->prepare("
        SELECT
            DATE_FORMAT(ngay_giao_dich, '%Y-%m') AS thang,

            COALESCE(SUM(
                CASE WHEN loai = 'thu'
                    THEN so_tien ELSE 0 END
            ), 0) AS tong_thu,

            COALESCE(SUM(
                CASE WHEN loai = 'chi'
                    THEN so_tien ELSE 0 END
            ), 0) AS tong_chi

        FROM thu_chi
        WHERE cau_lac_bo_id = :clb_id

        GROUP BY DATE_FORMAT(ngay_giao_dich, '%Y-%m')
        ORDER BY thang DESC
    ");

    $stmt->execute(['clb_id' => $clbId]);
    $thongKeThang = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Thống kê và báo cáo</h1>
                <p>
                    Theo dõi tình hình hoạt động và tài chính
                    của câu lạc bộ.
                </p>
            </div>

            <?php if ($clbHienTai): ?>
                <a
                    class="btn btn-primary"
                    href="xuat-bao-cao.php?clb_id=<?= $clbId ?>"
                >
                    Xuất báo cáo Excel (CSV)
                </a>
            <?php endif; ?>
        </section>

        <?php if (!$danhSachClb): ?>

            <div class="empty-state">
                <p>Bạn chưa quản lý câu lạc bộ nào.</p>
                <a href="them-clb.php" class="btn btn-primary">
                    + Tạo câu lạc bộ
                </a>
            </div>

        <?php else: ?>

            <!-- CHỌN CLB -->
            <div class="card">
                <form method="GET" action="thong-ke.php">
                    <div class="form-group">
                        <label for="clb_id">Chọn câu lạc bộ</label>
                        <select name="clb_id" id="clb_id">
                            <?php foreach ($danhSachClb as $clb): ?>
                                <option
                                    value="<?= (int)$clb['id'] ?>"
                                    <?= $clbId === (int)$clb['id']
                                        ? 'selected' : '' ?>
                                >
                                    <?= e($clb['ten_clb']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Xem thống kê
                    </button>
                </form>
            </div>

            <?php if ($clbHienTai): ?>

                <!-- TỔNG QUAN -->
                <section class="content-section">

                    <div class="section-header">
                        <div>
                            <h2><?= e($clbHienTai['ten_clb']) ?></h2>
                            <p>Thống kê toàn bộ dữ liệu hiện có</p>
                        </div>
                    </div>

                    <div class="dashboard-cards">
                        <div class="stat-card">
                            <h3><?= $tongThanhVien ?></h3>
                            <p>Thành viên hoạt động</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $tongSuKien ?></h3>
                            <p>Tổng sự kiện</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $tyLeCoMat ?>%</h3>
                            <p>Tỷ lệ có mặt trong lượt đã điểm danh</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= tien($soDu) ?></h3>
                            <p>Số dư quỹ</p>
                        </div>
                    </div>

                </section>

                <!-- TÀI CHÍNH -->
                <section class="content-section">

                    <div class="section-header">
                        <h2>Thống kê tài chính</h2>
                    </div>

                    <div class="dashboard-cards">
                        <div class="stat-card">
                            <h3><?= tien($tongThu) ?></h3>
                            <p>Tổng thu</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= tien($tongChi) ?></h3>
                            <p>Tổng chi</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= tien($soDu) ?></h3>
                            <p>Số dư còn lại</p>
                        </div>
                    </div>
                </section>

                <!-- ĐIỂM DANH -->
                <section class="content-section">

                    <div class="section-header">
                        <h2>Thống kê điểm danh</h2>
                    </div>

                    <div class="dashboard-cards">
                        <div class="stat-card">
                            <h3><?= $tongLuotDiemDanh ?></h3>
                            <p>Tổng lượt đã điểm danh</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $coMat ?></h3>
                            <p>Lượt có mặt</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $vang ?></h3>
                            <p>Lượt vắng</p>
                        </div>
                    </div>

                    <p style="color:#6d7f79;">
                        Tỷ lệ có mặt được tính trên các lượt đã
                        được điểm danh; không bao gồm những người
                        chưa được ghi nhận trạng thái.
                    </p>
                </section>

                <!-- THỐNG KÊ THEO THÁNG -->
                <section class="content-section">

                    <div class="section-header">
                        <h2>Thu – Chi theo tháng</h2>
                    </div>

                    <?php if ($thongKeThang): ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tháng</th>
                                        <th>Tổng thu</th>
                                        <th>Tổng chi</th>
                                        <th>Chênh lệch tháng</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($thongKeThang as $thang): ?>
                                        <?php
                                        $duThang =
                                            (float)$thang['tong_thu']
                                            - (float)$thang['tong_chi'];
                                        ?>

                                        <tr>
                                            <td><?= e($thang['thang']) ?></td>
                                            <td>
                                                <?= tien($thang['tong_thu']) ?>
                                            </td>
                                            <td>
                                                <?= tien($thang['tong_chi']) ?>
                                            </td>
                                            <td>
                                                <?= tien($duThang) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Chưa có giao dịch để thống kê.</p>
                        </div>
                    <?php endif; ?>

                </section>

                <section class="content-section">
                    <div class="card">
                        <h2>Xuất báo cáo</h2>
                        <p>
                            Báo cáo CSV gồm thông tin tổng quan,
                            kết quả điểm danh, thu chi theo tháng
                            và danh sách các giao dịch.
                        </p>

                        <a
                            href="xuat-bao-cao.php?clb_id=<?= $clbId ?>"
                            class="btn btn-primary"
                        >
                            Tải báo cáo CSV
                        </a>
                    </div>
                </section>

            <?php endif; ?>

        <?php endif; ?>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
