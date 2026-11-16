<?php
require_once BASE_PATH . '/app/models/Review.php';

class ReviewController extends Controller
{
    /**
     * POST review/store - Đánh giá 2 chiều sau khi dự án hoàn thành
     */
    public function store()
    {
        $this->requirePost();

        if (!Csrf::verify($_POST['csrf'] ?? '')) {
            $this->json([
                'success' => false,
                'message' => 'Phiên làm việc hết hạn, vui lòng tải lại trang.'
            ], 419);
        }

        $sessionUser = current_user();
        if (!$sessionUser) {
            $this->json([
                'success'  => false,
                'message'  => 'Bạn cần đăng nhập.',
                'redirect' => url('auth/login')
            ], 401);
        }

        $reviewerId = (int) $sessionUser['id'];
        $idDuAn     = (int) ($_POST['id_duan'] ?? 0);

        if ($idDuAn <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Không tìm thấy.'
            ], 404);
        }

        $context = Review::getProjectReviewContext($idDuAn);
        if (!$context) {
            $this->json([
                'success' => false,
                'message' => 'Không tìm thấy.'
            ], 404);
        }

        // Tự xác định người được đánh giá theo vai trò trong dự án
        $ownerId      = (int) $context['ID_KhachHang'];
        $freelancerId = (int) ($context['ID_FreelancerDuocChon'] ?? 0);
        $targetUserId = 0;

        if ($reviewerId === $ownerId) {
            $targetUserId = $freelancerId;
        } elseif ($freelancerId > 0 && $reviewerId === $freelancerId) {
            $targetUserId = $ownerId;
        } else {
            $this->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này.'
            ], 403);
        }

        // Kiểm tra dữ liệu hợp lệ (422)
        $diemRaw = $_POST['diem'] ?? '';
        $nhanXet = trim((string) ($_POST['nhanxet'] ?? ''));
        $errors  = [];

        $diem = filter_var($diemRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5]
        ]);
        if ($diem === false) {
            $errors['diem'] = 'Vui lòng chọn số sao từ 1 đến 5.';
        }

        if (mb_strlen($nhanXet, 'UTF-8') > 500) {
            $errors['nhanxet'] = 'Nhận xét tối đa 500 ký tự.';
        }

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin.',
                'errors'  => $errors
            ], 422);
        }

        // Kiểm tra trạng thái dự án & trùng lặp đánh giá (409)
        if ($context['TrangThai'] !== 'hoan_thanh' || $targetUserId <= 0 || $targetUserId === $reviewerId) {
            $this->json([
                'success' => false,
                'message' => 'Dự án chưa hoàn thành hoặc không đủ điều kiện đánh giá.'
            ], 409);
        }

        if (Review::hasReviewed($idDuAn, $reviewerId)) {
            $this->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá dự án này rồi.'
            ], 409);
        }

        try {
            $created = Review::create($idDuAn, $reviewerId, $targetUserId, (int) $diem, $nhanXet);
            if ($created) {
                $this->json([
                    'success'  => true,
                    'message'  => 'Gửi đánh giá thành công!',
                    'redirect' => url('project/show') . '?id=' . $idDuAn
                ], 200);
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->json([
                    'success' => false,
                    'message' => 'Bạn đã đánh giá dự án này rồi.'
                ], 409);
            }
            error_log('Review store error: ' . $e->getMessage());
        }

        $this->json([
            'success' => false,
            'message' => 'Có lỗi hệ thống, vui lòng thử lại.'
        ], 500);
    }
}