<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'ClubManager';

$userName = $_SESSION['ho_ten'] ?? 'Người dùng';
$userEmail = $_SESSION['email'] ?? '';

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle) ?>
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<header class="topbar">

    <div class="topbar-left">

        <h2>
            ClubManager
        </h2>

    </div>


    <div class="topbar-right">

        <div class="user-info">

            <strong>
                <?= htmlspecialchars($userName) ?>
            </strong>

            <?php if ($userEmail !== ''): ?>

                <span>
                    <?= htmlspecialchars($userEmail) ?>
                </span>

            <?php endif; ?>

        </div>


        <?php if (isset($_SESSION['user_id'])): ?>

            <a
                href="dang-xuat.php"
                class="btn btn-secondary"
            >
                Đăng xuất
            </a>

        <?php endif; ?>

    </div>

</header>