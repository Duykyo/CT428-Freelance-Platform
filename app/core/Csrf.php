<?php
class Csrf {
  // Hàm tạo token CSRF và lưu vào session
  public static function token() {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
  }
  // Hàm kiểm tra token CSRF
  public static function verify($token) {
    if(!isset($_SESSION['csrf_token']) || empty($token)) {
      return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
  }
}
?>