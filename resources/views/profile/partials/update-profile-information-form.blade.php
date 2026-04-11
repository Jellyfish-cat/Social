<section>
    <header class="mb-4">
        <h4 class="fw-bold">Thông tin cá nhân</h4>
        <p class="text-muted small">Cập nhật ảnh đại diện và các thông tin cơ bản của bạn.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-4">
        @csrf
        
        {{-- Avatar Section --}}
        <div class="mb-4 text-center">
            <div class="position-relative d-inline-block">
                <img id="avatarPreview" 
                     src="{{ asset('storage/' . ($user->profile->avatar ?? 'default-avatar.png')) }}" 
                     class="rounded-circle border border-3 border-white shadow-sm"
                     style="width: 120px; height: 120px; object-fit: cover;">
                
                <label for="avatar" class="position-absolute bottom-0 end-0 btn btn-dark btn-sm rounded-circle p-2 shadow" style="width: 35px; height: 35px; cursor: pointer;">
                    <i class="bi bi-camera"></i>
                    <input type="file" id="avatar" name="avatar" class="d-none" onchange="previewAvatar(event)">
                </label>
            </div>
            @if ($errors->has('avatar'))
                <div class="text-danger small mt-2">{{ $errors->first('avatar') }}</div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Họ và tên</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required autofocus>
                @if ($errors->has('name')) <div class="text-danger small">{{ $errors->first('name') }}</div> @endif
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small">Tên hiển thị</label>
                <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $user->profile->display_name ?? '') }}" required>
                @if ($errors->has('display_name')) <div class="text-danger small">{{ $errors->first('display_name') }}</div> @endif
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold small">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @if ($errors->has('email')) <div class="text-danger small">{{ $errors->first('email') }}</div> @endif

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2 p-2 bg-warning-subtle rounded border border-warning-subtle">
                        <p class="text-sm mb-1 text-warning-emphasis small">
                            {{ __('Your email address is unverified.') }}
                            <button form="send-verification" class="btn btn-link p-0 small text-decoration-none">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>
                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-success small">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold small">Tiểu sử</label>
                <textarea name="bio" class="form-control" rows="3" placeholder="Giới thiệu ngắn về bản thân...">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
                @if ($errors->has('bio')) <div class="text-danger small">{{ $errors->first('bio') }}</div> @endif
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-dark px-4 py-2">Lưu thay đổi</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small animated fadeIn">
                    <i class="bi bi-check-circle me-1"></i> Đã lưu thành công!
                </span>
            @endif
        </div>
    </form>
</section>

<script>
function previewAvatar(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('avatarPreview');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
