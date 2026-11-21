<?php
class ProposalController extends Controller {
    public function __construct() {
        require_once BASE_PATH . '/app/models/Proposal.php';
        require_once BASE_PATH . '/app/models/Project.php';
    }

    // GET: Danh sách đề xuất của freelancer
    public function mine(): void {
        $user = current_user();
        if (!$user) {
            redirect('auth/login');
        }
        if (($user['role'] ?? '') !== 'freelancer') {
            http_response_code(403);
            die('Chỉ Freelancer mới có quyền truy cập trang này.');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = Proposal::getByFreelancer((int) $user['id'], $page, 10);

        $this->view('proposals/mine', [
            'title'      => 'Đề xuất của tôi',
            'proposals'  => $result['items'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
            'total'      => $result['total']
        ]);
    }

    // POST: Gửi đề xuất cho dự án
    public function store(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'Phiên làm việc hết hạn, vui lòng tải lại trang.'], 419);
        }

        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập.', 'redirect' => url('auth/login')], 401);
        }
        if (($user['role'] ?? '') !== 'freelancer') {
            $this->json(['success' => false, 'message' => 'Chỉ Freelancer mới được gửi đề xuất.'], 403);
        }

        $projectId = (int) ($_POST['id_duan'] ?? 0);
        $chiPhi    = (int) ($_POST['chiphi'] ?? 0);
        $thoiGian  = (int) ($_POST['thoigian'] ?? 0);
        $noiDung   = trim((string) ($_POST['noidung'] ?? ''));

        if ($projectId <= 0) {
            $this->json(['success' => false, 'message' => 'Dự án không hợp lệ.'], 404);
        }
        if ($chiPhi <= 0) {
            $this->json(['success' => false, 'message' => 'Chi phí đề xuất phải lớn hơn 0 VNĐ.'], 422);
        }
        if ($thoiGian <= 0) {
            $this->json(['success' => false, 'message' => 'Thời gian thực hiện phải lớn hơn 0 ngày.'], 422);
        }
        if (mb_strlen($noiDung) < 20) {
            $this->json(['success' => false, 'message' => 'Nội dung đề xuất tối thiểu 20 ký tự.'], 422);
        }

        $projectModel = new Project();
        $project = $projectModel->findById($projectId);
        if (!$project) {
            $this->json(['success' => false, 'message' => 'Dự án không tồn tại.'], 404);
        }

        if ($project['TrangThai'] !== 'mo' || $project['HanNhanDeXuat'] < date('Y-m-d')) {
            $this->json(['success' => false, 'message' => 'Dự án đã đóng hoặc hết hạn nhận đề xuất.'], 409);
        }

        if (Proposal::findByProjectAndFreelancer($projectId, (int) $user['id'])) {
            $this->json(['success' => false, 'message' => 'Bạn đã gửi đề xuất cho dự án này rồi.'], 409);
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
            'message'  => 'Gửi đề xuất thành công!',
            'redirect' => url('project/show') . '?id=' . $projectId
        ]);
    }

    // POST: Sửa đề xuất
    public function update(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'Phiên làm việc hết hạn.'], 419);
        }

        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập.', 'redirect' => url('auth/login')], 401);
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $chiPhi   = (int) ($_POST['chiphi'] ?? 0);
        $thoiGian = (int) ($_POST['thoigian'] ?? 0);
        $noiDung  = trim((string) ($_POST['noidung'] ?? ''));

        $proposal = Proposal::findById($id);
        if (!$proposal) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy đề xuất.'], 404);
        }
        if ((int) $proposal['ID_Freelancer'] !== (int) $user['id']) {
            $this->json(['success' => false, 'message' => 'Bạn không có quyền sửa đề xuất này.'], 403);
        }
        if ($proposal['TrangThai'] !== 'cho_duyet' || $proposal['TrangThaiDuAn'] !== 'mo') {
            $this->json(['success' => false, 'message' => 'Chỉ có thể sửa đề xuất đang chờ duyệt.'], 422);
        }
        if ($chiPhi <= 0) {
            $this->json(['success' => false, 'message' => 'Chi phí đề xuất phải lớn hơn 0 VNĐ.'], 422);
        }
        if ($thoiGian <= 0) {
            $this->json(['success' => false, 'message' => 'Thời gian thực hiện phải lớn hơn 0 ngày.'], 422);
        }
        if (mb_strlen($noiDung) < 20) {
            $this->json(['success' => false, 'message' => 'Nội dung đề xuất tối thiểu 20 ký tự.'], 422);
        }

        Proposal::update($id, [
            'chiphi'   => $chiPhi,
            'thoigian' => $thoiGian,
            'noidung'  => $noiDung
        ]);

        $this->json([
            'success'  => true,
            'message'  => 'Đã cập nhật đề xuất!',
            'redirect' => url('proposal/mine')
        ]);
    }

    // POST: Rút (xóa) đề xuất
    public function withdraw(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'Phiên làm việc hết hạn.'], 419);
        }

        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập.', 'redirect' => url('auth/login')], 401);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $proposal = Proposal::findById($id);
        if (!$proposal) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy đề xuất.'], 404);
        }
        if ((int) $proposal['ID_Freelancer'] !== (int) $user['id']) {
            $this->json(['success' => false, 'message' => 'Bạn không có quyền rút đề xuất này.'], 403);
        }
        if ($proposal['TrangThai'] !== 'cho_duyet' || $proposal['TrangThaiDuAn'] !== 'mo') {
            $this->json(['success' => false, 'message' => 'Chỉ có thể rút đề xuất đang chờ duyệt.'], 422);
        }

        Proposal::delete($id);

        $this->json([
            'success'  => true,
            'message'  => 'Đã rút đề xuất thành công.',
            'redirect' => url('project/show') . '?id=' . (int) $proposal['ID_DuAn']
        ]);
    }

    // POST: Khách hàng chọn 1 đề xuất (Transaction: duoc_chon + tu_choi + dang_thuc_hien)
    public function select(): void {
        $this->accept();
    }

    public function accept(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'Phiên làm việc hết hạn.'], 419);
        }

        $user = current_user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập.', 'redirect' => url('auth/login')], 401);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $proposal = Proposal::findById($id);
        if (!$proposal) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy đề xuất.'], 404);
        }
        if ((int) $proposal['ID_KhachHang'] !== (int) $user['id']) {
            $this->json(['success' => false, 'message' => 'Bạn không phải chủ dự án này.'], 403);
        }
        if ($proposal['TrangThaiDuAn'] !== 'mo' || $proposal['TrangThai'] !== 'cho_duyet') {
            $this->json(['success' => false, 'message' => 'Không thể chọn đề xuất ở trạng thái hiện tại.'], 409);
        }

        $db = Database::get();
        try {
            $db->beginTransaction();

            $projectId = (int) $proposal['ID_DuAn'];
            // 1. Đề xuất được chọn -> duoc_chon
            $stmt1 = $db->prepare("UPDATE DeXuat SET TrangThai = 'duoc_chon' WHERE ID = :id");
            $stmt1->execute([':id' => $id]);

            // 2. Các đề xuất còn lại của dự án -> tu_choi
            $stmt2 = $db->prepare("UPDATE DeXuat SET TrangThai = 'tu_choi' WHERE ID_DuAn = :pid AND ID != :id");
            $stmt2->execute([':pid' => $projectId, ':id' => $id]);

            // 3. Trạng thái dự án -> dang_thuc_hien
            $stmt3 = $db->prepare("UPDATE DuAn SET TrangThai = 'dang_thuc_hien' WHERE ID = :pid");
            $stmt3->execute([':pid' => $projectId]);

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['success' => false, 'message' => 'Lỗi hệ thống khi chọn đề xuất.'], 500);
        }

        $this->json([
            'success'  => true,
            'message'  => 'Đã chọn freelancer cho dự án!',
            'redirect' => url('project/show') . '?id=' . (int) $proposal['ID_DuAn']
        ]);
    }
}