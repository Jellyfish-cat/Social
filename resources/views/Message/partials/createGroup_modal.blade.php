<div class="modal-header border-bottom-0">
    <h5 class="modal-title fw-bold">Tạo nhóm trò chuyện</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="newGroupForm" enctype="multipart/form-data">
        <div class="text-center mb-3">
            <label for="groupAvatarInput" class="cursor-pointer">
                <img src="{{ asset('storage/default-avatar.png') }}" id="groupAvatarPreview" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                <div class="small text-primary mt-1">Chọn ảnh nhóm</div>
            </label>
            <div class="mt-1">
                <a href="javascript:void(0)" class="small text-muted text-decoration-none" id="btnShowAvatarLibrary">Hoặc chọn từ thư viện</a>
            </div>
            <input type="file" id="groupAvatarInput" name="avatar" hidden accept="image/*">
            <input type="hidden" id="dicebearUrlInput" name="dicebear_url">
        </div>
        <div class="mb-3">
            <input type="text" name="name" class="form-control rounded-3" placeholder="Tên nhóm (VD: Hội Quán...)" required>
        </div>
        <div class="mb-3">
            <label class="small fw-bold mb-2">Thêm thành viên</label>
            <input type="text" id="groupUserSearch" class="form-control mb-2 rounded-3" placeholder="Tìm tên người dùng...">
            <div id="selectedUsers" class="d-flex flex-wrap gap-2 mb-2"></div>
            <div id="groupUserSuggestions" class="list-group list-group-flush border rounded-3 overflow-auto" style="max-height: 200px;"></div>
        </div>
    </form>
</div>
<div class="modal-footer border-top-0">
    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
    <button type="button" id="btnCreateGroup" class="btn btn-primary rounded-pill px-4">Tạo nhóm</button>
</div>
