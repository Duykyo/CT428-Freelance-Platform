<?php
class Proposal {
    public static function getByFreelancer(int $freelancerId, int $page = 1, int $perPage = 10): array {
        $db = Database::get();
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) FROM DeXuat WHERE ID_Freelancer = :fid");
        $countStmt->execute([':fid' => $freelancerId]);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT dx.*, da.TieuDe, da.HanNhanDeXuat, da.TrangThai AS TrangThaiDuAn
                FROM DeXuat dx
                JOIN DuAn da ON dx.ID_DuAn = da.ID
                WHERE dx.ID_Freelancer = :fid
                ORDER BY dx.NgayTao DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':fid', $freelancerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'      => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage))
        ];
    }

    public static function findById(int $id): ?array {
        $db = Database::get();
        $sql = "SELECT dx.*, da.TieuDe, da.ID_KhachHang, da.TrangThai AS TrangThaiDuAn, da.HanNhanDeXuat
                FROM DeXuat dx
                JOIN DuAn da ON dx.ID_DuAn = da.ID
                WHERE dx.ID = :id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByProjectAndFreelancer(int $projectId, int $freelancerId): ?array {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM DeXuat WHERE ID_DuAn = :pid AND ID_Freelancer = :fid LIMIT 1");
        $stmt->execute([':pid' => $projectId, ':fid' => $freelancerId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::get();
        $sql = "INSERT INTO DeXuat (ID_DuAn, ID_Freelancer, ChiPhiDeXuat, ThoiGianThucHien, NoiDung, TrangThai)
                VALUES (:id_duan, :id_fl, :chiphi, :thoigian, :noidung, 'cho_duyet')";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id_duan'  => $data['id_duan'],
            ':id_fl'    => $data['id_fl'],
            ':chiphi'   => $data['chiphi'],
            ':thoigian' => $data['thoigian'],
            ':noidung'  => $data['noidung']
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::get();
        $sql = "UPDATE DeXuat
                SET ChiPhiDeXuat = :chiphi, ThoiGianThucHien = :thoigian, NoiDung = :noidung
                WHERE ID = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':chiphi'   => $data['chiphi'],
            ':thoigian' => $data['thoigian'],
            ':noidung'  => $data['noidung'],
            ':id'       => $id
        ]);
    }

    public static function delete(int $id): bool {
        $db = Database::get();
        $stmt = $db->prepare("DELETE FROM DeXuat WHERE ID = :id");
        return $stmt->execute([':id' => $id]);
    }
}
