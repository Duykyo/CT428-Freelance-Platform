<?php require APP_ROOT . '/app/views/partials/header.php'; ?>

<main class="py-4">
  <div class="container" style="max-width: 980px;">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Bảng điều khiển</a></li>
        <li class="breadcrumb-item active" aria-current="page">Đăng dự án</li>
      </ol>
    </nav>
    <h2 class="fw-bold mb-4">Đăng dự án mới</h2>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="wl-panel">
          <form id="project-form" class="p-3 p-md-4" action="<?= url('/projects') ?>" method="post" data-ajax novalidate>
            <div data-alert></div>
            <?= Csrf::field() ?>

            <div class="mb-3">
              <label for="tieude" class="form-label fw-semibold">Tiêu đề dự án <span class="text-danger">*</span></label>
              <input type="text" id="tieude" name="tieude" class="form-control" maxlength="150" placeholder="VD: Thiết kế website bán hàng" required>
            </div>

            <div class="mb-3">
              <label for="mota" class="form-label fw-semibold">Mô tả chi tiết <span class="text-danger">*</span></label>
              <textarea id="mota" name="mota" class="form-control" rows="6" minlength="20" maxlength="5000"
                        placeholder="Mô tả yêu cầu, phạm vi công việc, sản phẩm bàn giao..." data-counter="#mota-count" required></textarea>
              <div class="form-text d-flex justify-content-between"><span>Tối thiểu 20 ký tự.</span><span id="mota-count"></span></div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <label for="ngansach" class="form-label fw-semibold">Ngân sách (VNĐ) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="text" inputmode="numeric" id="ngansach" name="ngansach" class="form-control" placeholder="15.000.000" data-money required>
                  <span class="input-group-text">đ</span>
                </div>
              </div>
              <div class="col-sm-6">
                <label for="hannhandexuat" class="form-label fw-semibold">Hạn nhận đề xuất <span class="text-danger">*</span></label>
                <input type="date" id="hannhandexuat" name="hannhandexuat" class="form-control" min="<?= date('Y-m-d') ?>" required>
              </div>
            </div>

            <div class="mb-4">
              <label for="kynang" class="form-label fw-semibold">Kỹ năng yêu cầu</label>
              <input type="text" id="kynang" name="kynang" class="form-control" maxlength="500" placeholder="VD: PHP, MySQL, Bootstrap">
              <div class="form-text">Không bắt buộc. Ngăn cách bằng dấu phẩy.</div>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Đăng dự án</button>
              <a href="<?= url('/dashboard') ?>" class="btn btn-light">Hủy</a>
            </div>
          </form>
        </div>
      </div>

      <aside class="col-lg-4">
        <div class="wl-panel p-3 p-md-4">
          <h6 class="fw-bold"><i class="bi bi-lightbulb text-warning"></i> Mẹo để nhận đề xuất tốt</h6>
          <ul class="small wl-muted ps-3 mb-0">
            <li class="mb-2">Viết tiêu đề ngắn gọn, nêu rõ loại công việc.</li>
            <li class="mb-2">Mô tả rõ yêu cầu, sản phẩm bàn giao và tiêu chí nghiệm thu.</li>
            <li class="mb-2">Đặt ngân sách thực tế để thu hút freelancer phù hợp.</li>
            <li>Hạn nhận đề xuất nên để từ 5–7 ngày.</li>
          </ul>
        </div>
      </aside>
    </div>

  </div>
</main>

<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
