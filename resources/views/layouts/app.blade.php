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
    <style>
        body {
            background-color: #fafafa;
            overflow-x: hidden;
        }
        /* Loại bỏ giới hạn 600px để tràn màn hình */
        .full-width-container {
            width: 100%;
            padding-left: 20px;
            padding-right: 20px;
        }
        .navbar {
            padding: 0.5rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .post-card {
            border: 1px solid #dbdbdb;
            background: white;
            margin-bottom: 2rem;
            border-radius: 8px;
        }
        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        /* Media hiển thị to và rõ */
        .post-image {
            width: 100%;
            max-height: 80vh; /* Giới hạn chiều cao bằng 80% màn hình để không phải cuộn quá nhiều */
            object-fit: contain; /* Giữ nguyên tỉ lệ ảnh không bị cắt */
            background-color: #000;
        }
        #loading-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            height: 4px;
            width: 0%;
            background: linear-gradient(90deg, #0d6efd, #0dcaf0);
            z-index: 9999;
            transition: width 0.3s ease;
        }
        /* Responsive cho mobile */
        @media (max-width: 768px) {
            .navbar { padding: 0.5rem 1rem; }
            .full-width-container { padding: 0; }
            .post-card { border-radius: 0; border-left: none; border-right: none; }
        }

        /* Sidebar Hover Effect */
        .sidebar-hover {
            position: fixed;   /* Cố định Sidebar hoàn toàn */
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;     /* Nổi lên trên nội dung khác */
            width: 90px;
            transition: width 0.3s ease !important;
            overflow-x: hidden;
            white-space: nowrap;
        }
        .sidebar-hover:hover {
            width: 230px;
        }

        /* Chuyển động content và navbar khi hover sidebar */
        .main-content {
            margin-left: 80px;
            transition: margin-left 0.3s ease cubic-bezier(0.19, 1, 0.22, 1) !important;
        }
        .sidebar-hover:hover ~ .main-content {
            margin-left: 230px;
        }
        .sidebar-hover:hover ~ .main-content .search-navbar {
            margin-left: 50px;
            margin-right: 50px;
        }

        .sidebar-hover .nav-text {
            display: inline-block;
            opacity: 0;
            transition: opacity 0.2s ease;
            vertical-align: middle;
            font-size: 16px;
        }
        .sidebar-hover:hover .nav-text {
            opacity: 1;
            transition-delay: 0.1s;
        }


        .sidebar-hover:not(:hover) .dropdown-menu {
            display: none ;
        }
        .hover-bg-light:hover {
            background-color: #f0f2f5;
        }
        .nav-item:hover{
            transform: scale(1.05);
        }
        .dropdown:hover{
             transform: scale(1.05);
        }

        /* Search Navbar Styles */
        .search-navbar {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #efefef;
            margin-left: 150px;
            margin-right: 150px;
            padding: 12px 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }
        .search-input-wrapper {
            position: relative;
            max-width: 9    00px;
            width: 100%;
        }
        .search-input-wrapper .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #8e8e8e;
            font-size: 1rem;
        }
        .search-input-wrapper input {
            background-color: #f1f2f5;
            border: 1px solid transparent;
            border-radius: 50px;
            padding: 10px 20px 10px 48px;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.2s ease;
        }
        .search-input-wrapper input:focus {
            background-color: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            outline: none;
        }
        .search-input-wrapper input::placeholder {
            color: #8e8e8e;
        }
    </style>
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
            <li class="nav-item">
                <a href="#" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light" style="gap: 5px;">
                    <i class="bi bi-chat-dots" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text">Nhắn tin</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-dark fs-5 d-flex align-items-center px-2 py-2 rounded-3 hover-bg-light" style="gap: 5px;">
                    <i class="bi bi-heart" style="min-width: 40px; text-align: center;"></i>
                    <span class="nav-text">Thông báo</span>
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
        
        <!-- Search Navbar -->
        <nav class="search-navbar shadow-sm rounded" >
            <div class="search-input-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" placeholder="Tìm kiếm người dùng, bài viết, hashtag..." aria-label="Tìm kiếm">
            </div>
        </nav>

        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9"> 
                     @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<footer>
    <div id="loading-bar"></div>
</footer>
</html>