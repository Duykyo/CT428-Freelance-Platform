<?php
// Định nghĩa đường dẫn gốc của ứng dụng
define('BASE_PATH', dirname(__DIR__));
define('APP_ROOT', BASE_PATH);

// 1. Nạp cấu hình ứng dụng & khởi tạo Session an toàn
require_once BASE_PATH . '/app/config/config.php';

// 2. Nạp cấu hình Database & lớp kết nối PDO
require_once BASE_PATH . '/app/config/database.php';
require_once BASE_PATH . '/app/core/Database.php';

// 3. Nạp các lớp Core & Hàm tiện ích
require_once BASE_PATH . '/app/core/Csrf.php';
require_once BASE_PATH . '/app/core/helpers.php';
require_once BASE_PATH . '/app/core/Controller.php';

// 4. Kiểm tra các file Core bắt buộc
$requiredFiles = [
    BASE_PATH . '/app/config/config.php',
    BASE_PATH . '/app/core/Database.php',
    BASE_PATH . '/app/core/Controller.php',
    BASE_PATH . '/app/core/Csrf.php',
    BASE_PATH . '/app/core/helpers.php',
];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        die("<h1>Lỗi Hệ Thống</h1><p>Thiếu file lõi: <b>$file</b></p>");
    }
}

// 5. Router cơ bản: /controller/action/id (Mặc định vào auth/login)
$urlPath  = trim($_GET['url'] ?? '', '/');
$segments = $urlPath !== '' ? explode('/', $urlPath) : [];

$controllerName = $_GET['controller'] ?? ($segments[0] ?? 'auth');
$defaultAction  = ($controllerName === 'auth' && !isset($segments[0])) ? 'login' : 'index';
$actionName     = $_GET['action'] ?? ($segments[1] ?? $defaultAction);

if (isset($segments[2]) && !isset($_GET['id'])) {
    $_GET['id'] = $segments[2];
}
$controllerClass = ucfirst($controllerName) . 'Controller';
$controllerFile  = BASE_PATH . '/app/controllers/' . $controllerClass . '.php';

// 6. Điều hướng
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $actionName) && is_callable([$controller, $actionName])) {
            $controller->$actionName();
        } else {
            http_response_code(404);
            die("<h1>Lỗi 404</h1><p>Action <b>'$actionName'</b> không tồn tại trong <b>$controllerClass</b>.</p>");
        }
    } else {
        http_response_code(500);
        die("<h1>Lỗi Hệ Thống</h1><p>Class <b>'$controllerClass'</b> không tồn tại.</p>");
    }
} else {
    http_response_code(404);
    die("<h1>Lỗi 404</h1><p>Không tìm thấy Controller: <b>$controllerFile</b>.</p>");
}