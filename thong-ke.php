<?php

$pageTitle = 'Thống kê';

$userName = 'Nguyễn Văn A';
$userRole = 'Quản trị viên';

$activePage = 'statistic';

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="content">

    <div class="page-heading">

        <div>

            <h1>
                Thống kê hoạt động
            </h1>

            <p>
                CLB Tin học
            </p>

        </div>


        <select
            id="nam-thong-ke"
            name="nam-thong-ke"
            aria-label="Chọn năm thống kê"
        >

            <option value="2026">
                Năm 2026
            </option>

            <option value="2025">
                Năm 2025
            </option>

        </select>

    </div>


    <section>

        <h2>
            Tổng quan
        </h2>


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


            <article class="stat-card blue">

                <span class="stat-title">
                    Số dư
                </span>

                <strong class="stat-number">
                    2.500.000đ
                </strong>

            </article>

        </div>

    </section>


    <div class="dashboard-grid">

        <section>

            <h2>
                Thu - Chi theo tháng
            </h2>

            <div class="club-detail-image chart-placeholder">
                Biểu đồ Thu - Chi
            </div>

        </section>


        <section>

            <h2>
                Tỷ lệ giao dịch
            </h2>

            <div class="club-detail-image chart-placeholder">
                Biểu đồ tỷ lệ Thu / Chi
            </div>

        </section>

    </div>


    <section>

        <h2>
            Thống kê sự kiện
        </h2>


        <div class="table-wrapper">

            <table>

                <caption>
                    Thống kê sự kiện CLB Tin học
                </caption>

                <thead>

                <tr>

                    <th scope="col">
                        STT
                    </th>

                    <th scope="col">
                        Tên sự kiện
                    </th>

                    <th scope="col">
                        Ngày tổ chức
                    </th>

                    <th scope="col">
                        Số người tham gia
                    </th>

                    <th scope="col">
                        Trạng thái
                    </th>

                </tr>

                </thead>


                <tbody>

                <tr>

                    <td>1</td>

                    <td>
                        Workshop Lập trình Web
                    </td>

                    <td>
                        10/11/2026
                    </td>

                    <td>
                        45
                    </td>

                    <td>

                        <span class="badge badge-green">
                            Đã tổ chức
                        </span>

                    </td>

                </tr>


                <tr>

                    <td>2</td>

                    <td>
                        Cuộc thi Hackathon
                    </td>

                    <td>
                        20/12/2026
                    </td>

                    <td>
                        80
                    </td>

                    <td>

                        <span class="badge badge-orange">
                            Sắp diễn ra
                        </span>

                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </section>

</main>

<?php
include 'includes/footer.php';
?>