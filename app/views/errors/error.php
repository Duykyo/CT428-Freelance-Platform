<?php require APP_ROOT . '/app/views/partials/header.php'; ?>
<main class="container py-5 text-center">
  <h1 class="display-1 fw-bold text-primary"><?= (int) $code ?></h1>
  <p class="lead wl-muted"><?= e($message) ?></p>
  <a href="<?= url('/') ?>" class="btn btn-primary">Về trang chủ</a>
</main>
<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
