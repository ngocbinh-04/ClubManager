<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$pageTitle = 'Quản lý câu lạc bộ';
$activePage = 'clb-quan-tri';


/* Lấy các CLB do người đang đăng nhập tạo */
$sql = "
    SELECT *
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
    ORDER BY id DESC
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'user_id' => $userId
]);

$danhSachClb = $stmt->fetchAll();

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>
                <h1>Quản lý câu lạc bộ</h1>

                <p>
                    Danh sách các câu lạc bộ do bạn tạo và quản lý.
                </p>
            </div>

            <a
                href="them-clb.php"
                class="btn btn-primary"
            >
                + Thêm câu lạc bộ
            </a>

        </section>


        <?php if (!empty($_GET['success'])): ?>

            <div class="alert-success">

                <?php if ($_GET['success'] === 'them'): ?>
                    Thêm câu lạc bộ thành công.
                <?php elseif ($_GET['success'] === 'sua'): ?>
                    Cập nhật câu lạc bộ thành công.
                <?php elseif ($_GET['success'] === 'xoa'): ?>
                    Xóa câu lạc bộ thành công.
                <?php endif; ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($danhSachClb)): ?>

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

                            <tr>

                                <td>
                                    <?= $clb['id'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($clb['ten_clb']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($clb['linh_vuc']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($clb['ngay_thanh_lap']) ?>
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

                                    <a
                                        href="chi-tiet-clb.php?id=<?= $clb['id'] ?>"
                                        class="btn"
                                    >
                                        Xem
                                    </a>

                                    <a
                                        href="sua-clb.php?id=<?= $clb['id'] ?>"
                                        class="btn"
                                    >
                                        Sửa
                                    </a>

                                    <a
                                        href="xoa-clb.php?id=<?= $clb['id'] ?>"
                                        class="btn"
                                        onclick="return confirm('Bạn có chắc muốn xóa câu lạc bộ này không?');"
                                    >
                                        Xóa
                                    </a>

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

<?php require_once 'includes/footer.php'; ?>