<?php
require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];
$clbId = (int) ($_GET['clb_id'] ?? 0);

function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
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
    exit('Bạn không có quyền thêm thành viên cho CLB này.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$nguoiDungId = 0;
$vaiTro = 'thanh_vien';

$vaiTroHopLe = [
    'chu_nhiem',
    'pho_chu_nhiem',
    'thanh_vien'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nguoiDungId = (int) ($_POST['nguoi_dung_id'] ?? 0);
    $vaiTro = $_POST['vai_tro'] ?? 'thanh_vien';

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        $error = 'Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.';
    } elseif (
        $nguoiDungId <= 0 ||
        !in_array($vaiTro, $vaiTroHopLe, true)
    ) {
        $error = 'Thông tin thành viên không hợp lệ.';
    } elseif ($nguoiDungId === $userId) {
        $error = 'Người tạo câu lạc bộ đã có quyền quản trị.';
    } else {
        try {
            $pdo->beginTransaction();

            /* Kiểm tra tài khoản có hoạt động không */
            $stmtUser = $pdo->prepare("
                SELECT id
                FROM nguoi_dung
                WHERE id = :id
                  AND trang_thai = 'hoat_dong'
                LIMIT 1
            ");

            $stmtUser->execute(['id' => $nguoiDungId]);

            if (!$stmtUser->fetch()) {
                throw new RuntimeException(
                    'Tài khoản không tồn tại hoặc đã bị khóa.'
                );
            }

            /* Khóa bản ghi tham gia hiện có nếu có */
            $stmtCheck = $pdo->prepare("
                SELECT id, trang_thai
                FROM tham_gia_clb
                WHERE nguoi_dung_id = :nguoi_dung_id
                  AND cau_lac_bo_id = :clb_id
                LIMIT 1
                FOR UPDATE
            ");

            $stmtCheck->execute([
                'nguoi_dung_id' => $nguoiDungId,
                'clb_id' => $clbId
            ]);

            $daTonTai = $stmtCheck->fetch();

            if ($daTonTai) {
                if ($daTonTai['trang_thai'] !== 'da_roi') {
                    throw new RuntimeException(
                        'Người dùng này đang thuộc câu lạc bộ.'
                    );
                }

                /* Thành viên cũ: kích hoạt lại */
                $stmtUpdate = $pdo->prepare("
                    UPDATE tham_gia_clb
                    SET vai_tro = :vai_tro,
                        trang_thai = 'hoat_dong',
                        ngay_tham_gia = CURDATE()
                    WHERE id = :id
                      AND cau_lac_bo_id = :clb_id
                ");

                $stmtUpdate->execute([
                    'vai_tro' => $vaiTro,
                    'id' => $daTonTai['id'],
                    'clb_id' => $clbId
                ]);
            } else {
                /* Thành viên mới: tạo quan hệ tham gia */
                $stmtInsert = $pdo->prepare("
                    INSERT INTO tham_gia_clb
                    (
                        nguoi_dung_id,
                        cau_lac_bo_id,
                        vai_tro,
                        ngay_tham_gia,
                        trang_thai
                    )
                    VALUES
                    (
                        :nguoi_dung_id,
                        :clb_id,
                        :vai_tro,
                        CURDATE(),
                        'hoat_dong'
                    )
                ");

                $stmtInsert->execute([
                    'nguoi_dung_id' => $nguoiDungId,
                    'clb_id' => $clbId,
                    'vai_tro' => $vaiTro
                ]);
            }

            $pdo->commit();

            header(
                'Location: thanh-vien.php?clb_id='
                . $clbId
                . '&success=them'
            );
            exit;

        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $e->getMessage();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());
            $error = 'Không thể thêm thành viên. Vui lòng thử lại.';
        }
    }
}

/* Lấy các tài khoản chưa tham gia hoặc đã rời */
$stmtNguoiDung = $pdo->prepare("
    SELECT
        nd.id,
        nd.ho_ten,
        nd.ten_dang_nhap,
        nd.email,
        tg.trang_thai AS trang_thai_tham_gia
    FROM nguoi_dung nd

    LEFT JOIN tham_gia_clb tg
        ON tg.nguoi_dung_id = nd.id
       AND tg.cau_lac_bo_id = :clb_id

    WHERE nd.trang_thai = 'hoat_dong'
      AND nd.id <> :user_id
      AND (
          tg.id IS NULL
          OR tg.trang_thai = 'da_roi'
      )

    ORDER BY nd.ho_ten ASC
");

$stmtNguoiDung->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);

$danhSachNguoiDung = $stmtNguoiDung->fetchAll();

$pageTitle = 'Thêm thành viên';
$activePage = 'thanh-vien';

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header">
            <div>
                <h1>Thêm thành viên</h1>
                <p>
                    Câu lạc bộ:
                    <strong><?= e($clb['ten_clb']) ?></strong>
                </p>
            </div>

            <a
                href="thanh-vien.php?clb_id=<?= $clbId ?>"
                class="btn"
            >
                Quay lại
            </a>
        </section>

        <?php if ($error !== ''): ?>
            <div class="alert-error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">

            <?php if ($danhSachNguoiDung): ?>

                <form method="POST"
                      action="them-thanh-vien.php?clb_id=<?= $clbId ?>">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['csrf_token']) ?>"
                    >

                    <div class="form-group">
                        <label for="nguoi_dung_id">
                            Chọn tài khoản
                        </label>

                        <select
                            name="nguoi_dung_id"
                            id="nguoi_dung_id"
                            required
                        >
                            <option value="">
                                -- Chọn người dùng --
                            </option>

                            <?php foreach ($danhSachNguoiDung as $nd): ?>
                                <option
                                    value="<?= (int)$nd['id'] ?>"
                                    <?= $nguoiDungId === (int)$nd['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= e($nd['ho_ten']) ?>
                                    -
                                    <?= e($nd['ten_dang_nhap']) ?>

                                    <?php if ($nd['email']): ?>
                                        (<?= e($nd['email']) ?>)
                                    <?php endif; ?>

                                    <?php if (
                                        $nd['trang_thai_tham_gia'] === 'da_roi'
                                    ): ?>
                                        - Đã từng tham gia
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="vai_tro">Vai trò trong CLB</label>

                        <select name="vai_tro" id="vai_tro">
                            <option
                                value="thanh_vien"
                                <?= $vaiTro === 'thanh_vien'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Thành viên
                            </option>

                            <option
                                value="pho_chu_nhiem"
                                <?= $vaiTro === 'pho_chu_nhiem'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Phó chủ nhiệm
                            </option>

                            <option
                                value="chu_nhiem"
                                <?= $vaiTro === 'chu_nhiem'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Chủ nhiệm
                            </option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Thêm thành viên
                    </button>

                    <a
                        href="thanh-vien.php?clb_id=<?= $clbId ?>"
                        class="btn"
                    >
                        Hủy
                    </a>

                </form>

            <?php else: ?>

                <div class="empty-state">
                    <p>Không có tài khoản phù hợp để thêm.</p>

                    <a
                        href="thanh-vien.php?clb_id=<?= $clbId ?>"
                        class="btn"
                    >
                        Quay lại
                    </a>
                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
