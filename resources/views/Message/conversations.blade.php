@extends('layouts.app')

@section('content')

<style>
    /* ===== RESET MAIN LAYOUT FOR MESSAGES PAGE ===== */
    .main-content > .container-fluid {
        padding: 0 !important;
        max-width: 100% !important;
    }
    .main-content > .container-fluid > .row {
        margin: 0 !important;
    }
    .main-content > .container-fluid > .row > div {
        padding: 0 !important;
        max-width: 100% !important;
    }
    /* ===== MESSAGE PAGE WRAPPER ===== */
    .msg-page {
        display: flex;
        height: 600px;
        border-top: 1px solid #efefef;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        overflow: hidden;
    }
    /* ===== LEFT PANEL ===== */
    .msg-left {
        width: 360px;
        min-width: 280px;
        border-right: 1px solid #efefef;
        display: flex;
        flex-direction: column;
        background: #fff;
    }
    .msg-left-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 20px 12px;
    }
    .msg-username {
        font-size: 16px;
        font-weight: 700;
        color: #0d0d0d;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .msg-username i {
        font-size: 13px;
        color: #555;
    }
    .msg-icon-btn {
        background: none;
        border: none;
        padding: 6px;
        border-radius: 50%;
        cursor: pointer;
        color: #0d0d0d;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }
    .msg-icon-btn:hover {
        background: #f0f0f0;
    }
    /* ===== SEARCH ===== */
    .msg-search-wrap {
        padding: 4px 16px 12px;
    }
    .msg-search {
        display: flex;
        align-items: center;
        background: #efefef;
        border-radius: 10px;
        padding: 8px 14px;
        gap: 8px;
    }

    .msg-search i {
        color: #8e8e8e;
        font-size: 14px;
    }

    .msg-search input {
        background: none;
        border: none;
        outline: none;
        font-size: 14px;
        color: #0d0d0d;
        width: 100%;
    }

    .msg-search input::placeholder {
        color: #8e8e8e;
    }

    /* ===== STORIES STRIP ===== */
    .msg-stories {
        padding: 0 16px 12px;
        display: flex;
        gap: 14px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .msg-stories::-webkit-scrollbar { display: none; }

    .msg-story-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .msg-story-ring {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        padding: 2px;
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        position: relative;
    }

    .msg-story-ring.seen {
        background: #dbdbdb;
    }

    .msg-story-ring .msg-story-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2.5px solid #fff;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .msg-story-ring img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .msg-story-label {
        font-size: 11px;
        color: #0d0d0d;
        text-align: center;
        max-width: 60px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .msg-story-label.muted {
        color: #8e8e8e;
    }

    /* ===== SECTION LABEL ===== */
    .msg-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 4px 16px 8px;
    }

    .msg-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #0d0d0d;
    }

    .msg-section-link {
        font-size: 13px;
        font-weight: 600;
        color: #0d0d0d;
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
    }

    .msg-section-link:hover { text-decoration: underline; }

    /* ===== CONVERSATION LIST ===== */
    .msg-convo-list {
        flex: 1;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #dbdbdb transparent;
    }

    .msg-convo-list::-webkit-scrollbar { width: 4px; }
    .msg-convo-list::-webkit-scrollbar-thumb { background: #dbdbdb; border-radius: 4px; }

    .msg-convo-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        cursor: pointer;
        border-radius: 8px;
        margin: 0 6px;
        transition: background 0.12s;
        position: relative;
    }

    .msg-convo-item:hover, .msg-convo-item.active {
        background: #f5f5f5;
    }

    .msg-avatar-wrap {
        position: relative;
        flex-shrink: 0;
    }

    .msg-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }

    .msg-online-dot {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        background: #31c44a;
        border-radius: 50%;
        border: 2px solid #fff;
    }

    .msg-convo-info {
        flex: 1;
        min-width: 0;
    }

    .msg-convo-name {
        font-size: 14px;
        font-weight: 600;
        color: #0d0d0d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .msg-convo-preview {
        font-size: 13px;
        color: #8e8e8e;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 2px;
    }

    .msg-convo-preview.unread {
        color: #0d0d0d;
        font-weight: 600;
    }

    .msg-convo-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        flex-shrink: 0;
    }

    .msg-convo-time {
        font-size: 12px;
        color: #8e8e8e;
    }

    .msg-unread-dot {
        width: 8px;
        height: 8px;
        background: #0095f6;
        border-radius: 50%;
    }

    /* ===== RIGHT PANEL - EMPTY STATE ===== */
    .msg-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        flex-direction: column;
        gap: 16px;
    }

    .msg-empty-icon {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        border: 3px solid #0d0d0d;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .msg-empty-icon i {
        font-size: 42px;
        color: #0d0d0d;
    }

    .msg-empty-title {
        font-size: 22px;
        font-weight: 300;
        color: #0d0d0d;
    }

    .msg-empty-desc {
        font-size: 14px;
        color: #8e8e8e;
        text-align: center;
        max-width: 300px;
    }

    .msg-send-btn {
        background: #0095f6;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    .msg-send-btn:hover { background: #1877f2; }

    /* ===== ACTIVE CHAT PANEL ===== */
    .msg-chat-panel {
        flex: 1;
        display: none;
        flex-direction: column;
        background: #fff;
    }

    .msg-chat-panel.open { display: flex; }

    .msg-chat-header {
        display: flex;
        align-items: center;
        padding: 14px 18px;
        border-bottom: 1px solid #efefef;
        gap: 12px;
    }

    .msg-chat-header-info {
        flex: 1;
        min-width: 0;
    }

    .msg-chat-header-name {
        font-size: 15px;
        font-weight: 700;
        color: #0d0d0d;
    }

    .msg-chat-header-status {
        font-size: 12px;
        color: #8e8e8e;
    }

    .msg-chat-actions {
        display: flex;
        gap: 6px;
    }

    /* ===== MESSAGES AREA ===== */
    .msg-chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        scrollbar-width: thin;
        scrollbar-color: #dbdbdb transparent;
    }

    .msg-chat-body::-webkit-scrollbar { width: 4px; }
    .msg-chat-body::-webkit-scrollbar-thumb { background: #dbdbdb; border-radius: 4px; }

    .msg-bubble-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .msg-bubble-row.mine {
        flex-direction: row-reverse;
    }

    .msg-bubble-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        margin-bottom: 2px;
        
    }

    .msg-bubble {
        max-width: 260px;
        padding: 10px 14px;
        border-radius: 22px;
        font-size: 14px;
        line-height: 1.4;
        position: relative;
    }

    .msg-bubble.theirs {
        background: #efefef;
        color: #0d0d0d;
        border-bottom-left-radius: 6px;
    }

    .msg-bubble.mine {
        background: #0095f6;
        color: #fff;
        border-bottom-right-radius: 6px;
    }

    .msg-bubble-time {
        font-size: 11px;
        color: #8e8e8e;
        text-align: center;
        margin: 8px 0 2px;
        align-self: center;
    }

    .msg-time {
        position: absolute;
        bottom: -18px;
        font-size: 10px;
        color: #b0b0b0;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    }

    /* Căn chỉnh thời gian theo phía người gửi */
    .mine .msg-time {
        right: 12px;
    }

    .theirs .msg-time {
        left: 12px;
    }

    .msg-bubble-row {
        transition: margin-bottom 0.2s ease;
    }

    .msg-bubble-row:has(.msg-bubble:hover) {
        margin-bottom: 22px;
    }

    .msg-bubble-row:has(.msg-bubble:hover) .msg-time {
        opacity: 1;
    }
    /* ===== CHAT INPUT ===== */
    .msg-chat-footer {
        padding: 12px 16px;
        border-top: 1px solid #efefef;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .msg-chat-input-wrap {
        flex: 1;
        display: flex;
        align-items: center;
        border: 1.5px solid #dbdbdb;
        border-radius: 22px;
        padding: 8px 14px;
        gap: 10px;
        transition: border-color 0.15s;
    }

    .msg-chat-input-wrap:focus-within { border-color: #0095f6; }

    .msg-chat-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 14px;
        color: #0d0d0d;
        background: transparent;
        resize: none;
        max-height: 100px;
        min-height: 20px;
        overflow-y: auto;
    }

    .msg-chat-input::placeholder { color: #8e8e8e; }

    .msg-input-icon {
        font-size: 20px;
        color: #0d0d0d;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: color 0.12s;
    }

    .msg-input-icon:hover { color: #0095f6; }

    .msg-send-icon-btn {
        font-size: 22px;
        color: #0095f6;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        display: flex;
        align-items: center;
        transition: opacity 0.12s;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .msg-left { width: 100%; min-width: unset; }
        .msg-right, .msg-chat-panel { display: none; }
        .msg-page.show-chat .msg-left { display: none; }
        .msg-page.show-chat .msg-chat-panel { display: flex; }
        .msg-page.show-chat .msg-right { display: flex; }
    }
</style>

<div class="msg-page rounded-4" id="msgPage">

    {{-- ===== LEFT SIDEBAR ===== --}}
    <div class="msg-left">

        {{-- Header --}}
        <div class="msg-left-header">
            <span class="msg-username">
                {{ auth()->user()->profile->display_name ?? auth()->user()->name }}
            </span>
            <button class="msg-icon-btn me-2" id="btnOpenGroupModal" title="Tạo nhóm trò chuyện">
                <i class="bi bi-people-fill"></i>
            </button>
        </div>

        {{-- Search --}}
        <div class="msg-search-wrap">
            <div class="msg-search">
                <i class="bi bi-search"></i>
                <input type="text" id="user-input"
                class="form-control form-control-sm"
                placeholder="Tìm kiếm" autocomplete="off">
            </div>
            <div id="suggestions-user" class="list-group mt-1 rounded-5 mt-2 mx-auto "></div>
        </div>

        {{-- Stories/Active --}}
       

        {{-- Section Header --}}
        <div class="msg-section-header">
            <span class="msg-section-title">Tin nhắn</span>
            <button class="msg-section-link">Tin nhắn đang chờ</button>
        </div>

        {{-- Conversation List --}}
         <div class="overflow-auto flex-grow-1" id="msgConvoList">
            @if($conversations !== null)
                @foreach($conversations as $conversation)
                    @php
                        $isGroup = $conversation->type === 'group';
                        $targetId = null;
                        $displayName = 'Unnamed';
                        $avatar = asset('storage/default-avatar.png');

                        if ($isGroup) {
                            $displayName = $conversation->name;
                            $avatar = $conversation->avatar ? asset('storage/' . $conversation->avatar) : asset('storage/default-group.png');
                            $targetId = $conversation->id;
                        } else {
                            $otherUser = $conversation->users->where('id', '!=', auth()->id())->first();
                            if(!$otherUser) continue;
                            $displayName = $otherUser->profile->display_name ?? $otherUser->name;
                            $avatar = asset('storage/' . ($otherUser->profile->avatar ?? 'default-avatar.png'));
                            $targetId = $otherUser->id;
                        }

                        $lastMsg = $conversation->latestMessage;
                        $previewText = 'Chưa có tin nhắn';
                        if ($lastMsg) {
                            $mediaCount = $lastMsg->media ? $lastMsg->media->count() : 0;
                            if ($mediaCount > 0) {
                                $previewText = $lastMsg->content 
                                    ? $lastMsg->content 
                                    : "📷 Đã gửi {$mediaCount} ảnh";
                            } else {
                                $previewText = $lastMsg->content;
                            }
                        }
                    @endphp
                 <div class="d-flex align-items-center px-3 py-2 gap-2 convo-item {{ $conversation->unread_count > 0 ? 'unread' : '' }}"
                         data-name="{{ $displayName }}"
                         data-status="{{ $lastMsg->content ?? '' }}"
                         data-online="false"
                         data-is-group="{{ $isGroup ? 'true' : 'false' }}"
                         data-convo-id="{{ $targetId }}"
                         data-user-id="{{ $targetId }}">
                        <img src="{{ $avatar }}"
                             class="rounded-circle flex-shrink-0" style="width: 50px; height: 50px; object-fit: cover;">

                        <div class="flex-grow-1 text-truncate">
                            <div class="fw-semibold">
                                {{ $displayName }}
                            </div>

                            <small class="text-muted">
                                @if($conversation->unread_count > 0) <strong> @endif
                                
                                {{ $lastMsg && $lastMsg->sender_id == auth()->id() ? 'Bạn: ' : '' }}
                                {{ Str::limit($previewText ?? 'Chưa có tin nhắn', 30) }}
                                
                                @if($conversation->unread_count > 0) </strong> @endif
                            </small>
                             @if($conversation->unread_count > 0)
                                <span class="msg-unread-count badge bg-primary">
                                    {{ $conversation->unread_count }}
                                </span>
                            @endif
                        </div>

                        <small class="text-muted">
                            {{ optional($lastMsg)->created_at?->diffForHumans() }}
                        </small>
                    </div>
                @endforeach
                @endif
            </div>
    </div>

    {{-- ===== RIGHT PANEL - EMPTY STATE ===== --}}
    <div class="msg-right" id="msgEmptyState">
        <div class="msg-empty-icon">
            <i class="bi bi-send"></i>
        </div>
        <div class="msg-empty-title">Tin nhắn của bạn</div>
        <p class="msg-empty-desc">Gửi ảnh và tin nhắn riêng tư cho bạn bè hoặc nhóm</p>
    </div>

    {{-- ===== ACTIVE CHAT PANEL ===== --}}
    <div class="msg-chat-panel" id="msgChatPanel">
        @php
            $currentConvo = $conversations && $conversations->isNotEmpty() ? $conversations->first() : null;
            $currentUser = $currentConvo ? $currentConvo->users->where('id', '!=', auth()->id())->first() : null;
            $status = $currentUser->status ?? 'show';
        @endphp

        {{-- Chat Header --}}
        <div class="msg-chat-header">
            <button class="msg-icon-btn d-md-none me-2" id="msgBackBtn">
                <i class="bi bi-arrow-left"></i>
            </button>
            <div class="msg-avatar-wrap">
                <img src="{{ asset('storage/' . ($currentUser->profile->avatar ?? 'default-avatar.png')) }}" class="msg-avatar" style="width:44px;height:44px;" id="chatAvatar" alt="Avatar">
                <span class="msg-online-dot" id="chatOnlineDot" style="display:none;"></span>
            </div>
            <div class="msg-chat-header-info">
                <div class="msg-chat-header-name" id="chatName">{{$currentUser->profile->display_name ?? $currentUser->name ?? ''}}</div>
                <div class="msg-chat-header-status" id="chatStatus">Đang hoạt động</div>
            </div>
            <div class="msg-chat-actions">
                <button class="msg-icon-btn" title="Thông tin"><i class="bi bi-info-circle"></i></button>
            </div>
        </div>

        {{-- Chat Body --}}
                    <div id="msgHeader"></div>
                <div class="msg-chat-body" id="msgChatBody">
                    

            </div>

        {{-- Chat Footer --}}
        @if($status !== 'hidden')
        <div class="chat-form">
            <div class="preview-media d-flex gap-2 px-3 py-1 w-100" style="display:none;"></div></div>
        <div class="msg-chat-footer chat-form constantIcon">
            <input type="file" id="msg-file-input" name="file" hidden accept="image/*,video/*" multiple onchange="previewMessageFiles(this)">
            <button type="button" class="btn-image btn msg-input-icon constantIcon" title="Gửi ảnh"
                onclick="event.preventDefault(); document.getElementById('msg-file-input').click();">
                <i class="bi bi-image fs-5"></i>
            </button>
            <div class="msg-chat-input-wrap">
                <textarea class="msg-chat-input" id="msgInput" placeholder="Nhắn tin..." rows="1"></textarea>
                <span class="msg-input-icon" title="Emoji" id="emojiBtn"><i class="bi bi-emoji-smile"></i></span>
                <div class="mb-5 me-2" id="emojiPicker" style="position:absolute; bottom:60px; right:100px; display:none;"></div>
            </div>
            <button class="msg-send-icon-btn" id="msgSendBtn" title="Gửi">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
        @else
        <div class="msg-chat-footer chat-form constantIcon justify-content-center">
            <div class="text-center text-muted py-3">
                <i class="bi bi-lock-fill fs-6"></i>
                <p class="mb-0 mt-1">Tài khoản này đã bị khóa do vi phạm</p>
            </div>
        </div>
        @endif
    </div>

{{-- New Group Modal --}}
<div class="modal fade" id="newGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Tạo nhóm trò chuyện</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newGroupForm" enctype="multipart/form-data">
                    <div class="text-center mb-3">
                        <label for="groupAvatarInput" class="cursor-pointer">
                            <img src="{{ asset('storage/default-avatar.png') }}" id="groupAvatarPreview" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #efefef;">
                            <div class="small text-primary mt-1">Chọn ảnh nhóm (tùy chọn)</div>
                        </label>
                        <input type="file" id="groupAvatarInput" name="avatar" hidden accept="image/*">
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
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnOpenGroupModal = document.getElementById('btnOpenGroupModal');
    if (btnOpenGroupModal) {
        btnOpenGroupModal.addEventListener('click', function(e) {
            e.preventDefault();
            const modalEl = document.getElementById('newGroupModal');
            if (modalEl) {
                const modalParams = new bootstrap.Modal(modalEl);
                modalParams.show();
            }
        });
    }
});
</script>

@endsection
