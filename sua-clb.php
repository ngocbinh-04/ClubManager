
<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];
$clbId = (int) ($_GET['id'] ?? 0);

$pageTitle = 'Sửa câu lạc bộ';
$activePage = 'clb-quan-tri';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function ngayClbHopLe($value) {
    if ($value === '') {
        return true;
    }

    if (!is_string($value)) {
        return false;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date !== false
        && $date->format('Y-m-d') === $value;
}

if ($clbId <= 0) {
    http_response_code(400);
    exit('Mã câu lạc bộ không hợp lệ.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* Chỉ chủ sở hữu được sửa CLB */
$stmtClb = $pdo->prepare("
    SELECT *
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
    exit('Bạn không có quyền chỉnh sửa câu lạc bộ này.');
}

$data = [
    'ten_clb' => $clb['ten_clb'],
    'linh_vuc' => $clb['linh_vuc'] ?? '',
    'ngay_thanh_lap' => $clb['ngay_thanh_lap'] ?? '',
    'trang_thai' => $clb['trang_thai'],
    'mo_ta' => $clb['mo_ta'] ?? ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $errors[] = 'Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.';
    }

    foreach ($data as $key => $default) {
        $value = $_POST[$key] ?? '';

        if (!is_string($value)) {
            $errors[] = 'Dữ liệu biểu mẫu không hợp lệ.';
            $value = '';
        }

        $data[$key] = trim($value);
    }

    if (
        mb_strlen($data['ten_clb']) < 2 ||
        mb_strlen($data['ten_clb']) > 150
    ) {
        $errors[] = 'Tên CLB phải có từ 2 đến 150 ký tự.';
    }

    if (
        $data['linh_vuc'] === '' ||
        mb_strlen($data['linh_vuc']) > 100
    ) {
        $errors[] = 'Lĩnh vực phải có từ 1 đến 100 ký tự.';
    }

    if (!ngayClbHopLe($data['ngay_thanh_lap'])) {
        $errors[] = 'Ngày thành lập không hợp lệ.';
    }

    if (!in_array(
        $data['trang_thai'],
        ['hoat_dong', 'tam_dung'],
        true
    )) {
        $errors[] = 'Trạng thái CLB không hợp lệ.';
    }

    if (empty($errors)) {
        try {

            $stmtUpdate = $pdo->prepare("
                UPDATE cau_lac_bo
                SET
                    ten_clb = :ten_clb,
                    linh_vuc = :linh_vuc,
                    mo_ta = :mo_ta,
                    ngay_thanh_lap = :ngay_thanh_lap,
                    trang_thai = :trang_thai
                WHERE id = :clb_id
                  AND nguoi_tao_id = :user_id
            ");

            $stmtUpdate->execute([
                'ten_clb' => $data['ten_clb'],
                'linh_vuc' => $data['linh_vuc'],
                'mo_ta' => $data['mo_ta'],
                'ngay_thanh_lap' =>
                    $data['ngay_thanh_lap'] !== ''
                        ? $data['ngay_thanh_lap']
                        : null,
                'trang_thai' => $data['trang_thai'],
                'clb_id' => $clbId,
                'user_id' => $userId
            ]);

            header('Location: clb-quan-tri.php?success=sua');
            exit;

        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors[] = 'Không thể cập nhật câu lạc bộ.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Sửa câu lạc bộ</h1>
                <p>
                    Đang chỉnh sửa:
                    <strong><?= e($clb['ten_clb']) ?></strong>
                </p>
            </div>
        </section>

        <?php foreach ($errors as $error): ?>
            <div class="alert-error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endforeach; ?>

        <div class="card">

            <form
                method="POST"
                action="sua-clb.php?id=<?= $clbId ?>"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >

                <div class="form-group">
                    <label for="ten_clb">Tên câu lạc bộ *</label>

                    <input
                        type="text"
                        id="ten_clb"
                        name="ten_clb"
                        minlength="2"
                        maxlength="150"
                        value="<?= e($data['ten_clb']) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="linh_vuc">Lĩnh vực *</label>

                    <input
                        type="text"
                        id="linh_vuc"
                        name="linh_vuc"
                        maxlength="100"
                        value="<?= e($data['linh_vuc']) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="ngay_thanh_lap">Ngày thành lập</label>

                    <input
                        type="date"
                        id="ngay_thanh_lap"
                        name="ngay_thanh_lap"
                        value="<?= e($data['ngay_thanh_lap']) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="trang_thai">Trạng thái</label>

                    <select name="trang_thai" id="trang_thai">

                        <option
                            value="hoat_dong"
                            <?= $data['trang_thai'] === 'hoat_dong'
                                ? 'selected' : '' ?>
                        >
                            Hoạt động
                        </option>

                        <option
                            value="tam_dung"
                            <?= $data['trang_thai'] === 'tam_dung'
                                ? 'selected' : '' ?>
                        >
                            Tạm dừng
                        </option>

                    </select>
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô tả</label>

                    <textarea
                        name="mo_ta"
                        id="mo_ta"
                        rows="5"
                    ><?= e($data['mo_ta']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Lưu thay đổi
                </button>

                <a href="clb-quan-tri.php" class="btn">
                    Hủy
                </a>

            </form>

        </div>

    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
