# Dự án Freelance Platform

Dưới đây là cấu trúc thư mục của dự án:

```text
CT428-Freelance-Platform/
├── .gitignore                         ✅ UTF-8, đã bỏ qua database.local.php, desktop.ini
├── README.md                          ✅ cần bổ sung hướng dẫn cài đặt
│
├── database/
│   └── schema.sql                     ✅ 3 bảng chính + DanhGia
│
├── app/
│   ├── config/
│   │   ├── config.php                 ✅ APP_ROOT, BASE_URL (tự nhận diện)
│   │   ├── database.php               ✅ ưu tiên đọc database.local.php
│   │   ├── database.local.example.php ✅ file mẫu cho cả nhóm
│   │   └── database.local.php         🔒 cấu hình DB riêng từng máy
│   │
│   ├── core/
│   │   ├── Database.php               ✅ PDO singleton
│   │   ├── Controller.php             ✅ view(), json(), requirePost(), requireRole()
│   │   ├── Csrf.php                   ✅
│   │   └── helpers.php                ✅ e(), url(), redirect(), current_user(), home_path()
│   │
│   ├── controllers/
│   │   ├── AuthController.php         ✅ login, register, doRegister, doLogin, logout
│   │   ├── ProjectController.php      ✅ create, store, index (giữ chỗ)
│   │   │                              ⏳ mine, show, edit, update, delete, updateStatus, search
│   │   ├── ProposalController.php     ⏳ store, mine, update, withdraw, select
│   │   ├── ProfileController.php      ⏳ index, update, password
│   │   └── ReviewController.php       ⏳ store
│   │
│   ├── models/
│   │   ├── User.php                   ✅ findByEmail, create
│   │   ├── Project.php                ✅ create | ⏳ listByOwner, find, update, delete, search
│   │   ├── Proposal.php               ⏳
│   │   └── Review.php                 ⏳
│   │
│   └── views/
│       ├── partials/
│       │   ├── header.php             ✅ menu theo vai trò
│       │   └── footer.php             ✅
│       ├── auth/
│       │   ├── login.php              ✅
│       │   └── register.php           ✅
│       ├── projects/
│       │   ├── create.php             ✅ form đăng dự án
│       │   ├── index.php              🆕 trang giữ chỗ cho freelancer
│       │   ├── mine.php               ⏳ danh sách dự án của tôi
│       │   ├── show.php               ⏳ chi tiết dự án
│       │   └── edit.php               ⏳ sửa dự án
│       ├── proposals/
│       │   └── mine.php               ⏳ đề xuất của tôi
│       └── profile/
│           └── index.php              ⏳ hồ sơ cá nhân
│
└── public/                            ← thư mục duy nhất truy cập được từ web
    ├── index.php                      ✅ front controller (router)
    ├── .htaccess                      ✅ rewrite về index.php
    └── assets/
        ├── css/
        │   └── style.css              ✅
        ├── js/
        │   └── app.js                 ✅ xử lý form AJAX dùng chung
        └── images/
            └── .gitkeep               giữ thư mục rỗng trên Git
```
