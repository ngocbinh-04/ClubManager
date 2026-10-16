<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

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


$error = '';


/* =====================================================
   THÊM THÀNH VIÊN
   ===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nguoiDungId = (int) ($_POST['nguoi_dung_id'] ?? 0);

    $vaiTro = $_POST['vai_tro'] ?? 'thanh_vien';


    $vaiTroHopLe = [
        'chu_nhiem',
        'pho_chu_nhiem',
        'thanh_vien'
    ];


    if (
        $nguoiDungId <= 0 ||
        !in_array($vaiTro, $vaiTroHopLe, true)
    ) {

        $error = 'Thông tin thành viên không hợp lệ.';

    } else {

        /* Kiểm tra người này đã có trong CLB chưa */

        $sqlCheck = "
            SELECT id
            FROM tham_gia_clb
            WHERE nguoi_dung_id = :nguoi_dung_id
              AND cau_lac_bo_id = :clb_id
            LIMIT 1
        ";

        $stmtCheck = $pdo->prepare($sqlCheck);

        $stmtCheck->execute([
            'nguoi_dung_id' => $nguoiDungId,
            'clb_id' => $clbId
        ]);

        $daTonTai = $stmtCheck->fetch();


        if ($daTonTai) {

            $error = 'Người dùng này đã thuộc câu lạc bộ.';

        } else {

            $sqlInsert = "
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
                    :clb_id,
                    :vai_tro,
                    CURDATE(),
                    'hoat_dong'
                )
            ";

            $stmtInsert = $pdo->prepare($sqlInsert);

            $stmtInsert->execute([
                'nguoi_dung_id' => $nguoiDungId,
                'clb_id' => $clbId,
                'vai_tro' => $vaiTro
            ]);

            header(
                'Location: thanh-vien.php?clb_id='
                . $clbId
                . '&success=them'
            );

            exit;
        }
    }
}


/* =====================================================
   LẤY USER CHƯA THAM GIA CLB
   ===================================================== */

$sqlNguoiDung = "
    SELECT
        nd.id,
        nd.ho_ten,
        nd.ten_dang_nhap,
        nd.email

    FROM nguoi_dung nd

    WHERE nd.trang_thai = 'hoat_dong'

      AND nd.id NOT IN
      (
          SELECT nguoi_dung_id
          FROM tham_gia_clb
          WHERE cau_lac_bo_id = :clb_id
      )

    ORDER BY nd.ho_ten ASC
";

$stmtNguoiDung = $pdo->prepare($sqlNguoiDung);

$stmtNguoiDung->execute([
    'clb_id' => $clbId
]);

$danhSachNguoiDung = $stmtNguoiDung->fetchAll();


$pageTitle = 'Thêm thành viên';
$activePage = 'thanh-vien';

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>Thêm thành viên</h1>

                <p>
                    Câu lạc bộ:
                    <strong>
                        <?= htmlspecialchars($clb['ten_clb']) ?>
                    </strong>
                </p>

            </div>

        </section>


        <?php if ($error !== ''): ?>

            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <div class="card">

            <?php if (!empty($danhSachNguoiDung)): ?>

                <form method="POST">

                    <div class="form-group">

                        <label for="nguoi_dung_id">
                            Chọn người dùng
                        </label>

                        <select
                            name="nguoi_dung_id"
                            id="nguoi_dung_id"
                            required
                        >

                            <option value="">
                                -- Chọn người dùng --
                            </option>

                            <?php foreach ($danhSachNguoiDung as $nd): ?>

                                <option value="<?= $nd['id'] ?>">

                                    <?= htmlspecialchars($nd['ho_ten']) ?>

                                    -

                                    <?= htmlspecialchars($nd['ten_dang_nhap']) ?>

                                    <?php if (!empty($nd['email'])): ?>

                                        (<?= htmlspecialchars($nd['email']) ?>)

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="vai_tro">
                            Vai trò
                        </label>

                        <select
                            name="vai_tro"
                            id="vai_tro"
                        >

                            <option value="thanh_vien">
                                Thành viên
                            </option>

                            <option value="pho_chu_nhiem">
                                Phó chủ nhiệm
                            </option>

                            <option value="chu_nhiem">
                                Chủ nhiệm
                            </option>

                        </select>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Thêm thành viên
                    </button>


                    <a
                        href="thanh-vien.php?clb_id=<?= $clbId ?>"
                        class="btn"
                    >
                        Hủy
                    </a>

                </form>

            <?php else: ?>

                <div class="empty-state">

                    <p>
                        Không còn tài khoản nào để thêm vào câu lạc bộ.
                    </p>

                    <a
                        href="thanh-vien.php?clb_id=<?= $clbId ?>"
                        class="btn"
                    >
                        Quay lại
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>