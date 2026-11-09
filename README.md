
# CLUBMANAGER – HỆ THỐNG QUẢN LÝ CÂU LẠC BỘ

## 1. Giới thiệu

ClubManager là website quản lý hoạt động của nhiều câu lạc bộ, được xây dựng bằng PHP và MySQL.

Hệ thống giúp người dùng quản lý câu lạc bộ, thành viên, sự kiện, điểm danh, thu–chi và thống kê hoạt động.

Một người dùng có thể tham gia nhiều câu lạc bộ và đồng thời quản lý các câu lạc bộ do mình tạo.

## 2. Công nghệ sử dụng

- HTML5: Xây dựng cấu trúc giao diện.
- CSS3: Thiết kế giao diện và bố cục responsive.
- JavaScript: Hỗ trợ tương tác trên trình duyệt.
- PHP: Xử lý nghiệp vụ phía máy chủ.
- MySQL: Lưu trữ dữ liệu.
- PDO: Truy vấn cơ sở dữ liệu an toàn.
- XAMPP: Môi trường Apache và MySQL.
- Git và GitHub: Quản lý mã nguồn.
- ZipArchive: Tạo báo cáo Excel .xlsx.

## 3. Chức năng của hệ thống

### 3.1. Quản lý tài khoản

- Đăng ký tài khoản.
- Đăng nhập bằng tên đăng nhập hoặc email.
- Đăng xuất khỏi hệ thống.
- Mã hóa mật khẩu bằng password_hash().
- Kiểm tra mật khẩu bằng password_verify().
- Kiểm tra trạng thái hoạt động của tài khoản.
- Quản lý phiên đăng nhập bằng PHP Session.

### 3.2. Quản lý câu lạc bộ

- Tạo câu lạc bộ mới.
- Xem danh sách câu lạc bộ đang quản lý.
- Xem chi tiết câu lạc bộ.
- Chỉnh sửa thông tin câu lạc bộ.
- Xóa câu lạc bộ có xác nhận.
- Phân biệt câu lạc bộ quản lý và câu lạc bộ tham gia.

### 3.3. Quản lý thành viên

- Xem danh sách thành viên.
- Tìm kiếm và lọc thành viên.
- Thêm tài khoản đã đăng ký vào câu lạc bộ.
- Cập nhật vai trò của thành viên.
- Cập nhật trạng thái tham gia.
- Cho thành viên rời câu lạc bộ.
- Hỗ trợ thêm lại thành viên đã rời câu lạc bộ.

Các vai trò thành viên:

- Quản trị.
- Chủ nhiệm.
- Phó chủ nhiệm.
- Thành viên.

### 3.4. Quản lý sự kiện

- Tạo sự kiện.
- Xem danh sách sự kiện.
- Cập nhật thông tin sự kiện.
- Xóa sự kiện.
- Theo dõi thời gian, địa điểm và trạng thái tổ chức.

Các trạng thái sự kiện:

- Sắp diễn ra.
- Đang diễn ra.
- Đã kết thúc.

### 3.5. Quản lý điểm danh

Người quản lý có thể:

- Chọn câu lạc bộ và sự kiện.
- Xem danh sách thành viên.
- Ghi nhận thành viên Có mặt hoặc Vắng.
- Nhập ghi chú điểm danh.
- Cập nhật kết quả điểm danh.

Thành viên có thể:

- Xem lịch sử điểm danh của chính mình.
- Xem số lần Có mặt.
- Xem số lần Vắng.
- Xem các sự kiện Chưa điểm danh.
- Theo dõi tỷ lệ có mặt.

Trạng thái Chưa điểm danh không được tự động tính là Vắng.

### 3.6. Quản lý thu–chi

- Thêm khoản thu.
- Thêm khoản chi.
- Sửa giao dịch.
- Xóa giao dịch.
- Xem danh sách giao dịch.
- Tính tổng thu, tổng chi và số dư quỹ.

Công thức:

Số dư = Tổng thu - Tổng chi

### 3.7. Thống kê và xuất báo cáo

- Thống kê số lượng thành viên.
- Thống kê số lượng sự kiện.
- Thống kê tình hình điểm danh.
- Thống kê tổng thu, tổng chi và số dư.
- Tổng hợp thu–chi theo tháng.
- Xuất báo cáo Excel định dạng .xlsx.

File Excel gồm 3 sheet:

1. Tong quan.
2. Thu chi theo thang.
3. Chi tiet giao dich.

## 4. Phân quyền người dùng

### 4.1. Người quản lý câu lạc bộ

Người tạo câu lạc bộ có quyền:

