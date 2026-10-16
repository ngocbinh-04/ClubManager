<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tenClb = trim($_POST['ten_clb'] ?? '');
    $moTa = trim($_POST['mo_ta'] ?? '');
    $linhVuc = trim($_POST['linh_vuc'] ?? '');
    $ngayThanhLap = $_POST['ngay_thanh_lap'] ?? '';
    $trangThai = $_POST['trang_thai'] ?? 'hoat_dong';


    if ($tenClb === '' || $linhVuc === '') {

        $error = 'Vui lòng nhập tên câu lạc bộ và lĩnh vực.';

    } else {

        $sql = "
            INSERT INTO cau_lac_bo
            (
                ten_clb,
                mo_ta,
                linh_vuc,
                ngay_thanh_lap,
                trang_thai,
                nguoi_tao_id
            )
            VALUES
            (
                :ten_clb,
                :mo_ta,
                :linh_vuc,
                :ngay_thanh_lap,
                :trang_thai,
                :nguoi_tao_id
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'ten_clb' => $tenClb,
            'mo_ta' => $moTa,
            'linh_vuc' => $linhVuc,
            'ngay_thanh_lap' => $ngayThanhLap !== '' ? $ngayThanhLap : null,
            'trang_thai' => $trangThai,
            'nguoi_tao_id' => $userId
        ]);

        $clbId = $pdo->lastInsertId();


        /*
         * Người tạo CLB cũng được thêm vào bảng tham_gia_clb
         * với vai trò quản trị.
         */

        $sqlThamGia = "
            INSERT INTO tham_gia_clb
            (
                nguoi_dung_id,
                cau_lac_bo_id,
                vai_tro,
                ngay_tham_gia,
                trang_thai
            )
            VALUES
            (
                :nguoi_dung_id,
                :cau_lac_bo_id,
                'quan_tri',
                CURDATE(),
                'hoat_dong'
            )
        ";

        $stmtThamGia = $pdo->prepare($sqlThamGia);

        $stmtThamGia->execute([
            'nguoi_dung_id' => $userId,
            'cau_lac_bo_id' => $clbId
        ]);


        header('Location: clb-quan-tri.php?success=them');
        exit;
    }
}


$pageTitle = 'Thêm câu lạc bộ';
$activePage = 'clb-quan-tri';

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>
                <h1>Thêm câu lạc bộ</h1>

                <p>
                    Tạo một câu lạc bộ mới trong hệ thống.
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

                    <label for="ten_clb">
                        Tên câu lạc bộ
                    </label>

                    <input
                        type="text"
                        id="ten_clb"
                        name="ten_clb"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="linh_vuc">
                        Lĩnh vực
                    </label>

                    <input
                        type="text"
                        id="linh_vuc"
                        name="linh_vuc"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="ngay_thanh_lap">
                        Ngày thành lập
                    </label>

                    <input
                        type="date"
                        id="ngay_thanh_lap"
                        name="ngay_thanh_lap"
                    >

                </div>


                <div class="form-group">

                    <label for="trang_thai">
                        Trạng thái
                    </label>

                    <select
                        id="trang_thai"
                        name="trang_thai"
                    >

                        <option value="hoat_dong">
                            Hoạt động
                        </option>

                        <option value="tam_dung">
                            Tạm dừng
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="mo_ta">
                        Mô tả
                    </label>

                    <textarea
                        id="mo_ta"
                        name="mo_ta"
                        rows="5"
                    ></textarea>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Thêm câu lạc bộ
                </button>

                <a
                    href="clb-quan-tri.php"
                    class="btn"
                >
                    Hủy
                </a>

            </form>

        </div>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>