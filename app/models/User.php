<?php
class User
{
    public static function findByEmail(string $email): ?array
    {
        $st = Database::get()->prepare('SELECT * FROM NguoiDung WHERE Email = :email LIMIT 1');
        $st->execute([':email' => $email]);
        return $st->fetch() ?: null;
    }

    public static function create(string $hoTen, string $email, string $matKhauHash, string $vaiTro, ?string $kyNang): int
    {
        $st = Database::get()->prepare(
            'INSERT INTO NguoiDung (HoTen, Email, MatKhau, VaiTro, KyNang)
             VALUES (:hoten, :email, :matkhau, :vaitro, :kynang)'
        );
        $st->execute([
            ':hoten'   => $hoTen,
            ':email'   => $email,
            ':matkhau' => $matKhauHash,
            ':vaitro'  => $vaiTro,
            ':kynang'  => $kyNang,
        ]);
        return (int)Database::get()->lastInsertId();
    }
}
