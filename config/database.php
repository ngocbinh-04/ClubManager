
<?php
/*
 * CLUBMANAGER - KẾT NỐI DATABASE
 * Cấu hình dành cho XAMPP trên máy cá nhân.
 */

$host = '127.0.0.1';
$dbname = 'clubmanager';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {
    error_log('ClubManager DB: ' . $e->getMessage());

    http_response_code(503);

    exit(
        'Không thể kết nối cơ sở dữ liệu. ' .
        'Vui lòng kiểm tra MySQL và thử lại.'
    );
}
