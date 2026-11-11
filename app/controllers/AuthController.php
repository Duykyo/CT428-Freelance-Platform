<?php
  require_once BASE_PATH . '/app/models/User.php';
  class AuthController extends Controller {
    //chuyen huong ve trang home neu da dang nhap
    public function login() {
      if(current_user()) {
        redirect(home_path(current_user()['role']));
      }
      $this->view('auth/login');
    }
    //chuyen huong ve trang home neu da dang nhap
    public function register() {
      if(current_user()) {
        redirect(home_path(current_user()['role']));
      }
      $this->view('auth/register');
    }
    //Xu ly dang ky
    public function doRegister() {
      $this->requirePost();

      if(!Csrf::verify($_POST['csrf'] ?? '')) {
        $this->json(['success' => false, 'message' => 'Token CSRF không hợp lệ. Vui lòng thử lại sau.'], 419);
      }
      
      $hoten = trim($_POST['hoten'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $matkhau = trim($_POST['matkhau'] ?? '');
      $vaitro = trim($_POST['vai_tro'] ?? '');
      $kynang = trim($_POST['ky_nang'] ?? '');

      $errors = [];

      if(empty($hoten)) {
        $errors['hoten'] = 'Vui lòng nhập họ tên.';
      }
      if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Vui lòng nhập email hợp lệ.';
      }
      if(strlen($matkhau) < 8) {
        $errors['matkhau'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
      }
      if(!in_array($vaitro, ['khachhang', 'freelancer'])) {
        $errors['vai_tro'] = 'Vai trò không hợp lệ.';
      }
      if(!empty($errors)) {
        $this->json(['success' => false, 'message' => "Dữ liệu nhập không hợp lệ.", 'errors' => $errors], 422);
      }
      if(User::findByEmail($email)) {
        $this->json(['success' => false, 'message' => 'Email này đã được đăng ký.', 'errors' => ['email' => 'Email này đã tồn tại.']], 409);
      }
      $created = User::create([
        'hoten'   => $hoten,
        'email'   => $email,
        'matkhau' => $matkhau,
        'vaitro'  => $vaitro,
        'kynang'  => $kynang
      ]);
      if($created) {
        $this->json([
          'success'  => true,
          'message'  => 'Đăng ký thành công.',
          'redirect' => url('auth/login')
        ]);
      } else {
        $this->json(['success' => false, 'message' => 'Lỗi hệ thống khi tạo tài khoản.'], 419);
      }
    }
    public function doLogin() {
      $this->requirePost();

      if(!Csrf::verify($_POST['csrf'] ?? '')) {
        $this->json(['success' => false, 'message' => 'Token CSRF không hợp lệ. Vui lòng thử lại sau.'], 419);
      }

      $email   = trim($_POST['email'] ?? '');
      $matkhau = $_POST['matkhau'] ?? '';

      $error = [];
      if(empty($email)) {
        $errors['email'] = 'Vui lòng nhập email.';
      }
      if(empty($matkhau)) {
        $errors['matkhau'] = 'Vui lòng nhập mật khẩu.';
      }

      if(!empty($errors)) {
        $this->json(['success' => false, 'message' => 'Dữ liệu nhập không hợp lệ.', 'errors' => $errors], 422);
      }

      $user = User::findByEmail($email);
      if(!$user || !password_verify($matkhau, $user['matkhau'])) {
        $this->json(['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác.'], 401);
      }

      session_regenerate_id(true);
      $_SESSION['user'] = [
        'id'   => $user['ID'],
        'name' => $user['HoTen'],
        'email'=> $user['Email'],
        'role' => $user['VaiTro']
      ];

      $this->json([
        'success' => true,
        'message' => 'Đăng nhập thành công!',
        'redirect'=> url(home_path($user['VaiTro']))
      ]);
    }
    public function logout() {
      $this->requirePost();
      unset($_SESSION['user']);
      session_destroy();
      $this->json([
        'success' => true,
        'message' => 'Đã đăng xuất.',
        'redirect'=> url('auth/login')
      ]);
    }
  }
  ?>