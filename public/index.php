<?php
// public/index.php – front controller (router)

// Khi chạy bằng `php -S`, để server trả file tĩnh (css/js/ảnh) trực tiếp
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && realpath($file) !== __FILE__) {
        return false;
    }
}

require dirname(__DIR__) . '/app/config/app.php';

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// Tự động nạp class theo tên file (thử cả tên viết thường: Database -> database.php)
spl_autoload_register(function (string $class): void {
    foreach (['core', 'controllers', 'models'] as $dir) {
        foreach ([$class, strtolower($class)] as $name) {
            $file = APP_ROOT . "/app/$dir/$name.php";
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
});
require APP_ROOT . '/app/core/helpers.php';

// [METHOD path] => [Controller, action]
$routes = [
    'GET /'                  => ['HomeController', 'index'],
    'GET /login'             => ['AuthController', 'login'],
    'POST /login'            => ['AuthController', 'doLogin'],
    'GET /register'          => ['AuthController', 'register'],
    'POST /register'         => ['AuthController', 'doRegister'],
    'POST /logout'           => ['AuthController', 'logout'],

    'GET /projects'          => ['ProjectController', 'index'],
    'GET /projects/create'   => ['ProjectController', 'create'],
    'POST /projects'         => ['ProjectController', 'store'],        // AJAX

    'POST /proposals'        => ['ProposalController', 'store'],       // AJAX

    'GET /dashboard'         => ['DashboardController', 'index'],
    'GET /profile'           => ['ProfileController', 'index'],
    'POST /profile'          => ['ProfileController', 'update'],       // AJAX
    'POST /profile/password' => ['ProfileController', 'password'],     // AJAX
];

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (BASE_URL !== '' && str_starts_with($path, BASE_URL)) {
    $path = substr($path, strlen(BASE_URL));
}
$path   = '/' . trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $route = $routes["$method $path"] ?? null;
    if ($route === null) {
        http_response_code(404);
        if (expects_json()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'Không tìm thấy đường dẫn.'], JSON_UNESCAPED_UNICODE);
        } else {
            (new Controller())->renderError(404, 'Không tìm thấy trang');
        }
        exit;
    }
    [$class, $action] = $route;
    (new $class())->$action();
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    if (expects_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.'], JSON_UNESCAPED_UNICODE);
    } else {
        (new Controller())->renderError(500, 'Lỗi hệ thống. Vui lòng thử lại sau.');
    }
}
