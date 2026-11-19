<?php
class ProposalController extends Controller {
    public function __construct() {
        require_once BASE_PATH . '/app/models/Proposal.php';
        require_once BASE_PATH . '/app/models/Project.php';
    }

    // GET: Danh sÃ¡ch Ä‘á» xuáº¥t cá»§a freelancer
    public function mine(): void {
        $user = current_user();
        if (!$user) {
            redirect('auth/login');
        }
        if (($user['role'] ?? '') !== 'freelancer') {
            http_response_code(403);
            die('Chá»‰ Freelancer má»›i cÃ³ quyá»n truy cáº­p trang nÃ y.');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = Proposal::getByFreelancer((int) $user['id'], $page, 10);

        $this->view('proposals/mine', [
            'title'      => 'Äá» xuáº¥t cá»§a tÃ´i',
            'proposals'  => $result['items'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
            'total'      => $result['total']
        ]);
    }

    // POST: Gá»­i Ä‘á» xuáº¥t cho dá»± Ã¡n
    public function store(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'PhiÃªn lÃ m viá»‡c háº¿t háº¡n, vui lÃ²ng táº£i láº¡i trang.'], 419);
        }

        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Vui lÃ²ng Ä‘Äƒng nháº­p.', 'redirect' => url('auth/login')], 401);
        }
        if (($user['role'] ?? '') !== 'freelancer') {
            $this->json(['success' => false, 'message' => 'Chá»‰ Freelancer má»›i Ä‘Æ°á»£c gá»­i Ä‘á» xuáº¥t.'], 403);
        }

        $projectId = (int) ($_POST['id_duan'] ?? 0);
        $chiPhi    = (int) ($_POST['chiphi'] ?? 0);
        $thoiGian  = (int) ($_POST['thoigian'] ?? 0);
        $noiDung   = trim((string) ($_POST['noidung'] ?? ''));

        if ($projectId <= 0) {
            $this->json(['success' => false, 'message' => 'Dá»± Ã¡n khÃ´ng há»£p lá»‡.'], 404);
        }
        if ($chiPhi <= 0) {
            $this->json(['success' => false, 'message' => 'Chi phÃ­ Ä‘á» xuáº¥t pháº£i lá»›n hÆ¡n 0 VNÄ.'], 422);
        }
        if ($thoiGian < 1 || $thoiGian > 365) {
            $this->json(['success' => false, 'message' => 'Thá»i gian thá»±c hiá»‡n tá»« 1 Ä‘áº¿n 365 ngÃ y.'], 422);
        }
        if (mb_strlen($noiDung, 'UTF-8') < 20) {
            $this->json(['success' => false, 'message' => 'Ná»™i dung Ä‘á» xuáº¥t tá»‘i thiá»ƒu 20 kÃ½ tá»±.'], 422);
        }

        $projectModel = new Project();
        $project = $projectModel->findById($projectId);
        if (!$project) {
            $this->json(['success' => false, 'message' => 'Dá»± Ã¡n khÃ´ng tá»“n táº¡i.'], 404);
        }

        if ($project['TrangThai'] !== 'mo' || $project['HanNhanDeXuat'] < date('Y-m-d')) {
            $this->json(['success' => false, 'message' => 'Dá»± Ã¡n Ä‘Ã£ Ä‘Ã³ng hoáº·c háº¿t háº¡n nháº­n Ä‘á» xuáº¥t.'], 409);
        }

        if (Proposal::findByProjectAndFreelancer($projectId, (int) $user['id'])) {
            $this->json(['success' => false, 'message' => 'Báº¡n Ä‘Ã£ gá»­i Ä‘á» xuáº¥t cho dá»± Ã¡n nÃ y rá»“i.'], 409);
        }

        Proposal::create([
            'id_duan'  => $projectId,
            'id_fl'    => (int) $user['id'],
            'chiphi'   => $chiPhi,
            'thoigian' => $thoiGian,
            'noidung'  => $noiDung
        ]);

        $this->json([
            'success'  => true,
            'message'  => 'Gá»­i Ä‘á» xuáº¥t thÃ nh cÃ´ng!',
            'redirect' => url('project/show') . '?id=' . $projectId
        ]);
    }

    // POST: Sá»­a Ä‘á» xuáº¥t
    public function update(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'PhiÃªn lÃ m viá»‡c háº¿t háº¡n.'], 419);
        }

        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Vui lÃ²ng Ä‘Äƒng nháº­p.', 'redirect' => url('auth/login')], 401);
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $chiPhi   = (int) ($_POST['chiphi'] ?? 0);
        $thoiGian = (int) ($_POST['thoigian'] ?? 0);
        $noiDung  = trim((string) ($_POST['noidung'] ?? ''));

        $proposal = Proposal::findById($id);
        if (!$proposal) {
            $this->json(['success' => false, 'message' => 'KhÃ´ng tÃ¬m tháº¥y Ä‘á» xuáº¥t.'], 404);
        }
        if ((int) $proposal['ID_Freelancer'] !== (int) $user['id']) {
            $this->json(['success' => false, 'message' => 'Báº¡n khÃ´ng cÃ³ quyá»n sá»­a Ä‘á» xuáº¥t nÃ y.'], 403);
        }
        if ($proposal['TrangThai'] !== 'cho_duyet' || $proposal['TrangThaiDuAn'] !== 'mo') {
            $this->json(['success' => false, 'message' => 'Chá»‰ cÃ³ thá»ƒ sá»­a Ä‘á» xuáº¥t Ä‘ang chá» duyá»‡t cá»§a dá»± Ã¡n Ä‘ang má»Ÿ.'], 409);
        }

        if ($chiPhi <= 0) {
            $this->json(['success' => false, 'message' => 'Chi phÃ­ Ä‘á» xuáº¥t pháº£i lá»›n hÆ¡n 0 VNÄ.'], 422);
        }
        if ($thoiGian < 1 || $thoiGian > 365) {
            $this->json(['success' => false, 'message' => 'Thá»i gian thá»±c hiá»‡n tá»« 1 Ä‘áº¿n 365 ngÃ y.'], 422);
        }
        if (mb_strlen($noiDung, 'UTF-8') < 20) {
            $this->json(['success' => false, 'message' => 'Ná»™i dung Ä‘á» xuáº¥t tá»‘i thiá»ƒu 20 kÃ½ tá»±.'], 422);
        }

        Proposal::update($id, [
            'chiphi'   => $chiPhi,
            'thoigian' => $thoiGian,
            'noidung'  => $noiDung
        ]);

        $this->json([
            'success'  => true,
            'message'  => 'ÄÃ£ cáº­p nháº­t Ä‘á» xuáº¥t!',
            'redirect' => url('proposal/mine')
        ]);
    }

    // POST: RÃºt (xÃ³a) Ä‘á» xuáº¥t
    public function withdraw(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'PhiÃªn lÃ m viá»‡c háº¿t háº¡n.'], 419);
        }

        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Vui lÃ²ng Ä‘Äƒng nháº­p.', 'redirect' => url('auth/login')], 401);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $proposal = Proposal::findById($id);
        if (!$proposal) {
            $this->json(['success' => false, 'message' => 'KhÃ´ng tÃ¬m tháº¥y Ä‘á» xuáº¥t.'], 404);
        }
        if ((int) $proposal['ID_Freelancer'] !== (int) $user['id']) {
            $this->json(['success' => false, 'message' => 'Báº¡n khÃ´ng cÃ³ quyá»n rÃºt Ä‘á» xuáº¥t nÃ y.'], 403);
        }
        if ($proposal['TrangThai'] !== 'cho_duyet' || $proposal['TrangThaiDuAn'] !== 'mo') {
            $this->json(['success' => false, 'message' => 'Chá»‰ cÃ³ thá»ƒ rÃºt Ä‘á» xuáº¥t Ä‘ang chá» duyá»‡t cá»§a dá»± Ã¡n Ä‘ang má»Ÿ.'], 409);
        }

        Proposal::delete($id);

        $this->json([
            'success'  => true,
            'message'  => 'ÄÃ£ rÃºt Ä‘á» xuáº¥t thÃ nh cÃ´ng.',
            'redirect' => url('project/show') . '?id=' . (int) $proposal['ID_DuAn']
        ]);
    }

    // POST: KhÃ¡ch hÃ ng chá»n 1 Ä‘á» xuáº¥t (Transaction: duoc_chon + tu_choi + dang_thuc_hien)
    public function select(): void {
        $this->accept();
    }

    public function accept(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'PhiÃªn lÃ m viá»‡c háº¿t háº¡n.'], 419);
        }

        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Vui lÃ²ng Ä‘Äƒng nháº­p.', 'redirect' => url('auth/login')], 401);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $proposal = Proposal::findById($id);
        if (!$proposal) {
            $this->json(['success' => false, 'message' => 'KhÃ´ng tÃ¬m tháº¥y Ä‘á» xuáº¥t.'], 404);
        }
        if ((int) $proposal['ID_KhachHang'] !== (int) $user['id']) {
            $this->json(['success' => false, 'message' => 'Báº¡n khÃ´ng pháº£i chá»§ dá»± Ã¡n nÃ y.'], 403);
        }
        if ($proposal['TrangThaiDuAn'] !== 'mo' || $proposal['TrangThai'] !== 'cho_duyet') {
            $this->json(['success' => false, 'message' => 'KhÃ´ng thá»ƒ chá»n Ä‘á» xuáº¥t á»Ÿ tráº¡ng thÃ¡i hiá»‡n táº¡i.'], 409);
        }

        $db = Database::get();
        try {
            $db->beginTransaction();

            $projectId = (int) $proposal['ID_DuAn'];
            // 1. Äá» xuáº¥t Ä‘Æ°á»£c chá»n -> duoc_chon
            $stmt1 = $db->prepare("UPDATE DeXuat SET TrangThai = 'duoc_chon' WHERE ID = :id");
            $stmt1->execute([':id' => $id]);

            // 2. CÃ¡c Ä‘á» xuáº¥t cÃ²n láº¡i cá»§a dá»± Ã¡n -> tu_choi
            $stmt2 = $db->prepare("UPDATE DeXuat SET TrangThai = 'tu_choi' WHERE ID_DuAn = :pid AND ID != :id");
            $stmt2->execute([':pid' => $projectId, ':id' => $id]);

            // 3. Tráº¡ng thÃ¡i dá»± Ã¡n -> dang_thuc_hien
            $stmt3 = $db->prepare("UPDATE DuAn SET TrangThai = 'dang_thuc_hien' WHERE ID = :pid");
            $stmt3->execute([':pid' => $projectId]);

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['success' => false, 'message' => 'Lá»—i há»‡ thá»‘ng khi chá»n Ä‘á» xuáº¥t.'], 500);
        }

        $this->json([
            'success'  => true,
            'message'  => 'ÄÃ£ chá»n freelancer cho dá»± Ã¡n!',
            'redirect' => url('project/show') . '?id=' . (int) $proposal['ID_DuAn']
        ]);
    }
}
