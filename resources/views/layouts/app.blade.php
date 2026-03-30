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
    <nav class="bg-white border-end shadow-sm  d-flex flex-column p-3 flex-shrink-0 sidebar-hover" style="overflow-y: auto; overflow-x: hidden;">
        <a class="navbar-brand fw-bold fs-4 mb-4 mt-2 d-flex align-items-center text-dark text-decoration-none" href="{{ route('home') }}" style="letter-spacing: -1px; padding-left: 0.2rem;">
            <img src="{{ asset('storage/'.'logo.png') }}" class=" fs-3 text-primary" style="min-width: 50px; text-align: center;"></i> 
            <span class="nav-text ms-2">GUNPLA SOCIAL</span>
        </a>
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
                    
                    {{-- Gắn thẻ CSS Badge ở đây - Ẩn đi nếu count = 0 --}}
                    <span id="global-msg-badge" class="badge msg-unread-count bg-danger rounded-pill {{ $globalUnreadMessages > 0 ? '' : 'd-none' }}" 
                          style="font-size: 0.8rem; transition: transform 0.2s ease-in-out;">
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
                <a href="{{ route('notifications.index') }}" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light" style="gap: 5px;">
                    <i class="bi bi-heart" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text flex-grow-1">Thông báo</span>
                    <span id="global-noti-badge" class="badge bg-danger rounded-pill {{ $globalUnreadNotifications > 0 ? '' : 'd-none' }}" 
                          style="font-size: 0.8rem; transition: transform 0.2s ease-in-out;">
                        {{ $globalUnreadNotifications }}
                    </span>
                </a>
            </li>
        </ul>
        <hr>
        
        <!-- Ngôn ngữ -->
        <div class="dropdown mb-3">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none px-2 py-2 rounded-3 hover-bg-light" data-bs-toggle="dropdown" style="gap: 5px;">
                <i class="bi bi-globe fs-5" style="min-width: 40px; text-align: center;"></i>
                <span class="nav-text">Ngôn ngữ</span>
            </a>
            <ul class="dropdown-menu shadow-sm">
                <li><a class="dropdown-item" href="{{ route('lang.switch','vi') }}">Tiếng Việt</a></li>
                <li><a class="dropdown-item" href="{{ route('lang.switch','en') }}">English</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="px-3 small text-muted">Current: {{ app()->getLocale() }}</li>
            </ul>
        </div>

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

<script type="module">
    document.addEventListener('DOMContentLoaded', function () {
        const userId = document.querySelector('meta[name="auth-user-id"]')?.getAttribute('content');
        if (userId && window.Echo) {
            window.Echo.private(`notifications.${userId}`)
                .listen('NotificationSent', (e) => {
                    const badge = document.getElementById('global-noti-badge');
                    if (badge) {
                        badge.classList.remove('d-none');
                        let count = parseInt(badge.innerText || 0);
                        badge.innerText = count + 1;
                        
                        // Hiệu ứng nảy thu hút sự chú ý
                        badge.style.transform = 'scale(1.5)';
                        setTimeout(() => badge.style.transform = 'scale(1)', 300);
                    }
                    
                    // Nếu người dùng đang mở trang danh sách thông báo, tự động reload để hiển thị thông báo mới
                    if (window.location.pathname === '/notifications') {
                        window.location.reload();
                    }
                });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<footer>
    <div id="loading-bar"></div>
</footer>
</html>