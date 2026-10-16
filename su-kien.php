<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$pageTitle = 'Sự kiện';
$activePage = 'su-kien';


/* =====================================================
   1. LẤY CÁC CLB LIÊN QUAN ĐẾN USER
   ===================================================== */

$sqlClb = "
    SELECT DISTINCT
        clb.id,
        clb.ten_clb,
        clb.nguoi_tao_id,

        CASE
            WHEN clb.nguoi_tao_id = :user_id_1
            THEN 1
            ELSE 0
        END AS la_quan_ly

    FROM cau_lac_bo clb

    LEFT JOIN tham_gia_clb tg
        ON clb.id = tg.cau_lac_bo_id

    WHERE clb.nguoi_tao_id = :user_id_2

       OR (
            tg.nguoi_dung_id = :user_id_3
            AND tg.trang_thai = 'hoat_dong'
       )

    ORDER BY clb.ten_clb ASC
";

$stmtClb = $pdo->prepare($sqlClb);

$stmtClb->execute([
    'user_id_1' => $userId,
    'user_id_2' => $userId,
    'user_id_3' => $userId
]);

$danhSachClb = $stmtClb->fetchAll();


/* =====================================================
   2. CLB ĐANG CHỌN
   ===================================================== */

$clbId = (int) ($_GET['clb_id'] ?? 0);

if ($clbId <= 0 && !empty($danhSachClb)) {
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


/* Nếu cố nhập CLB không liên quan */
if ($clbId > 0 && !$clbHienTai) {
    header('Location: su-kien.php');
    exit;
}


/* =====================================================
   3. LẤY SỰ KIỆN
   ===================================================== */

$danhSachSuKien = [];

if ($clbId > 0) {

    $sqlSuKien = "
        SELECT *
        FROM su_kien
        WHERE cau_lac_bo_id = :clb_id
        ORDER BY ngay_to_chuc DESC, gio_bat_dau DESC
    ";

    $stmtSuKien = $pdo->prepare($sqlSuKien);

    $stmtSuKien->execute([
        'clb_id' => $clbId
    ]);

    $danhSachSuKien = $stmtSuKien->fetchAll();
}

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>Sự kiện</h1>

                <p>
                    Xem và quản lý sự kiện của câu lạc bộ.
                </p>

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


        <?php if (!empty($_GET['success'])): ?>

            <div class="alert-success">

                <?php if ($_GET['success'] === 'them'): ?>
                    Thêm sự kiện thành công.
                <?php elseif ($_GET['success'] === 'sua'): ?>
                    Cập nhật sự kiện thành công.
                <?php elseif ($_GET['success'] === 'xoa'): ?>
                    Xóa sự kiện thành công.
                <?php endif; ?>

            </div>

        <?php endif; ?>


        <?php if (empty($danhSachClb)): ?>

            <div class="empty-state">
                Bạn chưa thuộc câu lạc bộ nào.
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
                            <?= count($danhSachSuKien) ?>
                            sự kiện
                        </p>

                    </div>

                </div>


                <?php if (!empty($danhSachSuKien)): ?>

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
                                            <?= htmlspecialchars($sk['ten_su_kien']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($sk['ngay_to_chuc']) ?>
                                        </td>

                                        <td>

                                            <?= htmlspecialchars(
                                                substr($sk['gio_bat_dau'], 0, 5)
                                            ) ?>

                                            -

                                            <?= htmlspecialchars(
                                                substr($sk['gio_ket_thuc'], 0, 5)
                                            ) ?>

                                        </td>

                                        <td>
                                            <?= htmlspecialchars($sk['dia_diem']) ?>
                                        </td>

                                        <td>

                                            <?php
                                            if ($sk['trang_thai'] === 'sap_dien_ra') {
                                                echo 'Sắp diễn ra';
                                            } elseif ($sk['trang_thai'] === 'dang_dien_ra') {
                                                echo 'Đang diễn ra';
                                            } else {
                                                echo 'Đã kết thúc';
                                            }
                                            ?>

                                        </td>


                                        <?php if ($laQuanLy): ?>

                                            <td>

                                                <a
                                                    href="sua-su-kien.php?id=<?= $sk['id'] ?>&clb_id=<?= $clbId ?>"
                                                    class="btn"
                                                >
                                                    Sửa
                                                </a>


                                                <form
                                                    method="POST"
                                                    action="xoa-su-kien.php"
                                                    style="display:inline;"
                                                    onsubmit="return confirm('Bạn có chắc muốn xóa sự kiện này không?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= $sk['id'] ?>"
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
                        Chưa có sự kiện nào.
                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>