<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$clbId = (int) ($_GET['id'] ?? 0);

if ($clbId <= 0) {
    header('Location: index.php');
    exit;
}


/* =====================================================
   1. LẤY THÔNG TIN CLB
   Chỉ cho xem nếu:
   - là người tạo CLB
   HOẶC
   - đang tham gia CLB
   ===================================================== */

$sqlClb = "
    SELECT DISTINCT
        clb.*,

        CASE
            WHEN clb.nguoi_tao_id = :user_id_1
            THEN 1
            ELSE 0
        END AS la_quan_ly,

        tg.vai_tro AS vai_tro_tham_gia

    FROM cau_lac_bo clb

    LEFT JOIN tham_gia_clb tg
        ON clb.id = tg.cau_lac_bo_id
        AND tg.nguoi_dung_id = :user_id_2
        AND tg.trang_thai = 'hoat_dong'

    WHERE clb.id = :clb_id

      AND (
            clb.nguoi_tao_id = :user_id_3
            OR tg.nguoi_dung_id = :user_id_4
      )

    LIMIT 1
";

$stmtClb = $pdo->prepare($sqlClb);

$stmtClb->execute([
    'user_id_1' => $userId,
    'user_id_2' => $userId,
    'clb_id' => $clbId,
    'user_id_3' => $userId,
    'user_id_4' => $userId
]);

$clb = $stmtClb->fetch();

if (!$clb) {
    header('Location: index.php');
    exit;
}

$laQuanLy = (bool) $clb['la_quan_ly'];


/* =====================================================
   2. ĐẾM THÀNH VIÊN HOẠT ĐỘNG
   ===================================================== */

$sqlThanhVien = "
    SELECT COUNT(*)

    FROM tham_gia_clb

    WHERE cau_lac_bo_id = :clb_id
      AND trang_thai = 'hoat_dong'
";

$stmtThanhVien = $pdo->prepare($sqlThanhVien);

$stmtThanhVien->execute([
    'clb_id' => $clbId
]);

$tongThanhVien = (int) $stmtThanhVien->fetchColumn();


/* =====================================================
   3. ĐẾM SỰ KIỆN
   ===================================================== */

$sqlSuKienCount = "
    SELECT COUNT(*)

    FROM su_kien

    WHERE cau_lac_bo_id = :clb_id
";

$stmtSuKienCount = $pdo->prepare($sqlSuKienCount);

$stmtSuKienCount->execute([
    'clb_id' => $clbId
]);

$tongSuKien = (int) $stmtSuKienCount->fetchColumn();


/* =====================================================
   4. THỐNG KÊ THU - CHI
   ===================================================== */

$sqlTaiChinh = "
    SELECT

        COALESCE(
            SUM(
                CASE
                    WHEN loai = 'thu'
                    THEN so_tien
                    ELSE 0
                END
            ),
            0
        ) AS tong_thu,

        COALESCE(
            SUM(
                CASE
                    WHEN loai = 'chi'
                    THEN so_tien
                    ELSE 0
                END
            ),
            0
        ) AS tong_chi

    FROM thu_chi

    WHERE cau_lac_bo_id = :clb_id
";

$stmtTaiChinh = $pdo->prepare($sqlTaiChinh);

$stmtTaiChinh->execute([
    'clb_id' => $clbId
]);

$taiChinh = $stmtTaiChinh->fetch();

$tongThu = (float) ($taiChinh['tong_thu'] ?? 0);
$tongChi = (float) ($taiChinh['tong_chi'] ?? 0);

$soDu = $tongThu - $tongChi;


/* =====================================================
   5. LẤY 5 SỰ KIỆN GẦN NHẤT
   ===================================================== */

$sqlSuKien = "
    SELECT *
    FROM su_kien

    WHERE cau_lac_bo_id = :clb_id

    ORDER BY
        ngay_to_chuc DESC,
        gio_bat_dau DESC

    LIMIT 5
";

$stmtSuKien = $pdo->prepare($sqlSuKien);

$stmtSuKien->execute([
    'clb_id' => $clbId
]);

$danhSachSuKien = $stmtSuKien->fetchAll();


/* =====================================================
   6. THÔNG TIN TRANG
   ===================================================== */

