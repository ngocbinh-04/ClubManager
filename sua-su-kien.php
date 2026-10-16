<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$suKienId = (int) ($_GET['id'] ?? 0);
$clbId = (int) ($_GET['clb_id'] ?? 0);


/* CLB phải thuộc user */
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
    header('Location: su-kien.php');
    exit;
}


/* Lấy sự kiện */
$sqlSk = "
    SELECT *
    FROM su_kien
    WHERE id = :id
      AND cau_lac_bo_id = :clb_id
    LIMIT 1
";

$stmtSk = $pdo->prepare($sqlSk);

$stmtSk->execute([
    'id' => $suKienId,
    'clb_id' => $clbId
]);

$suKien = $stmtSk->fetch();

if (!$suKien) {
    header('Location: su-kien.php?clb_id=' . $clbId);
    exit;
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ten = trim($_POST['ten_su_kien'] ?? '');
    $moTa = trim($_POST['mo_ta'] ?? '');
    $ngay = $_POST['ngay_to_chuc'] ?? '';
    $gioBatDau = $_POST['gio_bat_dau'] ?? '';
    $gioKetThuc = $_POST['gio_ket_thuc'] ?? '';
    $diaDiem = trim($_POST['dia_diem'] ?? '');
    $trangThai = $_POST['trang_thai'] ?? 'sap_dien_ra';


    if ($ten === '' || $ngay === '') {

        $error = 'Vui lòng nhập tên sự kiện và ngày tổ chức.';

    } else {

        $sqlUpdate = "
            UPDATE su_kien

            SET
                ten_su_kien = :ten,
                mo_ta = :mo_ta,
                ngay_to_chuc = :ngay,
                gio_bat_dau = :gio_bat_dau,
                gio_ket_thuc = :gio_ket_thuc,
                dia_diem = :dia_diem,
                trang_thai = :trang_thai

            WHERE id = :id
              AND cau_lac_bo_id = :clb_id
        ";

        $stmtUpdate = $pdo->prepare($sqlUpdate);

        $stmtUpdate->execute([
            'ten' => $ten,
            'mo_ta' => $moTa,
            'ngay' => $ngay,
            'gio_bat_dau' => $gioBatDau !== '' ? $gioBatDau : null,
            'gio_ket_thuc' => $gioKetThuc !== '' ? $gioKetThuc : null,
            'dia_diem' => $diaDiem,
            'trang_thai' => $trangThai,
            'id' => $suKienId,
            'clb_id' => $clbId
        ]);

        header(
            'Location: su-kien.php?clb_id='
            . $clbId
            . '&success=sua'
        );

        exit;
    }
}


$pageTitle = 'Sửa sự kiện';
$activePage = 'su-kien';

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>
                <h1>Sửa sự kiện</h1>
            </div>

        </section>


        <?php if ($error !== ''): ?>

            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <div class="card">

            <form method="POST">

                <div class="form-group">

                    <label>Tên sự kiện</label>

                    <input
                        type="text"
                        name="ten_su_kien"
                        value="<?= htmlspecialchars($suKien['ten_su_kien']) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Ngày tổ chức</label>

                    <input
                        type="date"
                        name="ngay_to_chuc"
                        value="<?= htmlspecialchars($suKien['ngay_to_chuc']) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Giờ bắt đầu</label>

                    <input
                        type="time"
                        name="gio_bat_dau"
                        value="<?= htmlspecialchars(substr($suKien['gio_bat_dau'], 0, 5)) ?>"
                    >

                </div>


                <div class="form-group">

                    <label>Giờ kết thúc</label>

                    <input
                        type="time"
                        name="gio_ket_thuc"
                        value="<?= htmlspecialchars(substr($suKien['gio_ket_thuc'], 0, 5)) ?>"
                    >

                </div>


                <div class="form-group">

                    <label>Địa điểm</label>

                    <input
                        type="text"
                        name="dia_diem"
                        value="<?= htmlspecialchars($suKien['dia_diem']) ?>"
                    >

                </div>


                <div class="form-group">

                    <label>Trạng thái</label>

                    <select name="trang_thai">

                        <option
                            value="sap_dien_ra"
                            <?= $suKien['trang_thai'] === 'sap_dien_ra' ? 'selected' : '' ?>
                        >
                            Sắp diễn ra
                        </option>

                        <option
                            value="dang_dien_ra"
                            <?= $suKien['trang_thai'] === 'dang_dien_ra' ? 'selected' : '' ?>
                        >
                            Đang diễn ra
                        </option>

                        <option
                            value="da_ket_thuc"
                            <?= $suKien['trang_thai'] === 'da_ket_thuc' ? 'selected' : '' ?>
                        >
                            Đã kết thúc
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>Mô tả</label>

                    <textarea
                        name="mo_ta"
                        rows="5"
                    ><?= htmlspecialchars($suKien['mo_ta']) ?></textarea>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Lưu thay đổi
                </button>

                <a
                    href="su-kien.php?clb_id=<?= $clbId ?>"
                    class="btn"
                >
                    Hủy
                </a>

            </form>

        </div>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>