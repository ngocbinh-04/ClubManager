
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$activePage = $activePage ?? '';
$userId = (int) ($_SESSION['user_id'] ?? 0);

$coQuyenQuanLy = false;

/* Kiểm tra người dùng có sở hữu CLB hay không */
if ($userId > 0) {

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM cau_lac_bo
        WHERE nguoi_tao_id = :user_id
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $coQuyenQuanLy =
        (int) $stmt->fetchColumn() > 0;
}

?>

<aside class="sidebar">

    <div class="sidebar-logo">

        <h2>
            <a
                href="index.php"
                title="Về trang chủ ClubManager"
                style="
                    color:inherit;
                    text-decoration:none;
                    cursor:pointer;
                "
            >
                ClubManager
            </a>
        </h2>

    </div>

    <nav class="sidebar-menu">

        <a
            href="index.php"
            class="<?= $activePage === 'index'
                ? 'active' : '' ?>"
        >
            Trang chủ
        </a>

        <?php if ($coQuyenQuanLy): ?>

            <a
                href="clb-quan-tri.php"
                class="<?= $activePage === 'clb-quan-tri'
                    ? 'active' : '' ?>"
            >
                Câu lạc bộ quản lý
            </a>

            <a
                href="thanh-vien.php"
                class="<?= $activePage === 'thanh-vien'
                    ? 'active' : '' ?>"
            >
                Thành viên
            </a>

            <a
                href="su-kien.php"
                class="<?= $activePage === 'su-kien'
                    ? 'active' : '' ?>"
            >
                Sự kiện
            </a>

            <a
                href="diem-danh.php"
                class="<?= $activePage === 'diem-danh'
                    ? 'active' : '' ?>"
            >
                Quản lý điểm danh
            </a>

            <a
                href="thu-chi.php"
                class="<?= $activePage === 'thu-chi'
                    ? 'active' : '' ?>"
            >
                Thu - Chi
            </a>

            <a
                href="thong-ke.php"
                class="<?= $activePage === 'thong-ke'
                    ? 'active' : '' ?>"
            >
                Thống kê
            </a>

        <?php else: ?>

            <a href="index.php#clb-tham-gia">
                Câu lạc bộ của tôi
            </a>

            <a
                href="su-kien.php"
                class="<?= $activePage === 'su-kien'
                    ? 'active' : '' ?>"
            >
                Sự kiện
            </a>

        <?php endif; ?>

        <!-- CẢ THÀNH VIÊN VÀ QUẢN LÝ ĐỀU XEM ĐƯỢC -->
        <a
            href="diem-danh-cua-toi.php"
            class="<?= $activePage === 'diem-danh-cua-toi'
                ? 'active' : '' ?>"
        >
            Điểm danh của tôi
        </a>

        <a href="dang-xuat.php">
            Đăng xuất
        </a>

    </nav>

</aside>
