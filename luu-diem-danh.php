<?php

require_once 'includes/quan-ly-auth.php';
require_once 'config/database.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: diem-danh.php');
    exit;
}


$clbId = (int) ($_POST['clb_id'] ?? 0);
$suKienId = (int) ($_POST['su_kien_id'] ?? 0);

$trangThai = $_POST['trang_thai'] ?? [];
$ghiChu = $_POST['ghi_chu'] ?? [];


/* =====================================================
   KIỂM TRA CLB THUỘC USER
   ===================================================== */

$sqlClb = "
    SELECT id
    FROM cau_lac_bo
    WHERE id = :clb_id
      AND nguoi_tao_id = :user_id
";

$stmtClb = $pdo->prepare($sqlClb);

$stmtClb->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);

if (!$stmtClb->fetch()) {
    header('Location: diem-danh.php');
    exit;
}


/* =====================================================
   KIỂM TRA SỰ KIỆN
   ===================================================== */

$sqlSuKien = "
    SELECT id
    FROM su_kien
    WHERE id = :su_kien_id
      AND cau_lac_bo_id = :clb_id
";

$stmtSuKien = $pdo->prepare($sqlSuKien);

$stmtSuKien->execute([
    'su_kien_id' => $suKienId,
    'clb_id' => $clbId
]);

if (!$stmtSuKien->fetch()) {

    header(
        'Location: diem-danh.php?clb_id='
        . $clbId
    );

    exit;
}


/* =====================================================
   LƯU TỪNG THÀNH VIÊN
   ===================================================== */

$sqlLuu = "
    INSERT INTO diem_danh
    (
        su_kien_id,
        nguoi_dung_id,
        trang_thai,
        ghi_chu
    )

    VALUES
    (
        :su_kien_id,
        :nguoi_dung_id,
        :trang_thai,
        :ghi_chu
    )

    ON DUPLICATE KEY UPDATE

        trang_thai = VALUES(trang_thai),
        ghi_chu = VALUES(ghi_chu)
";

$stmtLuu = $pdo->prepare($sqlLuu);


foreach ($trangThai as $nguoiDungId => $tt) {

    $nguoiDungId = (int) $nguoiDungId;

    if (!in_array($tt, ['co_mat', 'vang'], true)) {
        continue;
    }


    /* Kiểm tra user thật sự thuộc CLB */
    $sqlCheckThanhVien = "
        SELECT id
        FROM tham_gia_clb

        WHERE nguoi_dung_id = :nguoi_dung_id
          AND cau_lac_bo_id = :clb_id
          AND trang_thai = 'hoat_dong'
    ";

    $stmtCheckThanhVien = $pdo->prepare($sqlCheckThanhVien);

    $stmtCheckThanhVien->execute([
        'nguoi_dung_id' => $nguoiDungId,
        'clb_id' => $clbId
    ]);

    if (!$stmtCheckThanhVien->fetch()) {
        continue;
    }


    $stmtLuu->execute([
        'su_kien_id' => $suKienId,
        'nguoi_dung_id' => $nguoiDungId,
        'trang_thai' => $tt,
        'ghi_chu' => trim($ghiChu[$nguoiDungId] ?? '')
    ]);
}


header(
    'Location: diem-danh.php?clb_id='
    . $clbId
    . '&su_kien_id='
    . $suKienId
    . '&success=1'
);

exit;