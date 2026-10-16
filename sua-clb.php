<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$clbId = (int) ($_GET['id'] ?? 0);

if ($clbId <= 0) {
    header('Location: clb-quan-tri.php');
    exit;
}


/* Chỉ lấy CLB thuộc quyền của người hiện tại */
$sql = "
    SELECT *
    FROM cau_lac_bo
    WHERE id = :id
      AND nguoi_tao_id = :user_id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'id' => $clbId,
    'user_id' => $userId
]);

$clb = $stmt->fetch();

if (!$clb) {
    header('Location: clb-quan-tri.php');
    exit;
}


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

        $sqlUpdate = "
            UPDATE cau_lac_bo

            SET
                ten_clb = :ten_clb,
                mo_ta = :mo_ta,
                linh_vuc = :linh_vuc,
                ngay_thanh_lap = :ngay_thanh_lap,
                trang_thai = :trang_thai

            WHERE id = :id
              AND nguoi_tao_id = :user_id
        ";

        $stmtUpdate = $pdo->prepare($sqlUpdate);

        $stmtUpdate->execute([
            'ten_clb' => $tenClb,
            'mo_ta' => $moTa,
            'linh_vuc' => $linhVuc,
            'ngay_thanh_lap' => $ngayThanhLap !== '' ? $ngayThanhLap : null,
            'trang_thai' => $trangThai,
            'id' => $clbId,
            'user_id' => $userId
        ]);

        header('Location: clb-quan-tri.php?success=sua');
        exit;
    }
}


$pageTitle = 'Sửa câu lạc bộ';
$activePage = 'clb-quan-tri';

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>
                    Sửa câu lạc bộ
                </h1>

                <p>
                    Cập nhật thông tin câu lạc bộ.
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
                        value="<?= htmlspecialchars($clb['ten_clb']) ?>"
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
                        value="<?= htmlspecialchars($clb['linh_vuc']) ?>"
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
                        value="<?= htmlspecialchars($clb['ngay_thanh_lap']) ?>"
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

                        <option
                            value="hoat_dong"
                            <?= $clb['trang_thai'] === 'hoat_dong' ? 'selected' : '' ?>
                        >
                            Hoạt động
                        </option>

                        <option
                            value="tam_dung"
                            <?= $clb['trang_thai'] === 'tam_dung' ? 'selected' : '' ?>
                        >
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
                    ><?= htmlspecialchars($clb['mo_ta']) ?></textarea>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Lưu thay đổi
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