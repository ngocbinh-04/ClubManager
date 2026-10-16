<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Nếu chưa đăng nhập thì quay về trang đăng nhập */
if (!isset($_SESSION['user_id'])) {
    header('Location: dang-nhap.php');
    exit;
}