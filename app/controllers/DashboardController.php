<?php
// app/controllers/DashboardController.php
class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin();

        if ($user['vaitro'] === 'khachhang') {
            $byStatus   = Project::countByStatus($user['id']);
            $proposals  = Proposal::countByStatusForOwner($user['id']);
            $this->view('dashboard/customer', [
                'title'     => 'Bảng điều khiển',
                'active'    => 'dashboard',
                'user'      => $user,
                'byStatus'  => $byStatus,
                'totalProjects'  => array_sum($byStatus),
                'totalProposals' => array_sum($proposals),
                'pending'   => $proposals['cho_duyet'] ?? 0,
                'projects'  => Project::recentByOwner($user['id'], 5),
                'received'  => Proposal::recentForOwner($user['id'], 5),
            ]);
            return;
        }

        $byStatus = Proposal::countByStatus($user['id']);
        $this->view('dashboard/freelancer', [
            'title'    => 'Bảng điều khiển',
            'active'   => 'dashboard',
            'user'     => $user,
            'byStatus' => $byStatus,
            'total'    => array_sum($byStatus),
            'proposals' => Proposal::recentByFreelancer($user['id'], 6),
        ]);
    }
}
