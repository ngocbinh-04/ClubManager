<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$clbId = (int) ($_GET['clb_id'] ?? 0);


/* Kiểm tra CLB */
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

        $sql = "
            INSERT INTO su_kien
            (
                cau_lac_bo_id,
                ten_su_kien,
                mo_ta,
                ngay_to_chuc,
                gio_bat_dau,
                gio_ket_thuc,
                dia_diem,
                trang_thai,
                nguoi_tao_id
            )
            VALUES
            (
                :clb_id,
                :ten,
                :mo_ta,
                :ngay,
                :gio_bat_dau,
                :gio_ket_thuc,
                :dia_diem,
                :trang_thai,
                :nguoi_tao_id
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'clb_id' => $clbId,
            'ten' => $ten,
            'mo_ta' => $moTa,
            'ngay' => $ngay,
            'gio_bat_dau' => $gioBatDau !== '' ? $gioBatDau : null,
            'gio_ket_thuc' => $gioKetThuc !== '' ? $gioKetThuc : null,
            'dia_diem' => $diaDiem,
            'trang_thai' => $trangThai,
            'nguoi_tao_id' => $userId
        ]);

        header(
            'Location: su-kien.php?clb_id='
            . $clbId
            . '&success=them'
        );

        exit;
    }
}


$pageTitle = 'Thêm sự kiện';
$activePage = 'su-kien';

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>Thêm sự kiện</h1>

                <p>
                    <?= htmlspecialchars($clb['ten_clb']) ?>
                </p>

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
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Ngày tổ chức</label>

                    <input
                        type="date"
                        name="ngay_to_chuc"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Giờ bắt đầu</label>

                    <input
                        type="time"
                        name="gio_bat_dau"
                    >

                </div>


                <div class="form-group">

                    <label>Giờ kết thúc</label>

                    <input
                        type="time"
                        name="gio_ket_thuc"
                    >

                </div>


                <div class="form-group">

                    <label>Địa điểm</label>

                    <input
                        type="text"
                        name="dia_diem"
                    >

                </div>


                <div class="form-group">

                    <label>Trạng thái</label>

                    <select name="trang_thai">

                        <option value="sap_dien_ra">
                            Sắp diễn ra
                        </option>

                        <option value="dang_dien_ra">
                            Đang diễn ra
                        </option>

                        <option value="da_ket_thuc">
                            Đã kết thúc
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>Mô tả</label>

                    <textarea
                        name="mo_ta"
                        rows="5"
                    ></textarea>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Thêm sự kiện
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