/* =========================================================
   CLUBMANAGER - JAVASCRIPT
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    khoiTaoTimKiem();

    khoiTaoNutXoa();

    khoiTaoNutThem();

    khoiTaoNutSua();

    khoiTaoDiemDanh();

});


/* =========================================================
   1. TÌM KIẾM TRONG BẢNG / CARD
   ========================================================= */

function khoiTaoTimKiem() {

    const oTimKiem = document.querySelector(
        '.filter-bar input[type="search"]'
    );

    if (!oTimKiem) {
        return;
    }

    oTimKiem.addEventListener("input", function () {

        const tuKhoa = this.value
            .toLowerCase()
            .trim();

        timTrongBang(tuKhoa);

        timTrongClubCard(tuKhoa);

    });

}


/* =========================================================
   2. TÌM KIẾM TRONG BẢNG
   ========================================================= */

function timTrongBang(tuKhoa) {

    const cacDong = document.querySelectorAll(
        "table tbody tr"
    );

    cacDong.forEach(function (dong) {

        const noiDung = dong.textContent
            .toLowerCase();

        if (noiDung.includes(tuKhoa)) {

            dong.style.display = "";

        } else {

            dong.style.display = "none";

        }

    });

}


/* =========================================================
   3. TÌM KIẾM CÂU LẠC BỘ
   ========================================================= */

function timTrongClubCard(tuKhoa) {

    const cacClub = document.querySelectorAll(
        ".club-card"
    );

    cacClub.forEach(function (club) {

        const noiDung = club.textContent
            .toLowerCase();

        if (noiDung.includes(tuKhoa)) {

            club.style.display = "";

        } else {

            club.style.display = "none";

        }

    });

}


/* =========================================================
   4. XÁC NHẬN KHI XÓA
   ========================================================= */

function khoiTaoNutXoa() {

    const cacNutXoa = document.querySelectorAll(
        ".btn-danger"
    );

    cacNutXoa.forEach(function (nut) {

        if (
            !nut.textContent
                .toLowerCase()
                .includes("xóa")
        ) {
            return;
        }

        nut.addEventListener("click", function (event) {

            event.preventDefault();

            const dongY = confirm(
                "Bạn có chắc chắn muốn xóa dữ liệu này không?"
            );

            if (dongY) {

                const dong = nut.closest("tr");

                if (dong) {

                    dong.remove();

                    alert(
                        "Đã xóa dữ liệu khỏi giao diện."
                    );

                }

            }

        });

    });

}


/* =========================================================
   5. NÚT THÊM
   ========================================================= */

function khoiTaoNutThem() {

    const cacNut = document.querySelectorAll(
        "button"
    );

    cacNut.forEach(function (nut) {

        const noiDung = nut.textContent
            .toLowerCase()
            .trim();

        if (
            noiDung.includes("thêm thành viên") ||
            noiDung.includes("thêm sự kiện") ||
            noiDung.includes("thêm câu lạc bộ") ||
            noiDung.includes("thêm khoản thu") ||
            noiDung.includes("thêm khoản chi")
        ) {

            nut.addEventListener(
                "click",
                function () {

                    alert(
                        "Chức năng thêm sẽ được hoàn thiện ở phần PHP + MySQL."
                    );

                }
            );

        }

    });

}


/* =========================================================
   6. NÚT SỬA
   ========================================================= */

function khoiTaoNutSua() {

    const cacNut = document.querySelectorAll(
        "button"
    );

    cacNut.forEach(function (nut) {

        const noiDung = nut.textContent
            .toLowerCase()
            .trim();

        if (noiDung === "sửa") {

            nut.addEventListener(
                "click",
                function () {

                    alert(
                        "Chức năng sửa sẽ được xử lý bằng PHP ở bước sau."
                    );

                }
            );

        }

    });

}


/* =========================================================
   7. ĐIỂM DANH
   ========================================================= */

function khoiTaoDiemDanh() {

    const form = document.querySelector(
        "form"
    );

    const nutLuu = Array.from(
        document.querySelectorAll("button")
    ).find(function (nut) {

        return nut.textContent
            .toLowerCase()
            .includes("lưu điểm danh");

    });

    if (!form || !nutLuu) {
        return;
    }

    form.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();

            alert(
                "Đã lưu điểm danh trên giao diện."
            );

        }
    );

}