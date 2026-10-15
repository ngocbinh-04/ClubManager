<?php

$pageTitle = 'Quản lý sự kiện';

$userName = 'Nguyễn Văn A';
$userRole = 'Quản trị viên';

$activePage = 'event';

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="content">

    <div class="page-heading">

        <div>

            <h1>
                Danh sách sự kiện
            </h1>

            <p>
                CLB Tin học
            </p>

        </div>

        <button type="button">
            + Thêm sự kiện
        </button>

    </div>


    <section>

        <div class="filter-bar">

            <input
                type="search"
                placeholder="Tìm theo tên sự kiện..."
            >

            <select>

                <option>
                    Tất cả trạng thái
                </option>

                <option>
                    Sắp diễn ra
                </option>

                <option>
                    Đang diễn ra
                </option>

                <option>
                    Đã diễn ra
                </option>

            </select>

        </div>


        <div class="table-wrapper">

            <table>

                <caption>
                    Danh sách sự kiện CLB Tin học
                </caption>

                <thead>

                <tr>

                    <th scope="col">STT</th>
                    <th scope="col">Tên sự kiện</th>
                    <th scope="col">Thời gian</th>
                    <th scope="col">Địa điểm</th>
                    <th scope="col">Tham gia</th>
                    <th scope="col">Trạng thái</th>
                    <th scope="col">Thao tác</th>

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
                        Phòng A1
                    </td>

                    <td>
                        45
                    </td>

                    <td>

                        <span class="badge badge-orange">
                            Sắp diễn ra
                        </span>

                    </td>

                    <td class="table-actions">

                        <a
                            class="btn-secondary"
                            href="diem-danh.php"
                        >
                            Điểm danh
                        </a>

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
                        Cuộc thi Hackathon
                    </td>

                    <td>
                        20/12/2026
                    </td>

                    <td>
                        Hội trường
                    </td>

                    <td>
                        80
                    </td>

                    <td>

                        <span class="badge badge-orange">
                            Sắp diễn ra
                        </span>

                    </td>

                    <td class="table-actions">

                        <a
                            class="btn-secondary"
                            href="diem-danh.php"
                        >
                            Điểm danh
                        </a>

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