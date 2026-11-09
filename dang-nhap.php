
<?php
/*
 * CLUBMANAGER - ĐĂNG NHẬP
 *
 * - Đăng nhập bằng username hoặc email
 * - Mật khẩu mã hóa bằng password_hash
 * - Kiểm tra trạng thái tài khoản
 * - Chống CSRF
 * - Tạo lại Session ID khi đăng nhập thành công
 */

require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

function e($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

/* Nếu đã đăng nhập, để auth.php kiểm tra lại tài khoản */
if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/includes/auth.php';

    header('Location: index.php');
    exit;
}

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $loginInput = $_POST['login'] ?? '';
    $passwordInput = $_POST['mat_khau'] ?? '';
    $tokenInput = $_POST['csrf_token'] ?? '';

    if (
        !is_string($loginInput) ||
        !is_string($passwordInput) ||
        !is_string($tokenInput)
    ) {
        $error = 'Dữ liệu đăng nhập không hợp lệ.';

    } elseif (!hash_equals(
        $_SESSION['csrf_login'],
        $tokenInput
    )) {
        $error = 'Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.';

    } else {
        $login = trim($loginInput);
        $matKhau = $passwordInput;

        if ($login === '' || $matKhau === '') {
            $error = 'Vui lòng nhập tài khoản và mật khẩu.';

        } elseif (mb_strlen($login) > 100) {
            $error = 'Thông tin đăng nhập không hợp lệ.';

        } else {
            try {
                $stmt = $pdo->prepare("
                    SELECT
                        id,
                        ho_ten,
                        ten_dang_nhap,
                        email,
                        mat_khau,
                        trang_thai
                    FROM nguoi_dung
                    WHERE ten_dang_nhap = :login_name
                       OR email = :login_email
                    LIMIT 1
                ");

                $stmt->execute([
                    'login_name' => $login,
                    'login_email' => $login
                ]);

                $user = $stmt->fetch();

                if (
                    !$user ||
                    !password_verify(
                        $matKhau,
                        $user['mat_khau']
                    )
                ) {
                    $error = 'Tài khoản hoặc mật khẩu không đúng.';

                } elseif ($user['trang_thai'] !== 'hoat_dong') {
                    $error = 'Tài khoản đã bị khóa.';

                } else {
                    /* Chống Session Fixation */
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['ho_ten'] = $user['ho_ten'];
                    $_SESSION['ten_dang_nhap'] =
                        $user['ten_dang_nhap'];
                    $_SESSION['email'] = $user['email'];

                    /* Đổi token sau khi đăng nhập */
                    unset($_SESSION['csrf_login']);

                    header('Location: index.php');
                    exit;
                }

            } catch (PDOException $e) {
                error_log($e->getMessage());

                $error = 'Không thể đăng nhập lúc này. '
                       . 'Vui lòng thử lại.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Đăng nhập - ClubManager</title>

    <link
        rel="stylesheet"
        href="css/style.css?v=4"
    >
</head>

<body>

<div class="login-page">

    <div class="login-card">

        <h1>ClubManager</h1>

        <p>
            Đăng nhập để quản lý và tham gia câu lạc bộ
        </p>

        <?php if (($_GET['registered'] ?? '') === '1'): ?>
            <div class="alert-success">
                Đăng ký thành công! Hãy đăng nhập để tiếp tục.
            </div>
        <?php endif; ?>

        <?php if (($_GET['blocked'] ?? '') === '1'): ?>
            <div class="alert-error" role="alert">
                Phiên đăng nhập đã kết thúc vì tài khoản
                không còn hoạt động hoặc không còn tồn tại.
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert-error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form
            action="dang-nhap.php"
            method="POST"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_login']) ?>"
            >

            <div class="form-group">

                <label for="login">
                    Tên đăng nhập hoặc email
                </label>

                <input
                    type="text"
                    id="login"
                    name="login"
                    value="<?= e($login) ?>"
                    autocomplete="username"
                    maxlength="100"
                    required
                >

            </div>

            <div class="form-group">

                <label for="mat_khau">
                    Mật khẩu
                </label>

                <input
                    type="password"
                    id="mat_khau"
                    name="mat_khau"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Đăng nhập
            </button>

        </form>

        <p style="margin-top:20px;margin-bottom:0;">

            Chưa có tài khoản?

            <a
                href="dang-ky.php"
                style="color:#23836b;font-weight:700;"
            >
                Đăng ký ngay
            </a>

        </p>

    </div>

</div>

</body>
</html>
