<?php
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Review.php';

class ProfileController extends Controller {
    public function index(): void {
        $sessionUser = current_user();
        if (!$sessionUser) {
            redirect('auth/login');
        }

        $profile = User::findById((int) $sessionUser['id']);
        if (!$profile) {
            http_response_code(404);
            die('Không tìm thấy tài khoản.');
        }

        unset($profile['MatKhau']);
        $rating = Review::getUserRating((int) $profile['ID']);

        $this->view('profile/index', [
            'title'   => 'Hồ sơ cá nhân',
            'profile' => $profile,
            'rating'  => $rating
        ]);
    }

    public function update(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'Phiên làm việc hết hạn.'], 419);
        }

        $sessionUser = current_user();
        if (!$sessionUser) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập.', 'redirect' => url('auth/login')], 401);
        }

        $profile = User::findById((int) $sessionUser['id']);
        if (!$profile) {
            $this->json(['success' => false, 'message' => 'Tài khoản không tồn tại.'], 404);
        }

        $hoTen  = trim((string) ($_POST['hoten'] ?? ''));
        $kyNang = trim((string) ($_POST['kynang'] ?? ''));

        if (mb_strlen($hoTen, 'UTF-8') < 2 || mb_strlen($hoTen, 'UTF-8') > 100) {
            $this->json(['success' => false, 'message' => 'Họ và tên phải từ 2 đến 100 ký tự.'], 422);
        }

        $kyNangValue = null;
        if ($profile['VaiTro'] === 'freelancer') {
            if (mb_strlen($kyNang, 'UTF-8') > 500) {
                $this->json(['success' => false, 'message' => 'Kỹ năng tối đa 500 ký tự.'], 422);
            }
            $kyNangValue = $kyNang !== '' ? $kyNang : null;
        }

        User::updateProfile((int) $profile['ID'], $hoTen, $kyNangValue);

        if (isset($_SESSION['user'])) {
            $_SESSION['user']['name'] = $hoTen;
            $_SESSION['user']['HoTen'] = $hoTen;
        }

        $this->json([
            'success'  => true,
            'message'  => 'Đã cập nhật thông tin hồ sơ!',
            'redirect' => url('profile/index')
        ]);
    }

    public function password(): void {
        $this->requirePost();
        if (!Csrf::verify((string) ($_POST['csrf'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'Phiên làm việc hết hạn.'], 419);
        }

        $sessionUser = current_user();
        if (!$sessionUser) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập.', 'redirect' => url('auth/login')], 401);
        }

        $profile = User::findById((int) $sessionUser['id']);
        if (!$profile) {
            $this->json(['success' => false, 'message' => 'Tài khoản không tồn tại.'], 404);
        }

        $matKhauCu  = (string) ($_POST['matkhaucu'] ?? '');
        $matKhauMoi = (string) ($_POST['matkhaumoi'] ?? '');
        $xacNhan    = (string) ($_POST['xacnhan'] ?? '');

        if (!password_verify($matKhauCu, $profile['MatKhau'])) {
            $this->json(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.'], 422);
        }
        if (mb_strlen($matKhauMoi, 'UTF-8') < 8) {
            $this->json(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 8 ký tự.'], 422);
        }
        if ($matKhauMoi !== $xacNhan) {
            $this->json(['success' => false, 'message' => 'Xác nhận mật khẩu mới không khớp.'], 422);
        }

        User::updatePassword((int) $profile['ID'], $matKhauMoi);

        $this->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }
}
