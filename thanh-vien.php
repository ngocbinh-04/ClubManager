<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$pageTitle = 'Quản lý thành viên';
$activePage = 'thanh-vien';


/* =====================================================
   1. LẤY DANH SÁCH CLB DO USER QUẢN LÝ
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
   2. XÁC ĐỊNH CLB ĐANG CHỌN
   ===================================================== */

$clbId = (int) ($_GET['clb_id'] ?? 0);

/* Nếu chưa chọn thì lấy CLB đầu tiên */
if ($clbId <= 0 && !empty($danhSachClb)) {
    $clbId = (int) $danhSachClb[0]['id'];
}


/* =====================================================
   3. KIỂM TRA CLB CÓ THUỘC USER KHÔNG
   ===================================================== */

$clbHienTai = null;

if ($clbId > 0) {

    $sqlCheck = "
        SELECT *
        FROM cau_lac_bo
        WHERE id = :clb_id
          AND nguoi_tao_id = :user_id
        LIMIT 1
    ";

    $stmtCheck = $pdo->prepare($sqlCheck);

    $stmtCheck->execute([
        'clb_id' => $clbId,
        'user_id' => $userId
    ]);

    $clbHienTai = $stmtCheck->fetch();

    if (!$clbHienTai) {
        header('Location: thanh-vien.php');
        exit;
    }
}


/* =====================================================
   4. LẤY DANH SÁCH THÀNH VIÊN
   ===================================================== */

$danhSachThanhVien = [];

if ($clbId > 0) {

    $sqlThanhVien = "
        SELECT
            tg.id AS tham_gia_id,
            tg.vai_tro,
            tg.ngay_tham_gia,
            tg.trang_thai,
            nd.id AS nguoi_dung_id,
            nd.ho_ten,
            nd.ten_dang_nhap,
            nd.email,
            nd.so_dien_thoai
        FROM tham_gia_clb tg

        INNER JOIN nguoi_dung nd
            ON tg.nguoi_dung_id = nd.id

        WHERE tg.cau_lac_bo_id = :clb_id

        ORDER BY
            FIELD(
                tg.vai_tro,
                'quan_tri',
                'chu_nhiem',
                'pho_chu_nhiem',
                'thanh_vien'
            ),
            nd.ho_ten ASC
    ";

    $stmtThanhVien = $pdo->prepare($sqlThanhVien);

    $stmtThanhVien->execute([
        'clb_id' => $clbId
    ]);

    $danhSachThanhVien = $stmtThanhVien->fetchAll();
}

?>

<?php require_once 'includes/header.php'; ?>

<div class="app-layout">

    <?php require_once 'includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">

            <div>
                <h1>Quản lý thành viên</h1>

                <p>
                    Quản lý thành viên của từng câu lạc bộ.
                </p>
            </div>

        </section>


        <?php if (!empty($_GET['success'])): ?>

            <div class="alert-success">

                <?php if ($_GET['success'] === 'them'): ?>
                    Thêm thành viên thành công.

                <?php elseif ($_GET['success'] === 'sua'): ?>
                    Cập nhật thành viên thành công.

                <?php elseif ($_GET['success'] === 'xoa'): ?>
                    Thành viên đã rời câu lạc bộ.
                <?php endif; ?>

            </div>

        <?php endif; ?>


        <?php if (empty($danhSachClb)): ?>

            <div class="empty-state">

                <p>
                    Bạn chưa quản lý câu lạc bộ nào.
                </p>

                <a
                    href="them-clb.php"
                    class="btn btn-primary"
                >
                    + Tạo câu lạc bộ
                </a>

            </div>

        <?php else: ?>


            <!-- CHỌN CLB -->

            <div class="card">

                <form
                    method="GET"
                    action="thanh-vien.php"
                >

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


            <?php if ($clbHienTai): ?>

                <section class="content-section">

                    <div class="section-header">

                        <div>

                            <h2>
                                <?= htmlspecialchars($clbHienTai['ten_clb']) ?>
                            </h2>

                            <p>
                                Tổng số:
                                <?= count($danhSachThanhVien) ?>
                                thành viên
                            </p>

                        </div>


                        <a
                            href="them-thanh-vien.php?clb_id=<?= $clbId ?>"
                            class="btn btn-primary"
                        >
                            + Thêm thành viên
                        </a>

                    </div>


                    <?php if (!empty($danhSachThanhVien)): ?>

                        <div class="table-wrapper">

                            <table>

                                <thead>

                                    <tr>

                                        <th>Họ tên</th>

                                        <th>Tên đăng nhập</th>

                                        <th>Email</th>

                                        <th>Vai trò</th>

                                        <th>Ngày tham gia</th>

                                        <th>Trạng thái</th>

                                        <th>Thao tác</th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php foreach ($danhSachThanhVien as $tv): ?>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($tv['ho_ten']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($tv['ten_dang_nhap']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($tv['email'] ?? '') ?>
                                            </td>

                                            <td>

                                                <?php
                                                switch ($tv['vai_tro']) {

                                                    case 'quan_tri':
                                                        echo 'Quản trị';
                                                        break;

                                                    case 'chu_nhiem':
                                                        echo 'Chủ nhiệm';
                                                        break;

                                                    case 'pho_chu_nhiem':
                                                        echo 'Phó chủ nhiệm';
                                                        break;

                                                    default:
                                                        echo 'Thành viên';
                                                        break;
                                                }
                                                ?>

                                            </td>

                                            <td>
                                                <?= htmlspecialchars($tv['ngay_tham_gia']) ?>
                                            </td>

                                            <td>

                                                <?php if ($tv['trang_thai'] === 'hoat_dong'): ?>

                                                    <span class="badge badge-success">
                                                        Hoạt động
                                                    </span>

                                                <?php elseif ($tv['trang_thai'] === 'tam_dung'): ?>

                                                    <span class="badge badge-warning">
                                                        Tạm dừng
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge">
                                                        Đã rời
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <a
                                                    href="sua-thanh-vien.php?id=<?= $tv['tham_gia_id'] ?>&clb_id=<?= $clbId ?>"
                                                    class="btn"
                                                >
                                                    Sửa
                                                </a>


                                                <?php if ($tv['nguoi_dung_id'] != $userId): ?>

                                                    <form
                                                        method="POST"
                                                        action="xoa-thanh-vien.php"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Bạn có chắc muốn cho thành viên này rời câu lạc bộ không?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="tham_gia_id"
                                                            value="<?= $tv['tham_gia_id'] ?>"
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

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="empty-state">

                            <p>
                                Câu lạc bộ chưa có thành viên.
                            </p>

                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        <?php endif; ?>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>