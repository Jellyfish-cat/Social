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

<div class="msg-page" id="msgPage">

    {{-- ===== LEFT SIDEBAR ===== --}}
    <div class="msg-left">

        {{-- Header --}}
        <div class="msg-left-header">
            <span class="msg-username">
                {{ auth()->user()->profile->display_name ?? auth()->user()->name }}
                <i class="bi bi-chevron-down"></i>
            </span>
            <button class="msg-icon-btn" title="Tạo cuộc trò chuyện mới">
                <i class="bi bi-pencil-square"></i>
            </button>
        </div>

        {{-- Search --}}
        <div class="msg-search-wrap">
            <div class="msg-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Tìm kiếm" id="msgSearchInput">
            </div>
        </div>

        {{-- Stories/Active --}}
        <div class="msg-stories">
            <div class="msg-story-item">
                <div class="msg-story-ring seen">
                    <div class="msg-story-inner">
                        <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default-avatar.png')) }}" alt="Ghi chú">
                    </div>
                </div>
                <span class="msg-story-label muted">Ghi chú<br>của bạn</span>
            </div>
            {{-- Placeholder story items --}}
            @for($i = 0; $i < 3; $i++)
            <div class="msg-story-item">
                <div class="msg-story-ring">
                    <div class="msg-story-inner">
                        <img src="{{ asset('storage/default-avatar.png') }}" alt="User">
                    </div>
                </div>
                <span class="msg-story-label">Người dùng {{ $i+1 }}</span>
            </div>
            @endfor
        </div>

        {{-- Section Header --}}
        <div class="msg-section-header">
            <span class="msg-section-title">Tin nhắn</span>
            <button class="msg-section-link">Tin nhắn đang chờ</button>
        </div>

        {{-- Conversation List --}}
        <div class="msg-convo-list" id="msgConvoList">

            {{-- Item 1 (unread) --}}
            <div class="msg-convo-item" onclick="openChat(this, 'Tên F họ Lanh', 'Bạn đã gửi một file đính kèm.', false)">
                <div class="msg-avatar-wrap">
                    <img src="{{ asset('storage/default-avatar.png') }}" class="msg-avatar" alt="Avatar">
                </div>
                <div class="msg-convo-info">
                    <div class="msg-convo-name">Tên F họ Lanh</div>
                    <div class="msg-convo-preview unread">Bạn đã gửi một file đính kèm. · 1 tuần</div>
                </div>
                <div class="msg-convo-meta">
                    <span class="msg-convo-time">1 tuần</span>
                </div>
            </div>

            {{-- Item 2 --}}
            <div class="msg-convo-item" onclick="openChat(this, 'Liên Phương', 'Hoạt động 11 giờ trước', true)">
                <div class="msg-avatar-wrap">
                    <img src="{{ asset('storage/default-avatar.png') }}" class="msg-avatar" alt="Avatar">
                    <span class="msg-online-dot"></span>
                </div>
                <div class="msg-convo-info">
                    <div class="msg-convo-name">Liên Phương</div>
                    <div class="msg-convo-preview">Hoạt động 11 giờ trước</div>
                </div>
                <div class="msg-convo-meta">
                    <span class="msg-convo-time">11 giờ</span>
                </div>
            </div>

            {{-- Item 3 --}}
            <div class="msg-convo-item" onclick="openChat(this, 'Ngọc Trí', 'Bạn · 🔥 · 4 năm', true)">
                <div class="msg-avatar-wrap">
                    <img src="{{ asset('storage/default-avatar.png') }}" class="msg-avatar" alt="Avatar">
                </div>
                <div class="msg-convo-info">
                    <div class="msg-convo-name">Ngọc Trí</div>
                    <div class="msg-convo-preview">Bạn · 🔥 · 4 năm</div>
                </div>
                <div class="msg-convo-meta">
                    <span class="msg-convo-time">4 năm</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ===== RIGHT PANEL - EMPTY STATE ===== --}}
    <div class="msg-right" id="msgEmptyState">
        <div class="msg-empty-icon">
            <i class="bi bi-send"></i>
        </div>
        <div class="msg-empty-title">Tin nhắn của bạn</div>
        <p class="msg-empty-desc">Gửi ảnh và tin nhắn riêng tư cho bạn bè hoặc nhóm</p>
        <button class="msg-send-btn" onclick="showNewConvo()">Gửi tin nhắn</button>
    </div>

    {{-- ===== ACTIVE CHAT PANEL ===== --}}
    <div class="msg-chat-panel" id="msgChatPanel">

        {{-- Chat Header --}}
        <div class="msg-chat-header">
            <button class="msg-icon-btn d-md-none me-2" onclick="closeChat()">
                <i class="bi bi-arrow-left"></i>
            </button>
            <div class="msg-avatar-wrap">
                <img src="{{ asset('storage/default-avatar.png') }}" class="msg-avatar" style="width:44px;height:44px;" id="chatAvatar" alt="Avatar">
                <span class="msg-online-dot" id="chatOnlineDot" style="display:none;"></span>
            </div>
            <div class="msg-chat-header-info">
                <div class="msg-chat-header-name" id="chatName">Người dùng</div>
                <div class="msg-chat-header-status" id="chatStatus">Đang hoạt động</div>
            </div>
            <div class="msg-chat-actions">
                <button class="msg-icon-btn" title="Gọi điện"><i class="bi bi-telephone"></i></button>
                <button class="msg-icon-btn" title="Gọi video"><i class="bi bi-camera-video"></i></button>
                <button class="msg-icon-btn" title="Thông tin"><i class="bi bi-info-circle"></i></button>
            </div>
        </div>

        {{-- Chat Body --}}
        <div class="msg-chat-body" id="msgChatBody">
            <div class="msg-bubble-time">Hôm nay, 10:30 SA</div>

            <div class="msg-bubble-row">
                <img src="{{ asset('storage/default-avatar.png') }}" class="msg-bubble-avatar" alt="Avatar">
                <div class="msg-bubble theirs">Chào bạn! Bạn có khỏe không? 😊</div>
            </div>

            <div class="msg-bubble-row mine">
                <div class="msg-bubble mine">Mình khỏe, cảm ơn bạn! Bạn thì sao? 😄</div>
            </div>

            <div class="msg-bubble-row">
                <img src="{{ asset('storage/default-avatar.png') }}" class="msg-bubble-avatar" alt="Avatar">
                <div class="msg-bubble theirs">Mình cũng tốt. Hôm nay bạn có rảnh không?</div>
            </div>

            <div class="msg-bubble-row mine">
                <div class="msg-bubble mine">Có, bạn muốn làm gì vậy?</div>
            </div>
        </div>

        {{-- Chat Footer --}}
        <div class="msg-chat-footer">
            <button class="msg-input-icon" title="Gửi ảnh"><i class="bi bi-image"></i></button>
            <div class="msg-chat-input-wrap">
                <textarea class="msg-chat-input" id="msgInput" placeholder="Nhắn tin..." rows="1"></textarea>
                <span class="msg-input-icon" title="Emoji"><i class="bi bi-emoji-smile"></i></span>
            </div>
            <button class="msg-send-icon-btn" id="msgSendBtn" onclick="sendMessage()" title="Gửi">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>

