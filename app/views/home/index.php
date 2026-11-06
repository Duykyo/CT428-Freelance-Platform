<?php require APP_ROOT . '/app/views/partials/header.php'; ?>

<!-- HERO -->
<header class="wl-hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <span class="badge text-bg-primary-subtle text-primary mb-3 px-3 py-2">Kết nối khách hàng &amp; người làm tự do</span>
        <h1 class="display-5 mb-3">Đăng dự án, nhận đề xuất,<br>chọn <em>người phù hợp nhất</em>.</h1>
        <p class="lead wl-muted mb-4">Khách hàng đăng dự án với ngân sách và thời hạn rõ ràng. Người làm tự do tìm dự án phù hợp kỹ năng và gửi đề xuất chỉ trong vài phút.</p>

        <form class="wl-search row g-2 align-items-center" action="<?= url('/projects') ?>" method="get">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
              <input type="text" name="q" class="form-control" placeholder="Tìm dự án theo từ khóa...">
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" name="budget" aria-label="Ngân sách" id="budget-select">
              <option value="">Mọi ngân sách</option>
              <option value="0-5000000">Dưới 5 triệu</option>
              <option value="5000000-20000000">5 – 20 triệu</option>
              <option value="20000000-">Trên 20 triệu</option>
            </select>
          </div>
          <div class="col-md-3 d-grid"><button class="btn btn-primary py-2">Tìm dự án</button></div>
        </form>

        <div class="mt-4 d-flex flex-wrap gap-3">
          <a href="<?= url('/register?role=khachhang') ?>" class="btn btn-accent btn-lg">Tôi cần thuê người</a>
          <a href="<?= url('/register?role=freelancer') ?>" class="btn btn-outline-primary btn-lg">Tôi muốn nhận việc</a>
        </div>
      </div>

      <div class="col-lg-5 d-none d-lg-block">
        <div class="wl-hero-card">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="fw-bold mb-0">Thiết kế website bán hàng</h5>
            <span class="badge text-bg-success">Đang mở</span>
          </div>
          <p class="wl-muted small">Cần thiết kế và lập trình website bán hàng responsive, tích hợp giỏ hàng và thanh toán.</p>
          <div class="d-flex justify-content-between small mb-3">
            <span><i class="bi bi-cash-coin text-primary"></i> <strong>15.000.000đ</strong></span>
            <span><i class="bi bi-calendar-event text-primary"></i> Hạn: 15/11/2026</span>
          </div>
          <hr>
          <div class="small fw-semibold mb-2">3 đề xuất đã nhận</div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="wl-avatar">A</span>
            <div class="flex-grow-1 small"><strong>Nguyễn Văn A</strong><div class="wl-muted">12 triệu · 20 ngày</div></div>
            <span class="badge text-bg-warning">Chờ duyệt</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="wl-avatar" style="background:#14b8a6">B</span>
            <div class="flex-grow-1 small"><strong>Trần Thị B</strong><div class="wl-muted">14 triệu · 15 ngày</div></div>
            <span class="badge text-bg-warning">Chờ duyệt</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- HOW IT WORKS -->
<section class="wl-section" id="how">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="wl-section-title">Cách hoạt động</h2>
      <p class="wl-muted">Quy trình đơn giản cho cả hai phía</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="wl-filter h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-briefcase text-primary"></i> Dành cho Khách hàng</h5>
          <div class="d-flex gap-3 mb-3"><span class="wl-step-num flex-shrink-0">1</span><div><strong>Đăng dự án</strong><div class="wl-muted small">Nhập tiêu đề, mô tả, ngân sách và hạn nhận đề xuất.</div></div></div>
          <div class="d-flex gap-3 mb-3"><span class="wl-step-num flex-shrink-0">2</span><div><strong>Xem các đề xuất</strong><div class="wl-muted small">So sánh chi phí, thời gian thực hiện và nội dung của từng người.</div></div></div>
          <div class="d-flex gap-3"><span class="wl-step-num flex-shrink-0">3</span><div><strong>Chọn đề xuất &amp; theo dõi</strong><div class="wl-muted small">Chọn người phù hợp và cập nhật trạng thái dự án.</div></div></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="wl-filter h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-laptop text-primary"></i> Dành cho Người làm tự do</h5>
          <div class="d-flex gap-3 mb-3"><span class="wl-step-num flex-shrink-0" style="background:#14b8a6">1</span><div><strong>Hoàn thiện hồ sơ</strong><div class="wl-muted small">Cập nhật họ tên, email và các kỹ năng của bạn.</div></div></div>
          <div class="d-flex gap-3 mb-3"><span class="wl-step-num flex-shrink-0" style="background:#14b8a6">2</span><div><strong>Tìm dự án</strong><div class="wl-muted small">Tìm kiếm và lọc dự án theo từ khóa, ngân sách, hạn nhận đề xuất.</div></div></div>
          <div class="d-flex gap-3"><span class="wl-step-num flex-shrink-0" style="background:#14b8a6">3</span><div><strong>Gửi đề xuất</strong><div class="wl-muted small">Đưa ra chi phí, thời gian thực hiện và nội dung đề xuất.</div></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- LATEST PROJECTS (từ CSDL) -->
<section class="wl-section bg-white border-top border-bottom">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="wl-section-title mb-1">Dự án mới đăng</h2>
        <p class="wl-muted mb-0">Các dự án đang nhận đề xuất</p>
      </div>
      <a href="<?= url('/projects') ?>" class="btn btn-outline-primary">Xem tất cả <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if (!$latest): ?>
      <div class="text-center wl-muted py-4">Chưa có dự án nào đang mở.</div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($latest as $p): ?>
          <div class="col-md-6 col-lg-4">
            <div class="wl-project h-100">
              <div class="d-flex justify-content-between mb-2">
                <span class="badge text-bg-success">Đang mở</span>
                <small class="wl-muted"><?= e(time_ago($p['NgayTao'])) ?></small>
              </div>
              <h5 class="fw-bold"><a href="<?= url('/projects') ?>"><?= e($p['TieuDe']) ?></a></h5>
              <p class="wl-muted small"><?= e(mb_strimwidth($p['MoTa'], 0, 110, '…', 'UTF-8')) ?></p>
              <div class="d-flex justify-content-between align-items-center">
                <span class="wl-budget"><?= money($p['NganSach']) ?></span>
                <small class="wl-muted"><i class="bi bi-calendar-event"></i> <?= date_vn($p['HanNhanDeXuat']) ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- CTA -->
<?php if (!current_user()): ?>
<section class="wl-section">
  <div class="container">
    <div class="wl-cta text-center">
      <h2 class="fw-bold mb-2">Sẵn sàng bắt đầu?</h2>
      <p class="mb-4" style="color:#cbd5e1">Tạo tài khoản miễn phí và đăng dự án hoặc gửi đề xuất ngay hôm nay.</p>
      <a href="<?= url('/register') ?>" class="btn btn-accent btn-lg px-5">Đăng ký ngay</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
