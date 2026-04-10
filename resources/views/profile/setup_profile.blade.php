@extends($layout)

@section('content')

<div class="container d-flex justify-content-center align-items-center" style="height:90vh">

<div class="card shadow-sm" style="width:420px;border-radius:12px">

<div class="card-body text-center">

<h4 class="mb-4">Thiết lập hồ sơ</h4>

<form method="POST" action="{{ route('profile.setup.store') }}" enctype="multipart/form-data">
@csrf

<div class="mb-3">

<img id="avatarPreview"
src="https://i.imgur.com/HeIi0wU.png"
style="width:90px;height:90px;border-radius:50%;object-fit:cover">

</div>

<div class="mb-3">

<input type="file"
name="avatar"
class="form-control"
onchange="previewAvatar(event)">

</div>

<div class="mb-3">

<input type="text"
name="display_name"
class="form-control"
placeholder="Tên hiển thị"
required>

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

function previewAvatar(event){
let reader = new FileReader();

reader.onload = function(){
document.getElementById('avatarPreview').src = reader.result;
}

reader.readAsDataURL(event.target.files[0]);

}

</script>

@endsection