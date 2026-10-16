<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$userName = $_SESSION['ho_ten'] ?? 'Người dùng';


/* =====================================================
   1. CLB DO USER QUẢN LÝ
   ===================================================== */

$sqlQuanLy = "
    SELECT *
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
    ORDER BY id DESC
";

$stmtQuanLy = $pdo->prepare($sqlQuanLy);

$stmtQuanLy->execute([
    'user_id' => $userId
]);

$clbQuanLy = $stmtQuanLy->fetchAll();


/* =====================================================
   2. CLB USER ĐANG THAM GIA
   ===================================================== */

$sqlThamGia = "
    SELECT
        clb.*,
        tg.vai_tro,
        tg.ngay_tham_gia

    FROM tham_gia_clb tg

    INNER JOIN cau_lac_bo clb
        ON tg.cau_lac_bo_id = clb.id

    WHERE tg.nguoi_dung_id = :user_id

      AND tg.trang_thai = 'hoat_dong'

      AND clb.nguoi_tao_id <> :user_id

    ORDER BY clb.id DESC
";

$stmtThamGia = $pdo->prepare($sqlThamGia);

$stmtThamGia->execute([
    'user_id' => $userId
]);

$clbThamGia = $stmtThamGia->fetchAll();


/* =====================================================
   3. THÔNG TIN TRANG
   ===================================================== */

$pageTitle = 'Trang chủ - ClubManager';
$activePage = 'index';

?>

<?php require_once 'includes/header.php'; ?>


<div class="app-layout">


    <?php require_once 'includes/sidebar.php'; ?>


    <main class="main-content">


        <!-- ==========================================
             CHÀO USER
             ========================================== -->

        <section class="page-header">

            <div>

                <h1>
                    Xin chào, <?= htmlspecialchars($userName) ?>!
                </h1>

                <p>
                    Đây là các câu lạc bộ liên quan đến tài khoản của bạn.
                </p>

            </div>

        </section>



        <!-- ==========================================
             THỐNG KÊ
             ========================================== -->

        <section class="dashboard-cards">


            <div class="stat-card">

                <h3>
                    <?= count($clbQuanLy) ?>
                </h3>

                <p>
                    CLB đang quản lý
                </p>

            </div>


            <div class="stat-card">

                <h3>
                    <?= count($clbThamGia) ?>
                </h3>

                <p>
                    CLB đang tham gia
                </p>

            </div>


            <div class="stat-card">

                <h3>
                    <?= count($clbQuanLy) + count($clbThamGia) ?>
                </h3>

                <p>
                    Tổng câu lạc bộ
                </p>

            </div>


        </section>



        <!-- ==========================================
             CLB QUẢN LÝ
             ========================================== -->

        <?php if (!empty($clbQuanLy)): ?>

            <section class="content-section">

                <div class="section-header">

                    <div>

                        <h2>
                            Câu lạc bộ tôi quản lý
                        </h2>

                        <p>
                            Các câu lạc bộ do bạn tạo và đang quản lý.
                        </p>

                    </div>


                    <a
                        href="clb-quan-tri.php"
                        class="btn btn-primary"
                    >
                        Quản lý câu lạc bộ
                    </a>

                </div>


                <div class="card-grid">


                    <?php foreach ($clbQuanLy as $clb): ?>


                        <div class="card club-card">


                            <div class="club-card-header">


                                <h3>
                                    <?= htmlspecialchars($clb['ten_clb']) ?>
                                </h3>


                                <?php if ($clb['trang_thai'] === 'hoat_dong'): ?>

                                    <span class="badge badge-success">
                                        Hoạt động
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-warning">
                                        Tạm dừng
                                    </span>

                                <?php endif; ?>


                            </div>


                            <p>

                                <strong>
                                    Lĩnh vực:
                                </strong>

                                <?= htmlspecialchars($clb['linh_vuc']) ?>

                            </p>


                            <p>
                                <?= htmlspecialchars($clb['mo_ta']) ?>
                            </p>


                            <div class="card-actions">

                                <a
                                    href="chi-tiet-clb.php?id=<?= $clb['id'] ?>"
                                    class="btn btn-primary"
                                >
                                    Quản lý
                                </a>

                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


            </section>

        <?php endif; ?>



        <!-- ==========================================
             CLB THAM GIA
             ========================================== -->

        <section
            class="content-section"
            id="clb-tham-gia"
        >

            <div class="section-header">

                <div>

                    <h2>
                        Câu lạc bộ tôi tham gia
                    </h2>

                    <p>
                        Các câu lạc bộ mà bạn đang là thành viên.
                    </p>

                </div>

            </div>


            <?php if (!empty($clbThamGia)): ?>


                <div class="card-grid">


                    <?php foreach ($clbThamGia as $clb): ?>


                        <div class="card club-card">


                            <div class="club-card-header">


                                <h3>
                                    <?= htmlspecialchars($clb['ten_clb']) ?>
                                </h3>


                                <span class="badge">

                                    <?php

                                    switch ($clb['vai_tro']) {

                                        case 'quan_tri':
                                            echo 'Quản trị';
                                            break;

                                        case 'chu_nhiem':
                                            echo 'Chủ nhiệm';
                                            break;

                                        case 'pho_chu_nhiem':
                                            echo 'Phó chủ nhiệm';
                                            break;

                                        default:
                                            echo 'Thành viên';
                                            break;
                                    }

                                    ?>

                                </span>


                            </div>


                            <p>

                                <strong>
                                    Lĩnh vực:
                                </strong>

                                <?= htmlspecialchars($clb['linh_vuc']) ?>

                            </p>


                            <p>
                                <?= htmlspecialchars($clb['mo_ta']) ?>
                            </p>


                            <div class="card-actions">

                                <a
                                    href="chi-tiet-clb.php?id=<?= $clb['id'] ?>"
                                    class="btn btn-primary"
                                >
                                    Xem câu lạc bộ
                                </a>

                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


            <?php else: ?>


                <div class="empty-state">

                    <p>
                        Bạn chưa tham gia câu lạc bộ nào.
                    </p>

                </div>


            <?php endif; ?>


        </section>


    </main>


</div>


<?php require_once 'includes/footer.php'; ?>