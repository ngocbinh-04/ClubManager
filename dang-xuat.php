<?php

session_start();

/* Xóa toàn bộ dữ liệu session */
session_unset();

/* Hủy session */
session_destroy();

/* Quay về trang đăng nhập */
header('Location: dang-nhap.php');
exit;