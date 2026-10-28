<?php
class Project
{
    public static function create(int $khachHangId, array $d): int
    {
        $st = Database::get()->prepare(
            'INSERT INTO DuAn (TieuDe, MoTa, NganSach, HanNhanDeXuat, ID_KhachHang, TrangThai, KyNang)
             VALUES (:tieude, :mota, :ngansach, :han, :kh, "mo", :kynang)'
        );
        $st->execute([
            ':tieude'   => $d['tieude'],
            ':mota'     => $d['mota'],
            ':ngansach' => $d['ngansach'],
            ':han'      => $d['han'],
            ':kh'       => $khachHangId,
            ':kynang'   => $d['kynang'],
        ]);
        return (int)Database::get()->lastInsertId();
    }
}
