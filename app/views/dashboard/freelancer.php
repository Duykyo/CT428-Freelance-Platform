<?php require APP_ROOT . '/app/views/partials/header.php'; ?>

<main class="py-4">
  <div class="container">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1">Xin chào, <?= e($user['hoten']) ?> 👋</h2>
        <p class="wl-muted mb-0">Theo dõi các đề xuất bạn đã gửi và tìm thêm dự án mới.</p>
      </div>
      <a href="<?= url('/projects') ?>" class="btn btn-primary"><i class="bi bi-search"></i> Tìm dự án</a>
    </div>

    <!-- THỐNG KÊ -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="wl-stat">
          <span class="wl-stat-icon bg-primary-subtle text-primary"><i class="bi bi-send"></i></span>
          <div><div class="wl-stat-num"><?= (int) $total ?></div><div class="wl-stat-label">Đề xuất đã gửi</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="wl-stat">
          <span class="wl-stat-icon bg-warning-subtle text-warning-emphasis"><i class="bi bi-hourglass-split"></i></span>
          <div><div class="wl-stat-num"><?= (int) ($byStatus['cho_duyet'] ?? 0) ?></div><div class="wl-stat-label">Chờ duyệt</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="wl-stat">
          <span class="wl-stat-icon bg-success-subtle text-success"><i class="bi bi-trophy"></i></span>
          <div><div class="wl-stat-num"><?= (int) ($byStatus['duoc_chon'] ?? 0) ?></div><div class="wl-stat-label">Được chọn</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="wl-stat">
          <span class="wl-stat-icon bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i></span>
          <div><div class="wl-stat-num"><?= (int) ($byStatus['tu_choi'] ?? 0) ?></div><div class="wl-stat-label">Bị từ chối</div></div>
        </div>
      </div>
    </div>

    <!-- ĐỀ XUẤT CỦA TÔI -->
    <div class="wl-panel">
      <div class="wl-panel-head">
        <h5 class="fw-bold mb-0">Đề xuất gần đây</h5>
      </div>
      <?php if (!$proposals): ?>
        <div class="wl-empty">
          <i class="bi bi-send"></i>
          <p>Bạn chưa gửi đề xuất nào.</p>
          <a href="<?= url('/projects') ?>" class="btn btn-primary btn-sm">Xem danh sách dự án</a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle mb-0 wl-table">
            <thead>
              <tr>
                <th>Dự án</th>
                <th class="text-end d-none d-md-table-cell">Ngân sách</th>
                <th class="text-end">Chi phí đề xuất</th>
                <th class="text-center d-none d-sm-table-cell">Thời gian</th>
                <th>Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($proposals as $d): [$label, $color] = proposal_status($d['TrangThai']); ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?= e($d['TieuDe']) ?></div>
                    <small class="wl-muted"><?= e($d['TenKhachHang']) ?> · gửi <?= e(time_ago($d['NgayTao'])) ?></small>
                    <div class="d-sm-none small mt-1">Thực hiện trong <?= (int) $d['ThoiGianThucHien'] ?> ngày</div>
                  </td>
                  <td class="text-end text-nowrap d-none d-md-table-cell"><?= money($d['NganSach']) ?></td>
                  <td class="text-end text-nowrap fw-semibold"><?= money($d['ChiPhiDeXuat']) ?></td>
                  <td class="text-center text-nowrap d-none d-sm-table-cell"><?= (int) $d['ThoiGianThucHien'] ?> ngày</td>
                  <td><span class="badge text-bg-<?= $color ?>"><?= e($label) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
