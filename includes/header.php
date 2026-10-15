<?php
if (!isset($pageTitle)) {
    $pageTitle = 'ClubManager';
}

if (!isset($userName)) {
    $userName = 'Nguyễn Văn A';
}

if (!isset($userRole)) {
    $userRole = 'Quản trị viên';
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

    <meta
        name="description"
        content="Hệ thống quản lý câu lạc bộ ClubManager."
    >

    <title>
        <?php echo htmlspecialchars($pageTitle); ?> | ClubManager
    </title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="topbar">

    <div class="logo">
        ClubManager
    </div>

    <div class="topbar-search">
        <input
            type="search"
            placeholder="Tìm kiếm..."
        >
    </div>

    <div class="account">
        <strong>
            <?php echo htmlspecialchars($userName); ?>
        </strong>

        <span>
            <?php echo htmlspecialchars($userRole); ?>
        </span>
    </div>

</header>