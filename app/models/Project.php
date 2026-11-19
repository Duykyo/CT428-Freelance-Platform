<?php

class Project
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

   //Tim kiem, loc va phan trang cac du an dang mo va con han (danh cho Freelancer)
    public function searchOpen(array $filters, int $page = 1, int $perPage = 10): array
    {
        $where = [
            "d.TrangThai = 'mo'",
            "d.HanNhanDeXuat >= CURDATE()",
        ];
        $params = [];

        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = "(d.TieuDe LIKE :q1 OR d.MoTa LIKE :q2)";
            $params[':q1'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }

        $min = isset($filters['min']) && $filters['min'] !== '' ? (int)$filters['min'] : 0;
        if ($min > 0) {
            $where[] = "d.NganSach >= :min";
            $params[':min'] = $min;
        }

        $max = isset($filters['max']) && $filters['max'] !== '' ? (int)$filters['max'] : 0;
        if ($max > 0) {
            $where[] = "d.NganSach <= :max";
            $params[':max'] = $max;
        }

        $kynang = trim((string)($filters['kynang'] ?? ''));
        if ($kynang !== '') {
            $where[] = "d.KyNang LIKE :kynang";
            $params[':kynang'] = '%' . $kynang . '%';
        }

        $sort = (string)($filters['sort'] ?? 'newest');
        $orderBy = match ($sort) {
            'budget_desc' => 'd.NganSach DESC, d.ID DESC',
            'budget_asc'  => 'd.NganSach ASC, d.ID DESC',
            'deadline'    => 'd.HanNhanDeXuat ASC, d.ID DESC',
            default       => 'd.NgayTao DESC, d.ID DESC',
        };

        $whereSql = implode(' AND ', $where);

        // Đếm tổng số kết quả
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM DuAn d WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                d.ID,
                d.TieuDe,
                d.NganSach,
                d.HanNhanDeXuat,
                d.TrangThai,
                d.KyNang,
                d.NgayTao,
                u.HoTen AS TenKhachHang,
                (SELECT COUNT(*) FROM DeXuat dx WHERE dx.ID_DuAn = d.ID) AS SoDeXuat
            FROM DuAn d
            INNER JOIN NguoiDung u ON u.ID = d.ID_KhachHang
            WHERE {$whereSql}
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'      => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ];
    }

    //Lay danh sach du án cua Khach Hang (bonus loc trang thai va phan trang)
    public function getByClient(int $clientId, string $trangThai = '', int $page = 1, int $perPage = 10): array
    {
        $where = ["d.ID_KhachHang = :client_id"];
        $params = [':client_id' => $clientId];

        $validStatuses = ['mo', 'dang_thuc_hien', 'hoan_thanh', 'da_huy'];
        if ($trangThai !== '' && in_array($trangThai, $validStatuses, true)) {
            $where[] = "d.TrangThai = :trang_thai";
            $params[':trang_thai'] = $trangThai;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM DuAn d WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                d.*,
                (SELECT COUNT(*) FROM DeXuat dx WHERE dx.ID_DuAn = d.ID) AS SoDeXuat
            FROM DuAn d
            WHERE {$whereSql}
            ORDER BY d.NgayTao DESC, d.ID DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'projects'   => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ];
    }

    //Lay chi tiet mot du an kem ten Khach Hang
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                d.*,
                u.HoTen AS TenKhachHang
            FROM DuAn d
            INNER JOIN NguoiDung u ON u.ID = d.ID_KhachHang
            WHERE d.ID = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    //Lay danh sach de xuat mot du an (Owner du an xem)
    public function getProposalsByProject(int $projectId): array
    {
        $sql = "
            SELECT
                dx.*,
                u.HoTen AS TenFreelancer,
                u.KyNang AS KyNangFreelancer,
                (SELECT COUNT(*) FROM DanhGia dg WHERE dg.ID_NguoiDuocDanhGia = u.ID) AS SoLuotDanhGia,
                (SELECT ROUND(AVG(dg.Diem), 1) FROM DanhGia dg WHERE dg.ID_NguoiDuocDanhGia = u.ID) AS DiemTB
            FROM DeXuat dx
            INNER JOIN NguoiDung u ON u.ID = dx.ID_Freelancer
            WHERE dx.ID_DuAn = :id_duan
            ORDER BY (dx.TrangThai = 'duoc_chon') DESC, dx.NgayTao DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_duan' => $projectId]);

        return $stmt->fetchAll();
    }

    //Lay de xuat cua Freelancer cu the tren du an (neu da gui)
    public function getFreelancerProposal(int $projectId, int $freelancerId): ?array
    {
        $sql = "SELECT * FROM DeXuat WHERE ID_DuAn = :id_duan AND ID_Freelancer = :id_fl LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_duan' => $projectId,
            ':id_fl'   => $freelancerId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

   //Tao du an mo va tra ve ID vua tao
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO DuAn (TieuDe, MoTa, NganSach, HanNhanDeXuat, ID_KhachHang, KyNang)
            VALUES (:tieude, :mota, :ngansach, :han, :id_khachhang, :kynang)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tieude'       => $data['tieude'],
            ':mota'         => $data['mota'],
            ':ngansach'     => $data['ngansach'],
            ':han'          => $data['han'],
            ':id_khachhang' => $data['id_khachhang'],
            ':kynang'       => $data['kynang'] !== '' ? $data['kynang'] : null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    //Update thong tin du an
    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE DuAn
            SET TieuDe = :tieude,
                MoTa = :mota,
                NganSach = :ngansach,
                HanNhanDeXuat = :han,
                KyNang = :kynang
            WHERE ID = :id
        ";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':tieude'   => $data['tieude'],
            ':mota'     => $data['mota'],
            ':ngansach' => $data['ngansach'],
            ':han'      => $data['han'],
            ':kynang'   => $data['kynang'] !== '' ? $data['kynang'] : null,
            ':id'       => $id,
        ]);
    }

    //Update Status du an
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE DuAn SET TrangThai = :status WHERE ID = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id'     => $id,
        ]);
    }

    //Xoa du an
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM DuAn WHERE ID = :id");
        return $stmt->execute([':id' => $id]);
    }
}