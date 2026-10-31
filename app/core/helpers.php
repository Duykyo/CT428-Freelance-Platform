<?php
// app/core/helpers.php

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function home_path(): string
{
    return '/dashboard';
}

// Request do fetch()/AJAX gửi lên (JS luôn thêm header này)
function expects_json(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

// Kiểm tra nhanh có kết nối được CSDL không (để trang chủ vẫn mở được khi chưa bật MySQL)
function db_available(): bool
{
    static $ok = null;
    if ($ok === null) {
        try {
            $c = require APP_ROOT . '/app/config/database.php';
            new PDO(
                "mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset={$c['charset']}",
                $c['user'], $c['pass'], [PDO::ATTR_TIMEOUT => 2, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $ok = true;
        } catch (Throwable $e) {
            $ok = false;
        }
    }
    return $ok;
}

// Flash message: flash('error', 'abc') để đặt, flash('error') để lấy và xóa
function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

// Giữ lại dữ liệu form khi có lỗi
function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}
function clear_old(): void
{
    unset($_SESSION['old']);
}

function money($amount): string
{
    return number_format((float) $amount, 0, ',', '.') . 'đ';
}

function date_vn(?string $date): string
{
    return $date ? date('d/m/Y', strtotime($date)) : '';
}

function time_ago(string $datetime): string
{
    $diff = max(0, time() - strtotime($datetime));
    if ($diff < 3600)    return max(1, intdiv($diff, 60)) . ' phút trước';
    if ($diff < 86400)   return intdiv($diff, 3600) . ' giờ trước';
    if ($diff < 604800)  return intdiv($diff, 86400) . ' ngày trước';
    if ($diff < 2592000) return intdiv($diff, 604800) . ' tuần trước';
    return intdiv($diff, 2592000) . ' tháng trước';
}

// [nhãn, màu badge Bootstrap]
function project_status(string $status): array
{
    return [
        'mo'             => ['Đang mở', 'success'],
        'dang_thuc_hien' => ['Đang thực hiện', 'primary'],
        'hoan_thanh'     => ['Hoàn thành', 'secondary'],
        'da_huy'         => ['Đã hủy', 'danger'],
    ][$status] ?? [$status, 'secondary'];
}

function proposal_status(string $status): array
{
    return [
        'cho_duyet' => ['Chờ duyệt', 'warning'],
        'duoc_chon' => ['Được chọn', 'success'],
        'tu_choi'   => ['Từ chối', 'danger'],
    ][$status] ?? [$status, 'secondary'];
}
