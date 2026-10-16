<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];


/* Chỉ nhận POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: thanh-vien.php');
    exit;
}


$thamGiaId = (int) ($_POST['tham_gia_id'] ?? 0);

$clbId = (int) ($_POST['clb_id'] ?? 0);


if ($thamGiaId <= 0 || $clbId <= 0) {

    header('Location: thanh-vien.php');
    exit;
}


/* =====================================================
   KIỂM TRA CLB THUỘC USER
   ===================================================== */

$sqlClb = "
    SELECT id
    FROM cau_lac_bo
    WHERE id = :clb_id
      AND nguoi_tao_id = :user_id
    LIMIT 1
";

$stmtClb = $pdo->prepare($sqlClb);

$stmtClb->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);

if (!$stmtClb->fetch()) {

    header('Location: thanh-vien.php');
    exit;
}


/* =====================================================
   KIỂM TRA THÀNH VIÊN
   ===================================================== */

$sqlThanhVien = "
    SELECT *
    FROM tham_gia_clb
    WHERE id = :tham_gia_id
      AND cau_lac_bo_id = :clb_id
    LIMIT 1
";

$stmtThanhVien = $pdo->prepare($sqlThanhVien);

$stmtThanhVien->execute([
    'tham_gia_id' => $thamGiaId,
    'clb_id' => $clbId
]);

$thanhVien = $stmtThanhVien->fetch();


if (!$thanhVien) {

    header(
        'Location: thanh-vien.php?clb_id='
        . $clbId
    );

    exit;
}


/* Không cho xóa chính chủ CLB */
if ($thanhVien['nguoi_dung_id'] == $userId) {

    header(
        'Location: thanh-vien.php?clb_id='
        . $clbId
    );

    exit;
}


/* =====================================================
   CHUYỂN TRẠNG THÁI THÀNH ĐÃ RỜI
   ===================================================== */

$sqlUpdate = "
    UPDATE tham_gia_clb
    SET trang_thai = 'da_roi'
    WHERE id = :tham_gia_id
      AND cau_lac_bo_id = :clb_id
";

$stmtUpdate = $pdo->prepare($sqlUpdate);

$stmtUpdate->execute([
    'tham_gia_id' => $thamGiaId,
    'clb_id' => $clbId
]);


header(
    'Location: thanh-vien.php?clb_id='
    . $clbId
    . '&success=xoa'
);

exit;