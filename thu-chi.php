<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$pageTitle = 'Quản lý Thu - Chi';
$activePage = 'thu-chi';


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


/* =====================================================
   2. CLB ĐANG CHỌN
   ===================================================== */

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
    header('Location: thu-chi.php');
    exit;
}


/* =====================================================
   3. LẤY DANH SÁCH THU CHI
   ===================================================== */

$danhSachThuChi = [];

if ($clbId > 0) {

    $sqlThuChi = "
        SELECT
            tc.*,
            nd.ho_ten AS nguoi_thuc_hien

        FROM thu_chi tc

        INNER JOIN nguoi_dung nd
            ON tc.nguoi_thuc_hien_id = nd.id

        WHERE tc.cau_lac_bo_id = :clb_id

        ORDER BY tc.ngay_giao_dich DESC, tc.id DESC
    ";

    $stmtThuChi = $pdo->prepare($sqlThuChi);

    $stmtThuChi->execute([
        'clb_id' => $clbId
    ]);

    $danhSachThuChi = $stmtThuChi->fetchAll();
}


/* =====================================================
   4. TÍNH TỔNG THU / CHI / SỐ DƯ
   ===================================================== */

$tongThu = 0;
$tongChi = 0;

foreach ($danhSachThuChi as $giaoDich) {

    if ($giaoDich['loai'] === 'thu') {
        $tongThu += (float) $giaoDich['so_tien'];
    }

    if ($giaoDich['loai'] === 'chi') {
        $tongChi += (float) $giaoDich['so_tien'];
    }
}

$soDu = $tongThu - $tongChi;

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>

                <h1>Quản lý Thu - Chi</h1>

                <p>
                    Theo dõi tài chính của từng câu lạc bộ.
                </p>

            </div>


            <?php if ($clbId > 0): ?>

                <a
                    href="them-thu-chi.php?clb_id=<?= $clbId ?>"
                    class="btn btn-primary"
                >
                    + Thêm giao dịch
                </a>

            <?php endif; ?>

        </section>


        <?php if (!empty($_GET['success'])): ?>

            <div class="alert-success">

                <?php if ($_GET['success'] === 'them'): ?>

                    Thêm giao dịch thành công.

                <?php elseif ($_GET['success'] === 'sua'): ?>

                    Cập nhật giao dịch thành công.

                <?php elseif ($_GET['success'] === 'xoa'): ?>

                    Xóa giao dịch thành công.

                <?php endif; ?>

            </div>

        <?php endif; ?>


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


            <!-- THỐNG KÊ TÀI CHÍNH -->

            <section class="dashboard-cards">

                <div class="stat-card">

                    <h3>
                        <?= number_format($tongThu, 0, ',', '.') ?> đ
                    </h3>

                    <p>Tổng thu</p>

                </div>


                <div class="stat-card">

                    <h3>
                        <?= number_format($tongChi, 0, ',', '.') ?> đ
                    </h3>

                    <p>Tổng chi</p>

                </div>


                <div class="stat-card">

                    <h3>
                        <?= number_format($soDu, 0, ',', '.') ?> đ
                    </h3>

                    <p>Số dư</p>

                </div>

            </section>


            <section class="content-section">

                <div class="section-header">

                    <div>

                        <h2>
                            <?= htmlspecialchars($clbHienTai['ten_clb']) ?>
                        </h2>

                        <p>
                            <?= count($danhSachThuChi) ?>
                            giao dịch
                        </p>

                    </div>

                </div>


                <?php if (!empty($danhSachThuChi)): ?>

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>Ngày</th>

                                    <th>Loại</th>

                                    <th>Nội dung</th>

                                    <th>Số tiền</th>

                                    <th>Người thực hiện</th>

                                    <th>Ghi chú</th>

                                    <th>Thao tác</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($danhSachThuChi as $gd): ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($gd['ngay_giao_dich']) ?>
                                        </td>


                                        <td>

                                            <?php if ($gd['loai'] === 'thu'): ?>

                                                <span class="badge badge-success">
                                                    Thu
                                                </span>

                                            <?php else: ?>

                                                <span class="badge badge-warning">
                                                    Chi
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td>
                                            <?= htmlspecialchars($gd['noi_dung']) ?>
                                        </td>


                                        <td>
                                            <?= number_format(
                                                $gd['so_tien'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?> đ
                                        </td>


                                        <td>
                                            <?= htmlspecialchars($gd['nguoi_thuc_hien']) ?>
                                        </td>


                                        <td>
                                            <?= htmlspecialchars($gd['ghi_chu'] ?? '') ?>
                                        </td>


                                        <td>

                                            <a
                                                href="sua-thu-chi.php?id=<?= $gd['id'] ?>&clb_id=<?= $clbId ?>"
                                                class="btn"
                                            >
                                                Sửa
                                            </a>


                                            <form
                                                method="POST"
                                                action="xoa-thu-chi.php"
                                                style="display:inline;"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa giao dịch này không?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $gd['id'] ?>"
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

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <p>
                            Chưa có giao dịch thu chi nào.
                        </p>

                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>