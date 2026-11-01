
<?php
/* Kiểm tra dữ liệu giao dịch thu chi */

function kiemTraGiaoDich(array $input): array
{
    $errors = [];

    $data = [
        'loai' => '',
        'noi_dung' => '',
        'so_tien' => '',
        'ngay_giao_dich' => '',
        'ghi_chu' => ''
    ];

    foreach ($data as $key => $default) {
        $value = $input[$key] ?? '';

        if (!is_string($value)) {
            $errors[] = 'Dữ liệu gửi lên không hợp lệ.';
            $value = '';
        }

        $data[$key] = trim($value);
    }

    if (!in_array($data['loai'], ['thu', 'chi'], true)) {
        $errors[] = 'Loại giao dịch không hợp lệ.';
    }

    if (
        $data['noi_dung'] === '' ||
        mb_strlen($data['noi_dung']) > 255
    ) {
        $errors[] = 'Nội dung phải có từ 1 đến 255 ký tự.';
    }

    /* Số tiền VND nguyên dương, phù hợp DECIMAL(15,2) */
    if (
        !preg_match('/^[0-9]{1,13}$/', $data['so_tien']) ||
        (float)$data['so_tien'] <= 0
    ) {
        $errors[] = 'Số tiền phải là số nguyên dương, không vượt quá 13 chữ số.';
    } else {
        $data['so_tien'] = ltrim($data['so_tien'], '0');
    }

    $ngay = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $data['ngay_giao_dich']
    );

    if (
        !$ngay ||
        $ngay->format('Y-m-d') !== $data['ngay_giao_dich']
    ) {
        $errors[] = 'Ngày giao dịch không hợp lệ.';
    }

    return [
        'errors' => $errors,
        'data' => $data
    ];
}

function hienThiHTML($value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function taoCsrfThuChi(): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrfThuChiHopLe($token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
