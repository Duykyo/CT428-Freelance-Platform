<?php
// app/controllers/HomeController.php
class HomeController extends Controller
{
    public function index(): void
    {
        // Trang chủ vẫn hiển thị được khi chưa kết nối được CSDL
        $latest = [];
        if (db_available()) {
            try {
                $latest = Project::latest(3);
            } catch (Throwable $e) {
                error_log($e->getMessage());
            }
        }
        $this->view('home/index', ['title' => 'Nền tảng kết nối việc làm tự do', 'active' => 'home', 'latest' => $latest]);
    }
}