- Quản lý thông tin câu lạc bộ mình tạo.
- Thêm, sửa và quản lý thành viên.
- Thêm, sửa và xóa sự kiện.
- Thực hiện điểm danh.
- Quản lý các khoản thu–chi.
- Xem thống kê và xuất báo cáo.

Người quản lý chỉ được chỉnh sửa dữ liệu thuộc câu lạc bộ do mình tạo.

### 4.2. Thành viên thường

Thành viên thường được phép:

- Xem câu lạc bộ đang tham gia.
- Xem chi tiết câu lạc bộ.
- Xem các sự kiện.
- Xem lịch sử điểm danh của chính mình.

Thành viên không được phép thay đổi dữ liệu quản lý của câu lạc bộ khác.

Mọi tài khoản hợp lệ đều có thể tạo câu lạc bộ mới và trở thành người quản lý của câu lạc bộ đó.

## 5. Cơ sở dữ liệu

Tên cơ sở dữ liệu:

clubmanager

Hệ thống có 6 bảng chính:

1. nguoi_dung
2. cau_lac_bo
3. tham_gia_clb
4. su_kien
5. diem_danh
6. thu_chi

Mối quan hệ chính:

- Một người dùng có thể tạo nhiều câu lạc bộ.
- Một người dùng có thể tham gia nhiều câu lạc bộ.
- Một câu lạc bộ có nhiều thành viên.
- Một câu lạc bộ có nhiều sự kiện.
- Một sự kiện có nhiều bản ghi điểm danh.
- Một câu lạc bộ có nhiều giao dịch thu–chi.

Các bảng liên kết thông qua khóa chính và khóa ngoại.

## 6. Yêu cầu môi trường

Để chạy website, cần chuẩn bị:

- Windows 10 hoặc Windows 11.
- XAMPP.
- Apache.
- MySQL.
- PHP hỗ trợ PDO MySQL.
- PHP extension mbstring.
- PHP extension ZIP.
- Trình duyệt Chrome hoặc Microsoft Edge.

Không cần cài Composer.

## 7. Hướng dẫn cài đặt

### Bước 1: Tải mã nguồn

Repository GitHub:

https://github.com/ngocbinh-04/ClubManager

Có thể tải ZIP hoặc sử dụng Git:

git clone https://github.com/ngocbinh-04/ClubManager.git

### Bước 2: Đặt thư mục dự án

Đặt dự án tại:

C:\xampp\htdocs\ClubManager

### Bước 3: Khởi động XAMPP

Mở XAMPP Control Panel.

Khởi động:

- Apache
- MySQL

### Bước 4: Tạo database

Truy cập:

http://localhost/phpmyadmin/

Mở tab SQL và chạy nội dung file:

database/schema.sql

File schema.sql sẽ tạo cơ sở dữ liệu clubmanager và 6 bảng cần thiết.

### Bước 5: Nhập dữ liệu mẫu

Chọn cơ sở dữ liệu clubmanager vừa tạo.

Vào tab Import (Nhập).

Chọn file:

database/seed.sql

Nhấn Go / Thực hiện.

Lưu ý: Chỉ nhập seed.sql vào database mới và chưa có dữ liệu, không nhập lại vào cơ sở dữ liệu đang sử dụng.

### Bước 6: Kiểm tra kết nối

Mở file:

config/database.php

Cấu hình mặc định:

Host: 127.0.0.1
Database: clubmanager
Username: root
Password: để trống

Nếu cấu hình MySQL khác với XAMPP mặc định, cần điều chỉnh thông tin kết nối.

### Bước 7: Chạy website

Mở trình duyệt và truy cập:

http://localhost/ClubManager/

Nếu chưa đăng nhập, hệ thống sẽ chuyển đến trang đăng nhập.

## 8. Tài khoản kiểm thử

Dữ liệu tài khoản mẫu được khai báo trong database/seed.sql.

| Tên đăng nhập | Vai trò |
|---|---|
| admin_demo | Quản lý CLB Công nghệ |
| lan_demo | Quản lý CLB Tiếng Anh |
| bao_demo | Thành viên hai CLB |
| ha_demo | Thành viên CLB Tiếng Anh |

Mật khẩu dự kiến cho các tài khoản:

Club@12345

Lưu ý: Cần xác nhận mật khẩu đăng nhập thành công sau khi import seed.sql. Nếu không khớp, cần tạo lại hash bằng PHP password_hash() và cập nhật dữ liệu mẫu.

Các tài khoản chỉ sử dụng cho môi trường thử nghiệm.

## 9. Dữ liệu mẫu

