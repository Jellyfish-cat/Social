@extends($layout)

@section('content')
<style>
.insta-wrapper{
    max-width:350px;
    margin:10px auto;
}

.insta-card{
    background:#fff;
    border:1px solid #dbdbdb;
    padding:35px;
}

.insta-logo{
    font-family:'Segoe UI',sans-serif;
    font-size:32px;
    font-weight:600;
    text-align:center;
    margin-bottom:25px;
}

.insta-input{
    background:#fafafa;
    border:1px solid #dbdbdb;
    padding:9px;
    font-size:14px;
}

.insta-btn{
    background:#0095f6;
    border:none;
    color:#fff;
    font-weight:600;
    padding:8px;
    border-radius:6px;
}

.insta-btn:hover{
    background:#1877f2;
}

.signup-box{
    background:#fff;
    border:1px solid #dbdbdb;
    padding:20px;
    text-align:center;
    margin-top:10px;
}

.divider{
    display:flex;
    align-items:center;
    margin:15px 0;
}

.divider hr{
    flex:1;
}

.divider span{
    margin:0 10px;
    font-size:12px;
    color:#8e8e8e;
}
</style>


<div class="insta-wrapper">

    <!-- Login box -->
    <div class="insta-card">
        <div class="insta-logo">
           Gunpla Social Media
        </div>
        <x-auth-session-status class="mb-3" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Email"
                class="insta-input w-full mb-2"
                required
            >

            <x-input-error :messages="$errors->get('email')" class="mb-2" />

            <input
                type="password"
                name="password"
                placeholder="Password"
                class="insta-input w-full mb-3"
                required
            >

            <x-input-error :messages="$errors->get('password')" class="mb-2" />
                <div class="flex items-center mb-3">
    <input type="checkbox" name="remember" id="remember" class="mr-2">
    <label for="remember" class="text-sm text-gray-600">
        Ghi nhớ đăng nhập
    </label>
</div>  
            <button class="insta-btn w-full">
                {{ __('Log in') }}
            </button>

        </form>

        <div class="divider">
            <hr>
            <span>Hoặc</span>
            <hr>
        </div>

        <div class="mb-3 text-center">
            <a href="{{ route('google.login') }}" style="text-decoration: none; display: flex; align-items: center; justify-content: center; color: #385185; font-weight: 600; font-size: 14px;">
                <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" width="18" class="me-2">
                Đăng nhập bằng Google
            </a>
        </div>

        @if (Route::has('password.request'))
        <div class="text-center">
            <a href="{{ route('password.request') }}" class="text-sm text-blue-500">
                Bạn quên mật khẩu?
            </a>
        </div>
        @endif

    </div>


    <!-- Register box -->
    <div class="signup-box">

        Bạn chưa có tài khoản?

        <a href="{{ route('register') }}" class="text-blue-500 font-semibold">
            Đăng kí ngay
        </a>

    </div>
</div>
@endsection