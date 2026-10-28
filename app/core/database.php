<?php
class Database{
  private static ?PDO $pdo = null;
  private function __construct() {}
  private function __clone() {}

  public static function get(): PDO {
    if (self::$pdo === null) {
    if (!defined('APP_ROOT')) {
                error_log('Lỗi hệ thống: Hằng số APP_ROOT chưa được định nghĩa.');
                http_response_code(500);
                exit('Lỗi hệ thống. Vui lòng thử lại sau.');
    }

    $c = require APP_ROOT . '/app/config/database.php';
    if (!is_array($c)) {
    error_log('Cấu hình DB không hợp lệ: file database.php/database.local.php phải return một mảng.');
    http_response_code(500);
    exit('Cấu hình cơ sở dữ liệu không hợp lệ.');
}
    $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset={$c['charset']}";
      
    try{
      self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]);
    } catch(PDOException $e){
        error_log('DB connection failed: ' . $e->getMessage());
        http_response_code(500);
        exit('Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.');
    }
    }
    return self::$pdo;
  }
}