
<?php
/*
 * CLUBMANAGER - KIỂM TRA ĐĂNG NHẬP
 *
 * Chỉ cho phép tài khoản còn tồn tại
 * và có trạng thái hoat_dong sử dụng hệ thống.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/* Chưa đăng nhập */
$userId = $_SESSION['user_id'] ?? null;

if (
    !is_int($userId) ||
    $userId <= 0
) {
    header('Location: dang-nhap.php');
    exit;
}

/* Kiểm tra lại tài khoản trong database */
try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            ho_ten,
            ten_dang_nhap,
            email,
            trang_thai
        FROM nguoi_dung
        WHERE id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $user = $stmt->fetch();

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(503);
    exit('Không thể xác minh tài khoản. Vui lòng thử lại.');
}

/* Tài khoản bị xóa hoặc bị khóa */
if (
    !$user ||
    $user['trang_thai'] !== 'hoat_dong'
) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    header('Location: dang-nhap.php?blocked=1');
    exit;
}

/* Đồng bộ lại thông tin từ database */
$_SESSION['ho_ten'] = $user['ho_ten'];
$_SESSION['ten_dang_nhap'] = $user['ten_dang_nhap'];
$_SESSION['email'] = $user['email'];
