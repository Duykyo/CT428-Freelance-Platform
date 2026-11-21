<?php
class User {
    public static function findById(int $id): ?array {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM NguoiDung WHERE ID = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail($email) {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM NguoiDung WHERE Email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

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

    public static function updateProfile(int $id, string $hoTen, ?string $kyNang): bool {
        $db = Database::get();
        $stmt = $db->prepare("UPDATE NguoiDung SET HoTen = :hoten, KyNang = :kynang WHERE ID = :id");
        return $stmt->execute([
            ':hoten'  => $hoTen,
            ':kynang' => $kyNang,
            ':id'     => $id
        ]);
    }

    public static function updatePassword(int $id, string $newPassword): bool {
        $db = Database::get();
        $stmt = $db->prepare("UPDATE NguoiDung SET MatKhau = :matkhau WHERE ID = :id");
        return $stmt->execute([
            ':matkhau' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id'      => $id
        ]);
    }
}
