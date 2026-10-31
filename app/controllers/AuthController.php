<?php
// app/controllers/AuthController.php
class AuthController extends Controller
{
    public function login(): void
    {
        if (current_user()) {
            redirect(home_path());
        }
        $this->view('auth/login', ['title' => 'Đăng nhập', 'active' => 'login']);
        clear_old();
    }

    public function register(): void
    {
        if (current_user()) {
            redirect(home_path());
        }
        $role = $_GET['role'] ?? '';
        $this->view('auth/register', [
            'title'  => 'Đăng ký',
            'active' => 'register',
            'preset' => in_array($role, ['khachhang', 'freelancer'], true) ? $role : '',
        ]);
        clear_old();
    }

    public function doRegister(): void
    {
        $this->requirePost();

        $hoTen   = trim((string) ($_POST['hoten'] ?? ''));
        $email   = strtolower(trim((string) ($_POST['email'] ?? '')));
        $matKhau = (string) ($_POST['matkhau'] ?? '');
        $nhapLai = (string) ($_POST['nhaplai'] ?? '');
        $vaiTro  = (string) ($_POST['vaitro'] ?? '');
        $kyNang  = trim((string) ($_POST['kynang'] ?? ''));

        $_SESSION['old'] = ['hoten' => $hoTen, 'email' => $email, 'vaitro' => $vaiTro, 'kynang' => $kyNang];

        $errors = [];
        if (!in_array($vaiTro, ['khachhang', 'freelancer'], true)) $errors[] = 'Vui lòng chọn vai trò.';
        if ($hoTen === '' || mb_strlen($hoTen) > 100)              $errors[] = 'Họ tên không được để trống và tối đa 100 ký tự.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $errors[] = 'Email không hợp lệ.';
        if (strlen($matKhau) < 8)                                  $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        if ($matKhau !== $nhapLai)                                 $errors[] = 'Mật khẩu nhập lại không khớp.';
        if (mb_strlen($kyNang) > 500)                              $errors[] = 'Kỹ năng tối đa 500 ký tự.';
        if (empty($_POST['agree']))                                $errors[] = 'Bạn cần đồng ý với điều khoản sử dụng.';

        if (!$errors && User::findByEmail($email)) {
            $errors[] = 'Email này đã được đăng ký.';
        }
        if ($errors) {
            flash('error', implode("\n", $errors));
            redirect('/register');
        }

        User::create($hoTen, $email, $matKhau, $vaiTro, $vaiTro === 'freelancer' && $kyNang !== '' ? $kyNang : null);
        clear_old();
        flash('success', 'Đăng ký thành công! Hãy đăng nhập.');
        redirect('/login');
    }

    public function doLogin(): void
    {
        $this->requirePost();

        $email   = strtolower(trim((string) ($_POST['email'] ?? '')));
        $matKhau = (string) ($_POST['matkhau'] ?? '');
        $user    = $email !== '' ? User::findByEmail($email) : null;

        if (!$user || !password_verify($matKhau, $user['MatKhau'])) {
            $_SESSION['old'] = ['email' => $email];
            flash('error', 'Email hoặc mật khẩu không đúng.');
            redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'     => (int) $user['ID'],
            'hoten'  => $user['HoTen'],
            'vaitro' => $user['VaiTro'],
        ];
        redirect(home_path());
    }

    public function logout(): void
    {
        $this->requirePost();
        $_SESSION = [];
        session_destroy();
        header('Location: ' . url('/'));
        exit;
    }
}
