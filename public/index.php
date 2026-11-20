<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ã„ÂÃ¡Â»â€¹nh nghÃ„Â©a Ã„â€˜Ã†Â°Ã¡Â»Âng dÃ¡ÂºÂ«n gÃ¡Â»â€˜c cÃ¡Â»Â§a Ã¡Â»Â©ng dÃ¡Â»Â¥ng
define('BASE_PATH', dirname(__DIR__));


// 1. NÃ¡ÂºÂ¡p cÃ¡ÂºÂ¥u hÃƒÂ¬nh Ã¡Â»Â©ng dÃ¡Â»Â¥ng & khÃ¡Â»Å¸i tÃ¡ÂºÂ¡o Session an toÃƒÂ n
require_once BASE_PATH . '/app/config/config.php';

// 2. NÃ¡ÂºÂ¡p cÃ¡ÂºÂ¥u hÃƒÂ¬nh Database & lÃ¡Â»â€ºp kÃ¡ÂºÂ¿t nÃ¡Â»â€˜i PDO
require_once BASE_PATH . '/app/config/database.php';
require_once BASE_PATH . '/app/core/Database.php';

// 3. NÃ¡ÂºÂ¡p cÃƒÂ¡c lÃ¡Â»â€ºp Core & HÃƒÂ m tiÃ¡Â»â€¡n ÃƒÂ­ch
require_once BASE_PATH . '/app/core/Csrf.php';
require_once BASE_PATH . '/app/core/helpers.php';
require_once BASE_PATH . '/app/core/Controller.php';

// 4. KiÃ¡Â»Æ’m tra cÃƒÂ¡c file Core bÃ¡ÂºÂ¯t buÃ¡Â»â„¢c
$requiredFiles = [
    BASE_PATH . '/app/config/config.php',
    BASE_PATH . '/app/core/Database.php',
    BASE_PATH . '/app/core/Controller.php',
    BASE_PATH . '/app/core/Csrf.php',
    BASE_PATH . '/app/core/helpers.php',
];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        die("<h1>LÃ¡Â»â€”i HÃ¡Â»â€¡ ThÃ¡Â»â€˜ng</h1><p>ThiÃ¡ÂºÂ¿u file lÃƒÂµi: <b>$file</b></p>");
    }
}

// 5. Router cÃ†Â¡ bÃ¡ÂºÂ£n: /controller/action/id (MÃ¡ÂºÂ·c Ã„â€˜Ã¡Â»â€¹nh vÃƒÂ o auth/login)
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

// 6. Ã„ÂiÃ¡Â»Âu hÃ†Â°Ã¡Â»â€ºng
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $actionName) && is_callable([$controller, $actionName])) {
            $controller->$actionName();
        } else {
            http_response_code(404);
            die("<h1>LÃ¡Â»â€”i 404</h1><p>Action <b>'$actionName'</b> khÃƒÂ´ng tÃ¡Â»â€œn tÃ¡ÂºÂ¡i trong <b>$controllerClass</b>.</p>");
        }
    } else {
        http_response_code(500);
        die("<h1>LÃ¡Â»â€”i HÃ¡Â»â€¡ ThÃ¡Â»â€˜ng</h1><p>Class <b>'$controllerClass'</b> khÃƒÂ´ng tÃ¡Â»â€œn tÃ¡ÂºÂ¡i.</p>");
    }
} else {
    http_response_code(404);
    die("<h1>LÃ¡Â»â€”i 404</h1><p>KhÃƒÂ´ng tÃƒÂ¬m thÃ¡ÂºÂ¥y Controller: <b>$controllerFile</b>.</p>");
}
