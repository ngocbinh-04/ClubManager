<?php
if (!isset($activePage)) {
    $activePage = '';
}

if (!isset($userRole)) {
    $userRole = 'Quản trị viên';
}
?>

<nav class="sidebar">

    <ul>

        <?php if ($userRole === 'Thành viên') : ?>

            <li>
                <a
                    href="index.php"
                    class="<?php echo $activePage === 'club-member' ? 'active' : ''; ?>"
                >
                    Câu lạc bộ của tôi
                </a>
            </li>

            <li>
                <a
                    href="chi-tiet-clb.php"
                    class="<?php echo $activePage === 'club-detail' ? 'active' : ''; ?>"
                >
                    Thông tin CLB
                </a>
            </li>

            <li>
                <a href="dang-nhap.php">
                    Đăng xuất
                </a>
            </li>

        <?php else : ?>

            <li>
                <a
                    href="clb-quan-tri.php"
                    class="<?php echo $activePage === 'club-admin' ? 'active' : ''; ?>"
                >
                    Câu lạc bộ
                </a>
            </li>

            <li>
                <a
                    href="dashboard.php"
                    class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>"
                >
                    Tổng quan
                </a>
            </li>

            <li>
                <a
                    href="thanh-vien.php"
                    class="<?php echo $activePage === 'member' ? 'active' : ''; ?>"
                >
                    Thành viên
                </a>
            </li>

            <li>
                <a
                    href="su-kien.php"
                    class="<?php echo $activePage === 'event' ? 'active' : ''; ?>"
                >
                    Sự kiện
                </a>
            </li>

            <li>
                <a
                    href="thu-chi.php"
                    class="<?php echo $activePage === 'finance' ? 'active' : ''; ?>"
                >
                    Thu - Chi
                </a>
            </li>

            <li>
                <a
                    href="thong-ke.php"
                    class="<?php echo $activePage === 'statistic' ? 'active' : ''; ?>"
                >
                    Thống kê
                </a>
            </li>

            <li>
                <a href="dang-nhap.php">
                    Đăng xuất
                </a>
            </li>

        <?php endif; ?>

    </ul>

</nav>