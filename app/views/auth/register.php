<?php require __DIR__ . '/../partials/header.php'; ?>
<main class="container py-5">
  <div class="auth-card shadow-sm mx-auto bg-white p-4 p-lg-5" style="max-width:620px">
    <h3 class="mb-4">Tạo tài khoản</h3>
    <form action="<?= url('auth/doRegister') ?>" method="post" data-ajax novalidate>
      <input type="hidden" name="csrf" value="<?= Csrf::token() ?>">
      <div class="form-alert d-none" role="alert"></div>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <input type="radio" class="btn-check" name="vaitro" id="vt1" value="khachhang" required>
          <label class="role-card" for="vt1"><strong>Tôi cần thuê người</strong><small>Đăng dự án, chọn đề xuất</small></label>
        </div>
        <div class="col-6">
          <input type="radio" class="btn-check" name="vaitro" id="vt2" value="freelancer">
          <label class="role-card" for="vt2"><strong>Tôi là freelancer</strong><small>Tìm dự án, gửi đề xuất</small></label>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="hoten">Họ tên</label>
        <input type="text" class="form-control" id="hoten" name="hoten" maxlength="100" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="matkhau">Mật khẩu (từ 8 ký tự)</label>
          <input type="password" class="form-control" id="matkhau" name="matkhau" minlength="8" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="xacnhan">Xác nhận mật khẩu</label>
          <input type="password" class="form-control" id="xacnhan" name="xacnhan" required>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label" for="kynang">Kỹ năng <span class="text-muted small">(không bắt buộc, cách nhau dấu phẩy)</span></label>
        <input type="text" class="form-control" id="kynang" name="kynang" maxlength="500" placeholder="PHP, MySQL, Thiết kế UI">
      </div>
      <button type="submit" class="btn btn-brand w-100">Đăng ký</button>
      <p class="text-center mt-3 mb-0 small">Đã có tài khoản? <a href="<?= url('auth/login') ?>">Đăng nhập</a></p>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
