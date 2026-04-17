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
        /* Đảm bảo thứ tự hiển thị của các loại modal */
        #loginModal {
            z-index: 1075 !important;
        }
        #followDetailModal, #reportModal, #sharePostModal {
            z-index: 1065 !important;
        }

        /* Xử lý lớp nền (backdrop) khi mở chồng modal */
        /* Lớp nền thứ 2 (dành cho Follow/Share/Report) */
        .modal-backdrop.show ~ .modal-backdrop.show {
            z-index: 1060 !important;
        }
        /* Lớp nền thứ 3 (dành cho Login khi mở từ modal khác) */
        .modal-backdrop.show ~ .modal-backdrop.show ~ .modal-backdrop.show {
            z-index: 1070 !important;
        }
        /* Loại bỏ giới hạn 600px để tràn màn hình */
        body, html {
            -webkit-overflow-scrolling: touch;
        }
        .overflow-auto {
            scrollbar-width: none;
        }
        .overflow-auto::-webkit-scrollbar {
            display: none;
        }
    </style>
    <script>
        window.isLoggedIn = @json(auth()->check());
    </script>
    @if(Auth::check())
        <meta name="auth-user-id" content="{{ Auth::id() }}">
    @endif

</head>
<body>
<div class="d-flex flex-column flex-md-row ">
    <!-- Sidebar -->
    <nav class="bg-white border-end shadow-sm  d-flex flex-column p-3 flex-shrink-0 sidebar-hover"  style="height: 100vh; overflow: hidden;">
        <a class="navbar-brand fw-bold fs-4 mb-4 mt-2 d-flex align-items-center text-dark text-decoration-none" href="{{ route('home') }}" style="letter-spacing: -1px; padding-left: 0.2rem;">
            <img src="{{ asset('storage/'.'logo.png') }}" class=" fs-3 text-primary" style="max-width: 70px; text-align: center;"></i> 
            <span class="nav-text ms-2">GUNPLA SOCIAL</span>
        </a>
    <hr>
        <div class="flex-grow-1 overflow-auto hide-scrollbar d-flex justify-content-center" >
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
            <li class="nav-item">
                <a href="{{ route('conversations.index') }}" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light" style="gap: 5px;">
                    <i class="bi bi-chat-dots" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text flex-grow-1">Trò chuyện</span>
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
                <a href="#" onclick="event.preventDefault(); window.openNoti();" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
                    <i class="bi bi-heart" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text flex-grow-1">Thông báo</span>
                    <span id="global-noti-badge" class="badge bg-danger rounded-pill {{ $globalUnreadNotifications > 0 ? '' : 'd-none' }}" 
                          style="font-size: 0.8rem; transition: transform 0.2s ease-in-out;">
                        {{ $globalUnreadNotifications }}
                    </span>
                </a>
        </ul>
        @elseif(auth()->user()?->role === 'admin' || auth()->user()?->role === 'moderator')
    <ul class="nav nav-pills flex-column mb-auto gap-2">
    {{-- Dashboard --}}
    @if(auth()->user()?->role === 'admin')
    <li class="nav-item">
        <a href="{{route('admin.dashboard')}}"  
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-speedometer2" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Dashboard</span>
        </a>
    </li>
    @endif
     {{-- Quản lý bài viết --}}
    <li class="nav-item">
        <a href="{{route('admin.topics')}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-tags" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">chủ đề</span>
        </a>
    </li>
    {{-- Quản lý bài viết --}}
    <li class="nav-item">
        <a href="{{route('admin.posts')}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-file-earmark-text" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Bài viết</span>
        </a>
    </li>
    @if(auth()->user()?->role === 'admin')
    
    {{-- Quản lý user --}}
    <li class="nav-item">
        <a href="{{route('admin.users')}}"  
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-person-circle" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Người dùng</span>
        </a>
    </li>
    @endif  
        {{-- Quản lý bài viết --}}
    <li class="nav-item">
        <a href="{{route('admin.comments')}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-chat-left-text" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Bình luận</span>
        </a>
    </li>
        <li class="nav-item">
        <a href="{{route('admin.conversations')}}"  
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-people" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Hộp thoại</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{route('admin.messages')}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-chat-square-dots" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Tin nhắn</span>
        </a>
    </li>
                <li class="nav-item">
        <a href="{{route('admin.searchs')}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-clock-history" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text">Lịch sử tìm kiếm</span>
        </a>
    </li>
    {{-- Báo cáo --}}
    @php
        $totalReports = \App\Models\Report::where('status', 'pending')->count();
    @endphp
    <li class="nav-item">
        <a  href="{{route('admin.reports', 'pending')}}"
           class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
            <i class="bi bi-flag" style="min-width: 40px; text-align: center;"></i>
            <span class="nav-text flex-grow-1">Báo cáo</span>
            <span class="badge bg-danger rounded-pill {{ $totalReports > 0 ? '' : 'd-none' }}">
                {{ $totalReports }}
            </span>
        </a>
    </li>
