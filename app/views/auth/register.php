<?php
require APP_ROOT . '/app/views/partials/header.php';
// Vai trò được chọn: dữ liệu cũ (khi form lỗi) > ?role= > mặc định khách hàng
$role = $_SESSION['old']['vaitro'] ?? ($preset ?: 'khachhang');
?>

<main class="wl-auth-wrap">
  <div class="container">
    <div class="wl-auth-card mx-auto" style="max-width: 920px;">
      <div class="row g-0">
        <div class="col-md-5 d-none d-md-block">
          <div class="wl-auth-side d-flex flex-column justify-content-center">
            <h3 class="fw-bold mb-3">Tạo tài khoản miễn phí</h3>
            <p class="mb-4" style="opacity:.9">Chọn vai trò phù hợp để bắt đầu: đăng dự án hoặc nhận việc.</p>
            <div class="mb-3"><i class="bi bi-briefcase me-2"></i><strong>Khách hàng</strong><div class="small" style="opacity:.85">Đăng, sửa, xóa và theo dõi dự án; chọn đề xuất phù hợp.</div></div>
            <div><i class="bi bi-laptop me-2"></i><strong>Người làm tự do</strong><div class="small" style="opacity:.85">Tìm dự án và gửi đề xuất thực hiện.</div></div>
          </div>
        </div>

        <div class="col-md-7">
          <div class="p-4 p-md-5">
            <h3 class="fw-bold mb-1">Đăng ký</h3>
            <p class="wl-muted mb-4">Điền thông tin để tạo tài khoản</p>

            <form action="<?= url('/register') ?>" method="post">
              <?= Csrf::field() ?>
              <div class="mb-3">
                <label class="form-label fw-semibold">Bạn là</label>
                <div class="row g-2">
                  <div class="col-6 wl-role-option">
                    <input type="radio" name="vaitro" id="role-kh" value="khachhang" <?= $role === 'khachhang' ? 'checked' : '' ?>>
                    <label for="role-kh"><i class="bi bi-briefcase d-block fs-4"></i>Khách hàng</label>
                  </div>
                  <div class="col-6 wl-role-option">
                    <input type="radio" name="vaitro" id="role-fl" value="freelancer" <?= $role === 'freelancer' ? 'checked' : '' ?>>
                    <label for="role-fl"><i class="bi bi-laptop d-block fs-4"></i>Người làm tự do</label>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="hoten" class="form-label fw-semibold">Họ tên</label>
                <input type="text" id="hoten" name="hoten" class="form-control" placeholder="Nguyễn Văn A" maxlength="100" value="<?= old('hoten') ?>" required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="ban@example.com" maxlength="150" value="<?= old('email') ?>" required>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <label for="matkhau" class="form-label fw-semibold">Mật khẩu</label>
                  <input type="password" id="matkhau" name="matkhau" class="form-control" placeholder="Tối thiểu 8 ký tự" minlength="8" required>
                </div>
                <div class="col-sm-6">
                  <label for="nhaplai" class="form-label fw-semibold">Nhập lại mật khẩu</label>
                  <input type="password" id="nhaplai" name="nhaplai" class="form-control" placeholder="Nhập lại mật khẩu" required>
                </div>
              </div>

              <div class="mb-3 <?= $role === 'freelancer' ? '' : 'd-none' ?>" id="skills-group">
                <label for="kynang" class="form-label fw-semibold">Kỹ năng</label>
                <input type="text" id="kynang" name="kynang" class="form-control" maxlength="500" placeholder="VD: HTML, CSS, PHP, Thiết kế đồ họa" value="<?= old('kynang') ?>">
                <div class="form-text">Ngăn cách các kỹ năng bằng dấu phẩy.</div>
              </div>

              <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" required>
                <label class="form-check-label small" for="agree">Tôi đồng ý với <a href="#">điều khoản sử dụng</a></label>
              </div>

              <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg">Tạo tài khoản</button>
              </div>
            </form>

            <p class="text-center mb-0 wl-muted">Đã có tài khoản? <a href="<?= url('/login') ?>" class="fw-semibold">Đăng nhập</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
