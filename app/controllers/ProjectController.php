<?php
class ProjectController extends Controller
{
    /** Form đăng dự án (chỉ khách hàng) */
    public function create(): void
    {
        $user = current_user();
        if (!$user) {
            redirect('auth/login');
        }
        if ($user['role'] !== 'khachhang') {
            http_response_code(403);
            exit('Chỉ khách hàng mới được đăng dự án.');
        }
        $this->view('projects/create', ['title' => 'Đăng dự án mới']);
    }

    /** Lưu dự án mới (AJAX, POST) */
    public function store(): void
    {
        $this->requirePost();
        $user = $this->requireRole('khachhang');

        $tieuDe   = trim($_POST['tieude'] ?? '');
        $moTa     = trim($_POST['mota'] ?? '');
        $nganSach = filter_var($_POST['ngansach'] ?? '', FILTER_VALIDATE_INT);
        $han      = $_POST['han'] ?? '';
        $kyNang   = trim($_POST['kynang'] ?? '') ?: null;

        $errors = [];
        if (mb_strlen($tieuDe) < 5 || mb_strlen($tieuDe) > 150) $errors['tieude']   = 'Tiêu đề phải từ 5 đến 150 ký tự.';
        if (mb_strlen($moTa) < 20)                              $errors['mota']     = 'Mô tả cần ít nhất 20 ký tự.';
        if ($nganSach === false || $nganSach <= 0)              $errors['ngansach'] = 'Ngân sách phải là số nguyên dương.';

        $d = DateTime::createFromFormat('Y-m-d', (string)$han);
        if (!$d || $d->format('Y-m-d') !== $han || $d < new DateTime('today')) {
            $errors['han'] = 'Hạn nhận đề xuất phải là ngày hợp lệ, từ hôm nay trở đi.';
        }
        if ($kyNang !== null && mb_strlen($kyNang) > 500)       $errors['kynang']   = 'Kỹ năng quá dài (tối đa 500 ký tự).';

        if ($errors) {
            $this->json(['success' => false, 'message' => 'Vui lòng kiểm tra lại thông tin.', 'errors' => $errors], 422);
        }

        try {
            Project::create($user['id'], [
                'tieude'   => $tieuDe,
                'mota'     => $moTa,
                'ngansach' => $nganSach,
                'han'      => $han,
                'kynang'   => $kyNang,
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->json(['success' => false, 'message' => 'Không thể lưu dự án, vui lòng thử lại.'], 500);
        }

        // TODO (Giai đoạn 1): đổi redirect sang project/mine khi trang danh sách dự án đã có
        $this->json([
            'success'  => true,
            'message'  => 'Đăng dự án thành công!',
            'redirect' => url('project/create'),
        ]);
    }

    /** Trang tạm cho freelancer, sẽ thay bằng trang tìm kiếm ở Giai đoạn 2 */
    public function index(): void
    {
        if (!current_user()) {
            redirect('auth/login');
        }
        $this->view('projects/index', ['title' => 'Tìm dự án']);
    }
}
