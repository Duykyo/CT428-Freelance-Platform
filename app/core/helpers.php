<?php
 //Chống XSS bằng cách bọc mọi biến bắt buộc in ra views
 function e($string){
    return htmlspecialchars((string)$string, ENT_QUOTES, "UTF-8");
 }
 //Hàm tạo URL dựa trên BASE_URL
 function url($path = ''){
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
 }
 //Hàm chuyển hướng trang
 function redirect($path = ''){
    header('Location: ' . url($path));
    exit();
 }
 //Hàm kiểm tra người dùng đã đăng nhập chưa
 function current_user(){
    return isset($_SESSION['user']) ? $_SESSION['user'] :null;
 }
 //Hàm kiểm tra vai trò người dùng hiện tại
 function home_path($role){
    if($role === 'khach_hang'){
        return 'project/mine';
    } elseif($role === 'freelancer'){
        return 'project/index';
    }
    return 'auth/login'; 
 }


