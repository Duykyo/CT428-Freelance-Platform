<?php
// app/models/User.php
class User
{
    public static function findByEmail(string $email): ?array
    {
        $st = Database::get()->prepare('SELECT * FROM NguoiDung WHERE Email = :email LIMIT 1');
        $st->execute([':email' => $email]);
        return $st->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $st = Database::get()->prepare('SELECT * FROM NguoiDung WHERE ID = :id LIMIT 1');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public static function create(string $hoTen, string $email, string $matKhau, string $vaiTro, ?string $kyNang): int
    {
        $st = Database::get()->prepare(
            'INSERT INTO NguoiDung (HoTen, Email, MatKhau, VaiTro, KyNang)
             VALUES (:hoten, :email, :matkhau, :vaitro, :kynang)'
        );
        $st->execute([
            ':hoten'   => $hoTen,
            ':email'   => $email,
            ':matkhau' => password_hash($matKhau, PASSWORD_DEFAULT),
            ':vaitro'  => $vaiTro,
            ':kynang'  => $kyNang,
        ]);
        return (int) Database::get()->lastInsertId();
    }

    public static function updateProfile(int $id, string $hoTen, ?string $kyNang): void
    {
        $st = Database::get()->prepare('UPDATE NguoiDung SET HoTen = :hoten, KyNang = :kynang WHERE ID = :id');
        $st->execute([':hoten' => $hoTen, ':kynang' => $kyNang, ':id' => $id]);
    }

    public static function updatePassword(int $id, string $matKhauMoi): void
    {
        $st = Database::get()->prepare('UPDATE NguoiDung SET MatKhau = :mk WHERE ID = :id');
        $st->execute([':mk' => password_hash($matKhauMoi, PASSWORD_DEFAULT), ':id' => $id]);
    }
}
