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



<div class="container-fluid py-4">
    <div class="row justify-content-center">
        {{-- Thay vì fix 600px, chúng ta dùng hệ thống Grid để nó co giãn --}}
             @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>