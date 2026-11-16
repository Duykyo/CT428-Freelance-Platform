<?php

class Review
{
    /**
     * Tính điểm đánh giá trung bình và tổng số lượt của một người dùng
     */
    public static function getUserRatingSummary(int $userId): array
    {
        $db = Database::get();
        $sql = "
            SELECT
                COUNT(*) AS SoLuot,
                ROUND(AVG(Diem), 1) AS DiemTB
            FROM DanhGia
            WHERE ID_NguoiDuocDanhGia = :uid
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();

        $soLuot = (int) ($row['SoLuot'] ?? 0);
        return [
            'DiemTB' => $soLuot > 0 ? (float) $row['DiemTB'] : null,
            'SoLuot' => $soLuot,
        ];
    }

    /**
     * Kiểm tra người dùng đã đánh giá dự án này chưa
     */
    public static function hasReviewed(int $projectId, int $reviewerId): bool
    {
        $db = Database::get();
        $sql = "SELECT 1 FROM DanhGia WHERE ID_DuAn = :duan AND ID_NguoiDanhGia = :uid LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':duan' => $projectId,
            ':uid'  => $reviewerId,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Lấy thông tin dự án và ID freelancer được chọn để kiểm tra quyền đánh giá
     */
    public static function getProjectReviewContext(int $projectId): ?array
    {
        $db = Database::get();
        $sql = "
            SELECT
                d.ID,
                d.ID_KhachHang,
                d.TrangThai,
                (
                    SELECT dx.ID_Freelancer
                    FROM DeXuat dx
                    WHERE dx.ID_DuAn = d.ID AND dx.TrangThai = 'duoc_chon'
                    LIMIT 1
                ) AS ID_FreelancerDuocChon
            FROM DuAn d
            WHERE d.ID = :id
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Lưu đánh giá mới
     */
    public static function create(int $projectId, int $reviewerId, int $targetUserId, int $diem, ?string $nhanXet): bool
    {
        $db = Database::get();
        $sql = "
            INSERT INTO DanhGia (ID_DuAn, ID_NguoiDanhGia, ID_NguoiDuocDanhGia, Diem, NhanXet)
            VALUES (:duan, :nguoidanhgia, :nguoiduocdanhgia, :diem, :nhanxet)
        ";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':duan'             => $projectId,
            ':nguoidanhgia'     => $reviewerId,
            ':nguoiduocdanhgia' => $targetUserId,
            ':diem'             => $diem,
            ':nhanxet'          => ($nhanXet !== null && $nhanXet !== '') ? $nhanXet : null,
        ]);
    }
}