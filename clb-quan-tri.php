<?php

$pageTitle = 'Quản lý câu lạc bộ';

$userName = 'Nguyễn Văn A';
$userRole = 'Quản trị viên';

$activePage = 'club-admin';

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="content">

    <div class="page-heading">

        <div>

            <h1>
                Danh sách câu lạc bộ
            </h1>

            <p>
                Bạn đang quản lý 3 câu lạc bộ.
            </p>

        </div>

        <button type="button">
            + Thêm câu lạc bộ
        </button>

    </div>


    <section>

        <div class="filter-bar">

            <input
                type="search"
                placeholder="Tìm kiếm câu lạc bộ..."
            >

            <select>

                <option>
                    Tất cả
                </option>

                <option>
                    Đang hoạt động
                </option>

                <option>
                    Tạm dừng
                </option>

            </select>

        </div>


        <div class="club-grid">

            <article class="club-card">

                <div class="club-image">
                    Ảnh CLB
                </div>

                <h2>CLB Tin học</h2>

                <p>
                    Thành viên: 50
                </p>

                <a
                    class="btn-secondary"
                    href="dashboard.php"
                >
                    Quản lý câu lạc bộ
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
                    Thành viên: 35
                </p>

                <a
                    class="btn-secondary"
                    href="dashboard.php"
                >
                    Quản lý câu lạc bộ
                </a>

            </article>


            <article class="club-card">

                <div class="club-image">
                    Ảnh CLB
                </div>

                <h2>
                    CLB Tình nguyện
                </h2>

                <p>
                    Thành viên: 40
                </p>

                <a
                    class="btn-secondary"
                    href="dashboard.php"
                >
                    Quản lý câu lạc bộ
                </a>

            </article>

        </div>

    </section>

</main>

<?php
include 'includes/footer.php';
?>