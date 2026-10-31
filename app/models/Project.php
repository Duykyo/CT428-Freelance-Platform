<?php
// app/models/Project.php
class Project
{
    private const SORTS = [
        'moi_nhat' => 'p.NgayTao DESC',
        'cao_thap' => 'p.NganSach DESC',
        'thap_cao' => 'p.NganSach ASC',
        'sap_het'  => 'p.HanNhanDeXuat ASC',
    ];

    /** Câu SELECT chung. Khi có $withUser, thêm cột DaGuiDeXuat (người dùng đó đã gửi đề xuất chưa). */
    private static function selectSql(bool $withUser = false): string
    {
        return 'SELECT p.ID, p.TieuDe, p.MoTa, p.NganSach, p.HanNhanDeXuat, p.TrangThai, p.NgayTao,
                       u.HoTen AS TenKhachHang,
                       (SELECT COUNT(*) FROM DeXuat d WHERE d.ID_DuAn = p.ID) AS SoDeXuat'
            . ($withUser ? ', (SELECT COUNT(*) FROM DeXuat d2 WHERE d2.ID_DuAn = p.ID AND d2.ID_Freelancer = :uid) AS DaGuiDeXuat' : '')
            . ' FROM DuAn p JOIN NguoiDung u ON u.ID = p.ID_KhachHang';
    }

    public static function find(int $id): ?array
    {
        $st = Database::get()->prepare('SELECT * FROM DuAn WHERE ID = :id LIMIT 1');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    /** Thêm dự án mới (trạng thái mặc định 'mo'). Trả về ID. */
    public static function create(int $khachHangId, string $tieuDe, string $moTa, string $nganSach, string $han, ?string $kyNang): int
    {
        $st = Database::get()->prepare(
            'INSERT INTO DuAn (TieuDe, MoTa, NganSach, HanNhanDeXuat, ID_KhachHang, KyNang)
             VALUES (:tieude, :mota, :ngansach, :han, :kh, :kynang)'
        );
        $st->execute([
            ':tieude' => $tieuDe, ':mota' => $moTa, ':ngansach' => $nganSach,
            ':han' => $han, ':kh' => $khachHangId, ':kynang' => $kyNang,
        ]);
        return (int) Database::get()->lastInsertId();
    }

    /** Các dự án mới nhất đang mở (dùng cho trang chủ). */
    public static function latest(int $limit = 3): array
    {
        $st = Database::get()->prepare(
            self::selectSql() . " WHERE p.TrangThai = 'mo' ORDER BY p.NgayTao DESC LIMIT " . (int) $limit
        );
        $st->execute();
        return $st->fetchAll();
    }

    /**
     * Tìm kiếm + lọc + sắp xếp + phân trang.
     * $f: q, status[], min, max, han (số ngày), sort, page
     * @return array{0: array, 1: int} [danh sách, tổng số bản ghi]
     */
    public static function search(array $f, int $perPage, ?int $userId = null): array
    {
        $where  = [];
        $params = [];

        if ($f['q'] !== '') {
            $where[] = '(p.TieuDe LIKE :q OR p.MoTa LIKE :q)';
            $params[':q'] = '%' . addcslashes($f['q'], '%_\\') . '%';
        }
        if ($f['status']) {
            $marks = [];
            foreach (array_values($f['status']) as $i => $s) {
                $marks[] = ":st$i";
                $params[":st$i"] = $s;
            }
            $where[] = 'p.TrangThai IN (' . implode(',', $marks) . ')';
        }
        if ($f['min'] !== null) {
            $where[] = 'p.NganSach >= :min';
            $params[':min'] = $f['min'];
        }
        if ($f['max'] !== null) {
            $where[] = 'p.NganSach <= :max';
            $params[':max'] = $f['max'];
        }
        if ($f['han'] > 0) {
            $where[] = 'p.HanNhanDeXuat BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ' . (int) $f['han'] . ' DAY)';
        }
        $w = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $db = Database::get();

        $count = $db->prepare('SELECT COUNT(*) FROM DuAn p' . $w);
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $order  = self::SORTS[$f['sort']] ?? self::SORTS['moi_nhat'];
        $offset = max(0, ($f['page'] - 1) * $perPage);
        $st = $db->prepare(
            self::selectSql($userId !== null) . $w . " ORDER BY $order LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        if ($userId !== null) {
            $params[':uid'] = $userId;
        }
        $st->execute($params);

        return [$st->fetchAll(), $total];
    }

    // ---------- Dashboard khách hàng ----------

    /** @return array<string,int> số dự án theo trạng thái của một khách hàng */
    public static function countByStatus(int $khachHangId): array
    {
        $st = Database::get()->prepare('SELECT TrangThai, COUNT(*) AS c FROM DuAn WHERE ID_KhachHang = :id GROUP BY TrangThai');
        $st->execute([':id' => $khachHangId]);
        return array_map('intval', array_column($st->fetchAll(), 'c', 'TrangThai'));
    }

    public static function recentByOwner(int $khachHangId, int $limit = 5): array
    {
        $st = Database::get()->prepare(
            self::selectSql() . ' WHERE p.ID_KhachHang = :id ORDER BY p.NgayTao DESC LIMIT ' . (int) $limit
        );
        $st->execute([':id' => $khachHangId]);
        return $st->fetchAll();
    }
}
