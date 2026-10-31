<?php
require APP_ROOT . '/app/views/partials/header.php';
$isFreelancer = $me['VaiTro'] === 'freelancer';
$initial = mb_strtoupper(mb_substr($me['HoTen'], 0, 1, 'UTF-8'), 'UTF-8');
?>

<main class="py-4">
  <div class="container" style="max-width: 980px;">

    <h2 class="fw-bold mb-4">Hồ sơ cá nhân</h2>

    <div class="row g-4">
      <!-- THẺ TÓM TẮT -->
      <div class="col-lg-4">
        <div class="wl-panel text-center p-4">
          <span class="wl-avatar wl-avatar-lg mx-auto mb-3" data-user-initial><?= e($initial) ?></span>
          <h5 class="fw-bold mb-1" data-user-name><?= e($me['HoTen']) ?></h5>
          <div class="wl-muted small mb-2"><?= e($me['Email']) ?></div>
          <span class="badge text-bg-primary-subtle text-primary border"><?= $isFreelancer ? 'Người làm tự do' : 'Khách hàng' ?></span>
          <hr>
          <div class="small wl-muted">Tham gia từ <?= date_vn($me['NgayTao']) ?></div>
        </div>
      </div>

      <div class="col-lg-8 d-flex flex-column gap-4">
        <!-- THÔNG TIN CÁ NHÂN -->
        <div class="wl-panel">
          <div class="wl-panel-head"><h5 class="fw-bold mb-0">Thông tin cá nhân</h5></div>
          <form id="profile-form" class="p-3 p-md-4" action="<?= url('/profile') ?>" method="post" data-ajax novalidate>
            <div data-alert></div>
            <?= Csrf::field() ?>
            <div class="mb-3">
              <label for="hoten" class="form-label fw-semibold">Họ tên</label>
              <input type="text" id="hoten" name="hoten" class="form-control" maxlength="100" value="<?= e($me['HoTen']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label fw-semibold">Email</label>
              <input type="email" id="email" class="form-control" value="<?= e($me['Email']) ?>" disabled>
              <div class="form-text">Email dùng để đăng nhập nên không thể thay đổi.</div>
            </div>
            <?php if ($isFreelancer): ?>
              <div class="mb-3">
                <label for="kynang" class="form-label fw-semibold">Kỹ năng</label>
                <input type="text" id="kynang" name="kynang" class="form-control" maxlength="500"
                       placeholder="VD: HTML, CSS, PHP, Thiết kế đồ họa" value="<?= e($me['KyNang'] ?? '') ?>">
                <div class="form-text">Ngăn cách các kỹ năng bằng dấu phẩy.</div>
              </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Lưu thay đổi</button>
          </form>
        </div>

        <!-- ĐỔI MẬT KHẨU -->
        <div class="wl-panel">
          <div class="wl-panel-head"><h5 class="fw-bold mb-0">Đổi mật khẩu</h5></div>
          <form id="password-form" class="p-3 p-md-4" action="<?= url('/profile/password') ?>" method="post" data-ajax data-reset novalidate>
            <div data-alert></div>
            <?= Csrf::field() ?>
            <div class="mb-3">
              <label for="matkhau_cu" class="form-label fw-semibold">Mật khẩu hiện tại</label>
              <input type="password" id="matkhau_cu" name="matkhau_cu" class="form-control" autocomplete="current-password" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <label for="matkhau_moi" class="form-label fw-semibold">Mật khẩu mới</label>
                <input type="password" id="matkhau_moi" name="matkhau_moi" class="form-control" minlength="8" placeholder="Tối thiểu 8 ký tự" autocomplete="new-password" required>
              </div>
              <div class="col-sm-6">
                <label for="nhaplai" class="form-label fw-semibold">Nhập lại mật khẩu mới</label>
                <input type="password" id="nhaplai" name="nhaplai" class="form-control" autocomplete="new-password" required>
              </div>
            </div>
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-shield-lock"></i> Đổi mật khẩu</button>
          </form>
        </div>
      </div>
    </div>

  </div>
</main>

<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
