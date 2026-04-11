@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="header-edit mb-5 text-center">
                <h2 class="fw-bold display-6">Cài đặt tài khoản</h2>
                <p class="text-muted">Quản lý thông tin hồ sơ và bảo mật của bạn</p>
            </div>

            <div class="row g-4">
                {{-- Sidebar Navigation --}}
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 100px; border-radius: 15px;">
                        <div class="card-body p-3">
                            <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <button class="nav-link active mb-2 text-start p-3 profile-nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab">
                                    <i class="bi bi-person-circle me-2"></i> Hồ sơ cá nhân
                                </button>
                                <button class="nav-link mb-2 text-start p-3 profile-nav-link" id="v-pills-password-tab" data-bs-toggle="pill" data-bs-target="#v-pills-password" type="button" role="tab">
                                    <i class="bi bi-shield-lock me-2"></i> Mật khẩu & Bảo mật
                                </button>
                                <button class="nav-link text-start text-danger p-3 profile-nav-link" id="v-pills-delete-tab" data-bs-toggle="pill" data-bs-target="#v-pills-delete" type="button" role="tab">
                                    <i class="bi bi-trash me-2"></i> Xóa tài khoản
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Content Panels --}}
                <div class="col-md-8">
                    <div class="tab-content" id="v-pills-tabContent">
                        {{-- Profile Information --}}
                        <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel">
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                                <div class="card-body p-4 p-md-5">
                                    @include('profile.partials.update-profile-information-form')
                                </div>
                            </div>
                        </div>

                        {{-- Password Update --}}
                        <div class="tab-pane fade" id="v-pills-password" role="tabpanel">
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                                <div class="card-body p-4 p-md-5">
                                    @include('profile.partials.update-password-form')
                                </div>
                            </div>
                        </div>

                        {{-- Delete Account --}}
                        <div class="tab-pane fade" id="v-pills-delete" role="tabpanel">
                            <div class="card border-0 shadow-sm mb-4 border-danger-subtle" style="border-radius: 15px;">
                                <div class="card-body p-4 p-md-5">
                                    @include('profile.partials.delete-user-form')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-nav-link {
        border-radius: 10px !important;
        font-weight: 500;
        transition: all 0.3s ease;
        color: #6c757d;
        border: none;
        background: none;
    }
    .profile-nav-link:hover {
        background-color: #f8f9fa;
        color: #212529;
    }
    .profile-nav-link.active {
        background: linear-gradient(45deg, #212529, #495057) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .card {
        transition: transform 0.3s ease;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .tab-pane {
        animation: fadeIn 0.4s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
