
/*
 * CLUBMANAGER - JAVASCRIPT GIAO DIỆN
 *
 * Chỉ xử lý các chức năng hỗ trợ trên trình duyệt.
 * Kiểm tra dữ liệu, CSRF và phân quyền do PHP thực hiện.
 */

document.addEventListener("DOMContentLoaded", function () {

    // Tìm kiếm nhanh trên bảng nếu trang có ô tìm kiếm.
    const searchInput = document.querySelector(
        '.filter-bar input[type="search"]'
    );

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener("input", function () {

        const keyword = this.value
            .toLocaleLowerCase("vi")
            .trim();

        const rows = document.querySelectorAll(
            "table tbody tr"
        );

        rows.forEach(function (row) {

            const content = row.textContent
                .toLocaleLowerCase("vi");

            row.style.display = content.includes(keyword)
                ? ""
                : "none";
        });
    });
});
