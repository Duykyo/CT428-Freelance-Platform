<?php
declare(strict_types=1);

require __DIR__ . '/../app/config/config.php';
require APP_ROOT . '/app/core/helpers.php';

// Tự động nạp lớp theo tên file trong core, controllers, models
spl_autoload_register(function (string $class): void {
    foreach (['core', 'controllers', 'models'] as $dir) {
        $file = APP_ROOT . "/app/$dir/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

// URL dạng /controller/action, mặc định là auth/login
$parts  = explode('/', trim($_GET['url'] ?? '', '/'));
$name   = $parts[0] !== '' ? $parts[0] : 'auth';
$action = $parts[1] ?? 'login';

if (!preg_match('/^[a-z]+$/i', $name) || !preg_match('/^[a-z]+$/i', $action)) {
    http_response_code(404);
    exit('404 Not Found');
}

$class = ucfirst(strtolower($name)) . 'Controller';
if (!class_exists($class)) {
    http_response_code(404);
    exit('404 Not Found');
}

$controller = new $class();
// Method protected hoặc không tồn tại sẽ không gọi được
if (!is_callable([$controller, $action])) {
    http_response_code(404);
    exit('404 Not Found');
}
$controller->$action();
