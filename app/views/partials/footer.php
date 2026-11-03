<footer class="wl-footer mt-4">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5">
        <div class="wl-brand text-white mb-2">Work<span>Lance</span></div>
        <p class="small">Nền tảng kết nối khách hàng với người làm tự do. Đồ án môn CT428.</p>
      </div>
      <div class="col-6 col-md-3">
        <h6>Khám phá</h6>
        <ul class="list-unstyled small">
          <li><a href="<?= url('/projects') ?>">Danh sách dự án</a></li>
          <li><a href="<?= url('/') ?>#how">Cách hoạt động</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-4">
        <h6>Tài khoản</h6>
        <ul class="list-unstyled small">
          <?php if (current_user()): ?>
            <li><a href="<?= url('/dashboard') ?>">Bảng điều khiển</a></li>
            <li><a href="<?= url('/profile') ?>">Hồ sơ cá nhân</a></li>
          <?php else: ?>
            <li><a href="<?= url('/login') ?>">Đăng nhập</a></li>
            <li><a href="<?= url('/register') ?>">Đăng ký</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <hr class="border-secondary">
    <div class="small text-center">© <?= date('Y') ?> WorkLance – CT428 Freelance Platform</div>
  </div>
</footer>

<!-- Vùng hiển thị Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3 wl-toasts" id="toast-container" aria-live="polite" aria-atomic="true"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
