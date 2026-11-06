<?php
require APP_ROOT . '/app/views/partials/header.php';

$user = current_user();

// Giữ nguyên bộ lọc hiện tại khi chuyển trang
$qs = $_GET;
unset($qs['page']);
$pageUrl = fn(int $p) => url('/projects') . '?' . http_build_query($qs + ['page' => $p]);

$from = $total ? ($filters['page'] - 1) * PER_PAGE + 1 : 0;
$to   = min($total, $from + count($projects) - 1);
?>

<!-- PAGE HEADER + SEARCH -->
<section class="py-4 bg-white border-bottom">
  <div class="container">
    <div class="row align-items-center g-3">
      <div class="col-lg-5">
        <h2 class="fw-bold mb-1">Danh sách dự án</h2>
        <p class="wl-muted mb-0">Tìm dự án phù hợp và gửi đề xuất của bạn</p>
      </div>
      <div class="col-lg-7">
        <form class="wl-search d-flex gap-2" action="<?= url('/projects') ?>" method="get">
          <div class="input-group">
            <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Tìm theo tiêu đề hoặc mô tả..." value="<?= e($filters['q']) ?>">
          </div>
          <button class="btn btn-primary px-4">Tìm</button>
        </form>
      </div>
    </div>
  </div>
</section>

<main class="py-4">
  <div class="container">
    <form method="get" action="<?= url('/projects') ?>" id="filter-form">
      <input type="hidden" name="q" value="<?= e($filters['q']) ?>">
      <div class="row g-4">

        <!-- BỘ LỌC -->
        <aside class="col-lg-3">
          <!-- Màn hình nhỏ: bộ lọc thu gọn, bấm nút "Bộ lọc" để mở -->
          <button class="btn btn-outline-primary w-100 d-lg-none" type="button"
                  data-bs-toggle="collapse" data-bs-target="#filterBox" aria-expanded="false" aria-controls="filterBox">
            <i class="bi bi-funnel"></i> Bộ lọc
          </button>
          <div class="collapse d-lg-block mt-3 mt-lg-0" id="filterBox">
          <div class="wl-filter">
            <h6>Trạng thái</h6>
            <?php foreach (['mo', 'dang_thuc_hien', 'hoan_thanh'] as $s): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="st[]" value="<?= $s ?>" id="st-<?= $s ?>"
                       <?= in_array($s, $filters['status'], true) ? 'checked' : '' ?>>
                <label class="form-check-label" for="st-<?= $s ?>"><?= e(project_status($s)[0]) ?></label>
              </div>
            <?php endforeach; ?>

            <h6 class="mt-3">Ngân sách (VNĐ)</h6>
            <div class="row g-2 mb-3">
              <div class="col-6"><input type="number" min="0" name="min" class="form-control form-control-sm" placeholder="Từ" value="<?= e($filters['min'] ?? '') ?>"></div>
              <div class="col-6"><input type="number" min="0" name="max" class="form-control form-control-sm" placeholder="Đến" value="<?= e($filters['max'] ?? '') ?>"></div>
            </div>

            <h6>Hạn nhận đề xuất</h6>
            <select name="han" class="form-select form-select-sm mb-3">
              <option value="0">Tất cả</option>
              <?php foreach ([3 => 'Trong 3 ngày tới', 7 => 'Trong 7 ngày tới', 30 => 'Trong 30 ngày tới'] as $d => $label): ?>
                <option value="<?= $d ?>" <?= $filters['han'] === $d ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>

            <div class="d-grid gap-2">
              <button class="btn btn-primary btn-sm">Áp dụng bộ lọc</button>
              <a href="<?= url('/projects') ?>" class="btn btn-light btn-sm">Xóa bộ lọc</a>
            </div>
          </div>
          </div>
        </aside>

        <!-- DANH SÁCH -->
        <section class="col-lg-9">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="wl-muted">
              <?php if ($total): ?>Hiển thị <strong><?= $from ?>–<?= $to ?></strong> trên <strong><?= $total ?></strong> dự án
              <?php else: ?>Không có dự án nào<?php endif; ?>
            </div>
            <select name="sort" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="Sắp xếp">
              <?php foreach (['moi_nhat' => 'Mới nhất', 'cao_thap' => 'Ngân sách cao → thấp', 'thap_cao' => 'Ngân sách thấp → cao', 'sap_het' => 'Sắp hết hạn'] as $k => $label): ?>
                <option value="<?= $k ?>" <?= $filters['sort'] === $k ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if (!$projects): ?>
            <div class="wl-project text-center wl-muted py-5">
              <i class="bi bi-search fs-1 d-block mb-2"></i>
              Không tìm thấy dự án phù hợp. Hãy thử đổi từ khóa hoặc bỏ bớt bộ lọc.
            </div>
          <?php endif; ?>

          <div class="d-flex flex-column gap-3">
            <?php foreach ($projects as $p):
              [$label, $color] = project_status($p['TrangThai']);
              $open = $p['TrangThai'] === 'mo';
            ?>
              <article class="wl-project">
                <div class="row g-3">
                  <div class="col-md-9">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="badge text-bg-<?= $color ?>"><?= e($label) ?></span>
                      <small class="wl-muted">Đăng <?= e(time_ago($p['NgayTao'])) ?></small>
                    </div>
                    <h5 class="fw-bold mb-2"><a href="#"><?= e($p['TieuDe']) ?></a></h5>
                    <p class="wl-muted small mb-2"><?= e(mb_strimwidth($p['MoTa'], 0, 220, '…', 'UTF-8')) ?></p>
                    <div class="small wl-muted d-flex flex-wrap gap-3">
                      <span><i class="bi bi-person-circle"></i> <?= e($p['TenKhachHang']) ?></span>
                      <span><i class="bi bi-calendar-event"></i> Hạn nhận đề xuất: <?= $open ? date_vn($p['HanNhanDeXuat']) : 'đã đóng' ?></span>
                      <span><i class="bi bi-chat-left-text"></i> <span data-proposal-count="<?= (int) $p['ID'] ?>"><?= (int) $p['SoDeXuat'] ?></span> đề xuất</span>
                    </div>
                  </div>
                  <div class="col-md-3 text-md-end d-flex flex-md-column justify-content-between align-items-md-end">
                    <div class="wl-budget"><?= money($p['NganSach']) ?></div>
                    <?php if (!$open): ?>
                      <button type="button" class="btn btn-secondary btn-sm" disabled>Đã đóng nhận đề xuất</button>
                    <?php elseif (!$user): ?>
                      <a href="<?= url('/login') ?>" class="btn btn-primary btn-sm">Đăng nhập để gửi đề xuất</a>
                    <?php elseif ($user['vaitro'] === 'freelancer'): ?>
                      <?php if (!empty($p['DaGuiDeXuat'])): ?>
                        <button type="button" class="btn btn-success btn-sm" disabled><i class="bi bi-check2"></i> Đã gửi đề xuất</button>
                      <?php else: ?>
                        <button type="button" class="btn btn-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#proposalModal"
                                data-project-id="<?= (int) $p['ID'] ?>"
                                data-project-title="<?= e($p['TieuDe']) ?>"
                                data-project-budget="<?= e(money($p['NganSach'])) ?>">Gửi đề xuất</button>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <?php if ($pages > 1): ?>
            <nav class="mt-4" aria-label="Phân trang">
              <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $filters['page'] <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $pageUrl(max(1, $filters['page'] - 1)) ?>">Trước</a>
                </li>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                  <li class="page-item <?= $i === $filters['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $pageUrl($i) ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
                <li class="page-item <?= $filters['page'] >= $pages ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $pageUrl(min($pages, $filters['page'] + 1)) ?>">Sau</a>
                </li>
              </ul>
            </nav>
          <?php endif; ?>
        </section>

      </div>
    </form>
  </div>
