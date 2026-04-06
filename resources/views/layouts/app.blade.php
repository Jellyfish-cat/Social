<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Social App') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>


    <style>

        /* Loại bỏ giới hạn 600px để tràn màn hình */
        
    </style>
        @if(Auth::check())
        <meta name="auth-user-id" content="{{ Auth::id() }}">
    @endif

</head>
<body>
<div class="d-flex flex-column flex-md-row">
    <!-- Sidebar -->
    <nav class="bg-white border-end shadow-sm  d-flex flex-column p-3 flex-shrink-0 sidebar-hover"  style="height: 100vh; overflow: hidden;">
        <a class="navbar-brand fw-bold fs-4 mb-4 mt-2 d-flex align-items-center text-dark text-decoration-none" href="{{ route('home') }}" style="letter-spacing: -1px; padding-left: 0.2rem;">
            <img src="{{ asset('storage/'.'logo.png') }}" class=" fs-3 text-primary" style="max-width: 70px; text-align: center;"></i> 
            <span class="nav-text ms-2">GUNPLA SOCIAL</span>
        </a>
    <hr>
        <div class="flex-grow-1 overflow-auto hide-scrollbar d-flex justify-content-center" >
        @if(auth::user()?->role === "user")
        <ul class="nav nav-pills flex-column mb-auto gap-2">
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light" style="gap: 5px;">
                    <i class="bi bi-house-door" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text">Trang chủ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('posts.create') }}" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light" style="gap: 5px;">
                    <i class="bi bi-plus-square" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text">Tạo bài viết</span>
                </a>
            </li>
                        @php
                $globalUnreadMessages = 0;
                if(Auth::check()){
                    $globalUnreadMessages = \App\Models\Message::where('sender_id', '!=', auth()->id())
                        ->whereNull('read_at')
                        ->whereHas('conversation.users', function($q) {
                            $q->where('users.id', auth()->id());
                        })->count();
                }
            @endphp
            <li class="nav-item">
                <a href="{{ route('conversations.index') }}" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light" style="gap: 5px;">
                    <i class="bi bi-chat-dots" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text flex-grow-1">Nhắn tin</span>
                    <span id="global-mess-badge" class="badge bg-danger rounded-pill {{ $globalUnreadMessages > 0 ? '' : 'd-none' }}" style="font-size: 0.8rem; transition: transform 0.2s;">
                        {{ $globalUnreadMessages }}
                    </span>
                </a>
            </li>
            @php
                $globalUnreadNotifications = 0;
                if(Auth::check()){
                    $globalUnreadNotifications = auth()->user()->notifications()->where('is_read', false)->count();
                }
            @endphp
            <li class="nav-item">
                <a href="#" onclick="event.preventDefault(); window.openNoti();" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
                    <i class="bi bi-heart" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text flex-grow-1">Thông báo</span>
                    <span id="global-noti-badge" class="badge bg-danger rounded-pill {{ $globalUnreadNotifications > 0 ? '' : 'd-none' }}" 
                          style="font-size: 0.8rem; transition: transform 0.2s ease-in-out;">
                        {{ $globalUnreadNotifications }}
                    </span>
                </a>
            </li>
        </ul>
        @elseif(auth()->user()?->role === 'admin')
    <ul class="nav nav-pills flex-column mb-auto gap-2">
    {{-- Dashboard --}}
    <li class="nav-item">
        <a 
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-speedometer2" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Dashboard</span>
        </a>
    </li>
     {{-- Quản lý bài viết --}}
    <li class="nav-item">
        <a href="{{route("admin.topics")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-tags" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">chủ đề</span>
        </a>
    </li>
    {{-- Quản lý bài viết --}}
    <li class="nav-item">
        <a href="{{route("admin.posts")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-file-earmark-text" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Bài viết</span>
        </a>
    </li>
    {{-- Quản lý user --}}
    <li class="nav-item">
        <a href="{{route("admin.users")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-person-circle" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Người dùng</span>
        </a>
    </li>
        {{-- Quản lý bài viết --}}
    <li class="nav-item">
        <a href="{{route("admin.comments")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-chat-left-text" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Bình luận</span>
        </a>
    </li>
        <li class="nav-item">
        <a href="{{route("admin.conversations")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-people" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Hộp thoại</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{route("admin.messages")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-chat-dots" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Tin nhắn</span>
        </a>
    </li>
                <li class="nav-item">
        <a href="{{route("admin.searchs")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-clock-history" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Lịch sử tìm kiếm</span>
        </a>
    </li>
    {{-- Thông báo hệ thống --}}
    @php
        $adminNotifications = auth()->user()->notifications()->where('is_read', false)->count();
    @endphp
    <li class="nav-item">
        <a href="#" 
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-bell" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text flex-grow-1 ">Thông báo </span>
            <span class="badge bg-danger rounded-pill {{ $adminNotifications > 0 ? '' : 'd-none' }}">
                {{ $adminNotifications }}
            </span>
        </a>
    </li>
    {{-- Báo cáo --}}
    @php
        $totalReports = \App\Models\Report::where('status', 'pending')->count();
    @endphp
    <li class="nav-item">
        <a  href="{{route("admin.reports", "pending")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-flag" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text flex-grow-1">Báo cáo</span>
            <span class="badge bg-danger rounded-pill {{ $totalReports > 0 ? '' : 'd-none' }}">
                {{ $totalReports }}
            </span>
        </a>
    </li>
        {{-- đã xử lý --}}
    @php
        $totalReports = \App\Models\Report::where('status', 'resolved')->count();
    @endphp
    <li class="nav-item">
        <a  href="{{route("admin.reports", "resolved")}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-clipboard-check" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text flex-grow-1">Đã xử lý</span>
            <span class="badge bg-danger rounded-pill {{ $totalReports > 0 ? '' : 'd-none' }}">
                {{ $totalReports }}
            </span>
        </a>
    </li>
    

</ul>
        @endif
        </div>
        <hr>
        <!-- Profile -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none px-2 py-2 rounded-3 hover-bg-light" data-bs-toggle="dropdown" aria-expanded="false" style="gap: 5px;">
                <div style="min-width: 40px; text-align: center; display: inline-block;">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default-avatar.png')) }}" 
                         class="avatar shadow-sm mx-auto" style="display: block; width: 32px; height: 32px; border-radius: 50%;">
                </div>
                <strong class="text-truncate nav-text" style="max-width: 150px;">{{ auth()->user()->profile->display_name ?? 'Cá nhân' }}</strong>
            </a>
            <ul class="dropdown-menu shadow-sm text-small">
                <li><a class="dropdown-item" href="{{ route('profile.detail', Auth::id()) }}">Trang cá nhân</a></li>
                <li><a class="dropdown-item" href="#">Cài đặt</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger">Đăng xuất</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
    <!-- Main Content -->
    <div class="flex-grow-1 w-100 main-content">
            <form action="{{ route('search.result') }}" method="GET" class="search-wrapper search-form">
    <div class="search-wrapper">
        <nav class="search-navbar shadow-sm rounded-5">
            <div class="search-input-wrapper">
                <button type="button" class="btn-Search  search-icon">
                    <i class="bi bi-search"></i>
                </button>
                <input autocomplete="off" type="text" name="q" 
                class="search-input"placeholder="Tìm kiếm người dùng, bài viết, hashtag..." aria-label="Tìm kiếm">
                <button type="button" class="btn-cancel-Search text-muted me-2 search-icon-end">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
        </nav>
            <div id="suggestions" class="list-group mt-1 rounded-5 mt-2 mx-auto " style="width:1020px"></div>
    </div>
</form>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9"> 
                     @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal xem chi tiết bài viết -->
<div class="modal fade back-to" id="postDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1290px;">
        <div class="modal-content">
            <div class="modal-body p-0" id="postDetailContent">
                <!-- Nội dung chi tiết post sẽ load vào đây -->
            </div>
        </div>
    </div>
</div> 
<!-- Modal xem chi tiết người theo dõi -->
<div class="modal fade back-to-follow" id="followDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-body p-0 border rounded" id="followDetailContent">
                <!-- Nội dung chi tiết follow sẽ load vào đây -->
            </div>
        </div>
    </div>
</div>
<!-- Modal xem chi tiết người theo dõi -->
<div class="modal fade back-to-follow" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-body p-0" id="reportContent">
                <!-- Nội dung chi tiết report sẽ load vào đây -->
            </div>
        </div>
    </div>
</div> 
<!-- Overlay -->
<div id="notiOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" 
     style="background: rgba(0,0,0,0.3); z-index: 1040;"></div>

<!-- Notification Panel -->
<div id="notiPanel" 
     class="position-fixed top-0 start-0 h-100 bg-white shadow-lg"
     style="width: 380px; transform: translateX(-100%); transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); z-index: 1050; border-right: 1px solid #edf2f7; display: flex; flex-direction: column;">

    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center flex-shrink-0">
        <h4 class="mb-0 fw-bold">Thông báo</h4>
        <button class="btn btn-sm btn-light rounded-circle" onclick="window.closeNoti()" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-x-lg"></i></button>
    </div>

    <div id="notiContent" class="flex-grow-1" style="overflow-y: auto; overflow-x: hidden;">
        {{-- load notifications --}}
    </div>
</div>
<script type="module">
    
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<footer>
    <div id="loading-bar"></div>
</footer>
</html>