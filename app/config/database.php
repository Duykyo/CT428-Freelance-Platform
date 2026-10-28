<?php
// app/config/database.php
$local = __DIR__ . '/database.local.php';
if (is_file($local)) {
    return require $local;
}

return [
    'host'    => '127.0.0.1',
    'port'    => '3306',
    'dbname'  => 'freelance_platform',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];