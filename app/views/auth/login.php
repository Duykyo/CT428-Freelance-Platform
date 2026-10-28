<?php require __DIR__ . '/../partials/header.php'; ?>
<main class="container py-5">
  <div class="row auth-card shadow-sm mx-auto overflow-hidden">
    <div class="col-md-5 auth-banner d-none d-md-flex">
      <div>
        <h2 class="fw-bold">Chào mừng trở lại</h2>
        <p class="mb-0">Kết nối khách hàng và freelancer chỉ trong vài bước.</p>
      </div>
    </div>
    <div class="col-md-7 p-4 p-lg-5 bg-white">
      <h3 class="mb-4">Đăng nhập</h3>
      <form action="<?= url('auth/doLogin') ?>" method="post" data-ajax novalidate>
        <input type="hidden" name="csrf" value="<?= Csrf::token() ?>">
        <div class="form-alert d-none" role="alert"></div>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input type="email" class="form-control" id="email" name="email" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label" for="matkhau">Mật khẩu</label>
          <input type="password" class="form-control" id="matkhau" name="matkhau" required>
        </div>
        <button type="submit" class="btn btn-brand w-100">Đăng nhập</button>
        <p class="text-center mt-3 mb-0 small">Chưa có tài khoản? <a href="<?= url('auth/register') ?>">Đăng ký ngay</a></p>
      </form>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
