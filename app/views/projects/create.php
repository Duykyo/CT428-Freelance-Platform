<?php require __DIR__ . '/../partials/header.php'; ?>
<main class="container py-5">
  <h3 class="mb-4">Đăng dự án mới</h3>
  <form action="<?= url('project/store') ?>" method="post" data-ajax novalidate class="bg-white p-4 rounded-4 shadow-sm">
    <input type="hidden" name="csrf" value="<?= Csrf::token() ?>">
    <div class="form-alert d-none" role="alert"></div>
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="mb-3">
          <label class="form-label" for="tieude">Tiêu đề dự án</label>
          <input type="text" class="form-control" id="tieude" name="tieude" maxlength="150" required>
        </div>
        <div>
          <label class="form-label" for="mota">Mô tả chi tiết</label>
          <textarea class="form-control" id="mota" name="mota" rows="9" required
            placeholder="Mô tả yêu cầu, phạm vi công việc, sản phẩm bàn giao..."></textarea>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="mb-3">
          <label class="form-label" for="ngansach">Ngân sách (VNĐ)</label>
          <input type="number" class="form-control" id="ngansach" name="ngansach" min="1" step="1" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="han">Hạn nhận đề xuất</label>
          <input type="date" class="form-control" id="han" name="han" min="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="kynang">Kỹ năng yêu cầu</label>
          <input type="text" class="form-control" id="kynang" name="kynang" maxlength="500" placeholder="PHP, MySQL, Bootstrap">
        </div>
        <button type="submit" class="btn btn-brand w-100">Đăng dự án</button>
      </div>
    </div>
  </form>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
