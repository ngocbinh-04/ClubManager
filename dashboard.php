<?php

$pageTitle = 'Dashboard';

$userName = 'Nguyễn Văn A';
$userRole = 'Quản trị viên';

$activePage = 'dashboard';

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="content">

    <div class="page-heading">

        <div>

            <h1>
                Dashboard - CLB Tin học
            </h1>

            <p>
                Tổng quan hoạt động câu lạc bộ.
            </p>

        </div>

    </div>


    <section>

        <div class="stats-grid">

            <article class="stat-card">

                <span class="stat-title">
                    Thành viên
                </span>

                <strong class="stat-number">
                    50
                </strong>

            </article>


            <article class="stat-card blue">

                <span class="stat-title">
                    Sự kiện
                </span>

                <strong class="stat-number">
                    8
                </strong>

            </article>


            <article class="stat-card">

                <span class="stat-title">
                    Tổng thu
                </span>

                <strong class="stat-number">
                    5.000.000đ
                </strong>

            </article>


            <article class="stat-card red">

                <span class="stat-title">
                    Tổng chi
                </span>

                <strong class="stat-number">
                    2.500.000đ
                </strong>

            </article>

        </div>

    </section>


    <div class="dashboard-grid">

        <section>

            <div class="section-heading">

                <h2>
                    Sự kiện sắp diễn ra
                </h2>

                <a href="su-kien.php">
                    Xem tất cả
                </a>

            </div>


            <div class="event-item">

                <div>

                    <strong>
                        Workshop Lập trình Web
                    </strong>

                    <p>
                        10/11/2026 - Phòng A1
                    </p>

                </div>

                <span class="badge badge-orange">
                    Sắp diễn ra
                </span>

            </div>


            <div class="event-item">

                <div>

                    <strong>
                        Cuộc thi Hackathon
                    </strong>

                    <p>
                        20/12/2026 - Hội trường
                    </p>

                </div>

                <span class="badge badge-orange">
                    Sắp diễn ra
                </span>

            </div>

        </section>


        <section>

            <div class="section-heading">

                <h2>
                    Thu - chi gần đây
                </h2>

                <a href="thu-chi.php">
                    Xem tất cả
                </a>

            </div>


            <div class="transaction-item">

                <div>

                    <span class="badge badge-green">
                        Thu
                    </span>

                    Quỹ thành viên

                </div>

                <strong class="money-positive">
                    +1.000.000đ
                </strong>

            </div>


            <div class="transaction-item">

                <div>

                    <span class="badge badge-red">
                        Chi
                    </span>

                    Mua tài liệu

                </div>

                <strong class="money-negative">
                    -500.000đ
                </strong>

            </div>

        </section>

    </div>

</main>

<?php
include 'includes/footer.php';
?>