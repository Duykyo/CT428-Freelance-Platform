<?php
// app/config/app.php – cấu hình riêng của giao diện (file mới, không đụng config.php gốc)
require_once __DIR__ . '/config.php';   // định nghĩa APP_ROOT

// BASE_URL tự nhận diện: '' khi chạy ở gốc (php -S), '/CT428/public' khi đặt trong htdocs
if (!defined('BASE_URL')) {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    define('BASE_URL', rtrim(dirname($script), '/'));
}

define('APP_NAME', 'WorkLance');
define('PER_PAGE', 6);
