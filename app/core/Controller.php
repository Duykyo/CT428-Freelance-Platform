<?php
// app/core/Controller.php
class Controller
{
    protected function view(string $name, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require APP_ROOT . '/app/views/' . $name . '.php';
    }

    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Phản hồi thành công cho AJAX: {ok:true, message, ...extra} */
    protected function ok(string $message, array $extra = []): never
    {
        $this->json(['ok' => true, 'message' => $message] + $extra);
    }

    /** Phản hồi lỗi cho AJAX: {ok:false, message, errors[]} */
    protected function fail(string $message, array $errors = [], int $status = 422, array $extra = []): never
    {
        $this->json(['ok' => false, 'message' => $message, 'errors' => $errors] + $extra, $status);
    }

    // Lỗi chung: JSON nếu là AJAX, văn bản thường nếu không
    private function abort(int $status, string $message, array $extra = []): never
    {
        if (expects_json()) {
            $this->fail($message, [], $status, $extra);
        }
        http_response_code($status);
        exit($message);
    }

    // Chặn request không hợp lệ: phải là POST và đúng CSRF token
    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->abort(405, 'Phương thức không được hỗ trợ.');
        }
        if (!Csrf::verify()) {
            $this->abort(419, 'Phiên làm việc hết hạn, vui lòng tải lại trang và thử lại.');
        }
    }

    protected function requireLogin(): array
    {
        $user = current_user();
        if ($user === null) {
            if (expects_json()) {
                $this->abort(401, 'Vui lòng đăng nhập để tiếp tục.', ['redirect' => url('/login')]);
            }
            flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('/login');
        }
        return $user;
    }

    protected function requireRole(string $role): array
    {
        $user = $this->requireLogin();
        if ($user['vaitro'] !== $role) {
            if (expects_json()) {
                $this->abort(403, 'Bạn không có quyền thực hiện thao tác này.');
            }
            http_response_code(403);
            $this->renderError(403, 'Bạn không có quyền truy cập trang này.');
            exit;
        }
        return $user;
    }

    public function renderError(int $code, string $message): void
    {
        $this->view('errors/error', ['code' => $code, 'message' => $message, 'title' => "Lỗi $code"]);
    }
}
