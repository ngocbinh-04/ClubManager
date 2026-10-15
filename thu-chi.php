<?php

$pageTitle = 'Quản lý Thu - Chi';

$userName = 'Nguyễn Văn A';
$userRole = 'Quản trị viên';

$activePage = 'finance';

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="content">

    <div class="page-heading">

        <div>

            <h1>
                Danh sách giao dịch
            </h1>

            <p>
                CLB Tin học
            </p>

        </div>


        <div>

            <button type="button">
                + Thêm khoản thu
            </button>

            <button
                type="button"
                class="btn-danger"
            >
                + Thêm khoản chi
            </button>

        </div>

    </div>


    <section>

        <div class="stats-grid">

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


    <section>

        <div class="filter-bar">

            <input
                type="search"
                placeholder="Tìm theo nội dung giao dịch..."
            >

            <select>

                <option>
                    Tất cả loại
                </option>

                <option>
                    Thu
                </option>

                <option>
                    Chi
                </option>

            </select>

        </div>


        <div class="table-wrapper">

            <table>

                <caption>
                    Danh sách thu - chi
                </caption>

                <thead>

                <tr>

                    <th scope="col">
                        STT
                    </th>

                    <th scope="col">
                        Ngày
                    </th>

                    <th scope="col">
                        Loại
                    </th>

                    <th scope="col">
                        Nội dung
                    </th>

                    <th scope="col">
                        Số tiền
                    </th>

                    <th scope="col">
                        Người thực hiện
                    </th>

                    <th scope="col">
                        Thao tác
                    </th>

                </tr>

                </thead>


                <tbody>

                <tr>

                    <td>1</td>

                    <td>
                        05/10/2026
                    </td>

                    <td>

                        <span class="badge badge-green">
                            Thu
                        </span>

                    </td>

                    <td>
                        Quỹ thành viên
                    </td>

                    <td>
                        1.000.000đ
                    </td>

                    <td>
                        Nguyễn Văn A
                    </td>

                    <td class="table-actions">

                        <button>
                            Sửa
                        </button>

                        <button class="btn-danger">
                            Xóa
                        </button>

                    </td>

                </tr>


                <tr>

                    <td>2</td>

                    <td>
                        10/10/2026
                    </td>

                    <td>

                        <span class="badge badge-red">
                            Chi
                        </span>

                    </td>

                    <td>
                        Mua tài liệu
                    </td>

                    <td>
                        500.000đ
                    </td>

                    <td>
                        Nguyễn Văn A
                    </td>

                    <td class="table-actions">

                        <button>
                            Sửa
                        </button>

                        <button class="btn-danger">
                            Xóa
                        </button>

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