</ul>
        @else
        <ul class="nav nav-pills flex-column mb-auto gap-2">
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light" style="gap: 5px;">
                    <i class="bi bi-house-door" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text">Trang chủ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light require-login" style="gap: 5px;">
                    <i class="bi bi-plus-square" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text">Tạo bài viết</span>
                </a>
            </li>
        </ul>
        @endif
        </div>
        <hr>
            {{-- Thông báo hệ thống --}}
            @if(auth()->user()?->role === 'admin' || auth()->user()?->role === 'moderator')
            @php
                $globalUnreadNotifications = 0;
                if(Auth::check()){
                    $globalUnreadNotifications = auth()->user()->notifications()->where('is_read', false)->count();
                }
            @endphp
                <a href="#" onclick="event.preventDefault(); window.openNoti();" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light">
                    <i class="bi bi-heart" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text flex-grow-1">Thông báo</span>
                    <span id="global-noti-badge" class="badge bg-danger rounded-pill {{ $globalUnreadNotifications > 0 ? '' : 'd-none' }}" 
                          style="font-size: 0.8rem; transition: transform 0.2s ease-in-out;">
                        {{ $globalUnreadNotifications }}
                    </span>
                </a>
            @endif
        <hr>
        <!-- Profile -->
          @if(auth()->user()?->role === 'user' )
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
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger">Đăng xuất</button>
                    </form>
                </li>
            </ul>
        </div>
        @elseif(auth()->user()?->role === 'admin' || auth()->user()?->role === 'moderator')
        <div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link text-danger fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light border-0 bg-transparent w-100 text-start" style="gap: 5px;">
                    <i class="bi bi-box-arrow-right" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text fw-bold">Đăng xuất</span>
                </button>
            </form>
        </div>
       @else
       
        <div class="mt-auto d-grid gap-2">
            <button class="mt-3 btn btn-outline-primary fw-bold open-login-modal">Đăng nhập</button>
        </div>
        @endif
    </nav>
    <!-- Main Content -->
    <div class="flex-grow-1 w-100 main-content">
            <form action="{{ route('search.result') }}" method="GET" class="search-wrapper search-form"  onsubmit="return this.q.value.trim() !== ''">
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
            <div id="suggestions" class="list-group mt-1 rounded-5 mt-2 mx-auto " style=" overscroll-behavior: contain; width:1020px; max-height: 400px; overflow-y: auto; "></div>
    </div>
</form>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div id="content-area" class="col-12 col-lg-10 col-xl-9"> 
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
<!-- Modal Chia sẻ bài viết -->
<div class="modal fade" id="sharePostModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header border-bottom-0 pb-0 justify-content-center position-relative pt-3">
                <h6 class="modal-title fw-bold">Chia sẻ</h6>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.7rem;"></button>
            </div>
            <div class="modal-body p-0 mt-2">
                <!-- Search bar -->
                <div class="px-3 mb-2">
                    <div class="input-group input-group-sm bg-light rounded-3 px-2 py-1 border">
                        <span class="input-group-text bg-transparent border-0 text-muted p-0 me-2"><i class="bi bi-search"></i></span>
                        <input type="text" id="shareUserSearch" class="form-control bg-transparent border-0 p-0" placeholder="Tìm kiếm..." style="box-shadow: none; font-size: 14px;">
                    </div>
                </div>
                <!-- User list container -->
                <div id="shareUserList" style="max-height: 380px; min-height: 200px; overflow-y: auto;" class="px-2">
                    <div class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top px-3 py-2 justify-content-start bg-white">
                <div class="d-flex flex-column w-100">
                    <button class="btn btn-link text-decoration-none text-dark p-0 d-flex align-items-center w-100 hover-bg-light rounded-2 py-2" id="copyPostLinkBtn" data-url="">
                        <div class="rounded-circle border d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: #f8f9fa;">
                            <i class="bi bi-link-45deg fs-4"></i>
                        </div>
                        <div class="d-flex flex-column align-items-start">
                            <span class="fw-bold small">Sao chép liên kết</span>
                            <span class="text-muted" style="font-size: 11px;" id="copyStatusText">Click để copy link bài viết</span>
                        </div>
                    </button>
                </div>
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
<!-- Modal chỉnh sửa bài viết -->
<div class="modal fade back-to" id="editPostModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1290px;">
        <div class="modal-content">
            <div class="modal-body p-0" id="editPostContent">
                <!-- Nội dung edit post sẽ load vào đây -->
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
<!-- Modal đăng nhập động -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-lg" id="loginModalContent" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-body p-0 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
            </div>
        </div>
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