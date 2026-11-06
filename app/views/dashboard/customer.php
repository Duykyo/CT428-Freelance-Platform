<?php require APP_ROOT . '/app/views/partials/header.php'; ?>

<main class="py-4">
  <div class="container">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1">Xin chào, <?= e($user['hoten']) ?> 👋</h2>
        <p class="wl-muted mb-0">Tổng quan các dự án bạn đã đăng và đề xuất nhận được.</p>
      </div>
      <a href="<?= url('/projects/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Đăng dự án mới</a>
    </div>

    <!-- THỐNG KÊ -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="wl-stat">
          <span class="wl-stat-icon bg-primary-subtle text-primary"><i class="bi bi-briefcase"></i></span>
          <div><div class="wl-stat-num"><?= (int) $totalProjects ?></div><div class="wl-stat-label">Dự án đã đăng</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="wl-stat">
          <span class="wl-stat-icon bg-success-subtle text-success"><i class="bi bi-unlock"></i></span>
          <div><div class="wl-stat-num"><?= (int) ($byStatus['mo'] ?? 0) ?></div><div class="wl-stat-label">Đang mở</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="wl-stat">
          <span class="wl-stat-icon bg-info-subtle text-info-emphasis"><i class="bi bi-inbox"></i></span>
          <div><div class="wl-stat-num"><?= (int) $totalProposals ?></div><div class="wl-stat-label">Đề xuất nhận được</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="wl-stat">
          <span class="wl-stat-icon bg-warning-subtle text-warning-emphasis"><i class="bi bi-hourglass-split"></i></span>
          <div><div class="wl-stat-num"><?= (int) $pending ?></div><div class="wl-stat-label">Chờ bạn duyệt</div></div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- DỰ ÁN CỦA TÔI -->
      <div class="col-lg-7">
        <div class="wl-panel">
          <div class="wl-panel-head">
            <h5 class="fw-bold mb-0">Dự án gần đây</h5>
          </div>
          <?php if (!$projects): ?>
            <div class="wl-empty">
              <i class="bi bi-folder2-open"></i>
              <p>Bạn chưa đăng dự án nào.</p>
              <a href="<?= url('/projects/create') ?>" class="btn btn-primary btn-sm">Đăng dự án đầu tiên</a>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0 wl-table">
                <thead>
                  <tr><th>Dự án</th><th class="text-end d-none d-sm-table-cell">Ngân sách</th><th class="text-center d-none d-sm-table-cell">Đề xuất</th><th>Trạng thái</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($projects as $p): [$label, $color] = project_status($p['TrangThai']); ?>
                    <tr>
                      <td>
                        <div class="fw-semibold"><?= e($p['TieuDe']) ?></div>
                        <small class="wl-muted">Hạn: <?= date_vn($p['HanNhanDeXuat']) ?></small>
                        <div class="d-sm-none small mt-1"><?= money($p['NganSach']) ?> · <?= (int) $p['SoDeXuat'] ?> đề xuất</div>
                      </td>
                      <td class="text-end text-nowrap d-none d-sm-table-cell"><?= money($p['NganSach']) ?></td>
                      <td class="text-center d-none d-sm-table-cell"><span class="badge text-bg-light border"><?= (int) $p['SoDeXuat'] ?></span></td>
                      <td><span class="badge text-bg-<?= $color ?>"><?= e($label) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ĐỀ XUẤT MỚI NHẤT -->
      <div class="col-lg-5">
        <div class="wl-panel">
          <div class="wl-panel-head">
            <h5 class="fw-bold mb-0">Đề xuất mới nhận</h5>
          </div>
          <?php if (!$received): ?>
            <div class="wl-empty">
              <i class="bi bi-chat-square-text"></i>
              <p>Chưa có đề xuất nào cho dự án của bạn.</p>
            </div>
          <?php else: ?>
            <ul class="list-unstyled mb-0">
              <?php foreach ($received as $d): [$label, $color] = proposal_status($d['TrangThai']); ?>
                <li class="wl-list-item">
                  <span class="wl-avatar"><?= e(mb_strtoupper(mb_substr($d['TenFreelancer'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate"><?= e($d['TenFreelancer']) ?></div>
                    <div class="small wl-muted text-truncate"><?= e($d['TieuDe']) ?></div>
                    <div class="small"><?= money($d['ChiPhiDeXuat']) ?> · <?= (int) $d['ThoiGianThucHien'] ?> ngày</div>
                  </div>
                  <span class="badge text-bg-<?= $color ?>"><?= e($label) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</main>

<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
