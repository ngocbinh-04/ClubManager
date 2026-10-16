<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$thamGiaId = (int) ($_GET['id'] ?? 0);

$clbId = (int) ($_GET['clb_id'] ?? 0);


/* =====================================================
   KIỂM TRA CLB THUỘC USER
   ===================================================== */

$sqlClb = "
    SELECT *
    FROM cau_lac_bo
    WHERE id = :clb_id
      AND nguoi_tao_id = :user_id
    LIMIT 1
";

$stmtClb = $pdo->prepare($sqlClb);

$stmtClb->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);

$clb = $stmtClb->fetch();

if (!$clb) {
    header('Location: thanh-vien.php');
    exit;
}


/* =====================================================
   LẤY THÔNG TIN THÀNH VIÊN
   ===================================================== */

$sqlThanhVien = "
    SELECT
        tg.*,
        nd.ho_ten,
        nd.ten_dang_nhap,
        nd.email

    FROM tham_gia_clb tg

    INNER JOIN nguoi_dung nd
        ON tg.nguoi_dung_id = nd.id

    WHERE tg.id = :tham_gia_id
      AND tg.cau_lac_bo_id = :clb_id

    LIMIT 1
";

$stmtThanhVien = $pdo->prepare($sqlThanhVien);

$stmtThanhVien->execute([
    'tham_gia_id' => $thamGiaId,
    'clb_id' => $clbId
]);

$thanhVien = $stmtThanhVien->fetch();

if (!$thanhVien) {

    header(
        'Location: thanh-vien.php?clb_id='
        . $clbId
    );

    exit;
}


$error = '';


/* =====================================================
   CẬP NHẬT
   ===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vaiTro = $_POST['vai_tro'] ?? 'thanh_vien';

    $trangThai = $_POST['trang_thai'] ?? 'hoat_dong';


    $vaiTroHopLe = [
        'quan_tri',
        'chu_nhiem',
        'pho_chu_nhiem',
        'thanh_vien'
    ];


    $trangThaiHopLe = [
        'hoat_dong',
        'tam_dung',
        'da_roi'
    ];


    if (
        !in_array($vaiTro, $vaiTroHopLe, true) ||
        !in_array($trangThai, $trangThaiHopLe, true)
    ) {

        $error = 'Dữ liệu không hợp lệ.';

    } else {

        /*
         * Không cho hạ quyền chính người tạo CLB
         */

        if ($thanhVien['nguoi_dung_id'] == $userId) {

            $vaiTro = 'quan_tri';
            $trangThai = 'hoat_dong';
        }


        $sqlUpdate = "
            UPDATE tham_gia_clb

            SET
                vai_tro = :vai_tro,
                trang_thai = :trang_thai

            WHERE id = :tham_gia_id
              AND cau_lac_bo_id = :clb_id
        ";

        $stmtUpdate = $pdo->prepare($sqlUpdate);

        $stmtUpdate->execute([
            'vai_tro' => $vaiTro,
            'trang_thai' => $trangThai,
            'tham_gia_id' => $thamGiaId,
            'clb_id' => $clbId
        ]);


        header(
            'Location: thanh-vien.php?clb_id='
            . $clbId
            . '&success=sua'
        );

        exit;
    }
}


$pageTitle = 'Sửa thành viên';
$activePage = 'thanh-vien';

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>
                    Cập nhật thành viên
                </h1>

                <p>
                    <?= htmlspecialchars($thanhVien['ho_ten']) ?>
                </p>

            </div>

        </section>


        <?php if ($error !== ''): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <div class="card">

            <p>
                <strong>Tài khoản:</strong>
                <?= htmlspecialchars($thanhVien['ten_dang_nhap']) ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= htmlspecialchars($thanhVien['email'] ?? '') ?>
            </p>


            <form method="POST">

                <div class="form-group">

                    <label for="vai_tro">
                        Vai trò
                    </label>


                    <?php if ($thanhVien['nguoi_dung_id'] == $userId): ?>

                        <input
                            type="text"
                            value="Quản trị"
                            disabled
                        >

                        <input
                            type="hidden"
                            name="vai_tro"
                            value="quan_tri"
                        >

                    <?php else: ?>

                        <select
                            name="vai_tro"
                            id="vai_tro"
                        >

                            <option
                                value="thanh_vien"
                                <?= $thanhVien['vai_tro'] === 'thanh_vien' ? 'selected' : '' ?>
                            >
                                Thành viên
                            </option>

                            <option
                                value="pho_chu_nhiem"
                                <?= $thanhVien['vai_tro'] === 'pho_chu_nhiem' ? 'selected' : '' ?>
                            >
                                Phó chủ nhiệm
                            </option>

                            <option
                                value="chu_nhiem"
                                <?= $thanhVien['vai_tro'] === 'chu_nhiem' ? 'selected' : '' ?>
                            >
                                Chủ nhiệm
                            </option>

                        </select>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label for="trang_thai">
                        Trạng thái
                    </label>


                    <?php if ($thanhVien['nguoi_dung_id'] == $userId): ?>

                        <input
                            type="text"
                            value="Hoạt động"
                            disabled
                        >

                        <input
                            type="hidden"
                            name="trang_thai"
                            value="hoat_dong"
                        >

                    <?php else: ?>

                        <select
                            name="trang_thai"
                            id="trang_thai"
                        >

                            <option
                                value="hoat_dong"
                                <?= $thanhVien['trang_thai'] === 'hoat_dong' ? 'selected' : '' ?>
                            >
                                Hoạt động
                            </option>

                            <option
                                value="tam_dung"
                                <?= $thanhVien['trang_thai'] === 'tam_dung' ? 'selected' : '' ?>
                            >
                                Tạm dừng
                            </option>

                            <option
                                value="da_roi"
                                <?= $thanhVien['trang_thai'] === 'da_roi' ? 'selected' : '' ?>
                            >
                                Đã rời
                            </option>

                        </select>

                    <?php endif; ?>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Lưu thay đổi
                </button>


                <a
                    href="thanh-vien.php?clb_id=<?= $clbId ?>"
                    class="btn"
                >
                    Hủy
                </a>

            </form>

        </div>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>