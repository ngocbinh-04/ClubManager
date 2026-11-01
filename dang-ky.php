
<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$hoTen = '';
$tenDangNhap = '';
$email = '';
$soDienThoai = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim($_POST['ho_ten'] ?? '');
    $tenDangNhap = trim($_POST['ten_dang_nhap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $soDienThoai = trim($_POST['so_dien_thoai'] ?? '');
    $matKhau = $_POST['mat_khau'] ?? '';
    $xacNhanMatKhau = $_POST['xac_nhan_mat_khau'] ?? '';

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        $errors[] = 'Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
    }

    if (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 100) {
        $errors[] = 'Họ tên phải có từ 2 đến 100 ký tự.';
    }

    if (!preg_match('/^[a-zA-Z0-9_]{4,50}$/', $tenDangNhap)) {
        $errors[] = 'Tên đăng nhập phải có 4–50 ký tự, chỉ gồm chữ không dấu, số và dấu gạch dưới.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors[] = 'Email không hợp lệ.';
    }

    if ($soDienThoai !== '' &&
        !preg_match('/^[0-9+]{9,15}$/', $soDienThoai)) {
        $errors[] = 'Số điện thoại không hợp lệ.';
    }

    if (strlen($matKhau) < 8) {
        $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
    }

    if ($matKhau !== $xacNhanMatKhau) {
        $errors[] = 'Mật khẩu xác nhận không khớp.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "SELECT id, ten_dang_nhap, email
             FROM nguoi_dung
             WHERE ten_dang_nhap = :ten_dang_nhap
                OR email = :email"
        );

        $stmt->execute([
            'ten_dang_nhap' => $tenDangNhap,
            'email' => $email
        ]);

        foreach ($stmt->fetchAll() as $existing) {
            if ($existing['ten_dang_nhap'] === $tenDangNhap) {
                $errors[] = 'Tên đăng nhập đã được sử dụng.';
            }

            if (strcasecmp($existing['email'] ?? '', $email) === 0) {
                $errors[] = 'Email đã được đăng ký.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $matKhauHash = password_hash($matKhau, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO nguoi_dung
                    (ho_ten, ten_dang_nhap, mat_khau, email,
                     so_dien_thoai, trang_thai)
                 VALUES
                    (:ho_ten, :ten_dang_nhap, :mat_khau, :email,
                     :so_dien_thoai, 'hoat_dong')"
            );

            $stmt->execute([
                'ho_ten' => $hoTen,
                'ten_dang_nhap' => $tenDangNhap,
                'mat_khau' => $matKhauHash,
                'email' => $email,
                'so_dien_thoai' => $soDienThoai !== ''
                    ? $soDienThoai
                    : null
            ]);

            header('Location: dang-nhap.php?registered=1');
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());

            if ($e->getCode() === '23000') {
                $errors[] = 'Tên đăng nhập hoặc email đã tồn tại.';
            } else {
                $errors[] = 'Không thể tạo tài khoản. Vui lòng thử lại.';
            }
        }
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - ClubManager</title>
    <link rel="stylesheet" href="css/style.css?v=4">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h1>ClubManager</h1>
        <p>Tạo tài khoản để tham gia và quản lý câu lạc bộ</p>

        <?php if ($errors): ?>
            <div class="alert-error" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="dang-ky.php" method="POST">
            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="ho_ten">Họ và tên</label>
                <input type="text" id="ho_ten" name="ho_ten"
                       value="<?= e($hoTen) ?>" maxlength="100"
                       autocomplete="name" required>
            </div>

            <div class="form-group">
                <label for="ten_dang_nhap">Tên đăng nhập</label>
                <input type="text" id="ten_dang_nhap"
                       name="ten_dang_nhap"
                       value="<?= e($tenDangNhap) ?>"
                       minlength="4" maxlength="50"
                       pattern="[a-zA-Z0-9_]+"
                       autocomplete="username" required>
            </div>

            <div class="form-group">
                <label for="email">Email (Gmail)</label>
                <input type="email" id="email" name="email"
                       value="<?= e($email) ?>"
                       maxlength="100" autocomplete="email" required>
            </div>

            <div class="form-group">
                <label for="so_dien_thoai">Số điện thoại (không bắt buộc)</label>
                <input type="tel" id="so_dien_thoai" name="so_dien_thoai"
                       value="<?= e($soDienThoai) ?>" maxlength="15"
                       autocomplete="tel">
            </div>

            <div class="form-group">
                <label for="mat_khau">Mật khẩu</label>
                <input type="password" id="mat_khau" name="mat_khau"
                       minlength="8" autocomplete="new-password"
                       required>
            </div>

            <div class="form-group">
                <label for="xac_nhan_mat_khau">Xác nhận mật khẩu</label>
                <input type="password" id="xac_nhan_mat_khau"
                       name="xac_nhan_mat_khau" minlength="8"
                       autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-primary">
                Đăng ký tài khoản
            </button>
        </form>

        <p style="margin-top:20px; margin-bottom:0;">
            Đã có tài khoản?
            <a href="dang-nhap.php" style="color:#23836b;font-weight:700;">
                Đăng nhập
            </a>
        </p>
    </div>
</div>
</body>
</html>
