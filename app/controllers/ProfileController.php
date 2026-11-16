<?php
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Review.php';

class ProfileController extends Controller
{
    /**
     * GET profile/index - Hiển thị hồ sơ cá nhân của chính người đang đăng nhập
     */
    public function index()
    {
        $sessionUser = current_user();
        if (!$sessionUser) {
            redirect('auth/login');
        }

        $userId = (int) $sessionUser['id'];
        $profile = User::findById($userId);

        if (!$profile) {
            unset($_SESSION['user']);
            redirect('auth/login');
        }

        unset($profile['MatKhau']);
        $rating = Review::getUserRatingSummary($userId);

        $this->view('profile/index', [
            'title'   => 'Hồ sơ cá nhân',
            'profile' => $profile,
            'rating'  => $rating,
        ]);
    }

    /**
     * POST profile/update - Cập nhật họ tên và kỹ năng
     */
    public function update()
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

        $userId = (int) $sessionUser['id'];
        $role   = $sessionUser['role'] ?? '';

        $hoTen  = trim((string) ($_POST['hoten'] ?? ''));
        $kyNang = trim((string) ($_POST['kynang'] ?? ''));

        $errors = [];

        $lenName = mb_strlen($hoTen, 'UTF-8');
        if ($lenName < 2 || $lenName > 100) {
            $errors['hoten'] = 'Họ và tên phải từ 2 đến 100 ký tự.';
        }

        if ($role === 'freelancer' && mb_strlen($kyNang, 'UTF-8') > 500) {
            $errors['kynang'] = 'Kỹ năng tối đa 500 ký tự.';
        }

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin.',
                'errors'  => $errors
            ], 422);
        }

        $savedKyNang = ($role === 'freelancer') ? $kyNang : null;
        $updated = User::updateProfile($userId, $hoTen, $savedKyNang);

        if ($updated) {
            $_SESSION['user']['name'] = $hoTen;
            $this->json([
                'success'  => true,
                'message'  => 'Cập nhật hồ sơ thành công!',
                'redirect' => url('profile/index')
            ], 200);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Có lỗi hệ thống, vui lòng thử lại.'
            ], 500);
        }
    }

    /**
     * POST profile/password - Đổi mật khẩu
     */
    public function password()
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

        $matKhauCu  = (string) ($_POST['matkhaucu'] ?? '');
        $matKhauMoi = (string) ($_POST['matkhaumoi'] ?? '');
        $xacNhan    = (string) ($_POST['xacnhan'] ?? '');

        $errors = [];

        if ($matKhauCu === '') {
            $errors['matkhaucu'] = 'Vui lòng nhập mật khẩu hiện tại.';
        }
        if (strlen($matKhauMoi) < 8) {
            $errors['matkhaumoi'] = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
        }
        if ($matKhauMoi !== $xacNhan) {
            $errors['xacnhan'] = 'Mật khẩu nhập lại không khớp.';
        }

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin.',
                'errors'  => $errors
            ], 422);
        }

        $userId = (int) $sessionUser['id'];
        $dbUser = User::findById($userId);

        if (!$dbUser || !password_verify($matKhauCu, $dbUser['MatKhau'] ?? '')) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin.',
                'errors'  => ['matkhaucu' => 'Mật khẩu hiện tại không chính xác.']
            ], 422);
        }

        if (User::updatePassword($userId, $matKhauMoi)) {
            $this->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công!'
            ], 200);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Có lỗi hệ thống, vui lòng thử lại.'
            ], 500);
        }
    }
}