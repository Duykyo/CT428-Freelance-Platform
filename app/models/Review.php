<?php
class Review {
    public static function getUserRating(int $userId): array {
        $db = Database::get();
        $stmt = $db->prepare("SELECT ROUND(AVG(Diem), 1) AS DiemTB, COUNT(*) AS SoLuot FROM DanhGia WHERE ID_NguoiDuocDanhGia = :uid");
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();
        return [
            'DiemTB' => $row && $row['DiemTB'] !== null ? (float) $row['DiemTB'] : 0,
            'SoLuot' => (int) ($row['SoLuot'] ?? 0)
        ];
    }

    public static function getByProject(int $projectId): array {
        $db = Database::get();
        $sql = "SELECT dg.*, nd.HoTen AS TenNguoiDanhGia, nd.VaiTro AS VaiTroNguoiDanhGia
                FROM DanhGia dg
                JOIN NguoiDung nd ON dg.ID_NguoiDanhGia = nd.ID
                WHERE dg.ID_DuAn = :pid
                ORDER BY dg.NgayTao DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':pid' => $projectId]);
        return $stmt->fetchAll();
    }

    public static function hasReviewed(int $projectId, int $reviewerId): bool {
        $db = Database::get();
        $stmt = $db->prepare("SELECT 1 FROM DanhGia WHERE ID_DuAn = :pid AND ID_NguoiDanhGia = :rid LIMIT 1");
        $stmt->execute([':pid' => $projectId, ':rid' => $reviewerId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function create(array $data): int {
        $db = Database::get();
        $sql = "INSERT INTO DanhGia (ID_DuAn, ID_NguoiDanhGia, ID_NguoiDuocDanhGia, Diem, NhanXet)
                VALUES (:id_duan, :id_nguoigui, :id_nguoinhan, :diem, :nhanxet)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id_duan'     => $data['id_duan'],
            ':id_nguoigui' => $data['id_nguoigui'],
            ':id_nguoinhan'=> $data['id_nguoinhan'],
            ':diem'        => $data['diem'],
            ':nhanxet'     => $data['nhanxet']
        ]);
        return (int) $db->lastInsertId();
    }
}
