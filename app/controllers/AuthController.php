<?php
class AuthController extends Controller
{
    public function login(): void
    {
        if ($u = current_user()) {
            redirect(home_path($u['role']));
        }
        $this->view('auth/login', ['title' => 'Đăng nhập']);
    }

    public function register(): void
    {
        if ($u = current_user()) {
            redirect(home_path($u['role']));
        }
        $this->view('auth/register', ['title' => 'Đăng ký']);
    }

    public function doRegister(): void
    {
        $this->requirePost();

        $hoTen   = trim($_POST['hoten'] ?? '');
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $pass    = $_POST['matkhau'] ?? '';
        $confirm = $_POST['xacnhan'] ?? '';
        $vaiTro  = $_POST['vaitro'] ?? '';
        $kyNang  = trim($_POST['kynang'] ?? '') ?: null;

        $errors = [];
        if (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 100)      $errors['hoten']   = 'Họ tên phải từ 2 đến 100 ký tự.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))             $errors['email']   = 'Email không hợp lệ.';
        if (strlen($pass) < 8)                                      $errors['matkhau'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        if ($pass !== $confirm)                                     $errors['xacnhan'] = 'Mật khẩu xác nhận không khớp.';
        if (!in_array($vaiTro, ['khachhang', 'freelancer'], true))  $errors['vaitro']  = 'Vui lòng chọn vai trò.';
        if ($kyNang !== null && mb_strlen($kyNang) > 500)           $errors['kynang']  = 'Kỹ năng quá dài (tối đa 500 ký tự).';

        if ($errors) {
            $this->json(['success' => false, 'message' => 'Vui lòng kiểm tra lại thông tin.', 'errors' => $errors], 422);
        }

        if (User::findByEmail($email)) {
            $this->json(['success' => false, 'message' => 'Email này đã được đăng ký.'], 409);
        }

        try {
            User::create($hoTen, $email, password_hash($pass, PASSWORD_DEFAULT), $vaiTro, $kyNang);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // trùng email do hai yêu cầu đồng thời
                $this->json(['success' => false, 'message' => 'Email này đã được đăng ký.'], 409);
            }
            error_log($e->getMessage());
            $this->json(['success' => false, 'message' => 'Có lỗi hệ thống, vui lòng thử lại.'], 500);
        }

        $this->json([
            'success'  => true,
            'message'  => 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...',
            'redirect' => url('auth/login'),
        ]);
    }

    public function doLogin(): void
    {
        $this->requirePost();

        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass  = $_POST['matkhau'] ?? '';

        $user = User::findByEmail($email);
        // Thông báo chung để không lộ email nào đã tồn tại
        if (!$user || !password_verify($pass, $user['MatKhau'])) {
            $this->json(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'], 401);
        }

        if (password_needs_rehash($user['MatKhau'], PASSWORD_DEFAULT)) {
            $st = Database::get()->prepare('UPDATE NguoiDung SET MatKhau = :h WHERE ID = :id');
            $st->execute([':h' => password_hash($pass, PASSWORD_DEFAULT), ':id' => $user['ID']]);
        }

        session_regenerate_id(true); // chống session fixation
        $_SESSION['user'] = [
            'id'    => (int)$user['ID'],
            'name'  => $user['HoTen'],
            'email' => $user['Email'],
            'role'  => $user['VaiTro'],
        ];

        $this->json([
            'success'  => true,
            'message'  => 'Đăng nhập thành công!',
            'redirect' => url(home_path($user['VaiTro'])),
        ]);
    }

    public function logout(): void
    {
        $this->requirePost();
        $_SESSION = [];
        session_destroy();
        $this->json(['success' => true, 'message' => 'Đã đăng xuất.', 'redirect' => url('auth/login')]);
    }
}
