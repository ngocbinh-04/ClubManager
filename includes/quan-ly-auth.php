
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user_id'];

/* Kiểm tra tài khoản có quản lý CLB nào không */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM cau_lac_bo
    WHERE nguoi_tao_id = :user_id
");

$stmt->execute([
    'user_id' => $userId
]);

$soClbQuanLy = (int) $stmt->fetchColumn();

/* Thành viên thường không có quyền quản trị */
if ($soClbQuanLy <= 0) {

    http_response_code(403);

    $pageTitle = '403 - Không có quyền truy cập';
    require_once __DIR__ . '/header.php';
    ?>

    <main style="
        min-height: 75vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 24px;
    ">

        <div style="
            width: 100%;
            max-width: 540px;
            padding: 40px 30px;
            background: #ffffff;
            border: 1px solid #dcebe6;
            border-radius: 16px;
            text-align: center;
        ">

            <h1 style="
                font-size: 64px;
                color: #dc3545;
                margin-bottom: 12px;
            ">
                403
            </h1>

            <h2>Không có quyền truy cập</h2>

            <p style="
                color: #64748b;
                line-height: 1.7;
                margin: 20px 0;
            ">
                Trang này chỉ dành cho người quản lý
                câu lạc bộ.
                Tài khoản của bạn không có quyền
                truy cập chức năng này.
            </p>

            <a
                href="index.php"
                class="btn btn-primary"
            >
                Quay về trang chủ
            </a>

        </div>

    </main>

    <?php
    require_once __DIR__ . '/footer.php';
    exit;
}
