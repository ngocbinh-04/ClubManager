<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$activePage = $activePage ?? '';
$userId = $_SESSION['user_id'] ?? 0;

$coQuyenQuanLy = false;


/* =====================================================
   KIỂM TRA USER CÓ QUẢN LÝ CLB NÀO KHÔNG
   ===================================================== */

if ($userId > 0) {

    $sql = "
        SELECT COUNT(*)
        FROM cau_lac_bo
        WHERE nguoi_tao_id = :user_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'user_id' => $userId
    ]);

    $coQuyenQuanLy = (int) $stmt->fetchColumn() > 0;
}

?>

<aside class="sidebar">

    <div class="sidebar-logo">

        <h2>
            ClubManager
        </h2>

    </div>


    <nav class="sidebar-menu">


        <a
            href="index.php"
            class="<?= $activePage === 'index' ? 'active' : '' ?>"
        >
            Trang chủ
        </a>


        <?php if ($coQuyenQuanLy): ?>


            <a
                href="clb-quan-tri.php"
                class="<?= $activePage === 'clb-quan-tri' ? 'active' : '' ?>"
            >
                Câu lạc bộ quản lý
            </a>


            <a
                href="thanh-vien.php"
                class="<?= $activePage === 'thanh-vien' ? 'active' : '' ?>"
            >
                Thành viên
            </a>


            <a
                href="su-kien.php"
                class="<?= $activePage === 'su-kien' ? 'active' : '' ?>"
            >
                Sự kiện
            </a>


            <a
                href="diem-danh.php"
                class="<?= $activePage === 'diem-danh' ? 'active' : '' ?>"
            >
                Điểm danh
            </a>


            <a
                href="thu-chi.php"
                class="<?= $activePage === 'thu-chi' ? 'active' : '' ?>"
            >
                Thu - Chi
            </a>


            <a
                href="thong-ke.php"
                class="<?= $activePage === 'thong-ke' ? 'active' : '' ?>"
            >
                Thống kê
            </a>


        <?php else: ?>


            <a href="index.php#clb-tham-gia">
                Câu lạc bộ của tôi
            </a>


            <a
                href="su-kien.php"
                class="<?= $activePage === 'su-kien' ? 'active' : '' ?>"
            >
                Sự kiện
            </a>


        <?php endif; ?>


        <a href="dang-xuat.php">
            Đăng xuất
        </a>


    </nav>

</aside>