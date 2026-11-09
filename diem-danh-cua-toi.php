
<?php
/*
 * CLUBMANAGER - ĐIỂM DANH CỦA TÔI
 *
 * Người dùng xem lịch sử điểm danh của chính mình
 * tại các CLB đang tham gia hoặc do mình tạo.
 *
 * Không có thao tác thêm, sửa, xóa điểm danh.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

$pageTitle = 'Điểm danh của tôi - ClubManager';
$activePage = 'diem-danh-cua-toi';

function e($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function hienThiNgay($ngay)
{
    if (empty($ngay)) {
        return 'Chưa cập nhật';
    }

    $timestamp = strtotime($ngay);

    return $timestamp !== false
        ? date('d/m/Y', $timestamp)
        : $ngay;
}

/* =====================================================
   1. DANH SÁCH CLB CỦA NGƯỜI ĐĂNG NHẬP
   ===================================================== */

/*
 * Người dùng được xem:
 * - CLB do chính mình tạo;
 * - CLB đang tham gia và có trạng thái hoạt động.
 *
 * EXISTS giúp tránh trùng dòng CLB.
 */

$stmtClb = $pdo->prepare("
    SELECT
        clb.id,
        clb.ten_clb,
        clb.linh_vuc
    FROM cau_lac_bo clb

    WHERE clb.nguoi_tao_id = :owner_id

       OR EXISTS (
            SELECT 1
            FROM tham_gia_clb tg
            WHERE tg.cau_lac_bo_id = clb.id
              AND tg.nguoi_dung_id = :member_id
              AND tg.trang_thai = 'hoat_dong'
       )

    ORDER BY clb.ten_clb ASC
");

$stmtClb->execute([
    'owner_id' => $userId,
    'member_id' => $userId
]);

$danhSachClb = $stmtClb->fetchAll();

/* =====================================================
   2. XÁC ĐỊNH CLB ĐƯỢC CHỌN
   ===================================================== */

$clbId = 0;
$clbHienTai = null;

if (isset($_GET['clb_id'])) {
    $rawClbId = $_GET['clb_id'];

    if (
        !is_string($rawClbId) ||
        !ctype_digit($rawClbId) ||
        (int) $rawClbId <= 0
    ) {
        http_response_code(400);
        exit('Mã câu lạc bộ không hợp lệ.');
    }

    $clbId = (int) $rawClbId;
}

if ($clbId === 0 && !empty($danhSachClb)) {
    $clbId = (int) $danhSachClb[0]['id'];
}

foreach ($danhSachClb as $clb) {
    if ((int) $clb['id'] === $clbId) {
        $clbHienTai = $clb;
        break;
    }
}

/* Không cho truy cập điểm danh của CLB khác */
if ($clbId > 0 && !$clbHienTai) {
    http_response_code(403);
    exit(
        'Bạn không có quyền xem điểm danh ' .
        'của câu lạc bộ này.'
    );
}

/* =====================================================
   3. LẤY LỊCH SỬ ĐIỂM DANH CỦA CHÍNH MÌNH
   ===================================================== */

$danhSachDiemDanh = [];

if ($clbHienTai) {

    /*
     * LEFT JOIN:
     * - Có bản ghi điểm danh: Có mặt / Vắng
     * - Không có bản ghi: Chưa điểm danh
     *
     * Không lấy dữ liệu điểm danh người dùng khác.
     */

    $stmtDiemDanh = $pdo->prepare("
        SELECT
            sk.id AS su_kien_id,
            sk.ten_su_kien,
            sk.ngay_to_chuc,
            sk.gio_bat_dau,
            sk.gio_ket_thuc,
            sk.dia_diem,
            sk.trang_thai AS trang_thai_su_kien,

            dd.trang_thai AS trang_thai_diem_danh,
            dd.ghi_chu,
            dd.thoi_gian_cap_nhat

        FROM su_kien sk

        LEFT JOIN diem_danh dd
            ON dd.su_kien_id = sk.id
           AND dd.nguoi_dung_id = :user_id

        WHERE sk.cau_lac_bo_id = :clb_id

        ORDER BY
            sk.ngay_to_chuc DESC,
            sk.id DESC
    ");

    $stmtDiemDanh->execute([
        'user_id' => $userId,
        'clb_id' => $clbId
    ]);

    $danhSachDiemDanh = $stmtDiemDanh->fetchAll();
}

/* =====================================================
   4. THỐNG KÊ KẾT QUẢ
   ===================================================== */

$tongSuKien = count($danhSachDiemDanh);

$soCoMat = 0;
$soVang = 0;
$soChuaDiemDanh = 0;

foreach ($danhSachDiemDanh as $item) {

    $trangThai = $item['trang_thai_diem_danh'];

    if ($trangThai === 'co_mat') {
        $soCoMat++;

    } elseif ($trangThai === 'vang') {
        $soVang++;

    } else {
        $soChuaDiemDanh++;
    }
}

/*
 * Tỷ lệ có mặt tính trên những lượt ĐÃ ĐƯỢC
 * ghi nhận Có mặt hoặc Vắng.
 *
 * Không coi Chưa điểm danh là Vắng.
 */

$soDaDiemDanh = $soCoMat + $soVang;

$tyLeCoMat = $soDaDiemDanh > 0
    ? round(($soCoMat / $soDaDiemDanh) * 100, 1)
    : 0;

/* =====================================================
   5. HIỂN THỊ GIAO DIỆN
   ===================================================== */

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Điểm danh của tôi</h1>
                <p>
                    Theo dõi tình trạng có mặt, vắng mặt
                    tại các sự kiện câu lạc bộ bạn tham gia.
                </p>
            </div>
        </section>

        <?php if (empty($danhSachClb)): ?>

            <div class="empty-state">
                <h3>Bạn chưa tham gia câu lạc bộ nào</h3>

                <p>
                    Khi bạn tham gia câu lạc bộ,
                    lịch sử điểm danh sẽ hiển thị tại đây.
                </p>

                <a href="index.php" class="btn btn-primary">
                    Về trang chủ
                </a>
            </div>

        <?php else: ?>

            <!-- CHỌN CLB -->
            <div class="card">

                <form
                    method="GET"
                    action="diem-danh-cua-toi.php"
                >

                    <div class="form-group">
                        <label for="clb_id">
                            Chọn câu lạc bộ
                        </label>

                        <select
                            name="clb_id"
                            id="clb_id"
                        >

                            <?php foreach ($danhSachClb as $clb): ?>

                                <option
                                    value="<?= (int) $clb['id'] ?>"
                                    <?= (int) $clb['id'] === $clbId
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= e($clb['ten_clb']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Xem điểm danh
                    </button>

                </form>

            </div>

            <?php if ($clbHienTai): ?>

                <!-- THÔNG TIN CLB -->
                <section class="content-section">

                    <div class="section-header">
                        <div>
                            <h2>
                                <?= e($clbHienTai['ten_clb']) ?>
                            </h2>

                            <p>
                                Lịch sử điểm danh cá nhân
                                trong câu lạc bộ này.
                            </p>
                        </div>
                    </div>

                    <!-- CÁC Ô THỐNG KÊ -->
                    <div class="dashboard-cards">

                        <div class="stat-card">
                            <h3><?= $tongSuKien ?></h3>
                            <p>Tổng sự kiện</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $soCoMat ?></h3>
                            <p>Số lần có mặt</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $soVang ?></h3>
                            <p>Số lần vắng</p>
                        </div>

                        <div class="stat-card">
                            <h3><?= $soChuaDiemDanh ?></h3>
                            <p>Chưa điểm danh</p>
                        </div>

                    </div>

                    <!-- TỶ LỆ CÓ MẶT -->
                    <div class="card" style="margin-top:20px;">

                        <h3>Tỷ lệ có mặt</h3>

                        <p
                            style="
                                font-size:28px;
                                font-weight:700;
                                color:#23836b;
                                margin:10px 0;
                            "
                        >
                            <?= number_format(
                                $tyLeCoMat,
                                1,
                                ',',
                                '.'
                            ) ?>%
                        </p>

                        <p>
                            Đã ghi nhận
                            <strong><?= $soDaDiemDanh ?></strong>
                            lượt điểm danh, trong đó
                            <strong><?= $soCoMat ?></strong>
                            lượt có mặt.
                        </p>

                        <?php if ($soDaDiemDanh === 0): ?>
                            <p>
                                Chưa có lượt điểm danh nào
                                được ghi nhận.
                            </p>
                        <?php endif; ?>

                        <p style="color:#64748b;font-size:13px;">
                            Lưu ý: Các sự kiện chưa được điểm danh
                            không bị tính là vắng.
                            Tỷ lệ trên chỉ tính các lượt
                            đã có kết quả Có mặt hoặc Vắng.
                        </p>

                    </div>

                </section>

                <!-- LỊCH SỬ ĐIỂM DANH -->
                <section class="content-section">

                    <div class="section-header">
                        <div>
                            <h2>Lịch sử điểm danh</h2>
                            <p>
                                Kết quả do người quản lý
                                câu lạc bộ ghi nhận.
                            </p>
                        </div>
                    </div>

                    <?php if (empty($danhSachDiemDanh)): ?>

                        <div class="empty-state">
                            <p>
                                Câu lạc bộ này chưa có sự kiện nào.
                            </p>
                        </div>

                    <?php else: ?>

                        <div class="table-wrapper">

                            <table>

                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên sự kiện</th>
                                        <th>Ngày tổ chức</th>
                                        <th>Thời gian</th>
                                        <th>Địa điểm</th>
                                        <th>Điểm danh</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach (
                                        $danhSachDiemDanh as $index => $item
                                    ): ?>

                                        <?php
                                        $trangThai =
                                            $item['trang_thai_diem_danh'];

                                        if ($trangThai === 'co_mat') {
                                            $nhan = 'Có mặt';
                                            $badgeClass = 'badge-success';

                                        } elseif ($trangThai === 'vang') {
                                            $nhan = 'Vắng';
                                            $badgeClass = 'badge-warning';

                                        } else {
                                            $nhan = 'Chưa điểm danh';
                                            $badgeClass = '';
                                        }

                                        $gioBatDau = !empty(
                                            $item['gio_bat_dau']
                                        )
                                            ? substr(
                                                $item['gio_bat_dau'],
                                                0,
                                                5
                                            )
                                            : '--:--';

                                        $gioKetThuc = !empty(
                                            $item['gio_ket_thuc']
                                        )
                                            ? substr(
                                                $item['gio_ket_thuc'],
                                                0,
                                                5
                                            )
                                            : '--:--';
                                        ?>

                                        <tr>

                                            <td>
                                                <?= $index + 1 ?>
                                            </td>

                                            <td>
                                                <?= e(
                                                    $item['ten_su_kien']
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= e(hienThiNgay(
                                                    $item['ngay_to_chuc']
                                                )) ?>
                                            </td>

                                            <td>
                                                <?= e(
                                                    $gioBatDau .
                                                    ' - ' .
                                                    $gioKetThuc
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= e(
                                                    $item['dia_diem']
                                                        ?: 'Chưa cập nhật'
                                                ) ?>
                                            </td>

                                            <td>
                                                <span
                                                    class="badge <?= e(
                                                        $badgeClass
                                                    ) ?>"
                                                >
                                                    <?= e($nhan) ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if (
                                                    !empty($item['ghi_chu'])
                                                ): ?>

                                                    <?= e(
                                                        $item['ghi_chu']
                                                    ) ?>

                                                <?php else: ?>

                                                    —

                                                <?php endif; ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        <?php endif; ?>

    </main>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
