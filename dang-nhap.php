<?php

session_start();

require_once 'config/database.php';

/* Nếu đã đăng nhập thì không cần đăng nhập lại */
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tenDangNhap = trim($_POST['ten_dang_nhap'] ?? '');
    $matKhau = $_POST['mat_khau'] ?? '';

    if ($tenDangNhap === '' || $matKhau === '') {

        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';

    } else {

        $sql = "
            SELECT *
            FROM nguoi_dung
            WHERE ten_dang_nhap = :ten_dang_nhap
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'ten_dang_nhap' => $tenDangNhap
        ]);

        $user = $stmt->fetch();

        if (!$user) {

            $error = 'Tên đăng nhập không tồn tại.';

        } elseif ($user['trang_thai'] !== 'hoat_dong') {

            $error = 'Tài khoản đang bị khóa.';

        } elseif (!password_verify($matKhau, $user['mat_khau'])) {

            $error = 'Mật khẩu không đúng.';

        } else {

            /* Lưu thông tin người dùng vào session */
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['ho_ten'] = $user['ho_ten'];
            $_SESSION['ten_dang_nhap'] = $user['ten_dang_nhap'];
            $_SESSION['email'] = $user['email'];

            header('Location: index.php');
            exit;
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
        href="css/style.css"
    >

</head>

<body>

<div class="login-page">

    <div class="login-card">

        <h1>ClubManager</h1>

        <p>
            Đăng nhập vào hệ thống quản lý câu lạc bộ
        </p>


        <?php if ($error !== ''): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="dang-nhap.php"
        >

            <div class="form-group">

                <label for="ten_dang_nhap">
                    Tên đăng nhập
                </label>

                <input
                    type="text"
                    id="ten_dang_nhap"
                    name="ten_dang_nhap"
                    value="<?= htmlspecialchars($_POST['ten_dang_nhap'] ?? '') ?>"
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

    </div>

</div>

</body>

</html>