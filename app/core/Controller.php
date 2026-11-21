<?php
class Controller {
    protected function view($view, $data = []) {
        extract($data);

        $viewFile = BASE_PATH . '/app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            http_response_code(404);
            die("<h1>Lỗi 404</h1><p>Không tìm thấy file giao diện: <b>$viewFile</b></p>");
        }
    }

    protected function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    protected function requirePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức yêu cầu phải là POST'], 405);
        }
    }

    protected function requireRole($roles) {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            redirect('auth/login');
        }
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($user['role'] ?? '', $allowed, true)) {
            http_response_code(403);
            die("<h1>Lỗi 403</h1><p>Bạn không có quyền truy cập trang này.</p>");
        }
    }
}