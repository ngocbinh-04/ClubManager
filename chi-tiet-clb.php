<?php

$pageTitle = 'Chi tiết câu lạc bộ';

$userName = 'Nguyễn Văn A';
$userRole = 'Thành viên';

$activePage = 'club-detail';

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="content">

    <div class="page-heading">

        <div>

            <h1>
                CLB Tin học
            </h1>

            <p>
                Thông tin chi tiết câu lạc bộ.
            </p>

        </div>

    </div>


    <section>

        <div class="club-detail">

            <div class="club-detail-image">
                Ảnh CLB Tin học
            </div>


            <div>

                <h2>
                    Thông tin câu lạc bộ
                </h2>

                <ul class="info-list">

                    <li>
                        <strong>Tên CLB:</strong>
                        CLB Tin học
                    </li>

                    <li>
                        <strong>Ngày thành lập:</strong>
                        15/10/2022
                    </li>

                    <li>
                        <strong>Lĩnh vực:</strong>
                        Công nghệ thông tin
                    </li>

                    <li>
                        <strong>Số thành viên:</strong>
                        50
                    </li>

                    <li>
                        <strong>Vai trò của tôi:</strong>
                        Thành viên
                    </li>

                </ul>

            </div>

        </div>

    </section>


    <section>

        <h2>
            Giới thiệu
        </h2>

        <p>
            CLB Tin học là nơi kết nối sinh viên yêu thích
            công nghệ thông tin, lập trình và ứng dụng
            công nghệ trong học tập và đời sống.
        </p>

    </section>

</main>

<?php
include 'includes/footer.php';
?>