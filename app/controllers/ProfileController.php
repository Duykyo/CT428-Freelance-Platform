<?php
// app/controllers/ProfileController.php
class ProfileController extends Controller
{
    public function index(): void
    {
        $session = $this->requireLogin();
        $me = User::findById($session['id']);
        if (!$me) {                       // tài khoản đã bị xóa
            $_SESSION = [];
            redirect('/login');
        }
        $this->view('profile/index', ['title' => 'Hồ sơ cá nhân', 'active' => 'profile', 'me' => $me]);
    }

    /** Cập nhật họ tên / kỹ năng – AJAX, trả JSON. */
    public function update(): void
    {
        $this->requirePost();
        $user = $this->requireLogin();

        $hoTen  = trim((string) ($_POST['hoten'] ?? ''));
        $kyNang = trim((string) ($_POST['kynang'] ?? ''));

        $errors = [];
        if ($hoTen === '' || mb_strlen($hoTen) > 100) $errors[] = 'Họ tên không được để trống và tối đa 100 ký tự.';
        if (mb_strlen($kyNang) > 500)                 $errors[] = 'Kỹ năng tối đa 500 ký tự.';
        if ($errors) {
            $this->fail('Không thể lưu hồ sơ.', $errors);
        }

        $isFreelancer = $user['vaitro'] === 'freelancer';
        User::updateProfile($user['id'], $hoTen, $isFreelancer && $kyNang !== '' ? $kyNang : null);
        $_SESSION['user']['hoten'] = $hoTen;        // cập nhật tên hiển thị trên thanh menu

        $this->ok('Đã cập nhật hồ sơ.', ['hoten' => $hoTen]);
    }

    /** Đổi mật khẩu – AJAX, trả JSON. */
    public function password(): void
    {
        $this->requirePost();
        $user = $this->requireLogin();

        $cu     = (string) ($_POST['matkhau_cu'] ?? '');
        $moi    = (string) ($_POST['matkhau_moi'] ?? '');
        $nhapLai = (string) ($_POST['nhaplai'] ?? '');

        $me = User::findById($user['id']);
        $errors = [];
        if (!$me || !password_verify($cu, $me['MatKhau'])) $errors[] = 'Mật khẩu hiện tại không đúng.';
        if (strlen($moi) < 8)                               $errors[] = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
        if ($moi !== $nhapLai)                              $errors[] = 'Mật khẩu nhập lại không khớp.';
        if ($errors === [] && $moi === $cu)                 $errors[] = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
        if ($errors) {
            $this->fail('Không thể đổi mật khẩu.', $errors);
        }

        User::updatePassword($user['id'], $moi);
        session_regenerate_id(true);
        $this->ok('Đã đổi mật khẩu thành công.');
    }
}
