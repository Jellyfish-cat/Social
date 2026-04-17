@extends('layouts.app')

@section('content')
<div class="content-header mt-3">
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 fw-bold text-dark">Hệ Thống Phân Tích & Báo Cáo</h1>
                <p class="text-muted mb-0">Theo dõi hoạt động mạng xã hội thời gian thực</p>
            </div>
            <div class="col-sm-6 text-end">
                <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
                </button>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Tabs Navigation -->
        <div class="card shadow-sm border-0 rounded-4 px-3 pt-3 mb-4">
            <ul class="nav nav-pills gap-2 pb-3 flex-nowrap overflow-x-auto" id="dashboardTabs" role="tablist" style="white-space: nowrap; scrollbar-width: none;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-semibold px-4" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                        <i class="bi bi-grid-1x2 me-2"></i>Tổng quan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark hover-bg-light rounded-pill fw-semibold px-4" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab">
                        <i class="bi bi-people me-2"></i>Người dùng
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark hover-bg-light rounded-pill fw-semibold px-4" id="content-tab" data-bs-toggle="pill" data-bs-target="#content" type="button" role="tab">
                        <i class="bi bi-file-earmark-text me-2"></i>Nội dung
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark hover-bg-light rounded-pill fw-semibold px-4" id="engagement-tab" data-bs-toggle="pill" data-bs-target="#engagement" type="button" role="tab">
                        <i class="bi bi-heart me-2"></i>Tương tác
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark hover-bg-light rounded-pill fw-semibold px-4" id="moderation-tab" data-bs-toggle="pill" data-bs-target="#moderation" type="button" role="tab">
                        <i class="bi bi-shield-exclamation me-2"></i>Kiểm duyệt
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="dashboardTabsContent">
            <!-- TAB 1: TỔNG QUAN -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <!-- Summary Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm border-0 bg-primary text-white h-100 p-3 rounded-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-3"><i class="bi bi-people fs-3"></i></div>
                                <div class="ms-3">
                                    <h6 class="mb-1 opacity-75 small uppercase">Tổng User</h6>
                                    <h3 class="mb-0 fw-bold">{{ $totalUsersCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm border-0 bg-success text-white h-100 p-3 rounded-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-3"><i class="bi bi-file-earmark-text fs-3"></i></div>
                                <div class="ms-3">
                                    <h6 class="mb-1 opacity-75 small">Tổng Post</h6>
                                    <h3 class="mb-0 fw-bold">{{ $totalPostsCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm border-0 bg-warning text-dark h-100 p-3 rounded-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark bg-opacity-10 rounded-circle p-3"><i class="bi bi-chat-left-text fs-3"></i></div>
                                <div class="ms-3">
                                    <h6 class="mb-1 opacity-75 small">Tổng Comment</h6>
                                    <h3 class="mb-0 fw-bold">{{ $totalCommentsCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm border-0 bg-danger text-white h-100 p-3 rounded-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-3"><i class="bi bi-flag fs-3"></i></div>
                                <div class="ms-3">
                                    <h6 class="mb-1 opacity-75 small">Báo cáo chờ</h6>
                                    <h3 class="mb-0 fw-bold">{{ $totalPendingReports }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Tăng trưởng hệ thống</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="systemGrowthChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Bài viết (7 ngày)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="postsChartOverview" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: NGƯỜI DÙNG -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Đăng ký mới (6 tháng)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="usersChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Trạng thái (Active/Inactive)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="activeUsersChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-5 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Phân bố vai trò</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="roleDistributionChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Top người dùng hoạt động nhất</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">Thành viên</th>
                                                <th>Bài viết</th>
                                                <th>Bình luận</th>
                                                <th>Tổng cộng</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($topActiveUsers as $activeUser)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ asset('storage/' . ($activeUser->profile->avatar ?? 'default-avatar.png')) }}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                                        <div>
                                                            <div class="fw-bold">{{ $activeUser->profile->display_name ?? $activeUser->name }}</div>
                                                            <small class="text-muted">{{ $activeUser->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $activeUser->posts_count }}</td>
                                                <td>{{ $activeUser->comments_count }}</td>
                                                <td><span class="badge bg-primary rounded-pill">{{ $activeUser->posts_count + $activeUser->comments_count }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: NỘI DUNG -->
            <div class="tab-pane fade" id="content" role="tabpanel">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Bài viết hàng ngày (7 ngày)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="postsChartContent" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Top 5 bài viết nhiều Like</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="topPostsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Phân bố loại nội dung</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="contentTypeChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Hoạt động theo giờ (24h)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="peakHourChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: TƯƠNG TÁC -->
            <div class="tab-pane fade" id="engagement" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Xu hướng tương tác (Like+CMT+Share)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="engagementTrendsChart" height="350"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Tỷ lệ tương tác</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="interactionRatioChart" height="350"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Hoạt động Chat (7 ngày)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chatActivityChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: KIỂM DUYỆT -->
            <div class="tab-pane fade" id="moderation" role="tabpanel">
                <div class="row">
                    <div class="col-lg-7 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Báo cáo vi phạm theo phân loại</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="moderationReportsChart" height="350"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header border-0 bg-white pt-4">
                                <h5 class="fw-bold mb-0">Trạng thái xử lý báo cáo</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="reportStatusChart" height="350"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 border-start border-danger border-4 rounded-4 p-4 text-center">
                            <h3 class="fw-bold text-danger mb-0">{{ $totalPendingReports }}</h3>
                            <div class="text-muted small">Đang chờ xử lý</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 border-start border-success border-4 rounded-4 p-4 text-center">
                            <h3 class="fw-bold text-success mb-0">{{ $reportStatusData[1] }}</h3>
                            <div class="text-muted small">Đã giải quyết</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0 border-start border-secondary border-4 rounded-4 p-4 text-center">
                            <h3 class="fw-bold text-secondary mb-0">{{ $reportStatusData[2] }}</h3>
                            <div class="text-muted small">Đã bỏ qua</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ChartJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Overview & Content: Số bài viết theo ngày ---
        const postsData = {
            labels: @json($labels7Days),
            datasets: [{
                label: 'Bài viết',
                data: @json($dataPosts7Days),
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderColor: '#0d6efd',
                fill: true,
                tension: 0.4
            }]
        };
        new Chart(document.getElementById('postsChartOverview'), { type: 'line', data: postsData, options: { maintainAspectRatio: false } });
        new Chart(document.getElementById('postsChartContent'), { type: 'line', data: postsData, options: { maintainAspectRatio: false } });

        // --- 2. Bar Chart: User đăng ký (6 tháng) ---
        new Chart(document.getElementById('usersChart'), {
            type: 'bar',
            data: {
                labels: @json($labels6Months),
                datasets: [{
                    label: 'Đăng ký mới',
                    data: @json($dataUsers6Months),
                    backgroundColor: '#0dcaf0'
                }]
            },
            options: { maintainAspectRatio: false }
        });

        // --- 4. Pie Chart: Trạng thái người dùng ---
        new Chart(document.getElementById('activeUsersChart'), {
            type: 'pie',
            data: {
                labels: ['Đang hoạt động', 'Không hoạt động'],
                datasets: [{
                    data: @json($userStatusData),
                    backgroundColor: ['#198754', '#adb5bd']
                }]
            },
            options: { 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // --- 12. Role Distribution ---
        new Chart(document.getElementById('roleDistributionChart'), {
            type: 'bar',
            data: {
                labels: ['Admin', 'Moderator', 'User'],
                datasets: [{
                    label: 'Số lượng',
                    data: @json($userRolesData),
                    backgroundColor: ['#dc3545', '#ffc107', '#0d6efd']
                }]
            },
            options: { 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // --- 5. Engagement Trends ---
        new Chart(document.getElementById('engagementTrendsChart'), {
            type: 'line',
            data: {
                labels: @json($labels7Days),
                datasets: [{
                    label: 'Tổng tương tác',
                    data: @json($engagementTrendData),
                    borderColor: '#6610f2',
                    backgroundColor: 'rgba(102, 16, 242, 0.05)',
                    fill: true,
                    tension: 0.2
                }]
            },
            options: { maintainAspectRatio: false }
        });

        // --- 3. Interaction Ratio ---
        new Chart(document.getElementById('interactionRatioChart'), {
            type: 'doughnut',
            data: {
                labels: ['Thích', 'Bình luận', 'Chia sẻ', 'Tin nhắn'],
                datasets: [{
                    data: @json($interactionData),
                    backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#d63384']
                }]
            },
            options: { 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // --- 6. Top Content ---
        new Chart(document.getElementById('topPostsChart'), {
            type: 'bar',
            data: {
                labels: @json($topPostsLabels),
                datasets: [{
                    label: 'Số lượt yêu thích',
                    data: @json($topPostsData),
                    backgroundColor: '#20c997'
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // --- 7. Chat Activity ---
        new Chart(document.getElementById('chatActivityChart'), {
            type: 'line',
            data: {
                labels: @json($labels7Days),
                datasets: [{
                    label: 'Tin nhắn',
                    data: @json($chatActivityData),
                    backgroundColor: 'rgba(214, 51, 132, 0.1)',
                    borderColor: '#d63384',
                    fill: true
                }]
            },
            options: { maintainAspectRatio: false }
        });

        // --- 8. Content Distribution ---
        new Chart(document.getElementById('contentTypeChart'), {
            type: 'pie',
            data: {
                labels: ['Văn bản', 'Hình ảnh', 'Video'],
                datasets: [{
                    data: @json($contentDistributionData),
                    backgroundColor: ['#0d6efd', '#fd7e14', '#dc3545']
                }]
            },
            options: { 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // --- 9. Moderation Reports ---
        new Chart(document.getElementById('moderationReportsChart'), {
            type: 'bar',
            data: {
                labels: @json($reportLabels),
                datasets: [{
                    label: 'Số lượt báo cáo',
                    data: @json($reportData),
                    backgroundColor: '#dc3545'
                }]
            },
            options: { maintainAspectRatio: false }
        });

        // --- 14. Report Status Distribution ---
        new Chart(document.getElementById('reportStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Chờ xử lý', 'Đã xử lý', 'Đã bỏ qua'],
                datasets: [{
                    data: @json($reportStatusData),
                    backgroundColor: ['#ffc107', '#198754', '#6c757d']
                }]
            },
            options: { 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // --- 10. System Growth ---
        new Chart(document.getElementById('systemGrowthChart'), {
            type: 'line',
            data: {
                labels: @json($growthLabels),
                datasets: [
                    {
                        label: 'Tổng Users',
                        data: @json($userGrowth),
                        borderColor: '#0d6efd',
                        backgroundColor: '#0d6efd',
                        fill: false
                    },
                    {
                        label: 'Tổng Bài viết',
                        data: @json($postGrowth),
                        borderColor: '#198754',
                        backgroundColor: '#198754',
                        fill: false
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: { 
                    y: { type: 'linear', display: true, position: 'left' } 
                }
            }
        });

        // --- 11. Peak Activity Hour ---
        new Chart(document.getElementById('peakHourChart'), {
            type: 'line',
            data: {
                labels: @json($peakActivityLabels),
                datasets: [{
                    label: 'Lưu lượng hoạt động',
                    data: @json($peakActivityData),
                    borderColor: '#0dcaf0',
                    backgroundColor: 'rgba(13, 202, 240, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Fix chart rendering in hidden tabs
        document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(tabEl => {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                window.dispatchEvent(new Event('resize'));
            });
        });
    });
</script>
@endsection