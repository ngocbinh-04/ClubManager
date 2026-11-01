<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Quản lý thành viên';
$activePage = 'thanh-vien';

/* =====================================================
   BẢO MẬT CSRF
   ===================================================== */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =====================================================
   HÀM HỖ TRỢ
   ===================================================== */

function e($value) {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function hienThiVaiTro($vaiTro) {
    $ten = [
        'quan_tri' => 'Quản trị',
        'chu_nhiem' => 'Chủ nhiệm',
        'pho_chu_nhiem' => 'Phó chủ nhiệm',
        'thanh_vien' => 'Thành viên'
    ];

    return $ten[$vaiTro] ?? 'Thành viên';
}

function hienThiTrangThai($trangThai) {
    $ten = [
        'hoat_dong' => 'Hoạt động',
        'tam_dung' => 'Tạm dừng',
        'da_roi' => 'Đã rời'
    ];

    return $ten[$trangThai] ?? $trangThai;
}

/* =====================================================
   1. LẤY DANH SÁCH CLB NGƯỜI DÙNG QUẢN LÝ
   ===================================================== */

$stmtClb = $pdo->prepare("
    SELECT id, ten_clb
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
    ORDER BY ten_clb ASC
");

$stmtClb->execute([
    'user_id' => $userId
]);

$danhSachClb = $stmtClb->fetchAll();

/* =====================================================
   2. XÁC ĐỊNH CLB ĐANG CHỌN
   ===================================================== */

$clbId = filter_input(
    INPUT_GET,
    'clb_id',
    FILTER_VALIDATE_INT
);

$clbId = $clbId ?: 0;

if ($clbId <= 0 && !empty($danhSachClb)) {
    $clbId = (int) $danhSachClb[0]['id'];
}

/* =====================================================
   3. KIỂM TRA QUYỀN QUẢN LÝ CLB
   ===================================================== */

$clbHienTai = null;

if ($clbId > 0) {
    $stmtCheck = $pdo->prepare("
        SELECT id, ten_clb
        FROM cau_lac_bo
        WHERE id = :clb_id
          AND nguoi_tao_id = :user_id
        LIMIT 1
    ");

    $stmtCheck->execute([
        'clb_id' => $clbId,
        'user_id' => $userId
    ]);

    $clbHienTai = $stmtCheck->fetch();

    if (!$clbHienTai) {
        http_response_code(403);
        exit('Bạn không có quyền quản lý câu lạc bộ này.');
    }
}

/* =====================================================
   4. NHẬN DỮ LIỆU TÌM KIẾM
   ===================================================== */

$tuKhoa = trim($_GET['q'] ?? '');
$vaiTro = $_GET['vai_tro'] ?? '';
$trangThai = $_GET['trang_thai'] ?? '';

$vaiTroHopLe = [
    '',
    'quan_tri',
    'chu_nhiem',
    'pho_chu_nhiem',
    'thanh_vien'
];

$trangThaiHopLe = [
    '',
    'hoat_dong',
    'tam_dung',
    'da_roi'
];

if (!in_array($vaiTro, $vaiTroHopLe, true)) {
    $vaiTro = '';
}

if (!in_array($trangThai, $trangThaiHopLe, true)) {
    $trangThai = '';
}

$tuKhoa = mb_substr($tuKhoa, 0, 100);

/* =====================================================
   5. LẤY DANH SÁCH THÀNH VIÊN
   ===================================================== */

$danhSachThanhVien = [];
$tongThanhVien = 0;

if ($clbHienTai) {

    /* Đếm thành viên đang hoạt động */
    $stmtCount = $pdo->prepare("
        SELECT COUNT(*)
        FROM tham_gia_clb
        WHERE cau_lac_bo_id = :clb_id
          AND trang_thai = 'hoat_dong'
    ");

    $stmtCount->execute([
        'clb_id' => $clbId
    ]);

    $tongThanhVien = (int) $stmtCount->fetchColumn();

    /* Truy vấn thành viên */
    $sql = "
        SELECT
            tg.id AS tham_gia_id,
            tg.nguoi_dung_id,
            tg.vai_tro,
            tg.ngay_tham_gia,
            tg.trang_thai,
            nd.ho_ten,
            nd.ten_dang_nhap,
            nd.email,
            nd.so_dien_thoai

        FROM tham_gia_clb tg

        INNER JOIN nguoi_dung nd
            ON nd.id = tg.nguoi_dung_id

        WHERE tg.cau_lac_bo_id = :clb_id
    ";

    $params = [
        'clb_id' => $clbId
    ];

    /* Tìm kiếm tên, username, email */
    if ($tuKhoa !== '') {
        $sql .= "
            AND (
                nd.ho_ten LIKE :q_name
                OR nd.ten_dang_nhap LIKE :q_username
                OR nd.email LIKE :q_email
            )
        ";

        $like = '%' . $tuKhoa . '%';

        $params['q_name'] = $like;
        $params['q_username'] = $like;
        $params['q_email'] = $like;
    }

    /* Lọc vai trò */
    if ($vaiTro !== '') {
        $sql .= " AND tg.vai_tro = :vai_tro ";

        $params['vai_tro'] = $vaiTro;
    }

    /* Lọc trạng thái */
    if ($trangThai !== '') {
        $sql .= " AND tg.trang_thai = :trang_thai ";

        $params['trang_thai'] = $trangThai;
    }

    $sql .= "
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

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $danhSachThanhVien = $stmt->fetchAll();
}

/* =====================================================
   6. THÔNG BÁO
   ===================================================== */

$coBoLoc = (
    $tuKhoa !== ''
    || $vaiTro !== ''
    || $trangThai !== ''
);

$successMessages = [
    'them' => 'Thêm thành viên thành công.',
    'sua' => 'Cập nhật thành viên thành công.',
    'xoa' => 'Thành viên đã rời câu lạc bộ.'
];

$success = $_GET['success'] ?? '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <!-- TIÊU ĐỀ -->
        <section class="page-header">
            <div>
                <h1>Quản lý thành viên</h1>

                <p>
                    Tra cứu và quản lý thành viên theo từng câu lạc bộ.
                </p>
            </div>
        </section>

        <!-- THÔNG BÁO -->
        <?php if (isset($successMessages[$success])): ?>
            <div class="alert-success">
                <?= e($successMessages[$success]) ?>
            </div>
        <?php endif; ?>

        <!-- CHƯA CÓ CLB QUẢN LÝ -->
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
                            id="clb_id"
                            name="clb_id"
                        >

                            <?php foreach ($danhSachClb as $clb): ?>

                                <option
                                    value="<?= (int) $clb['id'] ?>"
                                    <?= $clbId === (int) $clb['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= e($clb['ten_clb']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Xem thành viên
                    </button>

                </form>

            </div>

            <?php if ($clbHienTai): ?>

                <section class="content-section">

                    <!-- THÔNG TIN CLB -->
                    <div class="section-header">

                        <div>
                            <h2>
                                <?= e($clbHienTai['ten_clb']) ?>
                            </h2>

                            <p>
                                <?= $tongThanhVien ?>
                                thành viên đang hoạt động
                            </p>
                        </div>

                        <a
                            href="them-thanh-vien.php?clb_id=<?= $clbId ?>"
                            class="btn btn-primary"
                        >
                            + Thêm thành viên
                        </a>

                    </div>

                    <!-- TÌM KIẾM THÀNH VIÊN -->
                    <div
                        class="card"
                        style="margin-bottom:20px;"
                    >

                        <form
                            action="thanh-vien.php"
                            method="GET"
                        >

                            <input
                                type="hidden"
                                name="clb_id"
                                value="<?= $clbId ?>"
                            >

                            <div
                                style="
                                    display:grid;
                                    grid-template-columns:
                                        repeat(auto-fit,minmax(190px,1fr));
                                    gap:14px;
                                    align-items:end;
                                "
                            >

                                <!-- TỪ KHÓA -->
                                <div class="form-group">

                                    <label for="q">
                                        Tìm kiếm thành viên
                                    </label>

                                    <input
                                        id="q"
                                        name="q"
                                        type="search"
                                        placeholder="Tên, tài khoản, email..."
                                        value="<?= e($tuKhoa) ?>"
                                    >

                                </div>

                                <!-- VAI TRÒ -->
                                <div class="form-group">

                                    <label for="vai_tro">
                                        Vai trò
                                    </label>

                                    <select
                                        id="vai_tro"
                                        name="vai_tro"
                                    >

                                        <option value="">
                                            Tất cả vai trò
                                        </option>

                                        <?php foreach (
                                            [
                                                'quan_tri',
                                                'chu_nhiem',
                                                'pho_chu_nhiem',
                                                'thanh_vien'
                                            ] as $role
                                        ): ?>

                                            <option
                                                value="<?= e($role) ?>"
                                                <?= $vaiTro === $role
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                <?= e(hienThiVaiTro($role)) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                                <!-- TRẠNG THÁI -->
                                <div class="form-group">

                                    <label for="trang_thai">
                                        Trạng thái
                                    </label>

                                    <select
                                        id="trang_thai"
                                        name="trang_thai"
                                    >

                                        <option value="">
                                            Tất cả trạng thái
                                        </option>

                                        <?php foreach (
                                            [
                                                'hoat_dong',
                                                'tam_dung',
                                                'da_roi'
                                            ] as $status
                                        ): ?>

                                            <option
                                                value="<?= e($status) ?>"
                                                <?= $trangThai === $status
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                <?= e(hienThiTrangThai($status)) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                            <div
                                style="
                                    display:flex;
                                    gap:10px;
                                    flex-wrap:wrap;
                                "
                            >

                                <button
                                    class="btn btn-primary"
                                    type="submit"
                                >
                                    Tìm kiếm
                                </button>

                                <a
                                    href="thanh-vien.php?clb_id=<?= $clbId ?>"
                                    class="btn"
                                >
                                    Xóa bộ lọc
                                </a>

                            </div>

                        </form>

                    </div>

                    <!-- SỐ KẾT QUẢ -->
                    <p
                        style="
                            margin-bottom:12px;
                            color:#6d7f79;
                        "
                    >
                        Kết quả:
                        <strong>
                            <?= count($danhSachThanhVien) ?>
                        </strong>
                        bản ghi
                    </p>

                    <!-- BẢNG THÀNH VIÊN -->
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
                                                <?= e($tv['ho_ten']) ?>
                                            </td>

                                            <td>
                                                <?= e($tv['ten_dang_nhap']) ?>
                                            </td>

                                            <td>
                                                <?= e($tv['email']) ?>
                                            </td>

                                            <td>
                                                <?= e(hienThiVaiTro($tv['vai_tro'])) ?>
                                            </td>

                                            <td>
                                                <?= e($tv['ngay_tham_gia']) ?>
                                            </td>

                                            <!-- TRẠNG THÁI -->
                                            <td>
                                                <?php
                                                $badgeClass = '';

                                                if ($tv['trang_thai'] === 'hoat_dong') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($tv['trang_thai'] === 'tam_dung') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                                ?>

                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= e(hienThiTrangThai($tv['trang_thai'])) ?>
                                                </span>
                                            </td>

                                            <!-- THAO TÁC -->
                                            <td>

                                                <a
                                                    class="btn"
                                                    href="sua-thanh-vien.php?id=<?= (int)$tv['tham_gia_id'] ?>&clb_id=<?= $clbId ?>"
                                                >
                                                    Sửa
                                                </a>

                                                <?php if (
                                                    (int)$tv['nguoi_dung_id'] !== $userId
                                                    && $tv['trang_thai'] !== 'da_roi'
                                                ): ?>

                                                    <form
                                                        method="POST"
                                                        action="xoa-thanh-vien.php"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Bạn có chắc muốn cho thành viên này rời câu lạc bộ không?');"
                                                    >

                                                        <!-- CSRF TOKEN -->
                                                        <input
                                                            type="hidden"
                                                            name="csrf_token"
                                                            value="<?= e($_SESSION['csrf_token']) ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="tham_gia_id"
                                                            value="<?= (int)$tv['tham_gia_id'] ?>"
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
                                                            Cho rời CLB
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
                                <?= $coBoLoc
                                    ? 'Không tìm thấy thành viên phù hợp.'
                                    : 'Câu lạc bộ chưa có thành viên.' ?>
                            </p>

                            <?php if ($coBoLoc): ?>

                                <a
                                    class="btn"
                                    href="thanh-vien.php?clb_id=<?= $clbId ?>"
                                >
                                    Xem tất cả thành viên
                                </a>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        <?php endif; ?>

    </main>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
