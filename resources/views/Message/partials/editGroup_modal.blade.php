<div class="modal-header border-bottom-0">
    <h5 class="modal-title fw-bold">Cập nhật nhóm</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editGroupForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="conversation_id" id="editConvoId" value="{{ $conversation->id ?? '' }}">
        <div class="text-center mb-3">
            <label for="editGroupAvatarInput" class="cursor-pointer">
                <img src="{{ asset('storage/' . ($conversation->avatar ?? 'default-avatar.png')) }}" id="editGroupAvatarPreview" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                <div class="small text-primary mt-1">Đổi ảnh nhóm</div>
            </label>
            <div class="mt-1">
                <a href="javascript:void(0)" class="small text-muted text-decoration-none" id="btnShowEditAvatarLibrary">Hoặc chọn từ thư viện</a>
            </div>
            <input type="file" id="editGroupAvatarInput" name="avatar" hidden accept="image/*">
            <input type="hidden" id="editDicebearUrlInput" name="dicebear_url">
        </div>
        <div class="mb-3">
            <label class="small fw-bold mb-1">Tên nhóm</label>
            <input type="text" name="name" id="editGroupNameInput" class="form-control rounded-3" placeholder="Tên nhóm..." value="{{ $conversation->name ?? '' }}" required>
        </div>
        <div class="mb-3">
            <label class="small fw-bold mb-2">Thành viên hiện tại & Thêm mới</label>
            <input type="text" id="editGroupUserSearch" class="form-control mb-2 rounded-3" placeholder="Tìm tên người dùng để thêm...">
            <div id="editSelectedUsers" class="d-flex flex-wrap gap-2 mb-2">
                @if(isset($conversation) && $conversation->users)
                    @foreach($conversation->users as $user)
                        <span class="badge bg-light text-dark border p-2 d-flex align-items-center gap-2 m-1">
                            {{ $user->profile->display_name ?? $user->name }}
                            @if($user->id !== auth()->id())
                                <i class="bi bi-x-circle cursor-pointer text-danger" onclick="window.removeUserFromEditGroup({{ $user->id }}, this)"></i>
                            @endif
                            <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                        </span>
                    @endforeach
                @endif
            </div>
            <div id="editGroupUserSuggestions" class="list-group list-group-flush border rounded-3 overflow-auto" style="max-height: 200px;"></div>
        </div>
    </form>
</div>
<div class="modal-footer border-top-0">
    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
    <button type="button" id="btnUpdateGroup" class="btn btn-primary rounded-pill px-4">Lưu thay đổi</button>
</div>