
/*
==========================================================
 CLUBMANAGER - DỮ LIỆU MẪU
 File: database/seed.sql

 Chỉ chạy sau schema.sql trên DATABASE MỚI, TRỐNG.
 Không chạy trên database clubmanager đang sử dụng.

 Mật khẩu của tất cả tài khoản mẫu: Club@12345
 Mật khẩu đã được mã hóa bằng PHP password_hash().
==========================================================
*/

SET NAMES utf8mb4;

START TRANSACTION;

/* ========================================================
   1. NGƯỜI DÙNG
   ======================================================== */

INSERT INTO nguoi_dung
(id, ho_ten, ten_dang_nhap, mat_khau,
 email, so_dien_thoai, trang_thai)
VALUES

(
    1,
    'Nguyễn Văn Minh',
    'admin_demo',
    '$2y$12$mqB5ZozNXUAdViQ/YC6tu.kFdA5sSo97KpKbT2hlqAXpFlpR1Amvy',
    'admin.demo@example.com',
    '0900000001',
    'hoat_dong'
),

(
    2,
    'Trần Thị Lan',
    'lan_demo',
    '$2y$12$mqB5ZozNXUAdViQ/YC6tu.kFdA5sSo97KpKbT2hlqAXpFlpR1Amvy',
    'lan.demo@example.com',
    '0900000002',
    'hoat_dong'
),

(
    3,
    'Lê Quốc Bảo',
    'bao_demo',
    '$2y$12$mqB5ZozNXUAdViQ/YC6tu.kFdA5sSo97KpKbT2hlqAXpFlpR1Amvy',
    'bao.demo@example.com',
    '0900000003',
    'hoat_dong'
),

(
    4,
    'Phạm Ngọc Hà',
    'ha_demo',
    '$2y$12$mqB5ZozNXUAdViQ/YC6tu.kFdA5sSo97KpKbT2hlqAXpFlpR1Amvy',
    'ha.demo@example.com',
    '0900000004',
    'hoat_dong'
);

/* ========================================================
   2. CÂU LẠC BỘ
   ======================================================== */

INSERT INTO cau_lac_bo
(id, ten_clb, mo_ta, linh_vuc,
 ngay_thanh_lap, trang_thai, nguoi_tao_id)
VALUES

(
    1,
    'CLB Công nghệ',
    'Nơi sinh viên học lập trình, trao đổi kiến thức và thực hiện dự án.',
    'Công nghệ thông tin',
    '2026-09-01',
    'hoat_dong',
    1
),

(
    2,
    'CLB Tiếng Anh',
    'Môi trường rèn luyện kỹ năng giao tiếp và học ngoại ngữ.',
    'Ngoại ngữ',
    '2026-09-05',
    'hoat_dong',
    2
);

/* ========================================================
   3. THAM GIA CÂU LẠC BỘ
   ======================================================== */

INSERT INTO tham_gia_clb
(id, nguoi_dung_id, cau_lac_bo_id,
 vai_tro, ngay_tham_gia, trang_thai)
VALUES

(1, 1, 1, 'quan_tri', '2026-09-01', 'hoat_dong'),
(2, 2, 1, 'thanh_vien', '2026-09-03', 'hoat_dong'),
(3, 3, 1, 'thanh_vien', '2026-09-04', 'hoat_dong'),

(4, 2, 2, 'quan_tri', '2026-09-05', 'hoat_dong'),
(5, 3, 2, 'thanh_vien', '2026-09-06', 'hoat_dong'),
(6, 4, 2, 'thanh_vien', '2026-09-07', 'hoat_dong');

/* ========================================================
   4. SỰ KIỆN
   ======================================================== */

INSERT INTO su_kien
(id, cau_lac_bo_id, ten_su_kien,
 mo_ta, ngay_to_chuc, gio_bat_dau,
 gio_ket_thuc, dia_diem, trang_thai,
 nguoi_tao_id)
VALUES

(
    1,
    1,
    'Workshop lập trình Web',
    'Hướng dẫn xây dựng website bằng PHP và MySQL.',
    '2026-10-15',
    '08:00:00',
    '11:00:00',
    'Phòng A1',
    'da_ket_thuc',
    1
),

(
    2,
    1,
    'Cuộc thi lập trình',
    'Hoạt động thực hành và thi lập trình theo nhóm.',
    '2026-12-20',
    '07:30:00',
    '17:00:00',
    'Hội trường A',
    'sap_dien_ra',
    1
),

(
    3,
    2,
    'English Speaking Day',
    'Sinh hoạt giao tiếp tiếng Anh theo chủ đề.',
    '2026-10-20',
    '14:00:00',
    '16:00:00',
    'Phòng B2',
    'da_ket_thuc',
    2
);

/* ========================================================
   5. ĐIỂM DANH
   ======================================================== */

INSERT INTO diem_danh
(id, su_kien_id, nguoi_dung_id,
 trang_thai, ghi_chu)
VALUES

(1, 1, 1, 'co_mat', 'Tham gia đầy đủ'),
(2, 1, 2, 'co_mat', 'Đúng giờ'),
(3, 1, 3, 'vang', 'Vắng có báo trước'),

(4, 3, 2, 'co_mat', 'Tham gia đầy đủ'),
(5, 3, 3, 'co_mat', 'Tham gia đầy đủ'),
(6, 3, 4, 'vang', 'Vắng mặt');

/* Sự kiện 2 chưa có bản ghi điểm danh,
   dùng để kiểm thử trạng thái Chưa điểm danh. */

/* ========================================================
   6. THU - CHI
   ======================================================== */

INSERT INTO thu_chi
(id, cau_lac_bo_id, loai, noi_dung,
 so_tien, ngay_giao_dich,
 nguoi_thuc_hien_id, ghi_chu)
VALUES

(
    1, 1, 'thu',
    'Thu quỹ thành viên',
    1000000,
    '2026-10-01',
    1,
    'Thu quỹ đầu học kỳ'
),

(
    2, 1, 'chi',
    'Mua tài liệu học tập',
    250000,
    '2026-10-10',
    1,
    'Tài liệu cho workshop'
),

(
    3, 2, 'thu',
    'Đóng góp quỹ câu lạc bộ',
    800000,
    '2026-10-02',
    2,
    'Quỹ sinh hoạt'
),

(
    4, 2, 'chi',
    'Mua dụng cụ sinh hoạt',
    150000,
    '2026-10-12',
    2,
    'Dụng cụ tổ chức hoạt động'
);

COMMIT;

/* ========================================================
   KẾT QUẢ MONG ĐỢI
   ========================================================

   Người dùng: 4
   Câu lạc bộ: 2
   Bản ghi tham gia CLB: 6
   Sự kiện: 3
   Bản ghi điểm danh: 6
   Giao dịch thu chi: 4

   CLB Công nghệ:
   Tổng thu: 1.000.000đ
   Tổng chi:   250.000đ
   Số dư:      750.000đ

   CLB Tiếng Anh:
   Tổng thu:   800.000đ
   Tổng chi:   150.000đ
   Số dư:      650.000đ
======================================================== */
