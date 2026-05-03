@extends($layout)

@section('content')

<div class="container d-flex justify-content-center align-items-center" style="height:90vh">

<div class="card shadow-sm" style="width:420px;border-radius:12px">

<div class="card-body text-center">

<h4 class="mb-4">Thiết lập hồ sơ</h4>

<form method="POST" action="{{ route('profile.setup.store') }}" enctype="multipart/form-data">
@csrf

<div class="mb-4">
    <div class="position-relative d-inline-block">
        <div class="avatar-wrapper shadow-sm" style="width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 3px solid #fff; cursor: pointer;" onclick="document.getElementById('avatarInput').click()">
            <img id="avatarPreview" 
                 src="https://i.imgur.com/HeIi0wU.png" 
                 style="width: 100%; height: 100%; object-fit: cover;">
            <div class="avatar-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-25 opacity-0 hover-opacity-100 transition-all">
                <i class="bi bi-camera text-white fs-4"></i>
            </div>
        </div>
        <div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
             style="width: 32px; height: 32px; border: 2px solid #fff; cursor: pointer;"
             onclick="document.getElementById('avatarInput').click()">
            <i class="bi bi-camera-fill" style="font-size: 0.8rem;"></i>
        </div>
        <input type="file" id="avatarInput" name="avatar" class="d-none" onchange="previewAvatar(event)">
    </div>
    <p class="small text-muted mt-2">Nhấn để đổi ảnh</p>
</div>

<style>
    .avatar-wrapper:hover .avatar-overlay { opacity: 1 !important; }
    .transition-all { transition: all 0.3s ease; }
</style>

<div class="mb-3 text-start">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="small text-muted mb-0">Tên người dùng</label>
        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="generateRandomUsername()">
            <i class="bi bi-arrow-clockwise"></i> Ngẫu nhiên
        </button>
    </div>
    <input type="text" 
           id="usernameInput"
           name="name" 
           class="form-control @error('name') is-invalid @enderror" 
           placeholder="username_cua_ban" 
           value="{{ old('name') }}" 
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-start">
    <label class="small text-muted mb-1">Tên hiển thị</label>
    <input type="text"
           name="display_name"
           class="form-control @error('display_name') is-invalid @enderror"
           placeholder="Tên hiển thị"
           value="{{ old('display_name') }}"
           required>
    @error('display_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">

<textarea
name="bio"
class="form-control"
placeholder="Tiểu sử"
rows="3"></textarea>

</div>

<button class="btn btn-primary w-100">
Hoàn tất
</button>

</form>

</div>
</div>
</div>

<script>

function generateRandomUsername() {
    const prefixes = ['user', 'social', 'member', 'dev', 'pro'];
    const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
    const randomNumber = Math.floor(1000 + Math.random() * 9000); // 4 số ngẫu nhiên
    const randomString = Math.random().toString(36).substring(7); // Chuỗi ngẫu nhiên
    
    const randomUsername = `${randomPrefix}_${randomString}${randomNumber}`;
    document.getElementById('usernameInput').value = randomUsername;
}

// Tự động tạo tên ngẫu nhiên khi vừa vào trang
window.onload = function() {
    const usernameInput = document.getElementById('usernameInput');
    if (!usernameInput.value) {
        generateRandomUsername();
    }
};

function previewAvatar(event){
let reader = new FileReader();

reader.onload = function(){
document.getElementById('avatarPreview').src = reader.result;
}

reader.readAsDataURL(event.target.files[0]);

}

</script>

@endsection