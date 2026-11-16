<?php
require_once BASE_PATH . '/app/models/ProjectSearch.php';

class ProjectController extends Controller
{
    public function index()
    {
        $this->requireRole('freelancer');
        $this->view('projects/index', ['title' => 'Tìm dự án']);
    }

    /**
     * GET project/search (JSON) - Dành cho freelancer
     */
    public function search()
    {
        $user = current_user();
        if (!$user) {
            $this->json([
                'success'  => false,
                'message'  => 'Bạn cần đăng nhập.',
                'redirect' => url('auth/login')
            ], 401);
        }

        if (($user['role'] ?? '') !== 'freelancer') {
            $this->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này.'
            ], 403);
        }

        $q      = trim((string) ($_GET['q'] ?? ''));
        $minRaw = trim((string) ($_GET['min'] ?? ''));
        $maxRaw = trim((string) ($_GET['max'] ?? ''));
        $kynang = trim((string) ($_GET['kynang'] ?? ''));
        $sort   = trim((string) ($_GET['sort'] ?? 'newest'));
        $page   = max(1, (int) ($_GET['page'] ?? 1));

        $errors = [];
        $min = null;
        $max = null;

        if ($minRaw !== '') {
            $minVal = filter_var($minRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($minVal === false) {
                $errors['min'] = 'Ngân sách tối thiểu phải là số nguyên không âm.';
            } else {
                $min = $minVal;
            }
        }

        if ($maxRaw !== '') {
            $maxVal = filter_var($maxRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($maxVal === false) {
                $errors['max'] = 'Ngân sách tối đa phải là số nguyên không âm.';
            } else {
                $max = $maxVal;
            }
        }

        if ($min !== null && $max !== null && $min > $max) {
            $errors['max'] = 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.';
        }

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin.',
                'errors'  => $errors
            ], 422);
        }

        $result = ProjectSearch::search([
            'q'      => $q,
            'min'    => $min,
            'max'    => $max,
            'kynang' => $kynang,
            'sort'   => $sort,
        ], $page, 10);

        $this->json([
            'success'    => true,
            'items'      => $result['items'],
            'page'       => $result['page'],
            'totalPages' => $result['totalPages'],
            'total'      => $result['total'],
        ], 200);
    }

    public function mine()   { $this->view('projects/mine'); }
    public function create() { $this->view('projects/create'); }
    public function show()   { $this->view('projects/show'); }
    public function edit()   { $this->view('projects/edit'); }
}