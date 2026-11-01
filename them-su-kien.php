
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];
$clbId = (int) ($_GET['clb_id'] ?? 0);

function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmtClb = $pdo->prepare("
    SELECT id, ten_clb
    FROM cau_lac_bo
    WHERE id = :clb_id
      AND nguoi_tao_id = :user_id
    LIMIT 1
");
$stmtClb->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);
$clb = $stmtClb->fetch();

if (!$clb) {
    http_response_code(403);
    exit('Bạn không có quyền thêm sự kiện cho CLB này.');
}

$errors = [];
$data = [
    'ten_su_kien' => '',
    'ngay_to_chuc' => '',
    'gio_bat_dau' => '',
    'gio_ket_thuc' => '',
    'dia_diem' => '',
    'trang_thai' => 'sap_dien_ra',
    'mo_ta' => ''
];

function ngayHopLe($ngay) {
    if (!is_string($ngay)) return false;

    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $ngay);
    return $dt && $dt->format('Y-m-d') === $ngay;
}

function gioHopLe($gio) {
    return $gio === '' || (
        is_string($gio) &&
        preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $gio)
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Biểu mẫu không hợp lệ. Hãy tải lại trang.';
    }

    foreach ($data as $key => $value) {
        $incoming = $_POST[$key] ?? '';
        if (!is_string($incoming)) {
            $errors[] = 'Dữ liệu gửi lên không hợp lệ.';
            $incoming = '';
        }
        $data[$key] = trim($incoming);
    }

    if ($data['ten_su_kien'] === '' ||
        mb_strlen($data['ten_su_kien']) > 150) {
        $errors[] = 'Tên sự kiện phải có từ 1 đến 150 ký tự.';
    }

    if (!ngayHopLe($data['ngay_to_chuc'])) {
        $errors[] = 'Ngày tổ chức không hợp lệ.';
    }

    if (!gioHopLe($data['gio_bat_dau']) ||
        !gioHopLe($data['gio_ket_thuc'])) {
        $errors[] = 'Giờ bắt đầu hoặc kết thúc không hợp lệ.';
    }

    if (
        $data['gio_bat_dau'] !== '' &&
        $data['gio_ket_thuc'] !== '' &&
        $data['gio_ket_thuc'] <= $data['gio_bat_dau']
    ) {
        $errors[] = 'Giờ kết thúc phải sau giờ bắt đầu.';
    }

    if (mb_strlen($data['dia_diem']) > 200) {
        $errors[] = 'Địa điểm không được vượt quá 200 ký tự.';
    }

    if (!in_array($data['trang_thai'], [
        'sap_dien_ra',
        'dang_dien_ra',
        'da_ket_thuc'
    ], true)) {
        $errors[] = 'Trạng thái sự kiện không hợp lệ.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO su_kien (
                    cau_lac_bo_id, ten_su_kien, mo_ta,
                    ngay_to_chuc, gio_bat_dau, gio_ket_thuc,
                    dia_diem, trang_thai, nguoi_tao_id
                )
                VALUES (
                    :clb_id, :ten, :mo_ta,
                    :ngay, :gio_bat_dau, :gio_ket_thuc,
                    :dia_diem, :trang_thai, :nguoi_tao_id
                )
            ");

            $stmt->execute([
                'clb_id' => $clbId,
                'ten' => $data['ten_su_kien'],
                'mo_ta' => $data['mo_ta'],
                'ngay' => $data['ngay_to_chuc'],
                'gio_bat_dau' => $data['gio_bat_dau'] ?: null,
                'gio_ket_thuc' => $data['gio_ket_thuc'] ?: null,
                'dia_diem' => $data['dia_diem'],
                'trang_thai' => $data['trang_thai'],
                'nguoi_tao_id' => $userId
            ]);

            header('Location: su-kien.php?clb_id=' . $clbId . '&success=them');
            exit;
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors[] = 'Không thể thêm sự kiện. Hãy thử lại.';
        }
    }
}

$pageTitle = 'Thêm sự kiện';
$activePage = 'su-kien';
require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <div>
                <h1>Thêm sự kiện</h1>
                <p>CLB: <?= e($clb['ten_clb']) ?></p>
            </div>
        </section>

        <?php foreach ($errors as $error): ?>
            <div class="alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <div class="card">
            <form method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="ten_su_kien">Tên sự kiện *</label>
                    <input type="text" id="ten_su_kien" name="ten_su_kien"
                           maxlength="150"
                           value="<?= e($data['ten_su_kien']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="ngay_to_chuc">Ngày tổ chức *</label>
                    <input type="date" id="ngay_to_chuc" name="ngay_to_chuc"
                           value="<?= e($data['ngay_to_chuc']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="gio_bat_dau">Giờ bắt đầu</label>
                    <input type="time" id="gio_bat_dau" name="gio_bat_dau"
                           value="<?= e($data['gio_bat_dau']) ?>">
                </div>

                <div class="form-group">
                    <label for="gio_ket_thuc">Giờ kết thúc</label>
                    <input type="time" id="gio_ket_thuc" name="gio_ket_thuc"
                           value="<?= e($data['gio_ket_thuc']) ?>">
                </div>

                <div class="form-group">
                    <label for="dia_diem">Địa điểm</label>
                    <input type="text" id="dia_diem" name="dia_diem"
                           maxlength="200"
                           value="<?= e($data['dia_diem']) ?>">
                </div>

                <div class="form-group">
                    <label for="trang_thai">Trạng thái</label>
                    <select id="trang_thai" name="trang_thai">
                        <?php
                        $statuses = [
                            'sap_dien_ra' => 'Sắp diễn ra',
                            'dang_dien_ra' => 'Đang diễn ra',
                            'da_ket_thuc' => 'Đã kết thúc'
                        ];
                        foreach ($statuses as $value => $label):
                        ?>
                            <option value="<?= e($value) ?>"
                                <?= $data['trang_thai'] === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô tả</label>
                    <textarea id="mo_ta" name="mo_ta" rows="5"><?= e($data['mo_ta']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Thêm sự kiện
                </button>

                <a href="su-kien.php?clb_id=<?= $clbId ?>" class="btn">
                    Hủy
                </a>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
