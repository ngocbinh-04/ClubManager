<?php

$pageTitle = 'Quản lý thành viên';

$userName = 'Nguyễn Văn A';
$userRole = 'Quản trị viên';

$activePage = 'member';

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="content">

    <div class="page-heading">

        <div>

            <h1>
                Danh sách thành viên
            </h1>

            <p>
                CLB Tin học
            </p>

        </div>

        <button type="button">
            + Thêm thành viên
        </button>

    </div>


    <section>

        <div class="filter-bar">

            <input
                type="search"
                placeholder="Tìm theo tên, mã sinh viên..."
            >

            <select>

                <option>
                    Tất cả vai trò
                </option>

                <option>
                    Chủ nhiệm
                </option>

                <option>
                    Thành viên
                </option>

            </select>

        </div>


        <div class="table-wrapper">

            <table>

                <caption>
                    Danh sách thành viên CLB Tin học
                </caption>

                <thead>

                <tr>

                    <th scope="col">
                        STT
                    </th>

                    <th scope="col">
                        Họ tên
                    </th>

                    <th scope="col">
                        Mã SV
                    </th>

                    <th scope="col">
                        Vai trò
                    </th>

                    <th scope="col">
                        Email
                    </th>

                    <th scope="col">
                        Trạng thái
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
                        Nguyễn Văn A
                    </td>

                    <td>
                        22123456
                    </td>

                    <td>
                        Chủ nhiệm
                    </td>

                    <td>
                        a@example.com
                    </td>

                    <td>

                        <span class="badge badge-green">
                            Hoạt động
                        </span>

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
                        Trần Thị B
                    </td>

                    <td>
                        22123457
                    </td>

                    <td>
                        Thành viên
                    </td>

                    <td>
                        b@example.com
                    </td>

                    <td>

                        <span class="badge badge-green">
                            Hoạt động
                        </span>

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