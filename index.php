<?php

$pageTitle = 'Câu lạc bộ của tôi';

$userName = 'Nguyễn Văn A';
$userRole = 'Thành viên';

$activePage = 'club-member';

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="content">

    <div class="page-heading">

        <div>

            <h1>
                Câu lạc bộ của tôi
            </h1>

            <p>
                Bạn đang tham gia 2 câu lạc bộ.
            </p>

        </div>

    </div>


    <section>

        <div class="filter-bar">

            <input
                type="search"
                placeholder="Tìm kiếm câu lạc bộ..."
            >

        </div>


        <div class="club-grid">

            <article class="club-card">

                <div class="club-image">
                    Ảnh CLB
                </div>

                <h2>
                    CLB Tin học
                </h2>

                <p>
                    <strong>Thành viên:</strong>
                    50
                </p>

                <p>
                    <strong>Vai trò:</strong>
                    Thành viên
                </p>

                <p class="club-description">
                    Câu lạc bộ dành cho sinh viên yêu thích
                    công nghệ thông tin và lập trình.
                </p>

                <a
                    class="btn-secondary"
                    href="chi-tiet-clb.php"
                >
                    Xem chi tiết
                </a>

            </article>


            <article class="club-card">

                <div class="club-image">
                    Ảnh CLB
                </div>

                <h2>
                    CLB Tiếng Anh
                </h2>

                <p>
                    <strong>Thành viên:</strong>
                    35
                </p>

                <p>
                    <strong>Vai trò:</strong>
                    Thành viên
                </p>

                <p class="club-description">
                    Nơi giao lưu, học tập và rèn luyện
                    kỹ năng tiếng Anh.
                </p>

                <a
                    class="btn-secondary"
                    href="chi-tiet-clb.php"
                >
                    Xem chi tiết
                </a>

            </article>

        </div>

    </section>

</main>

<?php
include 'includes/footer.php';
?>