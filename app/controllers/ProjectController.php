<?php

class ProjectController extends Controller
{
    private Project $projectModel;

    public function __construct()
    {
        require_once BASE_PATH . '/app/models/Project.php';
        require_once BASE_PATH . '/app/models/Review.php';
        $this->projectModel = new Project();
    }

    public function index(): void
    {
        $this->view('projects/index', [
            'title' => 'Tìm dự án',
        ]);
    }

    public function search(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->projectModel->searchOpen($_GET, $page, 10);

        $this->json([
            'success'    => true,
            'items'      => $result['items'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
        ]);
    }

    public function mine(): void
    {
        $user = $this->requireClient(false);
        $trangThai = trim((string)($_GET['trangthai'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = $this->projectModel->getByClient((int)$user['id'], $trangThai, $page, 10);

        $this->view('projects/mine', [
            'title'      => 'Dự án của tôi',
            'projects'   => $result['projects'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
            'trangThai'  => $trangThai,
        ]);
    }

    public function create(): void
    {
        $this->requireClient(false);
        $this->view('projects/create', [
            'title' => 'Đăng dự án',
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        $user = $this->requireClient(true);
        $this->verifyCsrf();

        [$data, $errors] = $this->validateProjectInput($_POST);
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin dự án.',
                'errors'  => $errors,
            ], 422);
        }

        $data['id_khachhang'] = (int)$user['id'];
        $newId = $this->projectModel->create($data);

        $this->json([
            'success'  => true,
            'message'  => 'Đăng dự án thành công!',
            'redirect' => url('project/show') . '?id=' . $newId,
        ]);
    }

    public function show($id = null): void
    {
        $projectId = (int)($id ?? ($_GET['id'] ?? 0));
        if ($projectId <= 0) {
            $this->abortNotFound('Không tìm thấy dự án yêu cầu.');
        }

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->abortNotFound('Dự án không tồn tại hoặc đã bị xóa.');
        }

        $user = current_user();
        $userId = $user ? (int)$user['id'] : 0;
        $role = $user['role'] ?? '';

        $isOwner = ($role === 'khachhang' && $userId === (int)$project['ID_KhachHang']);
        $proposals = $isOwner ? $this->projectModel->getProposalsByProject($projectId) : [];

        $myProposal = null;
        $canPropose = false;
        if ($role === 'freelancer' && $userId > 0) {
            $myProposal = $this->projectModel->getFreelancerProposal($projectId, $userId);
            $isOpen = ($project['TrangThai'] === 'mo' && $project['HanNhanDeXuat'] >= date('Y-m-d'));
            $canPropose = ($isOpen && $myProposal === null);
        }

        $this->view('projects/show', [
            'title'      => $project['TieuDe'],
            'project'    => $project,
            'isOwner'    => $isOwner,
            'proposals'  => $proposals,
            'myProposal' => $myProposal,
            'canPropose' => $canPropose,
            'canReview'  => ($project['TrangThai'] === 'hoan_thanh' && $userId > 0 && ($isOwner || ($myProposal && $myProposal['TrangThai'] === 'duoc_chon')) && !Review::hasReviewed($projectId, $userId)),
            'reviewed'   => ($project['TrangThai'] === 'hoan_thanh' && $userId > 0 && Review::hasReviewed($projectId, $userId)),
        ]);
    }

    public function edit($id = null): void
    {
        $user = $this->requireClient(false);
        $projectId = (int)($id ?? ($_GET['id'] ?? 0));
        $project = $this->projectModel->findById($projectId);

        if (!$project) {
            $this->abortNotFound('Dự án không tồn tại.');
        }
        if ((int)$project['ID_KhachHang'] !== (int)$user['id']) {
            $this->abortForbidden('Bạn không có quyền chỉnh sửa dự án này.');
        }
        if ($project['TrangThai'] !== 'mo') {
            redirect('project/show?id=' . $projectId);
        }

        $this->view('projects/edit', [
            'title'   => 'Sửa dự án',
            'project' => $project,
        ]);
    }

    public function update(): void
    {
        $this->requirePost();
        $user = $this->requireClient(true);
        $this->verifyCsrf();

        $projectId = (int)($_POST['id'] ?? 0);
        $project = $this->findOwnedProjectOrJson($projectId, (int)$user['id']);

        if ($project['TrangThai'] !== 'mo') {
            $this->json([
                'success' => false,
                'message' => 'Chỉ có thể sửa dự án khi đang ở trạng thái mở nhận đề xuất.',
            ], 422);
        }

        [$data, $errors] = $this->validateProjectInput($_POST);
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin cập nhật.',
                'errors'  => $errors,
            ], 422);
        }

        $this->projectModel->update($projectId, $data);

        $this->json([
            'success'  => true,
            'message'  => 'Đã lưu thay đổi dự án.',
            'redirect' => url('project/show') . '?id=' . $projectId,
        ]);
    }

    public function cancel(): void
    {
        $this->requirePost();
        $user = $this->requireClient(true);
        $this->verifyCsrf();

        $projectId = (int)($_POST['id'] ?? 0);
        $project = $this->findOwnedProjectOrJson($projectId, (int)$user['id']);

        if (!in_array($project['TrangThai'], ['mo', 'dang_thuc_hien'], true)) {
            $this->json([
                'success' => false,
                'message' => 'Không thể hủy dự án ở trạng thái hiện tại.',
            ], 422);
        }

        $this->projectModel->updateStatus($projectId, 'da_huy');

        $this->json([
            'success'  => true,
            'message'  => 'Đã hủy dự án.',
            'redirect' => url('project/show') . '?id=' . $projectId,
        ]);
    }

