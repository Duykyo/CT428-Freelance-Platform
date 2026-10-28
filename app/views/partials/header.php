<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'FreelanceHub') ?> | FreelanceHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand fw-bold text-brand" href="<?= url() ?>">FreelanceHub</a>
    <div class="ms-auto d-flex align-items-center gap-2">
      <?php if ($u = current_user()): ?>
        <?php if ($u['role'] === 'khachhang'): ?>
          <a class="btn btn-link btn-sm" href="<?= url('project/create') ?>">Đăng dự án</a>
        <?php else: ?>
          <a class="btn btn-link btn-sm" href="<?= url('project/index') ?>">Tìm dự án</a>
        <?php endif; ?>
        <span class="text-muted small d-none d-md-inline">Xin chào, <?= e($u['name']) ?></span>
        <form action="<?= url('auth/logout') ?>" method="post" data-ajax class="m-0">
          <input type="hidden" name="csrf" value="<?= Csrf::token() ?>">
          <button type="submit" class="btn btn-outline-secondary btn-sm">Đăng xuất</button>
        </form>
      <?php else: ?>
        <a class="btn btn-outline-primary btn-sm" href="<?= url('auth/login') ?>">Đăng nhập</a>
        <a class="btn btn-brand btn-sm" href="<?= url('auth/register') ?>">Đăng ký</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
