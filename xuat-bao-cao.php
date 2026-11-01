<?php
/*
 * CLUBMANAGER - XUẤT BÁO CÁO EXCEL XLSX
 * Định dạng: Microsoft Excel .xlsx
 * Không cần Composer hoặc PhpSpreadsheet.
 *
 * Yêu cầu:
 * - PHP PDO + MySQL
 * - Extension ZIP (ZipArchive)
 *
 * Lưu ý:
 * - Không đặt dòng trống trước <?php
 * - Không thêm dấu ?> ở cuối file
 */

/* Chặn ký tự thừa từ các file PHP được include */
ob_start();

require_once __DIR__ . '/includes/quan-ly-auth.php';
require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];
$clbId = (int) ($_GET['clb_id'] ?? 0);

/* =====================================================
   1. KIỂM TRA MÔI TRƯỜNG
   ===================================================== */

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    exit(
        'PHP chưa bật extension ZIP. ' .
        'Hãy bật extension=zip trong php.ini.'
    );
}

if ($clbId <= 0) {
    http_response_code(400);
    exit('Mã câu lạc bộ không hợp lệ.');
}

/* =====================================================
   2. KIỂM TRA QUYỀN SỞ HỮU CLB
   ===================================================== */

$stmt = $pdo->prepare("
    SELECT id, ten_clb
    FROM cau_lac_bo
    WHERE id = :clb_id
      AND nguoi_tao_id = :user_id
    LIMIT 1
");

$stmt->execute([
    'clb_id' => $clbId,
    'user_id' => $userId
]);

$clb = $stmt->fetch();

if (!$clb) {
    http_response_code(403);
    exit('Bạn không có quyền xuất báo cáo CLB này.');
}

/* =====================================================
   3. THỐNG KÊ THÀNH VIÊN
   ===================================================== */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM tham_gia_clb
    WHERE cau_lac_bo_id = :clb_id
      AND trang_thai = 'hoat_dong'
");

$stmt->execute([
    'clb_id' => $clbId
]);

$tongThanhVien = (int) $stmt->fetchColumn();

/* =====================================================
   4. THỐNG KÊ SỰ KIỆN
   ===================================================== */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM su_kien
    WHERE cau_lac_bo_id = :clb_id
");

$stmt->execute([
    'clb_id' => $clbId
]);

$tongSuKien = (int) $stmt->fetchColumn();

/* =====================================================
   5. THỐNG KÊ ĐIỂM DANH
   ===================================================== */

$stmt = $pdo->prepare("
    SELECT
        COUNT(dd.id) AS tong,

        COALESCE(
            SUM(
                CASE
                    WHEN dd.trang_thai = 'co_mat'
                    THEN 1 ELSE 0
                END
            ), 0
        ) AS co_mat,

        COALESCE(
            SUM(
                CASE
                    WHEN dd.trang_thai = 'vang'
                    THEN 1 ELSE 0
                END
            ), 0
        ) AS vang

    FROM diem_danh dd

    INNER JOIN su_kien sk
        ON sk.id = dd.su_kien_id

    WHERE sk.cau_lac_bo_id = :clb_id
");

$stmt->execute([
    'clb_id' => $clbId
]);

$diemDanh = $stmt->fetch();

$tongDiemDanh = (int) $diemDanh['tong'];
$coMat = (int) $diemDanh['co_mat'];
$vang = (int) $diemDanh['vang'];

$tyLeCoMat = $tongDiemDanh > 0
    ? round(($coMat / $tongDiemDanh) * 100, 1)
    : 0;

/* =====================================================
   6. THỐNG KÊ TÀI CHÍNH
   ===================================================== */

$stmt = $pdo->prepare("
    SELECT
        COALESCE(
            SUM(
                CASE
                    WHEN loai = 'thu'
                    THEN so_tien ELSE 0
                END
            ), 0
        ) AS tong_thu,

        COALESCE(
            SUM(
                CASE
                    WHEN loai = 'chi'
                    THEN so_tien ELSE 0
                END
            ), 0
        ) AS tong_chi

    FROM thu_chi
    WHERE cau_lac_bo_id = :clb_id
");

$stmt->execute([
    'clb_id' => $clbId
]);

$taiChinh = $stmt->fetch();

$tongThu = (float) $taiChinh['tong_thu'];
$tongChi = (float) $taiChinh['tong_chi'];

$soDu = $tongThu - $tongChi;

/* =====================================================
   7. THỐNG KÊ THU CHI THEO THÁNG
   ===================================================== */

$stmt = $pdo->prepare("
    SELECT
        DATE_FORMAT(
            ngay_giao_dich,
            '%Y-%m'
        ) AS thang,

        COALESCE(
            SUM(
                CASE
                    WHEN loai = 'thu'
                    THEN so_tien ELSE 0
                END
            ), 0
        ) AS tong_thu,

        COALESCE(
            SUM(
                CASE
                    WHEN loai = 'chi'
                    THEN so_tien ELSE 0
                END
            ), 0
        ) AS tong_chi

    FROM thu_chi

    WHERE cau_lac_bo_id = :clb_id

    GROUP BY
        DATE_FORMAT(ngay_giao_dich, '%Y-%m')

    ORDER BY thang ASC
");

$stmt->execute([
    'clb_id' => $clbId
]);

$thongKeThang = $stmt->fetchAll();

/* =====================================================
   8. LẤY CHI TIẾT GIAO DỊCH
   ===================================================== */

$stmt = $pdo->prepare("
    SELECT
        tc.ngay_giao_dich,
        tc.loai,
        tc.noi_dung,
        tc.so_tien,
        nd.ho_ten AS nguoi_thuc_hien,
        tc.ghi_chu

    FROM thu_chi tc

    INNER JOIN nguoi_dung nd
        ON nd.id = tc.nguoi_thuc_hien_id

    WHERE tc.cau_lac_bo_id = :clb_id

    ORDER BY
        tc.ngay_giao_dich ASC,
        tc.id ASC
");

$stmt->execute([
    'clb_id' => $clbId
]);

$danhSachGiaoDich = $stmt->fetchAll();

/* =====================================================
   9. CÁC HÀM TẠO EXCEL
   ===================================================== */

/* Chuyển dữ liệu thành chuỗi XML an toàn */
function xmlText($value): string
{
    $value = (string) ($value ?? '');

    /* Loại bỏ ký tự điều khiển không hợp lệ */
    $value = preg_replace(
        '/[\x00-\x08\x0B\x0C\x0E-\x1F]/',
        '',
        $value
    );

    return htmlspecialchars(
        $value,
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
}

/* Định nghĩa ô Excel */
function excelCell($value, int $style = 0): array
{
    return [
        'value' => $value,
        'style' => $style
    ];
}

/* Chuyển số cột thành tên cột Excel */
function excelColumn(int $number): string
{
    $result = '';

    while ($number > 0) {
        $number--;

        $result = chr(
            65 + ($number % 26)
        ) . $result;

        $number = intdiv($number, 26);
    }

    return $result;
}

/* =====================================================
   10. TẠO WORKSHEET XML
   ===================================================== */

function createWorksheet(
    array $rows,
    array $widths,
    array $merges = []
): string {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';

    $xml .=
        '<worksheet ' .
        'xmlns="http://schemas.openxmlformats.org/' .
        'spreadsheetml/2006/main">';

    /* Thiết lập khung nhìn */
    $xml .=
        '<sheetViews>' .
        '<sheetView workbookViewId="0"/>' .
        '</sheetViews>';

    $xml .=
        '<sheetFormatPr defaultRowHeight="20"/>';

    /* Thiết lập độ rộng cột */
    $xml .= '<cols>';

    foreach ($widths as $index => $width) {

        $column = $index + 1;

        $xml .=
            '<col min="' . $column . '"' .
            ' max="' . $column . '"' .
            ' width="' . (float) $width . '"' .
            ' customWidth="1"/>';
    }

    $xml .= '</cols>';

    /* Bắt đầu dữ liệu */
    $xml .= '<sheetData>';

    foreach ($rows as $rowIndex => $row) {

        $rowNumber = $rowIndex + 1;

        $xml .=
            '<row r="' . $rowNumber . '">';

        foreach ($row as $colIndex => $cell) {

            if ($cell === null) {
                continue;
            }

            if (
                is_array($cell) &&
                array_key_exists('value', $cell)
            ) {

                $value = $cell['value'];
                $style = (int) $cell['style'];

            } else {

                $value = $cell;
                $style = 0;
            }

            $address =
                excelColumn($colIndex + 1)
                . $rowNumber;

            /* Ô dữ liệu số */
            if (
                is_int($value) ||
                is_float($value)
            ) {

                if (!is_finite((float) $value)) {
                    $value = 0;
                }

                $xml .=
                    '<c r="' . $address . '"' .
                    ' s="' . $style . '">' .
                    '<v>' .
                    xmlText((string) $value) .
                    '</v>' .
                    '</c>';

            } else {

                /* Lưu chuỗi Unicode dạng inlineStr */
                $xml .=
                    '<c r="' . $address . '"' .
                    ' s="' . $style . '"' .
                    ' t="inlineStr">' .
                    '<is><t xml:space="preserve">' .
                    xmlText($value) .
                    '</t></is>' .
                    '</c>';
            }
        }

        $xml .= '</row>';
    }

    $xml .= '</sheetData>';

    /* Gộp các ô tiêu đề */
    if (!empty($merges)) {

        $xml .=
            '<mergeCells count="' .
            count($merges) . '">';

        foreach ($merges as $range) {
            $xml .=
                '<mergeCell ref="' .
                xmlText($range) .
                '"/>';
        }

        $xml .= '</mergeCells>';
    }

    $xml .= '</worksheet>';

    return $xml;
}

/* =====================================================
   11. SHEET 1 - TỔNG QUAN
   ===================================================== */

/*
 * Style:
 * 0: Văn bản thường
 * 1: Tiêu đề lớn
 * 2: Tiêu đề phần
 * 3: Đầu bảng
 * 4: Định dạng tiền
 * 5: Tiền in đậm
 * 6: Số nguyên
 * 7: Chữ in đậm
 */

$rowsOverview = [

    [
        excelCell(
            'BÁO CÁO TỔNG HỢP CÂU LẠC BỘ',
            1
        )
    ],

    [],

    [
        excelCell('Tên câu lạc bộ', 7),
        $clb['ten_clb']
    ],

    [
        excelCell('Ngày xuất báo cáo', 7),
        date('d/m/Y H:i:s')
    ],

    [],

    [
        excelCell(
            '1. THỐNG KÊ HOẠT ĐỘNG',
            2
        )
    ],

    [
        excelCell('Chỉ tiêu', 3),
        excelCell('Giá trị', 3)
    ],

    [
        'Thành viên hoạt động',
        excelCell($tongThanhVien, 6)
    ],

    [
        'Tổng số sự kiện',
        excelCell($tongSuKien, 6)
    ],

    [
        'Tổng lượt đã điểm danh',
        excelCell($tongDiemDanh, 6)
    ],

    [
        'Lượt có mặt',
        excelCell($coMat, 6)
    ],

    [
        'Lượt vắng',
        excelCell($vang, 6)
    ],

    [
        'Tỷ lệ có mặt (%)',
        $tyLeCoMat
    ],

    [],

    [
        excelCell(
            '2. THỐNG KÊ TÀI CHÍNH',
            2
        )
    ],

    [
        excelCell('Chỉ tiêu', 3),
        excelCell('Số tiền (VNĐ)', 3)
    ],

    [
        'Tổng thu',
        excelCell($tongThu, 4)
    ],

    [
        'Tổng chi',
        excelCell($tongChi, 4)
    ],

    [
        excelCell('SỐ DƯ QUỸ', 7),
        excelCell($soDu, 5)
    ]
];

/* =====================================================
   12. SHEET 2 - THU CHI THEO THÁNG
   ===================================================== */

$rowsMonthly = [

    [
        excelCell(
            'BÁO CÁO THU CHI THEO THÁNG',
            1
        )
    ],

    [],

    [
        excelCell('Tháng', 3),
        excelCell('Tổng thu (VNĐ)', 3),
        excelCell('Tổng chi (VNĐ)', 3),
        excelCell('Chênh lệch (VNĐ)', 3)
    ]
];

foreach ($thongKeThang as $thang) {

    $thu = (float) $thang['tong_thu'];
    $chi = (float) $thang['tong_chi'];

    $rowsMonthly[] = [
        $thang['thang'],
        excelCell($thu, 4),
        excelCell($chi, 4),
        excelCell($thu - $chi, 4)
    ];
}

/* Dòng tổng cộng */
$rowsMonthly[] = [];

$rowsMonthly[] = [
    excelCell('TỔNG CỘNG', 7),
    excelCell($tongThu, 5),
    excelCell($tongChi, 5),
    excelCell($soDu, 5)
];

/* =====================================================
   13. SHEET 3 - CHI TIẾT GIAO DỊCH
   ===================================================== */

$rowsDetails = [

    [
        excelCell(
            'CHI TIẾT GIAO DỊCH CÂU LẠC BỘ',
            1
        )
    ],

    [],

    [
        excelCell('Ngày giao dịch', 3),
        excelCell('Loại', 3),
        excelCell('Nội dung', 3),
        excelCell('Số tiền (VNĐ)', 3),
        excelCell('Người thực hiện', 3),
        excelCell('Ghi chú', 3)
    ]
];

foreach ($danhSachGiaoDich as $gd) {

    $rowsDetails[] = [

        $gd['ngay_giao_dich'],

        $gd['loai'] === 'thu'
            ? 'Thu'
            : 'Chi',

        $gd['noi_dung'],

        excelCell(
            (float) $gd['so_tien'],
            4
        ),

        $gd['nguoi_thuc_hien'],

        $gd['ghi_chu'] ?? ''
    ];
}

/* =====================================================
   14. ĐỊNH DẠNG WORKBOOK
   ===================================================== */

$styles = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <numFmts count="1">
        <numFmt numFmtId="164" formatCode="#,##0 &quot;đ&quot;"/>
    </numFmts>

    <fonts count="3">
        <font>
            <sz val="11"/>
            <name val="Calibri"/>
        </font>

        <font>
            <b/>
            <sz val="16"/>
            <color rgb="FFFFFFFF"/>
            <name val="Calibri"/>
        </font>

        <font>
            <b/>
            <sz val="11"/>
            <color rgb="FF176B58"/>
            <name val="Calibri"/>
        </font>
    </fonts>

    <fills count="3">
        <fill>
            <patternFill patternType="none"/>
        </fill>

        <fill>
            <patternFill patternType="gray125"/>
        </fill>

        <fill>
            <patternFill patternType="solid">
                <fgColor rgb="FF358E7C"/>
                <bgColor indexed="64"/>
            </patternFill>
        </fill>
    </fills>

    <borders count="1">
        <border>
            <left/>
            <right/>
            <top/>
            <bottom/>
            <diagonal/>
        </border>
    </borders>

    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>

    <cellXfs count="8">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
        <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
        <xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
        <xf numFmtId="164" fontId="2" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1"/>
        <xf numFmtId="1" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
        <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>
    </cellXfs>

    <cellStyles count="1">
        <cellStyle name="Normal" xfId="0" builtinId="0"/>
    </cellStyles>
</styleSheet>
XML;

/* =====================================================
   15. CẤU TRÚC XLSX
   ===================================================== */

$contentTypes = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;

$rootRels = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1"
        Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
        Target="xl/workbook.xml"/>
</Relationships>
XML;

$workbook = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Tong quan" sheetId="1" r:id="rId1"/>
        <sheet name="Thu chi theo thang" sheetId="2" r:id="rId2"/>
        <sheet name="Chi tiet giao dich" sheetId="3" r:id="rId3"/>
    </sheets>
</workbook>
XML;

$workbookRels = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1"
        Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
        Target="worksheets/sheet1.xml"/>

    <Relationship Id="rId2"
        Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
        Target="worksheets/sheet2.xml"/>

    <Relationship Id="rId3"
        Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
        Target="worksheets/sheet3.xml"/>

    <Relationship Id="rId4"
        Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"
        Target="styles.xml"/>
</Relationships>
XML;

/* =====================================================
   16. TẠO FILE EXCEL
   ===================================================== */

$tempFile = tempnam(
    sys_get_temp_dir(),
    'clubmanager_'
);

if ($tempFile === false) {
    http_response_code(500);
    exit('Không thể tạo file Excel tạm.');
}

$zip = new ZipArchive();

$zipResult = $zip->open(
    $tempFile,
    ZipArchive::CREATE | ZipArchive::OVERWRITE
);

if ($zipResult !== true) {

    @unlink($tempFile);

    http_response_code(500);

    exit('Không thể tạo file báo cáo Excel.');
}

/* Ghi cấu trúc workbook */
$zip->addFromString(
    '[Content_Types].xml',
    $contentTypes
);

$zip->addFromString(
    '_rels/.rels',
    $rootRels
);

$zip->addFromString(
    'xl/workbook.xml',
    $workbook
);

$zip->addFromString(
    'xl/_rels/workbook.xml.rels',
    $workbookRels
);

$zip->addFromString(
    'xl/styles.xml',
    $styles
);

/* Sheet 1 */
$zip->addFromString(
    'xl/worksheets/sheet1.xml',

    createWorksheet(
        $rowsOverview,
        [38, 32],
        [
            'A1:B1',
            'A6:B6',
            'A15:B15'
        ]
    )
);

/* Sheet 2 */
$zip->addFromString(
    'xl/worksheets/sheet2.xml',

    createWorksheet(
        $rowsMonthly,
        [24, 26, 26, 30],
        [
            'A1:D1'
        ]
    )
);

/* Sheet 3 */
$zip->addFromString(
    'xl/worksheets/sheet3.xml',

    createWorksheet(
        $rowsDetails,
        [20, 15, 45, 26, 30, 45],
        [
            'A1:F1'
        ]
    )
);

/* Hoàn tất cấu trúc ZIP */
if (!$zip->close()) {

    @unlink($tempFile);

    http_response_code(500);

    exit('Không thể hoàn tất báo cáo Excel.');
}

/* =====================================================
   17. XÓA DỮ LIỆU OUTPUT THỪA
   ===================================================== */

/*
 * Đây là phần sửa lỗi chính.
 *
 * Khi các file require_once vô tình xuất
 * khoảng trắng hoặc ký tự xuống dòng,
 * chúng có thể làm hỏng nội dung .xlsx.
 *
 * Xóa các output buffer trước khi gửi
 * dữ liệu ZIP cho trình duyệt.
 */

while (ob_get_level() > 0) {
    ob_end_clean();
}

/* =====================================================
   18. GỬI FILE XLSX VỀ TRÌNH DUYỆT
   ===================================================== */

$fileName = 'bao-cao-clb-'
    . $clbId
    . '-'
    . date('Y-m-d')
    . '.xlsx';

/* Đảm bảo không có đầu ra trước dữ liệu ZIP */
if (headers_sent($file, $line)) {

    @unlink($tempFile);

    http_response_code(500);

    exit(
        'Không thể xuất Excel vì header đã được gửi.'
    );
}

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' .
    $fileName .
    '"'
);

header(
    'Content-Length: ' . filesize($tempFile)
);

header('Cache-Control: no-store');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

/* Gửi dữ liệu nhị phân */
readfile($tempFile);

/* Xóa file tạm */
@unlink($tempFile);

exit;