</div>

<script>
    const emptyState = document.getElementById('msgEmptyState');
    const chatPanel = document.getElementById('msgChatPanel');
    const chatName  = document.getElementById('chatName');
    const chatStatus = document.getElementById('chatStatus');
    const chatOnlineDot = document.getElementById('chatOnlineDot');
    const chatBody  = document.getElementById('msgChatBody');
    const msgInput  = document.getElementById('msgInput');
    let currentItem = null;

    function openChat(el, name, status, online) {
        // Remove active from all
        document.querySelectorAll('.msg-convo-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
        currentItem = el;

        // Update header
        chatName.textContent = name;
        chatStatus.textContent = online ? 'Đang hoạt động' : status;
        chatOnlineDot.style.display = online ? 'block' : 'none';

        // Show chat, hide empty
        emptyState.style.display = 'none';
        chatPanel.classList.add('open');

        // Mobile
        document.getElementById('msgPage').classList.add('show-chat');

        // Scroll to bottom
        setTimeout(() => { chatBody.scrollTop = chatBody.scrollHeight; }, 50);
    }

    function closeChat() {
        document.getElementById('msgPage').classList.remove('show-chat');
        chatPanel.classList.remove('open');
        emptyState.style.display = 'flex';
    }

    function showNewConvo() {
        alert('Tính năng tìm kiếm người dùng để nhắn tin sẽ được phát triển sau.');
    }

    function sendMessage() {
        const text = msgInput.value.trim();
        if (!text) return;

        const row = document.createElement('div');
        row.className = 'msg-bubble-row mine';

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble mine';
        bubble.textContent = text;

        row.appendChild(bubble);
        chatBody.appendChild(row);

        msgInput.value = '';
        msgInput.style.height = 'auto';
        chatBody.scrollTop = chatBody.scrollHeight;

        // Update preview in conversation list
        if (currentItem) {
            const preview = currentItem.querySelector('.msg-convo-preview');
            if (preview) {
                preview.textContent = 'Bạn: ' + (text.length > 30 ? text.slice(0, 30) + '...' : text);
                preview.classList.remove('unread');
            }
        }
    }

    // Auto-resize textarea
    msgInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    // Enter to send (Shift+Enter for newline)
    msgInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Search filter
    document.getElementById('msgSearchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.msg-convo-item').forEach(item => {
            const name = item.querySelector('.msg-convo-name').textContent.toLowerCase();
            item.style.display = name.includes(q) ? '' : 'none';
        });
    });
</script>

@endsection
