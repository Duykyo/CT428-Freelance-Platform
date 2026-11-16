<?php
class Controller {
    // Hàm render view
    protected function view($view, $data = []) {
        extract($data);

        $viewFile = BASE_PATH . '/app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("<h1>Lỗi 404</h1><p>Không tìm thấy file giao diện: <b>$viewFile</b></p>");
        }
    }
    // Hàm render view JSON
    protected function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }
    // Hàm kiểm tra phương thức yêu cầu
    protected function requirePost() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Đã sửa: Thêm dấu $ vào $this
            $this->json(['success' => false, 'message' => 'Phương thức yêu cầu phải là POST'], 405);
        }
    }
    // Hàm kiểm tra vai trò người dùng
    protected function requireRole($role = null) {
        $user = current_user();
        if(!$user) {
            redirect('auth/login');
        }
        if($role !== null && $user['role'] !== $role) {
            die("<h1>Lỗi 403</h1><p>Bạn không có quyền truy cập trang này.</p>");
        }
    }
}
?>