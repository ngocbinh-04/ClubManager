<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$pageTitle = 'Thống kê';
$activePage = 'thong-ke';


/* =====================================================
   1. CLB DO USER QUẢN LÝ
   ===================================================== */

$sqlClb = "
    SELECT id, ten_clb
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
    ORDER BY ten_clb ASC
";

$stmtClb = $pdo->prepare($sqlClb);

$stmtClb->execute([
    'user_id' => $userId
]);

$danhSachClb = $stmtClb->fetchAll();


$clbId = (int) ($_GET['clb_id'] ?? 0);

if ($clbId <= 0 && !empty($danhSachClb)) {
    $clbId = (int) $danhSachClb[0]['id'];
}


$clbHienTai = null;

foreach ($danhSachClb as $clb) {

    if ((int) $clb['id'] === $clbId) {

        $clbHienTai = $clb;
        break;
    }
}


if ($clbId > 0 && !$clbHienTai) {

    header('Location: thong-ke.php');
    exit;
}


/* =====================================================
   GIÁ TRỊ MẶC ĐỊNH
   ===================================================== */

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


/* =====================================================
   2. THỐNG KÊ THÀNH VIÊN
   ===================================================== */

if ($clbId > 0) {

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


    /* =================================================
       3. TỔNG SỰ KIỆN
       ================================================= */

    $sqlSuKien = "
        SELECT COUNT(*)

        FROM su_kien

        WHERE cau_lac_bo_id = :clb_id
    ";

    $stmtSuKien = $pdo->prepare($sqlSuKien);

    $stmtSuKien->execute([
        'clb_id' => $clbId
    ]);

    $tongSuKien = (int) $stmtSuKien->fetchColumn();


    /* =================================================
       4. THU - CHI
       ================================================= */

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

    $tongThu = (float) $taiChinh['tong_thu'];
    $tongChi = (float) $taiChinh['tong_chi'];

    $soDu = $tongThu - $tongChi;


    /* =================================================
       5. ĐIỂM DANH
       ================================================= */

    $sqlDiemDanh = "
        SELECT

            COUNT(dd.id) AS tong,

            SUM(
                CASE
                    WHEN dd.trang_thai = 'co_mat'
                    THEN 1
                    ELSE 0
                END
            ) AS co_mat,

            SUM(
                CASE
                    WHEN dd.trang_thai = 'vang'
                    THEN 1
                    ELSE 0
                END
            ) AS vang

        FROM diem_danh dd

        INNER JOIN su_kien sk
            ON dd.su_kien_id = sk.id

        WHERE sk.cau_lac_bo_id = :clb_id
    ";

    $stmtDiemDanh = $pdo->prepare($sqlDiemDanh);

    $stmtDiemDanh->execute([
        'clb_id' => $clbId
    ]);

    $diemDanh = $stmtDiemDanh->fetch();


    $tongLuotDiemDanh = (int) ($diemDanh['tong'] ?? 0);

    $coMat = (int) ($diemDanh['co_mat'] ?? 0);

    $vang = (int) ($diemDanh['vang'] ?? 0);


    if ($tongLuotDiemDanh > 0) {

        $tyLeCoMat = round(
            ($coMat / $tongLuotDiemDanh) * 100,
            1
        );
    }


    /* =================================================
       6. THỐNG KÊ THU CHI THEO THÁNG
       ================================================= */

    $sqlThang = "
        SELECT

            DATE_FORMAT(
                ngay_giao_dich,
                '%Y-%m'
            ) AS thang,

            SUM(
                CASE
                    WHEN loai = 'thu'
                    THEN so_tien
                    ELSE 0
                END
            ) AS tong_thu,

            SUM(
                CASE
                    WHEN loai = 'chi'
                    THEN so_tien
                    ELSE 0
                END
            ) AS tong_chi

        FROM thu_chi

        WHERE cau_lac_bo_id = :clb_id

        GROUP BY
            DATE_FORMAT(
                ngay_giao_dich,
                '%Y-%m'
            )

        ORDER BY thang DESC
    ";

    $stmtThang = $pdo->prepare($sqlThang);

    $stmtThang->execute([
        'clb_id' => $clbId
    ]);

    $thongKeThang = $stmtThang->fetchAll();
}

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>Thống kê</h1>

                <p>
                    Tổng hợp hoạt động của câu lạc bộ.
                </p>

            </div>

        </section>


        <?php if (empty($danhSachClb)): ?>

            <div class="empty-state">

                <p>
                    Bạn chưa quản lý câu lạc bộ nào.
                </p>

            </div>

        <?php else: ?>

            <div class="card">

                <form method="GET">

                    <div class="form-group">

                        <label for="clb_id">
                            Chọn câu lạc bộ
                        </label>

                        <select
                            name="clb_id"
                            id="clb_id"
                            onchange="this.form.submit()"
                        >

                            <?php foreach ($danhSachClb as $clb): ?>

                                <option
                                    value="<?= $clb['id'] ?>"
                                    <?= $clbId == $clb['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($clb['ten_clb']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </form>

            </div>


            <section class="content-section">

                <div class="section-header">

                    <div>

                        <h2>
                            <?= htmlspecialchars($clbHienTai['ten_clb']) ?>
                        </h2>

                        <p>
                            Tổng quan câu lạc bộ
                        </p>

                    </div>

                </div>


                <div class="dashboard-cards">

                    <div class="stat-card">

                        <h3>
                            <?= $tongThanhVien ?>
                        </h3>

                        <p>Thành viên hoạt động</p>

                    </div>


                    <div class="stat-card">

                        <h3>
                            <?= $tongSuKien ?>
                        </h3>

                        <p>Sự kiện</p>

                    </div>


                    <div class="stat-card">

                        <h3>
                            <?= $tyLeCoMat ?>%
                        </h3>

                        <p>Tỷ lệ có mặt</p>

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

                        <p>Số dư quỹ</p>

                    </div>

                </div>

            </section>


            <!-- TÀI CHÍNH -->

            <section class="content-section">

                <div class="section-header">

                    <div>

                        <h2>
                            Thống kê tài chính
                        </h2>

                    </div>

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

                        <p>Còn lại</p>

                    </div>

                </div>

            </section>


            <!-- ĐIỂM DANH -->

            <section class="content-section">

                <div class="section-header">

                    <h2>
                        Thống kê điểm danh
                    </h2>

                </div>


                <div class="dashboard-cards">

                    <div class="stat-card">

                        <h3>
                            <?= $tongLuotDiemDanh ?>
                        </h3>

                        <p>Tổng lượt điểm danh</p>

                    </div>


                    <div class="stat-card">

                        <h3>
                            <?= $coMat ?>
                        </h3>

                        <p>Có mặt</p>

                    </div>


                    <div class="stat-card">

                        <h3>
                            <?= $vang ?>
                        </h3>

                        <p>Vắng</p>

                    </div>

                </div>

            </section>


            <!-- THU CHI THEO THÁNG -->

            <section class="content-section">

                <div class="section-header">

                    <h2>
                        Thu - Chi theo tháng
                    </h2>

                </div>


                <?php if (!empty($thongKeThang)): ?>

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>Tháng</th>

                                    <th>Tổng thu</th>

                                    <th>Tổng chi</th>

                                    <th>Số dư tháng</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($thongKeThang as $thang): ?>

                                    <?php
                                    $duThang =
                                        $thang['tong_thu']
                                        -
                                        $thang['tong_chi'];
                                    ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($thang['thang']) ?>
                                        </td>


                                        <td>
                                            <?= number_format(
                                                $thang['tong_thu'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?> đ
                                        </td>


                                        <td>
                                            <?= number_format(
                                                $thang['tong_chi'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?> đ
                                        </td>


                                        <td>
                                            <?= number_format(
                                                $duThang,
                                                0,
                                                ',',
                                                '.'
                                            ) ?> đ
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <p>
                            Chưa có dữ liệu tài chính để thống kê.
                        </p>

                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>