<?php
// Lớp cha của mọi controller
abstract class Controller
{
    /** Hiển thị view trong app/views (truyền biến qua $data) */
    protected function view(string $name, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require APP_ROOT . '/app/views/' . $name . '.php';
    }

    /** Trả JSON và kết thúc request */
    protected function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Bắt buộc phương thức POST và token CSRF hợp lệ */
    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
        }
        if (!Csrf::verify($_POST['csrf'] ?? null)) {
            $this->json(['success' => false, 'message' => 'Phiên làm việc hết hạn, vui lòng tải lại trang.'], 419);
        }
    }

    /** Bắt buộc đã đăng nhập và đúng vai trò; trả về thông tin người dùng */
    protected function requireRole(string $role): array
    {
        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Bạn cần đăng nhập.', 'redirect' => url('auth/login')], 401);
        }
        if ($user['role'] !== $role) {
            $this->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
        }
        return $user;
    }
}