</main>

<?php if ($user && $user['vaitro'] === 'freelancer'): ?>
<!-- MODAL GỬI ĐỀ XUẤT (AJAX) -->
<div class="modal fade" id="proposalModal" tabindex="-1" aria-labelledby="proposalModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form id="proposal-form" action="<?= url('/proposals') ?>" method="post" data-ajax data-reset novalidate>
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="proposalModalTitle">Gửi đề xuất thực hiện</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <div data-alert></div>
          <?= Csrf::field() ?>
          <input type="hidden" name="id_duan" value="">
          <div class="alert alert-light border small">
            Dự án: <strong data-project-title></strong><br>
            Ngân sách của khách hàng: <strong data-project-budget></strong>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="pp-chiphi" class="form-label fw-semibold">Chi phí đề xuất (VNĐ)</label>
              <input type="text" inputmode="numeric" id="pp-chiphi" name="chiphi" class="form-control" placeholder="12.000.000" data-money required>
            </div>
            <div class="col-sm-6">
              <label for="pp-thoigian" class="form-label fw-semibold">Thời gian thực hiện (ngày)</label>
              <input type="number" id="pp-thoigian" name="thoigian" class="form-control" min="1" max="365" placeholder="20" required>
            </div>
          </div>
          <div>
            <label for="pp-noidung" class="form-label fw-semibold">Nội dung đề xuất</label>
            <textarea id="pp-noidung" name="noidung" class="form-control" rows="4" minlength="10" maxlength="3000"
                      placeholder="Giới thiệu kinh nghiệm và cách bạn sẽ thực hiện dự án..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary">Gửi đề xuất</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
