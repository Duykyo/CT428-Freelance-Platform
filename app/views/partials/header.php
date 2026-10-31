<?php
// Biến dùng chung: $title, $active
$user   = current_user();
$active = $active ?? '';
// Thông báo flash (sau redirect) được hiển thị bằng Toast qua app.js
$flashError = flash('error');
$flashOk    = flash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? APP_NAME) ?> – <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg wl-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand wl-brand" href="<?= url('/') ?>">Work<span>Lance</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Mở menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto ms-lg-4">
        <li class="nav-item"><a class="nav-link <?= $active === 'home' ? 'active' : '' ?>" href="<?= url('/') ?>">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link <?= $active === 'projects' ? 'active' : '' ?>" href="<?= url('/projects') ?>">Dự án</a></li>
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link <?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= url('/dashboard') ?>">Bảng điều khiển</a></li>
          <?php if ($user['vaitro'] === 'khachhang'): ?>
            <li class="nav-item"><a class="nav-link <?= $active === 'create' ? 'active' : '' ?>" href="<?= url('/projects/create') ?>">Đăng dự án</a></li>
          <?php endif; ?>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('/') ?>#how">Cách hoạt động</a></li>
        <?php endif; ?>
      </ul>
      <div class="d-flex flex-wrap gap-2 align-items-center wl-nav-user">
        <?php if ($user): ?>
          <a href="<?= url('/profile') ?>" class="wl-nav-profile <?= $active === 'profile' ? 'active' : '' ?>" title="Hồ sơ cá nhân">
            <span class="wl-avatar wl-avatar-sm" data-user-initial><?= e(mb_strtoupper(mb_substr($user['hoten'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
            <span class="wl-nav-name" data-user-name><?= e($user['hoten']) ?></span>
            <span class="badge text-bg-light border"><?= $user['vaitro'] === 'khachhang' ? 'Khách hàng' : 'Người làm tự do' ?></span>
          </a>
          <form method="post" action="<?= url('/logout') ?>" class="m-0">
            <?= Csrf::field() ?>
            <button class="btn btn-outline-primary">Đăng xuất</button>
          </form>
        <?php else: ?>
          <a href="<?= url('/login') ?>" class="btn btn-outline-primary">Đăng nhập</a>
          <a href="<?= url('/register') ?>" class="btn btn-primary">Đăng ký</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Dữ liệu flash cho app.js hiển thị thành Toast -->
<div id="flash-data" hidden
     data-success="<?= e($flashOk ?? '') ?>"
     data-error="<?= e($flashError ?? '') ?>"></div>
