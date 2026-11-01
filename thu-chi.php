
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/kiem-tra-thu-chi.php';

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Quản lý Thu - Chi';
$activePage = 'thu-chi';

taoCsrfThuChi();

/* Danh sách CLB do người đăng nhập quản lý */
$stmtClb = $pdo->prepare("
    SELECT id, ten_clb
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
    ORDER BY ten_clb ASC
");

$stmtClb->execute(['user_id' => $userId]);
$danhSachClb = $stmtClb->fetchAll();

$clbId = (int) ($_GET['clb_id'] ?? 0);

if ($clbId <= 0 && $danhSachClb) {
    $clbId = (int) $danhSachClb[0]['id'];
}

$clbHienTai = null;

foreach ($danhSachClb as $clb) {
    if ((int)$clb['id'] === $clbId) {
        $clbHienTai = $clb;
        break;
    }
}

if ($clbId > 0 && !$clbHienTai) {
    http_response_code(403);
    exit('Bạn không có quyền xem tài chính CLB này.');
}

$danhSachThuChi = [];
$tongThu = 0;
$tongChi = 0;
$soDu = 0;

if ($clbHienTai) {

    /* Truy vấn các giao dịch */
    $stmtThuChi = $pdo->prepare("
        SELECT
            tc.id,
            tc.loai,
            tc.noi_dung,
            tc.so_tien,
            tc.ngay_giao_dich,
            tc.ghi_chu,
            nd.ho_ten AS nguoi_thuc_hien

        FROM thu_chi tc

        INNER JOIN nguoi_dung nd
            ON nd.id = tc.nguoi_thuc_hien_id

        WHERE tc.cau_lac_bo_id = :clb_id

        ORDER BY
            tc.ngay_giao_dich DESC,
            tc.id DESC
    ");

    $stmtThuChi->execute(['clb_id' => $clbId]);
    $danhSachThuChi = $stmtThuChi->fetchAll();

    /* Tổng hợp bằng SQL */
    $stmtTong = $pdo->prepare("
        SELECT
            COALESCE(SUM(
                CASE WHEN loai = 'thu'
                     THEN so_tien ELSE 0 END
            ), 0) AS tong_thu,

            COALESCE(SUM(
                CASE WHEN loai = 'chi'
                     THEN so_tien ELSE 0 END
            ), 0) AS tong_chi

        FROM thu_chi
        WHERE cau_lac_bo_id = :clb_id
    ");

    $stmtTong->execute(['clb_id' => $clbId]);
    $tong = $stmtTong->fetch();

    $tongThu = (float) $tong['tong_thu'];
    $tongChi = (float) $tong['tong_chi'];
    $soDu = $tongThu - $tongChi;
}

$successMessages = [
    'them' => 'Thêm giao dịch thành công.',
    'sua' => 'Cập nhật giao dịch thành công.',
    'xoa' => 'Xóa giao dịch thành công.'
];

$success = $_GET['success'] ?? '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Quản lý Thu - Chi</h1>
                <p>Theo dõi tài chính của từng câu lạc bộ.</p>
            </div>

            <?php if ($clbHienTai): ?>
                <a
                    href="them-thu-chi.php?clb_id=<?= $clbId ?>"
                    class="btn btn-primary"
                >
                    + Thêm giao dịch
                </a>
            <?php endif; ?>
        </section>

        <?php if (isset($successMessages[$success])): ?>
            <div class="alert-success">
                <?= hienThiHTML($successMessages[$success]) ?>
            </div>
        <?php endif; ?>

        <?php if (!$danhSachClb): ?>
            <div class="empty-state">
                <p>Bạn chưa quản lý câu lạc bộ nào.</p>
                <a href="them-clb.php" class="btn btn-primary">
                    + Tạo câu lạc bộ
                </a>
            </div>

        <?php else: ?>

            <div class="card">
                <form method="GET" action="thu-chi.php">
                    <div class="form-group">
                        <label for="clb_id">Chọn câu lạc bộ</label>

                        <select id="clb_id" name="clb_id">
                            <?php foreach ($danhSachClb as $clb): ?>
                                <option
                                    value="<?= (int)$clb['id'] ?>"
                                    <?= $clbId === (int)$clb['id']
                                        ? 'selected' : '' ?>
                                >
                                    <?= hienThiHTML($clb['ten_clb']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Xem tài chính
                    </button>
                </form>
            </div>

            <section class="content-section">
                <div class="dashboard-cards">

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
                        <p>Số dư quỹ</p>
                    </div>

                </div>
            </section>

            <section class="content-section">
                <div class="section-header">
                    <div>
                        <h2>
                            <?= hienThiHTML($clbHienTai['ten_clb']) ?>
                        </h2>

                        <p>
                            <?= count($danhSachThuChi) ?>
                            giao dịch
                        </p>
                    </div>
                </div>

                <?php if ($danhSachThuChi): ?>

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
                                            <?= hienThiHTML($gd['ngay_giao_dich']) ?>
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
                                            <?= hienThiHTML($gd['noi_dung']) ?>
                                        </td>

                                        <td>
                                            <?= number_format(
                                                (float)$gd['so_tien'],
                                                0, ',', '.'
                                            ) ?> đ
                                        </td>

                                        <td>
                                            <?= hienThiHTML($gd['nguoi_thuc_hien']) ?>
                                        </td>

                                        <td>
                                            <?= hienThiHTML($gd['ghi_chu']) ?>
                                        </td>

                                        <td>
                                            <a
                                                href="sua-thu-chi.php?id=<?= (int)$gd['id'] ?>&clb_id=<?= $clbId ?>"
                                                class="btn"
                                            >
                                                Sửa
                                            </a>

                                            <form
                                                method="POST"
                                                action="xoa-thu-chi.php"
                                                style="display:inline;"
                                                onsubmit="return confirm('Bạn chắc chắn muốn xóa giao dịch này? Số dư quỹ sẽ được tính lại.');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= hienThiHTML($_SESSION['csrf_token']) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$gd['id'] ?>"
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
                        <p>CLB chưa có giao dịch thu chi nào.</p>
                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
