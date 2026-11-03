<?php
// app/controllers/ProjectController.php
class ProjectController extends Controller
{
    private const STATUSES = ['mo', 'dang_thuc_hien', 'hoan_thanh', 'da_huy'];

    public function index(): void
    {
        $filters = $this->readFilters();
        $user = current_user();
        $uid  = ($user && $user['vaitro'] === 'freelancer') ? $user['id'] : null;
        [$projects, $total] = Project::search($filters, PER_PAGE, $uid);

        $this->view('projects/index', [
            'title'    => 'Danh sách dự án',
            'active'   => 'projects',
            'projects' => $projects,
            'total'    => $total,
            'filters'  => $filters,
            'pages'    => max(1, (int) ceil($total / PER_PAGE)),
        ]);
    }

    /** Form đăng dự án (chỉ khách hàng). */
    public function create(): void
    {
        $this->requireRole('khachhang');
        $this->view('projects/create', ['title' => 'Đăng dự án mới', 'active' => 'create']);
    }

    /** Xử lý form đăng dự án – AJAX, trả JSON. */
    public function store(): void
    {
        $this->requirePost();
        $user = $this->requireRole('khachhang');

        $tieuDe   = trim((string) ($_POST['tieude'] ?? ''));
        $moTa     = trim((string) ($_POST['mota'] ?? ''));
        $nganSach = preg_replace('/[.,\s]/', '', (string) ($_POST['ngansach'] ?? ''));   // cho phép nhập 15.000.000
        $han      = trim((string) ($_POST['hannhandexuat'] ?? ''));
        $kyNang   = trim((string) ($_POST['kynang'] ?? ''));

        $errors = [];
        if ($tieuDe === '' || mb_strlen($tieuDe) > 150) $errors[] = 'Tiêu đề không được để trống và tối đa 150 ký tự.';
        if (mb_strlen($moTa) < 20)                       $errors[] = 'Mô tả cần ít nhất 20 ký tự để freelancer hiểu rõ yêu cầu.';
        if (mb_strlen($moTa) > 5000)                     $errors[] = 'Mô tả tối đa 5000 ký tự.';
        if (!ctype_digit($nganSach) || (int) $nganSach <= 0 || strlen($nganSach) > 12) {
            $errors[] = 'Ngân sách phải là số dương (tối đa 12 chữ số).';
        }
        $d = DateTime::createFromFormat('!Y-m-d', $han);
        if (!$d || $d->format('Y-m-d') !== $han) {
            $errors[] = 'Hạn nhận đề xuất không hợp lệ.';
        } elseif ($han < date('Y-m-d')) {
            $errors[] = 'Hạn nhận đề xuất không được nằm trong quá khứ.';
        }
        if (mb_strlen($kyNang) > 500)                    $errors[] = 'Kỹ năng yêu cầu tối đa 500 ký tự.';

        if ($errors) {
            $this->fail('Vui lòng kiểm tra lại thông tin dự án.', $errors);
        }

        $id = Project::create($user['id'], $tieuDe, $moTa, ltrim($nganSach, '0'), $han, $kyNang !== '' ? $kyNang : null);
        flash('success', 'Đã đăng dự án "' . $tieuDe . '" thành công!');
        $this->ok('Đăng dự án thành công!', ['id' => $id, 'redirect' => url('/dashboard')]);
    }

    private function readFilters(): array
    {
        $num = fn($v) => (is_string($v) && ctype_digit($v)) ? $v : null;

        $status = $_GET['st'] ?? null;
        $status = is_array($status) ? array_values(array_intersect($status, self::STATUSES)) : null;
        if ($status === null) {
            $status = ['mo'];          // mặc định chỉ hiện dự án đang mở
        }

        $han = (int) ($_GET['han'] ?? 0);

        $min = $num($_GET['min'] ?? null);
        $max = $num($_GET['max'] ?? null);
        // Khoảng ngân sách nhanh từ ô chọn ở trang chủ: "từ-đến", vd "5000000-20000000"
        if ($min === null && $max === null && preg_match('/^(\d*)-(\d*)$/', (string) ($_GET['budget'] ?? ''), $m)) {
            $min = $m[1] !== '' && $m[1] !== '0' ? $m[1] : null;
            $max = $m[2] !== '' ? $m[2] : null;
        }

        return [
            'q'      => trim((string) ($_GET['q'] ?? '')),
            'status' => $status,
            'min'    => $min,
            'max'    => $max,
            'han'    => in_array($han, [3, 7, 30], true) ? $han : 0,
            'sort'   => (string) ($_GET['sort'] ?? 'moi_nhat'),
            'page'   => max(1, (int) ($_GET['page'] ?? 1)),
        ];
    }
}
