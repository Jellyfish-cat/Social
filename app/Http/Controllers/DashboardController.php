<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\LikePost;
use App\Models\Message;
use App\Models\Report;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Line Chart: Posts per day (Last 7 days)
        $postsDaily = Post::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        $labels7Days = [];
        $dataPosts7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $displayDate = now()->subDays($i)->format('d/m');
            $labels7Days[] = $displayDate;
            $dataPosts7Days[] = $postsDaily[$date] ?? 0;
        }

        // 2. Bar Chart: User registrations (Last 6 months)
        $registrationsMonthly = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->startOfMonth()->subMonths(5))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');

        $labels6Months = [];
        $dataUsers6Months = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthKey = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $monthLabel = 'T' . now()->startOfMonth()->subMonths($i)->format('n');
            $labels6Months[] = $monthLabel;
            $dataUsers6Months[] = $registrationsMonthly[$monthKey] ?? 0;
        }

        // 3. Pie Chart: Interaction Ratio
        $likesCount = LikePost::count();
        $commentsCount = Comment::count();
        $sharesCount = Post::whereNotNull('shared_post_id')->count();
        $messagesCount = Message::count();
        $interactionData = [$likesCount, $commentsCount, $sharesCount, $messagesCount];

        // 4. Pie Chart: Active vs Inactive (Active = logged/updated in last 7 days)
        $activeUsers = User::where('updated_at', '>=', now()->subDays(7))->count();
        $totalUsers = User::count();
        $inactiveUsers = max(0, $totalUsers - $activeUsers);
        $userStatusData = [$activeUsers, $inactiveUsers];

        // 5. Line Chart: Engagement Trends (Like + CMT + Share per day)
        $likesDaily = LikePost::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')->get()->pluck('count', 'date');
        $cmtsDaily = Comment::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')->get()->pluck('count', 'date');
        $sharesDaily = Post::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereNotNull('shared_post_id')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')->get()->pluck('count', 'date');

        $engagementTrendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $total = ($likesDaily[$date] ?? 0) + ($cmtsDaily[$date] ?? 0) + ($sharesDaily[$date] ?? 0);
            $engagementTrendData[] = $total;
        }

        // 6. Horizontal Bar: Top 5 Posts by Like
        $topPosts = Post::withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->take(5)
            ->get();
        $topPostsLabels = $topPosts->map(fn($p) => substr($p->content, 0, 20) . '...')->toArray();
        $topPostsData = $topPosts->pluck('likes_count')->toArray();

        // 7. Area Chart: Chat Activity (Messages per day)
        $chatDaily = Message::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
        $chatActivityData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chatActivityData[] = $chatDaily[$date] ?? 0;
        }

        // 8. Pie Chart: Content Distribution
        $imagePosts = Media::where('type', 'image')->distinct('post_id')->count();
        $videoPosts = Media::where('type', 'video')->distinct('post_id')->count();
        $totalPosts = Post::count();
        $textPosts = max(0, $totalPosts - ($imagePosts + $videoPosts));
        $contentDistributionData = [$textPosts, $imagePosts, $videoPosts];

        // 9. Bar Chart: Reports by Category
        $reportsByCategory = Report::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category');
        $reportLabels = ['Spam', 'Toxic/Hate', 'Spam Link', 'Fake News'];
        $reportData = [];
        foreach ($reportLabels as $cat) {
            $reportData[] = $reportsByCategory[$cat] ?? 0;
        }

        // 10. Line Chart: System Growth (Cumulative)
        $growthLabels = [];
        $userGrowth = [];
        $postGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subWeeks($i)->endOfWeek();
            $growthLabels[] = 'Tuần ' . (6 - $i);
            $userGrowth[] = User::where('created_at', '<=', $date)->count();
            $postGrowth[] = Post::where('created_at', '<=', $date)->count();
        }

        // 11. Line Chart: Peak Activity by Hour (0h-23h)
        // Aggregate posts, comments, likes by hour
        $postsByHour = Post::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')->get()->pluck('count', 'hour');
        $likesByHour = LikePost::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')->get()->pluck('count', 'hour');
        $commentsByHour = Comment::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')->get()->pluck('count', 'hour');

        $peakActivityData = [];
        $peakActivityLabels = [];
        for ($h = 0; $h < 24; $h++) {
            $peakActivityLabels[] = $h . 'h';
            $peakActivityData[] = ($postsByHour[$h] ?? 0) + ($likesByHour[$h] ?? 0) + ($commentsByHour[$h] ?? 0);
        }

        // 12. Role Distribution
        $rolesDataData = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')->get()->pluck('count', 'role');
        $userRolesData = [
            $rolesDataData['admin'] ?? 0,
            $rolesDataData['moderator'] ?? 0,
            $rolesDataData['user'] ?? 0
        ];

        // 13. Top 5 Active Users (posts + comments)
        $topActiveUsers = User::with(['profile'])->withCount(['posts', 'comments'])
            ->get()
            ->sortByDesc(fn($u) => $u->posts_count + $u->comments_count)
            ->take(5);

        // 14. Report Status Distribution
        $reportStatusCounts = Report::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->get()->pluck('count', 'status');
        $reportStatusData = [
            $reportStatusCounts['pending'] ?? 0,
            $reportStatusCounts['resolved'] ?? 0,
            $reportStatusCounts['dismissed'] ?? 0
        ];

        return view('admin.dashboard', compact(
            'labels7Days', 'dataPosts7Days',
            'labels6Months', 'dataUsers6Months',
            'interactionData',
            'userStatusData',
            'engagementTrendData',
            'topPostsLabels', 'topPostsData',
            'chatActivityData',
            'contentDistributionData',
            'reportLabels', 'reportData',
            'growthLabels', 'userGrowth', 'postGrowth',
            'peakActivityData', 'peakActivityLabels',
            'userRolesData', 'topActiveUsers', 'reportStatusData'
        ))->with([
            'totalUsersCount' => $totalUsers,
            'totalPostsCount' => $totalPosts,
            'totalCommentsCount' => Comment::count(),
            'totalPendingReports' => Report::where('status', 'pending')->count(),
        ]);
    }
}
