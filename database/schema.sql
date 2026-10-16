/* =========================================================
   DATABASE CLUBMANAGER
   ========================================================= */

CREATE DATABASE IF NOT EXISTS clubmanager
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE clubmanager;


/* =========================================================
   1. NGƯỜI DÙNG
   ========================================================= */

CREATE TABLE nguoi_dung (
    id INT AUTO_INCREMENT PRIMARY KEY,

    ho_ten VARCHAR(100) NOT NULL,

    ten_dang_nhap VARCHAR(50)
        NOT NULL
        UNIQUE,

    mat_khau VARCHAR(255)
        NOT NULL,

    email VARCHAR(100)
        UNIQUE,

    so_dien_thoai VARCHAR(20),

    trang_thai ENUM(
        'hoat_dong',
        'tam_khoa'
    )
    NOT NULL DEFAULT 'hoat_dong',

    ngay_tao DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP
);


/* =========================================================
   2. CÂU LẠC BỘ
   ========================================================= */

CREATE TABLE cau_lac_bo (
    id INT AUTO_INCREMENT PRIMARY KEY,

    ten_clb VARCHAR(150)
        NOT NULL,

    mo_ta TEXT,

    linh_vuc VARCHAR(100),

    ngay_thanh_lap DATE,

    trang_thai ENUM(
        'hoat_dong',
        'tam_dung'
    )
    NOT NULL DEFAULT 'hoat_dong',

    nguoi_tao_id INT
        NOT NULL,

    ngay_tao DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_clb_nguoi_tao
        FOREIGN KEY (nguoi_tao_id)
        REFERENCES nguoi_dung(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


/* =========================================================
   3. THAM GIA CÂU LẠC BỘ
   ========================================================= */

CREATE TABLE tham_gia_clb (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nguoi_dung_id INT
        NOT NULL,

    cau_lac_bo_id INT
        NOT NULL,

    vai_tro ENUM(
        'quan_tri',
        'chu_nhiem',
        'pho_chu_nhiem',
        'thanh_vien'
    )
    NOT NULL DEFAULT 'thanh_vien',

    ngay_tham_gia DATE
        NOT NULL,

    trang_thai ENUM(
        'hoat_dong',
        'tam_dung',
        'da_roi'
    )
    NOT NULL DEFAULT 'hoat_dong',

    UNIQUE (
        nguoi_dung_id,
        cau_lac_bo_id
    ),

    CONSTRAINT fk_thamgia_nguoidung
        FOREIGN KEY (nguoi_dung_id)
        REFERENCES nguoi_dung(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_thamgia_clb
        FOREIGN KEY (cau_lac_bo_id)
        REFERENCES cau_lac_bo(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


/* =========================================================
   4. SỰ KIỆN
   ========================================================= */

CREATE TABLE su_kien (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cau_lac_bo_id INT
        NOT NULL,

    ten_su_kien VARCHAR(150)
        NOT NULL,

    mo_ta TEXT,

    ngay_to_chuc DATE
        NOT NULL,

    gio_bat_dau TIME,

    gio_ket_thuc TIME,

    dia_diem VARCHAR(200),

    trang_thai ENUM(
        'sap_dien_ra',
        'dang_dien_ra',
        'da_ket_thuc'
    )
    NOT NULL DEFAULT 'sap_dien_ra',

    nguoi_tao_id INT
        NOT NULL,

    ngay_tao DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sukien_clb
        FOREIGN KEY (cau_lac_bo_id)
        REFERENCES cau_lac_bo(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_sukien_nguoi_tao
        FOREIGN KEY (nguoi_tao_id)
        REFERENCES nguoi_dung(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


/* =========================================================
   5. ĐIỂM DANH
   ========================================================= */

CREATE TABLE diem_danh (
    id INT AUTO_INCREMENT PRIMARY KEY,

    su_kien_id INT
        NOT NULL,

    nguoi_dung_id INT
        NOT NULL,

    trang_thai ENUM(
        'co_mat',
        'vang'
    )
    NOT NULL,

    ghi_chu VARCHAR(255),

    thoi_gian_cap_nhat DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (
        su_kien_id,
        nguoi_dung_id
    ),

    CONSTRAINT fk_diemdanh_sukien
        FOREIGN KEY (su_kien_id)
        REFERENCES su_kien(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_diemdanh_nguoidung
        FOREIGN KEY (nguoi_dung_id)
        REFERENCES nguoi_dung(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


/* =========================================================
   6. THU - CHI
   ========================================================= */

CREATE TABLE thu_chi (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cau_lac_bo_id INT
        NOT NULL,

    loai ENUM(
        'thu',
        'chi'
    )
    NOT NULL,

    noi_dung VARCHAR(255)
        NOT NULL,

    so_tien DECIMAL(15,2)
        NOT NULL,

    ngay_giao_dich DATE
        NOT NULL,

    nguoi_thuc_hien_id INT
        NOT NULL,

    ghi_chu TEXT,

    ngay_tao DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_thuchi_clb
        FOREIGN KEY (cau_lac_bo_id)
        REFERENCES cau_lac_bo(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_thuchi_nguoidung
        FOREIGN KEY (nguoi_thuc_hien_id)
        REFERENCES nguoi_dung(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);