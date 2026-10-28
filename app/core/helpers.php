<?php
// Hàm tiện ích dùng chung

/** Escape dữ liệu trước khi in ra HTML (chống XSS) */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Tạo URL tuyệt đối theo BASE_URL */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Chuyển trang rồi dừng chương trình */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/** Người dùng đang đăng nhập (hoặc null) */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** Trang đích sau đăng nhập theo vai trò */
function home_path(string $role): string
{
    return $role === 'khachhang' ? 'project/create' : 'project/index';
}
