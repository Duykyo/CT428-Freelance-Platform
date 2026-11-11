<?php
// Đường dẫn: C:\xampp\htdocs\CT428-Freelance-Platform\public\index.php

// 1. Bật toàn bộ báo lỗi để dễ debug trên local
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Khởi tạo Session và bảo mật Session ID
session_start();
session_regenerate_id(true);

// 3. Định nghĩa đường dẫn gốc
define('BASE_PATH', dirname(__DIR__));

// 4. Nạp các file cấu hình và core (bắt lỗi nếu thiếu file)
$requiredFiles = [
    BASE_PATH . '/app/config/config.php',
    BASE_PATH . '/app/core/helpers.php',
    BASE_PATH . '/app/core/Database.php',
    BASE_PATH . '/app/core/Csrf.php',
    BASE_PATH . '/app/core/Controller.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        die("<h1>Lỗi Hệ Thống</h1><p>Thiếu file quan trọng: <b>$file</b></p>");
    }
}

// 5. Router cơ bản: /controller/action (Mặc định vào auth/login)
$controllerName = isset($_GET['controller']) ? $_GET['controller'] : 'auth';
$actionName = isset($_GET['action']) ? $_GET['action'] : 'login';

$controllerClass = ucfirst($controllerName) . 'Controller';
$controllerFile = BASE_PATH . '/app/controllers/' . $controllerClass . '.php';

// 6. Điều hướng
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        // Chỉ cho phép gọi các method public của Controller
        if (method_exists($controller, $actionName) && is_callable([$controller, $actionName])) {
            $controller->$actionName();
        } else {
            die("<h1>Lỗi 404</h1><p>Action <b>'$actionName'</b> không tồn tại trong <b>$controllerClass</b>.</p>");
        }
    } else {
        die("<h1>Lỗi Hệ Thống</h1><p>Class <b>'$controllerClass'</b> không tồn tại.</p>");
    }
} else {
    die("<h1>Lỗi 404</h1><p>Không tìm thấy Controller: <b>$controllerFile</b>.</p>");
}