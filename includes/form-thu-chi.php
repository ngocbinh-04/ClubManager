<?php
/* Sử dụng từ them-thu-chi.php hoặc sua-thu-chi.php */
?>

<div class="card">
    <form method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= hienThiHTML($_SESSION['csrf_token']) ?>"
        >

        <div class="form-group">
            <label for="loai">Loại giao dịch</label>
            <select name="loai" id="loai" required>
                <option value="thu"
                    <?= $data['loai'] === 'thu' ? 'selected' : '' ?>>
                    Thu
                </option>
                <option value="chi"
                    <?= $data['loai'] === 'chi' ? 'selected' : '' ?>>
                    Chi
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="noi_dung">Nội dung *</label>
            <input
                type="text"
                name="noi_dung"
                id="noi_dung"
                maxlength="255"
                value="<?= hienThiHTML($data['noi_dung']) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="so_tien">Số tiền (VNĐ) *</label>
            <input
                type="number"
                name="so_tien"
                id="so_tien"
                min="1"
                step="1"
                value="<?= hienThiHTML($data['so_tien']) ?>"
                placeholder="Ví dụ: 1000000"
                required
            >
        </div>

        <div class="form-group">
            <label for="ngay_giao_dich">Ngày giao dịch *</label>
            <input
                type="date"
                name="ngay_giao_dich"
                id="ngay_giao_dich"
                value="<?= hienThiHTML($data['ngay_giao_dich']) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="ghi_chu">Ghi chú</label>
            <textarea
                name="ghi_chu"
                id="ghi_chu"
                rows="4"
            ><?= hienThiHTML($data['ghi_chu']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            <?= hienThiHTML($nutLuu) ?>
        </button>

        <a
            href="thu-chi.php?clb_id=<?= $clbId ?>"
            class="btn"
        >
            Hủy
        </a>
    </form>
</div>
