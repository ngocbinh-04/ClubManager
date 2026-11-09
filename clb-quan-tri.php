
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Quản lý câu lạc bộ';
$activePage = 'clb-quan-tri';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* Danh sách CLB do người đăng nhập tạo */
$stmt = $pdo->prepare("
    SELECT
        id,
        ten_clb,
        linh_vuc,
        ngay_thanh_lap,
        trang_thai
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
    ORDER BY id DESC
");

$stmt->execute([
    'user_id' => $userId
]);

$danhSachClb = $stmt->fetchAll();

$successMessages = [
    'them' => 'Thêm câu lạc bộ thành công.',
    'sua' => 'Cập nhật câu lạc bộ thành công.',
    'xoa' => 'Xóa câu lạc bộ thành công.'
];

$success = $_GET['success'] ?? '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Quản lý câu lạc bộ</h1>
                <p>
                    Danh sách các câu lạc bộ do bạn tạo và quản lý.
                </p>
            </div>

            <a href="them-clb.php" class="btn btn-primary">
                + Thêm câu lạc bộ
            </a>
        </section>

        <?php if (isset($successMessages[$success])): ?>
            <div class="alert-success">
                <?= e($successMessages[$success]) ?>
            </div>
        <?php endif; ?>

        <?php if ($danhSachClb): ?>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên câu lạc bộ</th>
                            <th>Lĩnh vực</th>
                            <th>Ngày thành lập</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($danhSachClb as $clb): ?>

                            <?php
                            $clbId = (int) $clb['id'];
                            ?>

                            <tr>

                                <td>
                                    <?= $clbId ?>
                                </td>

                                <td>
                                    <?= e($clb['ten_clb']) ?>
                                </td>

                                <td>
                                    <?= e($clb['linh_vuc']) ?>
                                </td>

                                <td>
                                    <?= $clb['ngay_thanh_lap']
                                        ? e($clb['ngay_thanh_lap'])
                                        : 'Chưa cập nhật' ?>
                                </td>

                                <td>
                                    <?php if ($clb['trang_thai'] === 'hoat_dong'): ?>

                                        <span class="badge badge-success">
                                            Hoạt động
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-warning">
                                            Tạm dừng
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div
                                        style="
                                            display:flex;
                                            align-items:center;
                                            gap:8px;
                                            flex-wrap:wrap;
                                        "
                                    >

                                        <a
                                            href="chi-tiet-clb.php?id=<?= $clbId ?>"
                                            class="btn"
                                        >
                                            Xem
                                        </a>

                                        <a
                                            href="sua-clb.php?id=<?= $clbId ?>"
                                            class="btn"
                                        >
                                            Sửa
                                        </a>

                                        <a
                                            href="xoa-clb.php?id=<?= $clbId ?>"
                                            class="btn"
                                        >
                                            Xóa
                                        </a>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <p>
                    Bạn chưa tạo câu lạc bộ nào.
                </p>

                <a
                    href="them-clb.php"
                    class="btn btn-primary"
                >
                    Tạo câu lạc bộ đầu tiên
                </a>

            </div>

        <?php endif; ?>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