    public function complete(): void
    {
        $this->requirePost();
        $user = $this->requireClient(true);
        $this->verifyCsrf();

        $projectId = (int)($_POST['id'] ?? 0);
        $project = $this->findOwnedProjectOrJson($projectId, (int)$user['id']);

        if ($project['TrangThai'] !== 'dang_thuc_hien') {
            $this->json([
                'success' => false,
                'message' => 'Chỉ có thể hoàn thành dự án đang thực hiện.',
            ], 422);
        }

        $this->projectModel->updateStatus($projectId, 'hoan_thanh');

        $this->json([
            'success'  => true,
            'message'  => 'Dự án đã được đánh dấu hoàn thành!',
            'redirect' => url('project/show') . '?id=' . $projectId,
        ]);
    }

    public function delete(): void
    {
        $this->requirePost();
        $user = $this->requireClient(true);
        $this->verifyCsrf();

        $projectId = (int)($_POST['id'] ?? 0);
        $project = $this->findOwnedProjectOrJson($projectId, (int)$user['id']);

        if (!in_array($project['TrangThai'], ['mo', 'da_huy'], true)) {
            $this->json([
                'success' => false,
                'message' => 'Chỉ được xóa dự án đang mở hoặc đã hủy.',
            ], 422);
        }

        $this->projectModel->delete($projectId);

        $this->json([
            'success'  => true,
            'message'  => 'Đã xóa dự án.',
            'redirect' => url('project/mine'),
        ]);
    }

    private function validateProjectInput(array $input): array
    {
        $tieude = trim((string)($input['tieude'] ?? ''));
        $mota = trim((string)($input['mota'] ?? ''));
        $ngansachRaw = $input['ngansach'] ?? '';
        $han = trim((string)($input['han'] ?? ''));
        $kynang = trim((string)($input['kynang'] ?? ''));

        $errors = [];

        $titleLen = mb_strlen($tieude);
        if ($titleLen < 5 || $titleLen > 150) {
            $errors['tieude'] = 'Tiêu đề phải từ 5 đến 150 ký tự.';
        }

        if (mb_strlen($mota) < 20) {
            $errors['mota'] = 'Mô tả công việc phải có ít nhất 20 ký tự.';
        }

        if (!is_numeric($ngansachRaw) || (int)$ngansachRaw <= 0) {
            $errors['ngansach'] = 'Ngân sách phải là số nguyên lớn hơn 0.';
        }

        $dt = DateTime::createFromFormat('Y-m-d', $han);
        if (!$dt || $dt->format('Y-m-d') !== $han) {
            $errors['han'] = 'Hạn nhận đề xuất không hợp lệ.';
        } elseif ($han < date('Y-m-d')) {
            $errors['han'] = 'Hạn nhận đề xuất phải từ hôm nay trở đi.';
        }

        if (mb_strlen($kynang) > 500) {
            $errors['kynang'] = 'Danh sách kỹ năng tối đa 500 ký tự.';
        }

        return [
            [
                'tieude'   => $tieude,
                'mota'     => $mota,
                'ngansach' => (int)$ngansachRaw,
                'han'      => $han,
                'kynang'   => $kynang,
            ],
            $errors,
        ];
    }

    private function requireClient(bool $isAjax): array
    {
        $user = current_user();
        if (!$user) {
            if ($isAjax) {
                $this->json([
                    'success'  => false,
                    'message'  => 'Vui lòng đăng nhập để tiếp tục.',
                    'redirect' => url('auth/login'),
                ], 401);
            }
            redirect('auth/login');
        }

        if (($user['role'] ?? '') !== 'khachhang') {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Chức năng này chỉ dành cho tài khoản Khách hàng.',
                ], 403);
            }
            $this->abortForbidden('Chức năng này chỉ dành cho tài khoản Khách hàng.');
        }

        return $user;
    }

    private function verifyCsrf(): void
    {
        $token = (string)($_POST['csrf'] ?? '');
        if (!Csrf::verify($token)) {
            $this->json([
                'success' => false,
                'message' => 'Token CSRF không hợp lệ. Vui lòng thử lại sau.',
            ], 419);
        }
    }

    private function findOwnedProjectOrJson(int $projectId, int $clientId): array
    {
        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->json(['success' => false, 'message' => 'Dự án không tồn tại.'], 404);
        }
        if ((int)$project['ID_KhachHang'] !== $clientId) {
            $this->json(['success' => false, 'message' => 'Bạn không có quyền thao tác trên dự án này.'], 403);
        }
        return $project;
    }

    private function abortNotFound(string $message): void
    {
        http_response_code(404);
        $errorView = BASE_PATH . '/app/views/errors/404.php';
        if (file_exists($errorView)) {
            $title = 'Không tìm thấy trang';
            require $errorView;
            exit();
        }
        exit($message);
    }

    private function abortForbidden(string $message): void
    {
        http_response_code(403);
        $errorView = BASE_PATH . '/app/views/errors/403.php';
        if (file_exists($errorView)) {
            $title = 'Không có quyền truy cập';
            require $errorView;
            exit();
        }
        exit($message);
    }
}