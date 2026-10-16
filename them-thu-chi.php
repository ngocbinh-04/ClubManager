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
    header('Location: thu-chi.php');
    exit;
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $loai = $_POST['loai'] ?? '';
    $noiDung = trim($_POST['noi_dung'] ?? '');
    $soTien = (float) ($_POST['so_tien'] ?? 0);
    $ngayGiaoDich = $_POST['ngay_giao_dich'] ?? '';
    $ghiChu = trim($_POST['ghi_chu'] ?? '');


    if (
        !in_array($loai, ['thu', 'chi'], true) ||
        $noiDung === '' ||
        $soTien <= 0 ||
        $ngayGiaoDich === ''
    ) {

        $error = 'Vui lòng nhập đầy đủ và đúng thông tin.';

    } else {

        $sqlInsert = "
            INSERT INTO thu_chi
            (
                cau_lac_bo_id,
                loai,
                noi_dung,
                so_tien,
                ngay_giao_dich,
                nguoi_thuc_hien_id,
                ghi_chu
            )
            VALUES
            (
                :clb_id,
                :loai,
                :noi_dung,
                :so_tien,
                :ngay_giao_dich,
                :nguoi_thuc_hien_id,
                :ghi_chu
            )
        ";

        $stmtInsert = $pdo->prepare($sqlInsert);

        $stmtInsert->execute([
            'clb_id' => $clbId,
            'loai' => $loai,
            'noi_dung' => $noiDung,
            'so_tien' => $soTien,
            'ngay_giao_dich' => $ngayGiaoDich,
            'nguoi_thuc_hien_id' => $userId,
            'ghi_chu' => $ghiChu
        ]);


        header(
            'Location: thu-chi.php?clb_id='
            . $clbId
            . '&success=them'
        );

        exit;
    }
}


$pageTitle = 'Thêm giao dịch';
$activePage = 'thu-chi';

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>Thêm giao dịch</h1>

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

                    <label for="loai">
                        Loại giao dịch
                    </label>

                    <select
                        name="loai"
                        id="loai"
                        required
                    >

                        <option value="thu">
                            Thu
                        </option>

                        <option value="chi">
                            Chi
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="noi_dung">
                        Nội dung
                    </label>

                    <input
                        type="text"
                        name="noi_dung"
                        id="noi_dung"
                        value="<?= htmlspecialchars($_POST['noi_dung'] ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="so_tien">
                        Số tiền
                    </label>

                    <input
                        type="number"
                        name="so_tien"
                        id="so_tien"
                        min="1"
                        step="1"
                        value="<?= htmlspecialchars($_POST['so_tien'] ?? '') ?>"
                        placeholder="Ví dụ: 1000000"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="ngay_giao_dich">
                        Ngày giao dịch
                    </label>

                    <input
                        type="date"
                        name="ngay_giao_dich"
                        id="ngay_giao_dich"
                        value="<?= htmlspecialchars(
                            $_POST['ngay_giao_dich'] ?? date('Y-m-d')
                        ) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="ghi_chu">
                        Ghi chú
                    </label>

                    <textarea
                        name="ghi_chu"
                        id="ghi_chu"
                        rows="4"
                    ><?= htmlspecialchars($_POST['ghi_chu'] ?? '') ?></textarea>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Thêm giao dịch
                </button>


                <a
                    href="thu-chi.php?clb_id=<?= $clbId ?>"
                    class="btn"
                >
                    Hủy
                </a>

            </form>

        </div>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>