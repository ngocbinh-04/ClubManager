<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['ho_ten'] ?? 'Người dùng';

$stmtQuanLy = $pdo->prepare(
    "SELECT *
     FROM cau_lac_bo
     WHERE nguoi_tao_id = :user_id
     ORDER BY id DESC"
);

$stmtQuanLy->execute(['user_id' => $userId]);
$clbQuanLy = $stmtQuanLy->fetchAll();

$stmtThamGia = $pdo->prepare(
    "SELECT clb.*, tg.vai_tro, tg.ngay_tham_gia
     FROM tham_gia_clb tg
     INNER JOIN cau_lac_bo clb
         ON clb.id = tg.cau_lac_bo_id
     WHERE tg.nguoi_dung_id = :user_id
       AND tg.trang_thai = 'hoat_dong'
       AND clb.nguoi_tao_id <> :owner_id
     ORDER BY clb.id DESC"
);

$stmtThamGia->execute([
    'user_id' => $userId,
    'owner_id' => $userId
]);

$clbThamGia = $stmtThamGia->fetchAll();

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function tenVaiTro($vaiTro) {
    $labels = [
        'quan_tri' => 'Quản trị',
        'chu_nhiem' => 'Chủ nhiệm',
        'pho_chu_nhiem' => 'Phó chủ nhiệm',
        'thanh_vien' => 'Thành viên'
    ];

    return $labels[$vaiTro] ?? 'Thành viên';
}

$pageTitle = 'Trang chủ - ClubManager';
$activePage = 'index';

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Xin chào, <?= e($userName) ?>!</h1>
                <p>Quản lý và theo dõi những câu lạc bộ của bạn.</p>
            </div>

            <a href="them-clb.php" class="btn btn-primary">
                + Tạo câu lạc bộ
            </a>
        </section>

        <section class="dashboard-cards">
            <div class="stat-card">
                <h3><?= count($clbQuanLy) ?></h3>
                <p>CLB tôi quản lý</p>
            </div>

            <div class="stat-card">
                <h3><?= count($clbThamGia) ?></h3>
                <p>CLB tôi tham gia</p>
            </div>

            <div class="stat-card">
                <h3><?= count($clbQuanLy) + count($clbThamGia) ?></h3>
                <p>Tổng câu lạc bộ</p>
            </div>
        </section>

        <section class="content-section">
            <div class="section-header">
                <div>
                    <h2>Câu lạc bộ tôi quản lý</h2>
                    <p>Những câu lạc bộ do bạn tạo.</p>
                </div>

                <?php if ($clbQuanLy): ?>
                    <a href="clb-quan-tri.php" class="btn">
                        Quản lý tất cả
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($clbQuanLy): ?>
                <div class="card-grid">
                    <?php foreach ($clbQuanLy as $clb): ?>
                        <article class="card club-card">
                            <div class="club-card-header">
                                <h3><?= e($clb['ten_clb']) ?></h3>

                                <span class="badge <?= $clb['trang_thai'] === 'hoat_dong'
                                    ? 'badge-success'
                                    : 'badge-warning' ?>">
                                    <?= $clb['trang_thai'] === 'hoat_dong'
                                        ? 'Hoạt động'
                                        : 'Tạm dừng' ?>
                                </span>
                            </div>

                            <p>
                                <strong>Lĩnh vực:</strong>
                                <?= e($clb['linh_vuc']) ?>
                            </p>

                            <p><?= e($clb['mo_ta']) ?></p>

                            <div class="card-actions">
                                <a class="btn btn-primary"
                                   href="chi-tiet-clb.php?id=<?= (int)$clb['id'] ?>">
                                    Quản lý câu lạc bộ
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Bạn chưa tạo câu lạc bộ nào.</p>
                    <a href="them-clb.php" class="btn btn-primary">
                        + Tạo câu lạc bộ đầu tiên
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <section class="content-section" id="clb-tham-gia">
            <div class="section-header">
                <div>
                    <h2>Câu lạc bộ tôi tham gia</h2>
                    <p>Những câu lạc bộ bạn đang là thành viên.</p>
                </div>
            </div>

            <?php if ($clbThamGia): ?>
                <div class="card-grid">
                    <?php foreach ($clbThamGia as $clb): ?>
                        <article class="card club-card">
                            <div class="club-card-header">
                                <h3><?= e($clb['ten_clb']) ?></h3>
                                <span class="badge">
                                    <?= e(tenVaiTro($clb['vai_tro'])) ?>
                                </span>
                            </div>

                            <p>
                                <strong>Lĩnh vực:</strong>
                                <?= e($clb['linh_vuc']) ?>
                            </p>

                            <p><?= e($clb['mo_ta']) ?></p>

                            <div class="card-actions">
                                <a class="btn btn-primary"
                                   href="chi-tiet-clb.php?id=<?= (int)$clb['id'] ?>">
                                    Xem câu lạc bộ
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Bạn chưa tham gia câu lạc bộ nào.</p>
                </div>
            <?php endif; ?>
        </section>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
