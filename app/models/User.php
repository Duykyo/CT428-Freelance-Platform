<?php
class User {
    //tim nguoi dung theo email
    public static function findByEmail($email) {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM NguoiDung WHERE Email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    //tao tai nguoi dung moi
    public static function create($data) {
        $db = Database::get();
        $sql = "INSERT INTO NguoiDung (HoTen, Email, MatKhau, VaiTro, KyNang) 
                VALUES (:hoten, :email, :matkhau, :vaitro, :kynang)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':hoten'   => $data['hoten'],
            ':email'   => $data['email'],
            ':matkhau' => password_hash($data['matkhau'], PASSWORD_DEFAULT),
            ':vaitro'  => $data['vaitro'],
            ':kynang'  => $data['kynang'] ?? null
        ]);
    }
}