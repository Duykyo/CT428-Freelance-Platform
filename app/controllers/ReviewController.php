<?php
require_once BASE_PATH . '/app/models/Review.php';
require_once BASE_PATH . '/app/models/Project.php';

class ReviewController extends Controller {
    public function store(): void {
        try {
            $this->requirePost();
            if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
                $this->json(['success' => false, 'message' => 'Phiên làm việc hết hạn.'], 419);
            }

            $user = current_user();
            if (!$user) {
                $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập.', 'redirect' => url('auth/login')], 401);
            }

            $userId    = (int) $user['id'];
            $projectId = (int) ($_POST['id_duan'] ?? 0);
            $diem      = (int) ($_POST['diem'] ?? 0);
            $nhanXet   = trim((string) ($_POST['nhanxet'] ?? ''));

            if ($projectId <= 0) {
                $this->json(['success' => false, 'message' => 'Dự án không hợp lệ.'], 404);
            }
            if ($diem < 1 || $diem > 5) {
                $this->json(['success' => false, 'message' => 'Vui lòng chọn điểm đánh giá từ 1 đến 5 sao.'], 422);
            }
            if (mb_strlen($nhanXet, 'UTF-8') > 500) {
                $this->json(['success' => false, 'message' => 'Nhận xét tối đa 500 ký tự.'], 422);
            }

            $projectModel = new Project();
            $project = $projectModel->findById($projectId);
            if (!$project) {
                $this->json(['success' => false, 'message' => 'Không tìm thấy dự án.'], 404);
            }
            if ($project['TrangThai'] !== 'hoan_thanh') {
                $this->json(['success' => false, 'message' => 'Chỉ có thể đánh giá khi dự án đã hoàn thành.'], 409);
            }

            $db = Database::get();
            $stmt = $db->prepare("SELECT ID_Freelancer FROM DeXuat WHERE ID_DuAn = :pid AND TrangThai = 'duoc_chon' LIMIT 1");
            $stmt->execute([':pid' => $projectId]);
            $selectedFreelancerId = (int) $stmt->fetchColumn();

            if ($selectedFreelancerId <= 0) {
                $this->json(['success' => false, 'message' => 'Dự án chưa có freelancer được chọn để đánh giá.'], 409);
            }

            $clientId = (int) $project['ID_KhachHang'];
            if ($userId === $clientId) {
                $targetUserId = $selectedFreelancerId;
            } elseif ($userId === $selectedFreelancerId) {
                $targetUserId = $clientId;
            } else {
                $this->json(['success' => false, 'message' => 'Bạn không tham gia thực hiện dự án này.'], 403);
            }

            if (Review::hasReviewed($projectId, $userId)) {
                $this->json(['success' => false, 'message' => 'Bạn đã đánh giá dự án này rồi.'], 409);
            }

            Review::create([
                'id_duan'      => $projectId,
                'id_nguoigui'  => $userId,
                'id_nguoinhan' => $targetUserId,
                'diem'         => $diem,
                'nhanxet'      => $nhanXet !== '' ? $nhanXet : null
            ]);

            $this->json([
                'success'  => true,
                'message'  => 'Cảm ơn bạn đã gửi đánh giá!',
                'redirect' => url('project/show') . '?id=' . $projectId
            ]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage() . ' (dòng ' . $e->getLine() . ')'
            ], 500);
        }
    }
}