Dữ liệu mẫu bao gồm:

- 4 người dùng.
- 2 câu lạc bộ.
- 6 bản ghi tham gia câu lạc bộ.
- 3 sự kiện.
- 6 bản ghi điểm danh.
- 4 giao dịch thu–chi.

### CLB Công nghệ

Tổng thu: 1.000.000đ

Tổng chi: 250.000đ

Số dư: 750.000đ

### CLB Tiếng Anh

Tổng thu: 800.000đ

Tổng chi: 150.000đ

Số dư: 650.000đ

## 10. Cấu trúc thư mục

```text
ClubManager/
│
├── config/
│   └── database.php
│
├── css/
│   └── style.css
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
├── includes/
│   ├── auth.php
│   ├── quan-ly-auth.php
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   ├── form-thu-chi.php
│   └── kiem-tra-thu-chi.php
│
├── js/
│   └── app.js
│
├── index.php
├── dang-nhap.php
├── dang-ky.php
├── dang-xuat.php
│
├── clb-quan-tri.php
├── chi-tiet-clb.php
├── them-clb.php
├── sua-clb.php
├── xoa-clb.php
│
├── thanh-vien.php
├── them-thanh-vien.php
├── sua-thanh-vien.php
├── xoa-thanh-vien.php
│
├── su-kien.php
├── them-su-kien.php
├── sua-su-kien.php
├── xoa-su-kien.php
│
├── diem-danh.php
├── luu-diem-danh.php
├── diem-danh-cua-toi.php
│
├── thu-chi.php
├── them-thu-chi.php
├── sua-thu-chi.php
├── xoa-thu-chi.php
│
├── thong-ke.php
├── xuat-bao-cao.php
│
└── README.md
```

## 11. Các biện pháp bảo mật

Website áp dụng:

- PDO Prepared Statements để hạn chế SQL Injection.
- password_hash() và password_verify() để bảo vệ mật khẩu.
- PHP Session để xác thực người dùng.
- session_regenerate_id() khi đăng nhập.
- Kiểm tra quyền truy cập trên máy chủ.
- CSRF Token tại các biểu mẫu có thay đổi dữ liệu.
- htmlspecialchars() để hạn chế XSS khi xuất nội dung HTML.
- Kiểm tra dữ liệu nhập bằng PHP.
- Sử dụng transaction ở các thao tác cần bảo đảm tính toàn vẹn dữ liệu.
- Kiểm tra quyền sở hữu trước khi cập nhật hoặc xóa dữ liệu.

## 12. Kiểm thử hệ thống

Hệ thống đã được rà soát theo các nhóm:

### Kiểm thử tài khoản

- Đăng ký tài khoản.
- Đăng nhập đúng và sai mật khẩu.
- Đăng xuất.
- Kiểm tra tài khoản bị khóa.

### Kiểm thử phân quyền

- Thành viên không được vào trang quản lý.
- Người quản lý chỉ được quản lý CLB của mình.
- Thành viên chỉ xem kết quả điểm danh cá nhân.

### Kiểm thử chức năng

- Tạo và sửa câu lạc bộ.
- Quản lý thành viên.
- Tạo và sửa sự kiện.
- Ghi nhận điểm danh.
- Quản lý thu–chi.
- Tính số dư.
- Xuất báo cáo Excel.

### Kiểm thử cơ sở dữ liệu

- Kiểm tra dữ liệu lưu trong MySQL.
- Kiểm tra liên kết giữa các bảng.
- Kiểm tra tổng thu, tổng chi và số dư.
- Kiểm tra kết quả điểm danh.
- Import dữ liệu mẫu trên database thử nghiệm.

## 13. Một số lưu ý

- Không import dữ liệu mẫu vào database có dữ liệu thật.
- Không công khai mật khẩu MySQL trên môi trường triển khai.
- Sao lưu dữ liệu trước khi xóa câu lạc bộ.
- Khi xóa CLB, các dữ liệu liên quan có thể bị ảnh hưởng theo ràng buộc khóa ngoại.
- Không sử dụng tài khoản kiểm thử trên môi trường thực tế.
- Đảm bảo extension ZIP đã được bật để xuất Excel.

## 14. Kết luận

ClubManager là một hệ thống website quản lý nhiều câu lạc bộ bằng PHP và MySQL.

Website hỗ trợ quản lý tài khoản, câu lạc bộ, thành viên, sự kiện, điểm danh, tài chính và thống kê báo cáo.

Hệ thống có phân quyền giữa người quản lý và thành viên, giúp đảm bảo người dùng chỉ thực hiện các thao tác phù hợp với quyền của mình.
