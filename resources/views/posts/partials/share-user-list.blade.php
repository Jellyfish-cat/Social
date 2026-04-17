@forelse($friends as $friend)
<div class="user-share-item d-flex align-items-center justify-content-between px-2 py-2 mb-1 rounded-3 hover-bg-light cursor-pointer" 
     data-user-id="{{ $friend->id }}" data-post-id="{{ $postId }}">
    <div class="d-flex align-items-center">
        <img src="{{ asset('storage/' . ($friend->profile->avatar ?? 'default-avatar.png')) }}" 
             class="rounded-circle me-3" style="width: 44px; height: 44px; object-fit: cover;">
        <div class="d-flex flex-column">
            <span class="fw-bold small">{{ $friend->profile->display_name ?? $friend->name }}</span>
            <span class="text-muted" style="font-size: 12px;">{{ $friend->name }}</span>
        </div>
    </div>
    <div class="form-check">
        <input class="form-check-input select-user-share" type="checkbox" value="{{ $friend->id }}" style="width: 20px; height: 20px;">
    </div>
</div>
@empty
<div class="text-center py-4 text-muted small">Không tìm thấy người dùng nào.</div>
@endforelse

<div class="px-2 mt-2 mb-3 d-none" id="sendShareBtnContainer">
    <button class="btn btn-primary w-100 fw-bold py-2 rounded-3" id="sendShareBtn">Gửi</button>
</div>
