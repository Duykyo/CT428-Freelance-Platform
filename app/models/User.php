<?php
class User
{
    // Tìm người dùng theo email
    public static function findByEmail($email)
    {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM NguoiDung WHERE Email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    // Tìm người dùng theo ID
    public static function findById(int $id)
    {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM NguoiDung WHERE ID = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Tạo người dùng mới
    public static function create($data)
    {
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

    // Cập nhật họ tên và kỹ năng
    public static function updateProfile(int $id, string $hoTen, ?string $kyNang): bool
    {
        $db = Database::get();
        $sql = "UPDATE NguoiDung SET HoTen = :hoten, KyNang = :kynang WHERE ID = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':hoten'  => $hoTen,
            ':kynang' => ($kyNang !== null && $kyNang !== '') ? $kyNang : null,
            ':id'     => $id,
        ]);
    }

    // Cập nhật mật khẩu mới (đã băm)
    public static function updatePassword(int $id, string $plainPassword): bool
    {
        $db = Database::get();
        $sql = "UPDATE NguoiDung SET MatKhau = :matkhau WHERE ID = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':matkhau' => password_hash($plainPassword, PASSWORD_DEFAULT),
            ':id'      => $id,
        ]);
    }
}