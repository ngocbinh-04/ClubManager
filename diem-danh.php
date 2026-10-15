<?php

$pageTitle = 'Điểm danh';

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
                Điểm danh - Workshop Lập trình Web
            </h1>

            <p>
                10/11/2026 - Phòng A1
            </p>

        </div>


        <a
            class="btn-secondary"
            href="su-kien.php"
        >
            Quay lại
        </a>

    </div>


    <section>

        <div class="filter-bar">

            <input
                type="search"
                placeholder="Tìm theo tên, mã sinh viên..."
            >

            <select>

                <option>
                    Tất cả
                </option>

                <option>
                    Có mặt
                </option>

                <option>
                    Vắng
                </option>

            </select>

        </div>


        <form>

            <div class="table-wrapper">

                <table>

                    <caption>
                        Danh sách điểm danh
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
                            Trạng thái
                        </th>

                        <th scope="col">
                            Ghi chú
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

                            <label>

                                <input
                                    type="radio"
                                    name="tv1"
                                    value="co-mat"
                                >

                                Có mặt

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="tv1"
                                    value="vang"
                                >

                                Vắng

                            </label>

                        </td>

                        <td>

                            <input
                                type="text"
                                placeholder="Ghi chú"
                            >

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

                            <label>

                                <input
                                    type="radio"
                                    name="tv2"
                                    value="co-mat"
                                >

                                Có mặt

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="tv2"
                                    value="vang"
                                >

                                Vắng

                            </label>

                        </td>

                        <td>

                            <input
                                type="text"
                                placeholder="Ghi chú"
                            >

                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>


            <p>

                <button type="submit">
                    Lưu điểm danh
                </button>

            </p>

        </form>

    </section>

</main>

<?php
include 'includes/footer.php';
?>