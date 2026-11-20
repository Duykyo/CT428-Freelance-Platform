<?php
// Äá»‹nh nghÄ©a Ä‘Æ°á»ng dáº«n gá»‘c cá»§a á»©ng dá»¥ng
define('BASE_PATH', dirname(__DIR__));


// 1. Náº¡p cáº¥u hÃ¬nh á»©ng dá»¥ng & khá»Ÿi táº¡o Session an toÃ n
require_once BASE_PATH . '/app/config/config.php';

// 2. Náº¡p cáº¥u hÃ¬nh Database & lá»›p káº¿t ná»‘i PDO
require_once BASE_PATH . '/app/config/database.php';
require_once BASE_PATH . '/app/core/Database.php';

// 3. Náº¡p cÃ¡c lá»›p Core & HÃ m tiá»‡n Ã­ch
require_once BASE_PATH . '/app/core/Csrf.php';
require_once BASE_PATH . '/app/core/helpers.php';
require_once BASE_PATH . '/app/core/Controller.php';

// 4. Kiá»ƒm tra cÃ¡c file Core báº¯t buá»™c
$requiredFiles = [
    BASE_PATH . '/app/config/config.php',
    BASE_PATH . '/app/core/Database.php',
    BASE_PATH . '/app/core/Controller.php',
    BASE_PATH . '/app/core/Csrf.php',
    BASE_PATH . '/app/core/helpers.php',
];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        die("<h1>Lá»—i Há»‡ Thá»‘ng</h1><p>Thiáº¿u file lÃµi: <b>$file</b></p>");
    }
}

// 5. Router cÆ¡ báº£n: /controller/action/id (Máº·c Ä‘á»‹nh vÃ o auth/login)
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

// 6. Äiá»u hÆ°á»›ng
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $actionName) && is_callable([$controller, $actionName])) {
            $controller->$actionName();
        } else {
            http_response_code(404);
            die("<h1>Lá»—i 404</h1><p>Action <b>'$actionName'</b> khÃ´ng tá»“n táº¡i trong <b>$controllerClass</b>.</p>");
        }
    } else {
        http_response_code(500);
        die("<h1>Lá»—i Há»‡ Thá»‘ng</h1><p>Class <b>'$controllerClass'</b> khÃ´ng tá»“n táº¡i.</p>");
    }
} else {
    http_response_code(404);
    die("<h1>Lá»—i 404</h1><p>KhÃ´ng tÃ¬m tháº¥y Controller: <b>$controllerFile</b>.</p>");
}
