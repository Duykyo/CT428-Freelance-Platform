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

    /**
     * Trang tÃ¬m dá»± Ã¡n (Freelancer).
     */
    public function index(): void
    {
        $this->view('projects/index', [
            'title' => 'TÃ¬m dá»± Ã¡n',
        ]);
    }

    /**
     * Endpoint JSON phá»¥c vá»¥ tÃ¬m kiáº¿m & lá»c AJAX táº¡i project/index.
     */
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

    /**
     * Trang Dá»± Ã¡n cá»§a tÃ´i (KhÃ¡ch hÃ ng).
     */
    public function mine(): void
    {
        $user = $this->requireClient(false);
        $trangThai = trim((string)($_GET['trangthai'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = $this->projectModel->getByClient((int)$user['id'], $trangThai, $page, 10);

        $this->view('projects/mine', [
            'title'      => 'Dá»± Ã¡n cá»§a tÃ´i',
            'projects'   => $result['projects'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
            'trangThai'  => $trangThai,
        ]);
    }

    /**
     * Form Ä‘Äƒng dá»± Ã¡n má»›i.
     */
    public function create(): void
    {
        $this->requireClient(false);
        $this->view('projects/create', [
            'title' => 'ÄÄƒng dá»± Ã¡n',
        ]);
    }

    /**
     * Xá»­ lÃ½ POST lÆ°u dá»± Ã¡n má»›i.
     */
    public function store(): void
    {
        $this->requirePost();
        $user = $this->requireClient(true);
        $this->verifyCsrf();

        [$data, $errors] = $this->validateProjectInput($_POST);
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Vui lÃ²ng kiá»ƒm tra láº¡i thÃ´ng tin dá»± Ã¡n.',
                'errors'  => $errors,
            ], 422);
        }

        $data['id_khachhang'] = (int)$user['id'];
        $newId = $this->projectModel->create($data);

        $this->json([
            'success'  => true,
            'message'  => 'ÄÄƒng dá»± Ã¡n thÃ nh cÃ´ng!',
            'redirect' => url('project/show') . '?id=' . $newId,
        ]);
    }

    /**
     * Trang chi tiáº¿t dá»± Ã¡n (dÃ¹ng chung cho KhÃ¡ch hÃ ng, Freelancer vÃ  khÃ¡ch vÃ£ng lai).
     */
    public function show($id = null): void
    {
        $projectId = (int)($id ?? ($_GET['id'] ?? 0));
        if ($projectId <= 0) {
            $this->abortNotFound('KhÃ´ng tÃ¬m tháº¥y dá»± Ã¡n yÃªu cáº§u.');
        }

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->abortNotFound('Dá»± Ã¡n khÃ´ng tá»“n táº¡i hoáº·c Ä‘Ã£ bá»‹ xÃ³a.');
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

    /**
     * Form sá»­a dá»± Ã¡n.
     */
    public function edit($id = null): void
    {
        $user = $this->requireClient(false);
        $projectId = (int)($id ?? ($_GET['id'] ?? 0));
        $project = $this->projectModel->findById($projectId);

        if (!$project) {
            $this->abortNotFound('Dá»± Ã¡n khÃ´ng tá»“n táº¡i.');
        }
        if ((int)$project['ID_KhachHang'] !== (int)$user['id']) {
            $this->abortForbidden('Báº¡n khÃ´ng cÃ³ quyá»n chá»‰nh sá»­a dá»± Ã¡n nÃ y.');
        }
        if ($project['TrangThai'] !== 'mo') {
            redirect('project/show?id=' . $projectId);
        }

        $this->view('projects/edit', [
            'title'   => 'Sá»­a dá»± Ã¡n',
            'project' => $project,
        ]);
    }

    /**
     * Xá»­ lÃ½ POST cáº­p nháº­t dá»± Ã¡n.
     */
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
                'message' => 'Chá»‰ cÃ³ thá»ƒ sá»­a dá»± Ã¡n khi Ä‘ang á»Ÿ tráº¡ng thÃ¡i má»Ÿ nháº­n Ä‘á» xuáº¥t.',
            ], 422);
        }

        [$data, $errors] = $this->validateProjectInput($_POST);
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Vui lÃ²ng kiá»ƒm tra láº¡i thÃ´ng tin cáº­p nháº­t.',
                'errors'  => $errors,
            ], 422);
        }

        $this->projectModel->update($projectId, $data);

        $this->json([
            'success'  => true,
            'message'  => 'ÄÃ£ lÆ°u thay Ä‘á»•i dá»± Ã¡n.',
            'redirect' => url('project/show') . '?id=' . $projectId,
        ]);
    }

    /**
     * Xá»­ lÃ½ POST há»§y dá»± Ã¡n (mo / dang_thuc_hien -> da_huy).
     */
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
                'message' => 'KhÃ´ng thá»ƒ há»§y dá»± Ã¡n á»Ÿ tráº¡ng thÃ¡i hiá»‡n táº¡i.',
            ], 422);
        }

        $this->projectModel->updateStatus($projectId, 'da_huy');

        $this->json([
            'success'  => true,
            'message'  => 'ÄÃ£ há»§y dá»± Ã¡n.',
            'redirect' => url('project/show') . '?id=' . $projectId,
        ]);
    }

    /**
     * Xá»­ lÃ½ POST Ä‘Ã¡nh dáº¥u hoÃ n thÃ nh (dang_thuc_hien -> hoan_thanh).
     */
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
                'message' => 'Chá»‰ cÃ³ thá»ƒ hoÃ n thÃ nh dá»± Ã¡n Ä‘ang thá»±c hiá»‡n.',
            ], 422);
        }

        $this->projectModel->updateStatus($projectId, 'hoan_thanh');

        $this->json([
            'success'  => true,
            'message'  => 'Dá»± Ã¡n Ä‘Ã£ Ä‘Æ°á»£c Ä‘Ã¡nh dáº¥u hoÃ n thÃ nh!',
            'redirect' => url('project/show') . '?id=' . $projectId,
        ]);
    }

    /**
     * Xá»­ lÃ½ POST xÃ³a dá»± Ã¡n (chá»‰ khi mo hoáº·c da_huy).
     */
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
                'message' => 'Chá»‰ Ä‘Æ°á»£c xÃ³a dá»± Ã¡n Ä‘ang má»Ÿ hoáº·c Ä‘Ã£ há»§y.',
            ], 422);
        }

        $this->projectModel->delete($projectId);

        $this->json([
            'success'  => true,
            'message'  => 'ÄÃ£ xÃ³a dá»± Ã¡n.',
            'redirect' => url('project/mine'),
        ]);
    }

    /* ==================== HÃ€M Há»– TRá»¢ Ná»˜I Bá»˜ ==================== */

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
            $errors['tieude'] = 'TiÃªu Ä‘á» pháº£i tá»« 5 Ä‘áº¿n 150 kÃ½ tá»±.';
        }

        if (mb_strlen($mota) < 20) {
            $errors['mota'] = 'MÃ´ táº£ cÃ´ng viá»‡c pháº£i cÃ³ Ã­t nháº¥t 20 kÃ½ tá»±.';
        }

        if (!is_numeric($ngansachRaw) || (int)$ngansachRaw <= 0) {
            $errors['ngansach'] = 'NgÃ¢n sÃ¡ch pháº£i lÃ  sá»‘ nguyÃªn lá»›n hÆ¡n 0.';
        }

        $dt = DateTime::createFromFormat('Y-m-d', $han);
        if (!$dt || $dt->format('Y-m-d') !== $han) {
            $errors['han'] = 'Háº¡n nháº­n Ä‘á» xuáº¥t khÃ´ng há»£p lá»‡.';
        } elseif ($han < date('Y-m-d')) {
            $errors['han'] = 'Háº¡n nháº­n Ä‘á» xuáº¥t pháº£i tá»« hÃ´m nay trá»Ÿ Ä‘i.';
        }

        if (mb_strlen($kynang) > 500) {
            $errors['kynang'] = 'Danh sÃ¡ch ká»¹ nÄƒng tá»‘i Ä‘a 500 kÃ½ tá»±.';
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
                    'message'  => 'Vui lÃ²ng Ä‘Äƒng nháº­p Ä‘á»ƒ tiáº¿p tá»¥c.',
                    'redirect' => url('auth/login'),
                ], 401);
            }
            redirect('auth/login');
        }

        if (($user['role'] ?? '') !== 'khachhang') {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Chá»©c nÄƒng nÃ y chá»‰ dÃ nh cho tÃ i khoáº£n KhÃ¡ch hÃ ng.',
                ], 403);
            }
            $this->abortForbidden('Chá»©c nÄƒng nÃ y chá»‰ dÃ nh cho tÃ i khoáº£n KhÃ¡ch hÃ ng.');
        }

        return $user;
    }

  private function verifyCsrf(): void
    {
        $token = (string)($_POST['csrf'] ?? '');
        if (!Csrf::verify($token)) {
            $this->json([
                'success' => false,
                'message' => 'Token CSRF khÃ´ng há»£p lá»‡. Vui lÃ²ng thá»­ láº¡i sau.',
            ], 419);
        }
    }

    private function findOwnedProjectOrJson(int $projectId, int $clientId): array
    {
        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $this->json(['success' => false, 'message' => 'Dá»± Ã¡n khÃ´ng tá»“n táº¡i.'], 404);
        }
        if ((int)$project['ID_KhachHang'] !== $clientId) {
            $this->json(['success' => false, 'message' => 'Báº¡n khÃ´ng cÃ³ quyá»n thao tÃ¡c trÃªn dá»± Ã¡n nÃ y.'], 403);
        }
        return $project;
    }

    private function abortNotFound(string $message): void
    {
        http_response_code(404);
        $errorView = BASE_PATH . '/app/views/errors/404.php';
        if (file_exists($errorView)) {
            $title = 'KhÃ´ng tÃ¬m tháº¥y trang';
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
            $title = 'KhÃ´ng cÃ³ quyá»n truy cáº­p';
            require $errorView;
            exit();
        }
        exit($message);
    }
}
