@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: #ffffff;">
                <div class="card-header bg-white py-4 border-0 text-center">
                    <h4 class="mb-0 fw-bold text-dark">Chỉnh sửa nhóm</h4>
                    <p class="text-muted small mb-0">Cập nhật thông tin và thành viên cho nhóm của bạn</p>
                </div>
                <div class="card-body p-4 pt-0">
                    <form action="{{ route('conversation.group.update', $conversation->id) }}" method="POST" enctype="multipart/form-data" id="editGroupFullForm">
                        @csrf
                        @method('PUT')

                        {{-- Avatar Section --}}
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <div class="avatar-container shadow-sm border rounded-circle p-1 bg-white" style="width: 130px; height: 130px;">
                                    <img src="{{ asset('storage/' . ($conversation->avatar ?? 'default-avatar.png')) }}" 
                                         id="editGroupAvatarPreview" 
                                         class="rounded-circle" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <label for="editGroupAvatarInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle shadow p-2 cursor-pointer border-white border-2" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border: 3px solid #fff;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <input type="file" id="editGroupAvatarInput" name="avatar" hidden accept="image/*" onchange="previewAvatar(this)">
                            <div class="mt-2">
                                <a href="javascript:void(0)" class="text-primary text-decoration-none small fw-semibold" id="btnShowEditAvatarLibrary">Chọn từ thư viện DiceBear</a>
                            </div>
                        </div>

                        {{-- Group Name --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase mb-1 ms-1">Tên nhóm</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3"><i class="bi bi-people text-muted"></i></span>
                                <input type="text" name="name" class="form-control bg-light border-0 rounded-end-3 py-2" 
                                       placeholder="Nhập tên nhóm..." value="{{ $conversation->name }}" required>
                            </div>
                        </div>

                        {{-- Members Management --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase mb-1 ms-1">Thành viên ({{ $conversation->users->count() }})</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light border-0 rounded-start-3"><i class="bi bi-person-plus text-muted"></i></span>
                                <input type="text" id="editGroupUserSearch" class="form-control bg-light border-0 rounded-end-3" placeholder="Tìm tên bạn bè để thêm...">
                            </div>
                            
                            <div id="editSelectedUsers" class="d-flex flex-wrap gap-2 p-2 border rounded-3 bg-white mb-2 min-vh-10" style="min-height: 80px; align-content: flex-start;">
                                @foreach($conversation->users as $user)
                                    <div class="member-badge bg-light border rounded-pill px-3 py-1 d-flex align-items-center gap-2" data-user-id="{{ $user->id }}">
                                        <img src="{{ asset('storage/' . ($user->profile->avatar ?? 'default-avatar.png')) }}" class="rounded-circle" style="width:20px;height:20px;object-fit:cover;">
                                        <span class="small fw-medium">{{ $user->profile->display_name ?? $user->name }}</span>
                                        @if($user->id !== auth()->id())
                                            <i class="bi bi-x-circle-fill text-muted cursor-pointer hover-danger" onclick="removeMemberFromEdit({{ $user->id }}, this)"></i>
                                        @endif
                                        <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                                    </div>
                                @endforeach
                            </div>
                            <div id="editGroupUserSuggestions" class="list-group list-group-flush border rounded-3 shadow-sm d-none" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>

                        <div class="d-flex gap-3 pt-2">
                            <a href="{{ route('conversations.index') }}" class="btn btn-light rounded-pill py-2 flex-grow-1 fw-bold text-muted border">Quay lại</a>
                            <button type="submit" class="btn btn-primary rounded-pill py-2 flex-grow-1 fw-bold shadow-sm">Lưu thông tin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .hover-danger:hover { color: #dc3545 !important; }
    .member-badge { transition: all 0.2s; }
    .member-badge:hover { background: #f0f0f0 !important; }
    .list-group-item:hover { background-color: #f8f9fa; }
</style>

<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('editGroupAvatarPreview').src = e.target.result;
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeMemberFromEdit(id, el) {
        el.closest('.member-badge').remove();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('editGroupUserSearch');
        const suggestions = document.getElementById('editGroupUserSuggestions');
        const container = document.getElementById('editSelectedUsers');

        if (searchInput) {
            let debounce;
            searchInput.addEventListener('input', () => {
                clearTimeout(debounce);
                debounce = setTimeout(async () => {
                    const q = searchInput.value.trim();
                    if (!q) {
                        suggestions.innerHTML = '';
                        suggestions.classList.add('d-none');
                        return;
                    }
                    try {
                        const res = await fetch(`/conversation/search?q=${q}`);
                        const data = await res.json();
                        if (data.length > 0) {
                            suggestions.classList.remove('d-none');
                            suggestions.innerHTML = data.map(u => `
                                <div class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 cursor-pointer" onclick="addMemberToEdit(${u.id}, '${(u.profile?.display_name || u.name).replace(/'/g, "\\'")}', '${u.profile?.avatar ? '/storage/' + u.profile.avatar : '/storage/default-avatar.png'}')">
                                    <img src="${u.profile?.avatar ? '/storage/' + u.profile.avatar : '/storage/default-avatar.png'}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold small">${u.profile?.display_name ?? u.name}</div>
                                        <div class="text-muted" style="font-size: 10px;">@${u.name}</div>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            suggestions.innerHTML = '<div class="list-group-item text-muted small">Không tìm thấy người dùng</div>';
                        }
                    } catch (e) { console.error(e); }
                }, 300);
            });
        }

        window.addMemberToEdit = function(id, name, avatar) {
            // Check if already in list
            if (document.querySelector(`.member-badge[data-user-id="${id}"]`)) {
                searchInput.value = '';
                suggestions.innerHTML = '';
                suggestions.classList.add('d-none');
                return;
            }

            const badge = document.createElement('div');
            badge.className = 'member-badge bg-light border rounded-pill px-3 py-1 d-flex align-items-center gap-2';
            badge.dataset.userId = id;
            badge.innerHTML = `
                <img src="${avatar}" class="rounded-circle" style="width:20px;height:20px;object-fit:cover;">
                <span class="small fw-medium">${name}</span>
                <i class="bi bi-x-circle-fill text-muted cursor-pointer hover-danger" onclick="removeMemberFromEdit(${id}, this)"></i>
                <input type="hidden" name="user_ids[]" value="${id}">
            `;
            container.appendChild(badge);
            searchInput.value = '';
            suggestions.innerHTML = '';
            suggestions.classList.add('d-none');
        };
    });
</script>
@endsection
