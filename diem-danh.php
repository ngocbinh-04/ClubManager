<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$pageTitle = 'Điểm danh';
$activePage = 'diem-danh';


/* =====================================================
   CLB DO USER QUẢN LÝ
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


/* Kiểm tra CLB */
$clbHienTai = null;

foreach ($danhSachClb as $clb) {

    if ((int) $clb['id'] === $clbId) {
        $clbHienTai = $clb;
        break;
    }
}

if ($clbId > 0 && !$clbHienTai) {
    header('Location: diem-danh.php');
    exit;
}


/* =====================================================
   SỰ KIỆN CỦA CLB
   ===================================================== */

$danhSachSuKien = [];

if ($clbId > 0) {

    $sqlSuKien = "
        SELECT *
        FROM su_kien
        WHERE cau_lac_bo_id = :clb_id
        ORDER BY ngay_to_chuc DESC
    ";

    $stmtSuKien = $pdo->prepare($sqlSuKien);

    $stmtSuKien->execute([
        'clb_id' => $clbId
    ]);

    $danhSachSuKien = $stmtSuKien->fetchAll();
}


$suKienId = (int) ($_GET['su_kien_id'] ?? 0);

if ($suKienId <= 0 && !empty($danhSachSuKien)) {
    $suKienId = (int) $danhSachSuKien[0]['id'];
}


/* =====================================================
   THÀNH VIÊN + DỮ LIỆU ĐIỂM DANH
   ===================================================== */

$danhSachThanhVien = [];

if ($suKienId > 0) {

    /* đảm bảo sự kiện thuộc CLB */
    $sqlCheckSuKien = "
        SELECT id
        FROM su_kien
        WHERE id = :su_kien_id
          AND cau_lac_bo_id = :clb_id
    ";

    $stmtCheck = $pdo->prepare($sqlCheckSuKien);

    $stmtCheck->execute([
        'su_kien_id' => $suKienId,
        'clb_id' => $clbId
    ]);

    if (!$stmtCheck->fetch()) {
        header('Location: diem-danh.php?clb_id=' . $clbId);
        exit;
    }


    $sqlThanhVien = "
        SELECT
            nd.id AS nguoi_dung_id,
            nd.ho_ten,
            tg.vai_tro,

            dd.trang_thai AS diem_danh_trang_thai,
            dd.ghi_chu

        FROM tham_gia_clb tg

        INNER JOIN nguoi_dung nd
            ON tg.nguoi_dung_id = nd.id

        LEFT JOIN diem_danh dd
            ON dd.nguoi_dung_id = nd.id
            AND dd.su_kien_id = :su_kien_id

        WHERE tg.cau_lac_bo_id = :clb_id

          AND tg.trang_thai = 'hoat_dong'

        ORDER BY nd.ho_ten ASC
    ";

    $stmtThanhVien = $pdo->prepare($sqlThanhVien);

    $stmtThanhVien->execute([
        'su_kien_id' => $suKienId,
        'clb_id' => $clbId
    ]);

    $danhSachThanhVien = $stmtThanhVien->fetchAll();
}

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>Điểm danh</h1>

                <p>
                    Điểm danh thành viên theo từng sự kiện.
                </p>

            </div>

        </section>


        <?php if (!empty($_GET['success'])): ?>

            <div class="alert-success">
                Lưu điểm danh thành công.
            </div>

        <?php endif; ?>


        <?php if (empty($danhSachClb)): ?>

            <div class="empty-state">
                Bạn chưa quản lý câu lạc bộ nào.
            </div>

        <?php else: ?>

            <div class="card">

                <form method="GET">

                    <div class="form-group">

                        <label>Chọn câu lạc bộ</label>

                        <select
                            name="clb_id"
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


                <?php if (!empty($danhSachSuKien)): ?>

                    <form method="GET">

                        <input
                            type="hidden"
                            name="clb_id"
                            value="<?= $clbId ?>"
                        >

                        <div class="form-group">

                            <label>Chọn sự kiện</label>

                            <select
                                name="su_kien_id"
                                onchange="this.form.submit()"
                            >

                                <?php foreach ($danhSachSuKien as $sk): ?>

                                    <option
                                        value="<?= $sk['id'] ?>"
                                        <?= $suKienId == $sk['id'] ? 'selected' : '' ?>
                                    >

                                        <?= htmlspecialchars($sk['ten_su_kien']) ?>

                                        -

                                        <?= htmlspecialchars($sk['ngay_to_chuc']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </form>

                <?php endif; ?>

            </div>


            <?php if (empty($danhSachSuKien)): ?>

                <div class="empty-state">
                    Câu lạc bộ chưa có sự kiện.
                </div>

            <?php elseif (!empty($danhSachThanhVien)): ?>

                <form
                    method="POST"
                    action="luu-diem-danh.php"
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
                                    <th>Điểm danh</th>
                                    <th>Ghi chú</th>
                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($danhSachThanhVien as $tv): ?>

                                    <?php
                                    $trangThai =
                                        $tv['diem_danh_trang_thai']
                                        ?? 'co_mat';
                                    ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($tv['ho_ten']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($tv['vai_tro']) ?>
                                        </td>

                                        <td>

                                            <select
                                                name="trang_thai[<?= $tv['nguoi_dung_id'] ?>]"
                                            >

                                                <option
                                                    value="co_mat"
                                                    <?= $trangThai === 'co_mat' ? 'selected' : '' ?>
                                                >
                                                    Có mặt
                                                </option>

                                                <option
                                                    value="vang"
                                                    <?= $trangThai === 'vang' ? 'selected' : '' ?>
                                                >
                                                    Vắng
                                                </option>

                                            </select>

                                        </td>

                                        <td>

                                            <input
                                                type="text"
                                                name="ghi_chu[<?= $tv['nguoi_dung_id'] ?>]"
                                                value="<?= htmlspecialchars($tv['ghi_chu'] ?? '') ?>"
                                                placeholder="Ghi chú..."
                                            >

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Lưu điểm danh
                    </button>

                </form>

            <?php endif; ?>

        <?php endif; ?>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>