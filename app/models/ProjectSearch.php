<?php

class ProjectSearch
{
    /**
     * Tìm kiếm dự án đang mở (mo) và còn hạn nhận đề xuất
     */
    public static function search(array $filters, int $page = 1, int $limit = 10): array
    {
        $db = Database::get();

        $where = [
            "d.TrangThai = 'mo'",
            "d.HanNhanDeXuat >= CURDATE()"
        ];
        $params = [];

        // 1. Lọc theo từ khóa (tách :q1 và :q2 vì EMULATE_PREPARES = false)
        if ($filters['q'] !== '') {
            $escapedQ = addcslashes($filters['q'], '%_\\');
            $where[] = "(d.TieuDe LIKE :q1 OR d.MoTa LIKE :q2)";
            $params[':q1'] = '%' . $escapedQ . '%';
            $params[':q2'] = '%' . $escapedQ . '%';
        }

        // 2. Lọc ngân sách tối thiểu
        if ($filters['min'] !== null) {
            $where[] = "d.NganSach >= :min";
            $params[':min'] = (int) $filters['min'];
        }

        // 3. Lọc ngân sách tối đa
        if ($filters['max'] !== null) {
            $where[] = "d.NganSach <= :max";
            $params[':max'] = (int) $filters['max'];
        }

        // 4. Lọc kỹ năng
        if ($filters['kynang'] !== '') {
            $escapedSkill = addcslashes($filters['kynang'], '%_\\');
            $where[] = "d.KyNang LIKE :kynang";
            $params[':kynang'] = '%' . $escapedSkill . '%';
        }

        $whereSql = implode(' AND ', $where);

        // 5. Whitelist sắp xếp
        $sortMap = [
            'newest'      => 'd.NgayTao DESC, d.ID DESC',
            'budget_desc' => 'd.NganSach DESC, d.ID DESC',
            'budget_asc'  => 'd.NganSach ASC, d.ID DESC',
            'deadline'    => 'd.HanNhanDeXuat ASC, d.ID DESC',
        ];
        $orderBy = $sortMap[$filters['sort']] ?? $sortMap['newest'];

        // 6. Đếm tổng số dự án thỏa điều kiện
        $countSql = "SELECT COUNT(*) FROM DuAn d WHERE {$whereSql}";
        $countStmt = $db->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $totalPages = $total > 0 ? (int) ceil($total / $limit) : 1;
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $limit;

        // 7. Truy vấn danh sách dự án theo trang
        $sql = "
            SELECT
                d.ID,
                d.TieuDe,
                d.NganSach,
                d.HanNhanDeXuat,
                d.KyNang,
                u.HoTen AS TenKhachHang,
                (SELECT COUNT(*) FROM DeXuat dx WHERE dx.ID_DuAn = d.ID) AS SoDeXuat
            FROM DuAn d
            INNER JOIN NguoiDung u ON d.ID_KhachHang = u.ID
            WHERE {$whereSql}
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['ID'] = (int) $item['ID'];
            $item['NganSach'] = (int) $item['NganSach'];
            $item['SoDeXuat'] = (int) $item['SoDeXuat'];
        }

        return [
            'items'      => $items,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
        ];
    }
}