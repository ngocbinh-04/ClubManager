document.addEventListener("DOMContentLoaded", function () {

    // Xác nhận xóa cho các nút/link có class btn-danger
    const deleteButtons = document.querySelectorAll(".btn-danger");

    deleteButtons.forEach(function (button) {
        button.addEventListener("click", function (event) {

            const ok = confirm("Bạn có chắc muốn xóa dữ liệu này không?");

            if (!ok) {
                event.preventDefault();
            }

        });
    });


    // Tìm kiếm bảng nếu trang có ô search
    const searchInput = document.querySelector(
        '.filter-bar input[type="search"]'
    );

    if (searchInput) {

        searchInput.addEventListener("input", function () {

            const keyword = this.value
                .toLowerCase()
                .trim();

            const rows = document.querySelectorAll(
                "table tbody tr"
            );

            rows.forEach(function (row) {

                const content = row.textContent
                    .toLowerCase();

                row.style.display =
                    content.includes(keyword)
                        ? ""
                        : "none";

            });

        });

    }

});