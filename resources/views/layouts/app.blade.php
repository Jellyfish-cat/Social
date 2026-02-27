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
        /* Responsive cho mobile */
        @media (max-width: 768px) {
            .navbar { padding: 0.5rem 1rem; }
            .full-width-container { padding: 0; }
            .post-card { border-radius: 0; border-left: none; border-right: none; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid d-flex justify-content-between align-items-center">

        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}" style="letter-spacing: -1px;">
            GUNPLA SOCIAL MEDIA
        </a>

        <div class="d-flex align-items-center gap-4">
            <a href="{{ route('home') }}" class="text-dark"><i class="bi bi-house-door fs-4"></i></a>
            <a href="{{ route('posts.create') }}" class="text-dark"><i class="bi bi-plus-square fs-4"></i></a>
            <a href="#" class="text-dark"><i class="bi bi-chat-dots fs-4"></i></a>
            <a href="#" class="text-dark"><i class="bi bi-heart fs-4"></i></a>

            {{-- Profile Avatar --}}
            <div class="dropdown">
                <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default-avatar.png')) }}" 
                         class="avatar shadow-sm">
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Trang cá nhân</a></li>
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
        </div>

    </div>
</nav>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        {{-- Thay vì fix 600px, chúng ta dùng hệ thống Grid để nó co giãn --}}
        <div class="col-12 col-lg-10 col-xl-11"> 
             @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>