$pageTitle = 'Chi tiết câu lạc bộ';
$activePage = $laQuanLy ? 'clb-quan-tri' : '';

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">


        <!-- ==========================================
             HEADER
             ========================================== -->

        <section class="page-header">

            <div>

                <h1>
                    <?= htmlspecialchars($clb['ten_clb']) ?>
                </h1>

                <p>
                    <?= htmlspecialchars($clb['linh_vuc']) ?>
                </p>

            </div>


            <?php if ($laQuanLy): ?>

                <a
                    href="sua-clb.php?id=<?= $clbId ?>"
                    class="btn btn-primary"
                >
                    Sửa câu lạc bộ
                </a>

            <?php endif; ?>

        </section>


        <!-- ==========================================
             THÔNG TIN CLB
             ========================================== -->

        <section class="content-section">

            <div class="card">

                <h2>Thông tin câu lạc bộ</h2>

                <p>
                    <strong>Mô tả:</strong>
                    <?= htmlspecialchars($clb['mo_ta'] ?? '') ?>
                </p>

                <p>
                    <strong>Lĩnh vực:</strong>
                    <?= htmlspecialchars($clb['linh_vuc']) ?>
                </p>

                <p>
                    <strong>Ngày thành lập:</strong>

                    <?php if (!empty($clb['ngay_thanh_lap'])): ?>

                        <?= htmlspecialchars($clb['ngay_thanh_lap']) ?>

                    <?php else: ?>

                        Chưa cập nhật

                    <?php endif; ?>

                </p>

                <p>
                    <strong>Trạng thái:</strong>

                    <?php if ($clb['trang_thai'] === 'hoat_dong'): ?>

                        <span class="badge badge-success">
                            Hoạt động
                        </span>

                    <?php else: ?>

                        <span class="badge badge-warning">
                            Tạm dừng
                        </span>

                    <?php endif; ?>

                </p>


                <?php if (!$laQuanLy): ?>

                    <p>
                        <strong>Vai trò của bạn:</strong>

                        <?php

                        switch ($clb['vai_tro_tham_gia']) {

                            case 'quan_tri':
                                echo 'Quản trị';
                                break;

                            case 'chu_nhiem':
                                echo 'Chủ nhiệm';
                                break;

                            case 'pho_chu_nhiem':
                                echo 'Phó chủ nhiệm';
                                break;

                            default:
                                echo 'Thành viên';
                                break;
                        }

                        ?>

                    </p>

                <?php endif; ?>

            </div>

        </section>


        <!-- ==========================================
             THỐNG KÊ NHANH
             ========================================== -->

        <section class="content-section">

            <div class="section-header">

                <h2>Tổng quan</h2>

            </div>


            <div class="dashboard-cards">

                <div class="stat-card">

                    <h3>
                        <?= $tongThanhVien ?>
                    </h3>

                    <p>
                        Thành viên hoạt động
                    </p>

                </div>


                <div class="stat-card">

                    <h3>
                        <?= $tongSuKien ?>
                    </h3>

                    <p>
                        Sự kiện
                    </p>

                </div>


                <div class="stat-card">

                    <h3>
                        <?= number_format(
                            $tongThu,
                            0,
                            ',',
                            '.'
                        ) ?> đ
                    </h3>

                    <p>
                        Tổng thu
                    </p>

                </div>


                <div class="stat-card">

                    <h3>
                        <?= number_format(
                            $soDu,
                            0,
                            ',',
                            '.'
                        ) ?> đ
                    </h3>

                    <p>
                        Số dư
                    </p>

                </div>

            </div>

        </section>


        <!-- ==========================================
             CHỨC NĂNG QUẢN LÝ
             ========================================== -->

        <?php if ($laQuanLy): ?>

            <section class="content-section">

                <div class="section-header">

                    <h2>Quản lý câu lạc bộ</h2>

                </div>


                <div class="card-grid">

                    <div class="card">

                        <h3>Thành viên</h3>

                        <p>
                            Quản lý thành viên và vai trò trong câu lạc bộ.
                        </p>

                        <a
                            href="thanh-vien.php?clb_id=<?= $clbId ?>"
                            class="btn btn-primary"
                        >
                            Quản lý thành viên
                        </a>

                    </div>


                    <div class="card">

                        <h3>Sự kiện</h3>

                        <p>
                            Thêm, sửa và quản lý các sự kiện.
                        </p>

                        <a
                            href="su-kien.php?clb_id=<?= $clbId ?>"
                            class="btn btn-primary"
                        >
                            Quản lý sự kiện
                        </a>

                    </div>


                    <div class="card">

                        <h3>Điểm danh</h3>

                        <p>
                            Điểm danh thành viên theo từng sự kiện.
                        </p>

                        <a
                            href="diem-danh.php?clb_id=<?= $clbId ?>"
                            class="btn btn-primary"
                        >
                            Điểm danh
                        </a>

                    </div>


                    <div class="card">

                        <h3>Thu - Chi</h3>

                        <p>
                            Quản lý tài chính của câu lạc bộ.
                        </p>

                        <a
                            href="thu-chi.php?clb_id=<?= $clbId ?>"
                            class="btn btn-primary"
                        >
                            Quản lý Thu - Chi
                        </a>

                    </div>


                    <div class="card">

                        <h3>Thống kê</h3>

                        <p>
                            Xem số liệu tổng hợp của câu lạc bộ.
                        </p>

                        <a
                            href="thong-ke.php?clb_id=<?= $clbId ?>"
                            class="btn btn-primary"
                        >
                            Xem thống kê
                        </a>

                    </div>

                </div>

            </section>

        <?php endif; ?>


        <!-- ==========================================
             SỰ KIỆN GẦN ĐÂY
             ========================================== -->

        <section class="content-section">

            <div class="section-header">

                <div>

                    <h2>Sự kiện gần đây</h2>

                    <p>
                        Các hoạt động của câu lạc bộ.
                    </p>

                </div>


                <a
                    href="su-kien.php?clb_id=<?= $clbId ?>"
                    class="btn"
                >
                    Xem tất cả
                </a>

            </div>


            <?php if (!empty($danhSachSuKien)): ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>Tên sự kiện</th>

                                <th>Ngày tổ chức</th>

                                <th>Địa điểm</th>

                                <th>Trạng thái</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($danhSachSuKien as $sk): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars($sk['ten_su_kien']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($sk['ngay_to_chuc']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($sk['dia_diem'] ?? '') ?>
                                    </td>

                                    <td>

                                        <?php if ($sk['trang_thai'] === 'sap_dien_ra'): ?>

                                            <span class="badge badge-warning">
                                                Sắp diễn ra
                                            </span>

                                        <?php elseif ($sk['trang_thai'] === 'dang_dien_ra'): ?>

                                            <span class="badge badge-success">
                                                Đang diễn ra
                                            </span>

                                        <?php else: ?>

                                            <span class="badge">
                                                Đã kết thúc
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <p>
                        Câu lạc bộ chưa có sự kiện nào.
                    </p>

                </div>

            <?php endif; ?>

        </section>


        <!-- ==========================================
             TÀI CHÍNH
             ========================================== -->

        <?php if ($laQuanLy): ?>

            <section class="content-section">

                <div class="section-header">

                    <h2>Tài chính</h2>

                </div>


                <div class="dashboard-cards">

                    <div class="stat-card">

                        <h3>
                            <?= number_format(
                                $tongThu,
                                0,
                                ',',
                                '.'
                            ) ?> đ
                        </h3>

                        <p>Tổng thu</p>

                    </div>


                    <div class="stat-card">

                        <h3>
                            <?= number_format(
                                $tongChi,
                                0,
                                ',',
                                '.'
                            ) ?> đ
                        </h3>

                        <p>Tổng chi</p>

                    </div>


                    <div class="stat-card">

                        <h3>
                            <?= number_format(
                                $soDu,
                                0,
                                ',',
                                '.'
                            ) ?> đ
                        </h3>

                        <p>Số dư</p>

                    </div>

                </div>

            </section>

        <?php endif; ?>


        <!-- ==========================================
             QUAY LẠI
             ========================================== -->

        <section class="content-section">

            <a
                href="index.php"
                class="btn"
            >
                ← Quay lại trang chủ
            </a>

        </section>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>