<?php
// app/controllers/ProposalController.php
class ProposalController extends Controller
{
    /** Freelancer gửi đề xuất cho một dự án – AJAX, trả JSON. */
    public function store(): void
    {
        $this->requirePost();
        $user = $this->requireRole('freelancer');

        $duAnId  = (int) ($_POST['id_duan'] ?? 0);
        $chiPhi  = preg_replace('/[.,\s]/', '', (string) ($_POST['chiphi'] ?? ''));
        $thoiGian = trim((string) ($_POST['thoigian'] ?? ''));
        $noiDung = trim((string) ($_POST['noidung'] ?? ''));

        $errors = [];
        if (!ctype_digit($chiPhi) || (int) $chiPhi <= 0 || strlen($chiPhi) > 12) {
            $errors[] = 'Chi phí đề xuất phải là số dương (tối đa 12 chữ số).';
        }
        if (!ctype_digit($thoiGian) || (int) $thoiGian < 1 || (int) $thoiGian > 365) {
            $errors[] = 'Thời gian thực hiện phải từ 1 đến 365 ngày.';
        }
        if (mb_strlen($noiDung) < 10)   $errors[] = 'Nội dung đề xuất cần ít nhất 10 ký tự.';
        if (mb_strlen($noiDung) > 3000) $errors[] = 'Nội dung đề xuất tối đa 3000 ký tự.';
        if ($errors) {
            $this->fail('Vui lòng kiểm tra lại đề xuất của bạn.', $errors);
        }

        $project = $duAnId > 0 ? Project::find($duAnId) : null;
        if (!$project) {
            $this->fail('Dự án không tồn tại.', [], 404);
        }
        if ($project['TrangThai'] !== 'mo' || $project['HanNhanDeXuat'] < date('Y-m-d')) {
            $this->fail('Dự án này đã đóng nhận đề xuất.', [], 409);
        }

        try {
            Proposal::create($duAnId, $user['id'], ltrim($chiPhi, '0'), (int) $thoiGian, $noiDung);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {        // vi phạm UNIQUE (ID_DuAn, ID_Freelancer)
                $this->fail('Bạn đã gửi đề xuất cho dự án này rồi.', [], 409, ['project_id' => $duAnId]);
            }
            throw $e;
        }

        $this->ok('Đã gửi đề xuất thành công!', [
            'project_id' => $duAnId,
            'count'      => Proposal::countForProject($duAnId),
        ]);
    }
}
