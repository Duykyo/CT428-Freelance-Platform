<?php
// app/models/Proposal.php
class Proposal
{
    /** Thêm đề xuất. Ném PDOException (SQLSTATE 23000) nếu freelancer đã gửi đề xuất cho dự án này. */
    public static function create(int $duAnId, int $freelancerId, string $chiPhi, int $thoiGian, string $noiDung): int
    {
        $st = Database::get()->prepare(
            'INSERT INTO DeXuat (ID_DuAn, ID_Freelancer, ChiPhiDeXuat, ThoiGianThucHien, NoiDung)
             VALUES (:da, :fl, :chiphi, :tg, :nd)'
        );
        $st->execute([':da' => $duAnId, ':fl' => $freelancerId, ':chiphi' => $chiPhi, ':tg' => $thoiGian, ':nd' => $noiDung]);
        return (int) Database::get()->lastInsertId();
    }

    public static function countForProject(int $duAnId): int
    {
        $st = Database::get()->prepare('SELECT COUNT(*) FROM DeXuat WHERE ID_DuAn = :id');
        $st->execute([':id' => $duAnId]);
        return (int) $st->fetchColumn();
    }

    // ---------- Dashboard freelancer ----------

    /** @return array<string,int> số đề xuất theo trạng thái của một freelancer */
    public static function countByStatus(int $freelancerId): array
    {
        $st = Database::get()->prepare('SELECT TrangThai, COUNT(*) AS c FROM DeXuat WHERE ID_Freelancer = :id GROUP BY TrangThai');
        $st->execute([':id' => $freelancerId]);
        return array_map('intval', array_column($st->fetchAll(), 'c', 'TrangThai'));
    }

    public static function recentByFreelancer(int $freelancerId, int $limit = 6): array
    {
        $st = Database::get()->prepare(
            'SELECT d.ID, d.ChiPhiDeXuat, d.ThoiGianThucHien, d.TrangThai, d.NgayTao,
                    p.TieuDe, p.NganSach, p.TrangThai AS TrangThaiDuAn, u.HoTen AS TenKhachHang
             FROM DeXuat d
             JOIN DuAn p ON p.ID = d.ID_DuAn
             JOIN NguoiDung u ON u.ID = p.ID_KhachHang
             WHERE d.ID_Freelancer = :id
             ORDER BY d.NgayTao DESC, d.ID DESC LIMIT ' . (int) $limit
        );
        $st->execute([':id' => $freelancerId]);
        return $st->fetchAll();
    }

    // ---------- Dashboard khách hàng ----------

    /** @return array<string,int> số đề xuất theo trạng thái trên các dự án của khách hàng */
    public static function countByStatusForOwner(int $khachHangId): array
    {
        $st = Database::get()->prepare(
            'SELECT d.TrangThai, COUNT(*) AS c FROM DeXuat d JOIN DuAn p ON p.ID = d.ID_DuAn
             WHERE p.ID_KhachHang = :id GROUP BY d.TrangThai'
        );
        $st->execute([':id' => $khachHangId]);
        return array_map('intval', array_column($st->fetchAll(), 'c', 'TrangThai'));
    }

    public static function recentForOwner(int $khachHangId, int $limit = 5): array
    {
        $st = Database::get()->prepare(
            'SELECT d.ID, d.ChiPhiDeXuat, d.ThoiGianThucHien, d.TrangThai, d.NgayTao,
                    p.TieuDe, u.HoTen AS TenFreelancer
             FROM DeXuat d
             JOIN DuAn p ON p.ID = d.ID_DuAn
             JOIN NguoiDung u ON u.ID = d.ID_Freelancer
             WHERE p.ID_KhachHang = :id
             ORDER BY d.NgayTao DESC, d.ID DESC LIMIT ' . (int) $limit
        );
        $st->execute([':id' => $khachHangId]);
        return $st->fetchAll();
    }
}
