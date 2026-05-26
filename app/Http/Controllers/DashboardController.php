<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $stats = $this->dashboardService->getDashboardStats();

        return view('admin.dashboard', [
            'labels7Days' => $stats['labels7Days'],
            'dataPosts7Days' => $stats['dataPosts7Days'],
            'labels6Months' => $stats['labels6Months'],
            'dataUsers6Months' => $stats['dataUsers6Months'],
            'interactionData' => $stats['interactionData'],
            'userStatusData' => $stats['userStatusData'],
            'engagementTrendData' => $stats['engagementTrendData'],
            'topPostsLabels' => $stats['topPostsLabels'],
            'topPostsData' => $stats['topPostsData'],
            'chatActivityData' => $stats['chatActivityData'],
            'contentDistributionData' => $stats['contentDistributionData'],
            'reportLabels' => $stats['reportLabels'],
            'reportData' => $stats['reportData'],
            'growthLabels' => $stats['growthLabels'],
            'userGrowth' => $stats['userGrowth'],
            'postGrowth' => $stats['postGrowth'],
            'peakActivityData' => $stats['peakActivityData'],
            'peakActivityLabels' => $stats['peakActivityLabels'],
            'userRolesData' => $stats['userRolesData'],
            'topActiveUsers' => $stats['topActiveUsers'],
            'reportStatusData' => $stats['reportStatusData'],
        ])->with([
            'totalUsersCount' => $stats['totalUsersCount'],
            'totalPostsCount' => $stats['totalPostsCount'],
            'totalCommentsCount' => $stats['totalCommentsCount'],
            'totalPendingReports' => $stats['totalPendingReports'],
        ]);
    }
}
