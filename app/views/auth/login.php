<?php require APP_ROOT . '/app/views/partials/header.php'; ?>

<main class="wl-auth-wrap">
  <div class="container">
    <div class="wl-auth-card mx-auto" style="max-width: 920px;">
      <div class="row g-0">
        <div class="col-md-5 d-none d-md-block">
          <div class="wl-auth-side d-flex flex-column justify-content-center">
            <h3 class="fw-bold mb-3">Chào mừng trở lại!</h3>
            <p class="mb-4" style="opacity:.9">Đăng nhập để quản lý dự án, theo dõi đề xuất và tìm công việc mới.</p>
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i>Đăng và quản lý dự án</li>
              <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i>Gửi đề xuất nhanh chóng</li>
              <li><i class="bi bi-check-circle-fill me-2"></i>Theo dõi trạng thái công việc</li>
            </ul>
          </div>
        </div>

        <div class="col-md-7">
          <div class="p-4 p-md-5">
            <h3 class="fw-bold mb-1">Đăng nhập</h3>
            <p class="wl-muted mb-4">Nhập thông tin tài khoản của bạn</p>

            <form action="<?= url('/login') ?>" method="post">
              <?= Csrf::field() ?>
              <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                  <input type="email" id="email" name="email" class="form-control" placeholder="ban@example.com" value="<?= old('email') ?>" required autofocus>
                </div>
              </div>

              <div class="mb-4">
                <label for="matkhau" class="form-label fw-semibold">Mật khẩu</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-lock"></i></span>
                  <input type="password" id="matkhau" name="matkhau" class="form-control" placeholder="••••••••" required>
                </div>
              </div>

              <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg">Đăng nhập</button>
              </div>
            </form>

            <p class="text-center mb-0 wl-muted">Chưa có tài khoản? <a href="<?= url('/register') ?>" class="fw-semibold">Đăng ký ngay</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
