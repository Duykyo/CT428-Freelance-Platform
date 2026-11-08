# Dự án Freelance Platform

Dưới đây là cấu trúc thư mục của dự án:

```text
freelance-platform/
├── database/
│   └── schema.sql
├── app/
│   ├── config/
│   │   ├── config.php          # BASE_URL, APP_ROOT
│   │   └── database.php        # thông tin kết nối
│   ├── core/
│   │   ├── Database.php        # PDO singleton
│   │   ├── Controller.php      # lớp cha: view(), json(), requireLogin()...
│   │   ├── Csrf.php
│   │   └── helpers.php         # e(), url(), redirect()
│   ├── controllers/
│   │   ├── AuthController.php  # đăng ký / đăng nhập / đăng xuất
│   │   ├── ProjectController.php
│   │   ├── ProposalController.php   # (các bước sau)
│   │   └── ProfileController.php    # (các bước sau)
│   ├── models/
│   │   ├── User.php
│   │   ├── Project.php
│   │   ├── Proposal.php        # (các bước sau)
│   │   └── Review.php          # (các bước sau)
│   └── views/
│       ├── partials/header.php, footer.php
│       ├── auth/login.php, register.php
│       └── projects/create.php
└── public/                     # chỉ thư mục này được truy cập từ web
    ├── index.php               # front controller (router)
    ├── .htaccess
    └── assets/
        ├── css/style.css
        └── js/app.js

--------------------------------------------------------------
# CT428 - Nền tảng Kết nối Việc làm Tự do

Ứng dụng giúp khách hàng đăng dự án và người làm tự do gửi đề xuất thực hiện.
Đồ án học phần CT428, Trường Đại học Cần Thơ.

## Công nghệ
PHP thuần (MVC cơ bản), AJAX, MySQL, HTML/CSS/JavaScript, Bootstrap 5.

## Yêu cầu môi trường
- XAMPP có PHP 8.1 trở lên và MySQL/MariaDB
- Git, VS Code

## Cài đặt
1. Clone repo vào `C:\xampp\htdocs`:
```

git clone <link-repo-GitHub>

```
2. Copy `app/config/database.local.example.php` thành `app/config/database.local.php`, điền thông tin MySQL của máy bạn.
3. Bật Apache và MySQL trong XAMPP.
4. Mở phpMyAdmin, tab Import, chọn `database/schema.sql`.
5. Mở `http://localhost/CT428-Freelance-Platform/public/`.

## Cấu trúc thư mục
- `app/config`: cấu hình
- `app/core`: lõi (kết nối DB, Controller, CSRF, tiện ích)
- `app/controllers`, `app/models`, `app/views`: mô hình MVC
- `public`: điểm vào web và tài nguyên (CSS, JS, ảnh)
- `database`: script SQL

## Quy ước làm việc nhóm
- Không commit trực tiếp lên `main` hoặc `develop`; mỗi việc một nhánh `feature/<tên>-<việc>`.
- Pull Request luôn có base là `develop`; Duy duyệt và merge.
- Không commit `database.local.php`.

## Thành viên
| Họ tên | MSSV | Vai trò |
|---|---|---|
| Trần Hoàng Duy | B2405493 | Trưởng nhóm, Backend, Database và Bảo mật |
| Lộc | (điền) | Frontend và UI/UX |
| Quang | (điền) | Backend và Technical Writer |
```
