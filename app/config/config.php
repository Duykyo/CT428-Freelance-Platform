<?php
// Thư mục gốc của dự án (chứa app/, public/, database/)
define('APP_ROOT', dirname(__DIR__, 2));

// Tự nhận diện đường dẫn web, ví dụ: /CT428-Freelance-Platform/public
// Nhờ vậy mỗi máy trong nhóm không phải sửa theo tên thư mục của mình.
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));