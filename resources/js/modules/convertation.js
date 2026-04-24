// ===== Conversation / Message Page =====
// Chỉ chạy trên trang có #msgPage
const msgPage = document.getElementById('msgPage');
window.currentItem = null;
window.currentUserId = null;
if (msgPage) {
    const emptyState = document.getElementById('msgEmptyState');
    const chatPanel = document.getElementById('msgChatPanel');
    const chatName = document.getElementById('chatName');
    const chatStatus = document.getElementById('chatStatus');
    const chatOnlineDot = document.getElementById('chatOnlineDot');
    const chatBody = document.getElementById('msgChatBody');
    const msgInput = document.getElementById('msgInput');

    // ===== Mở chat khi click vào conversation =====
    window.openChat = function (el, name, status, online) {
        document.querySelectorAll('.convo-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
        window.currentItem = el;
        window.currentUserId = el.dataset.userId;
        chatName.textContent = name;
        chatStatus.textContent = online ? 'Đang hoạt động' : status;
        chatOnlineDot.style.display = online ? 'block' : 'none';
        emptyState.style.display = 'none';
        chatPanel.classList.add('open');
        msgPage.classList.add('show-chat');

        const avatarSrc = currentItem.querySelector('img')?.src;
        if (avatarSrc) document.getElementById('chatAvatar').src = avatarSrc;
        // Fetch messages từ server
        const isGroup = el.dataset.isGroup === 'true';
        const targetId = isGroup ? el.dataset.convoId : el.dataset.userId;
        chatName.innerHTML = isGroup
            ? `${name} <i class="bi bi-people text-muted ms-1" title="Nhóm"></i>`
            : name;
        if (targetId) {
            chatBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Đang tải...</div>
        </div>
     `;
            startLoading();
            const fetchUrl = isGroup ? `/message/group/chat/${targetId}` : `/message/chat/${targetId}`;

            fetch(fetchUrl)
                .then(res => res.text())
                .then(html => {
                    chatBody.innerHTML = html;

                    setTimeout(() => { chatBody.scrollTop = chatBody.scrollHeight; }, 50);
                })
                .catch(() => {
                    chatBody.innerHTML = '<div class="text-center text-muted py-3">Không thể tải tin nhắn</div>';
                })
                .finally(() => {
                    finishLoading();
                });
        }
        // ===== ĐÁNH DẤU ĐÃ ĐỌC =====
        if (el.classList.contains('unread')) {
            el.classList.remove('unread');
            const unreadBadge = el.querySelector('.msg-unread-count');
            let unreadCount = 1;
            if (unreadBadge) {
                unreadCount = parseInt(unreadBadge.textContent) || 1;
                unreadBadge.remove();
            }

            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (csrfTokenElement) {
                fetch(`/messages/read/${window.currentUserId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfTokenElement.content,
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const globalBadge = document.getElementById('global-mess-badge');
                            if (globalBadge) {
                                let count = parseInt(globalBadge.textContent) || 0;
                                count = Math.max(0, count - unreadCount);
                                globalBadge.textContent = count;
                                if (count === 0) globalBadge.classList.add('d-none');
                            }
                        }
                    })
                    .catch(err => console.error("Lỗi cập nhật tin đã đọc:", err));
            }
        }
        // reset preview về bình thường (bỏ bold)
        const preview = el.querySelector('small.text-muted');
        if (preview) preview.innerHTML = preview.textContent;

    }

    // ===== Đóng chat (mobile) =====
    function closeChat() {
        msgPage.classList.remove('show-chat');
        chatPanel.classList.remove('open');
        emptyState.style.display = 'flex';
    }

    // ===== Logic gửi tin nhắn đã được chuyển sang message.js =====

    // ===== Tìm kiếm conversation =====
    const searchInput = document.getElementById('msgSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.convo-item').forEach(item => {
                const name = item.querySelector('.fw-semibold')?.textContent.toLowerCase() || '';
                item.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    // ===== Bind events bằng addEventListener thay vì onclick =====

    // Click vào từng conversation item
    document.addEventListener('click', function (e) {
        const item = e.target.closest('.convo-item');
        if (!item) return;
        const name = item.dataset.name || '';
        const status = item.dataset.status || '';
        const online = item.dataset.online === 'true';
        const userId = item.dataset.userId;
        if (!userId) return;
        window.openChat(item, name, status, online);
        updateHistory(userId);
    });

    // Nút gửi tin nhắn trong chat
    // Nút back (mobile)
    const backBtn = document.getElementById('msgBackBtn');
    if (backBtn) backBtn.addEventListener('click', closeChat);

    function updateHistory(userId) {
        const url = new URL(window.location.href);
        url.searchParams.set('chat', userId);
        window.history.pushState({ chat: userId }, '', url.toString());
    }
    //bắt click back
    window.addEventListener("popstate", function (e) {
        if (e.state && e.state.chat) {
            const chatId = e.state.chat;
            const convoItem = document.querySelector(`.convo-item[data-user-id="${chatId}"]`);
            if (convoItem) {
                const name = convoItem.dataset.name || '';
                const status = convoItem.dataset.status || '';
                const online = convoItem.dataset.online === 'true';
                window.openChat(convoItem, name, status, online);
            }
        }
    });
    window.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        const chatId = urlParams.get('chat');
        if (chatId) {
            window.currentUserId = chatId;

            // Tìm item trong danh sách để biết là Group hay Private
            const convoItem = document.querySelector(`.convo-item[data-user-id="${chatId}"], .convo-item[data-convo-id="${chatId}"]`);
            const isGroup = convoItem ? (convoItem.dataset.isGroup === 'true') : false;

            startLoading();
            const fetchUrl = isGroup ? `/message/group/chat/${chatId}` : `/message/chat/${chatId}`;

            fetch(fetchUrl)
                .then(res => res.text())
                .then(html => {
                    chatBody.innerHTML = html;
                    // set UI cơ bản
                    emptyState.style.display = 'none';
                    chatPanel.classList.add('open');
                    msgPage.classList.add('show-chat');

                    if (convoItem) {
                        window.currentItem = convoItem;
                        convoItem.classList.add('active');

                        const dName = convoItem.dataset.name || '';
                        const isGrp = convoItem.dataset.isGroup === 'true';
                        chatName.innerHTML = isGrp
                            ? `${dName} <i class="bi bi-people text-muted ms-1" title="Nhóm"></i>`
                            : dName;

                        const avatarSrc = convoItem.querySelector('img')?.src;
                        if (avatarSrc) document.getElementById('chatAvatar').src = avatarSrc;
                    } else {
                        // Nếu không thấy item trong list (có thể do load chậm hoặc URL trực tiếp)
                        const avatarSrc = chatBody.querySelector('.msg-header img')?.src;
                        if (avatarSrc) document.getElementById('chatAvatar').src = avatarSrc;
                    }
                    requestAnimationFrame(() => {
                        chatBody.scrollTop = chatBody.scrollHeight;
                    });
                })
                .finally(() => finishLoading());
        }
    });

    // ===== Thu hồi tin nhắn đã chuyển sang message.js =====
}
//hàm gợi ý user
const input = document.getElementById("user-input");
const suggestions_user = document.getElementById("suggestions-user");
let controller;
if (input) {
    let debounce;
    input.addEventListener("input", () => {
        clearTimeout(debounce);
        debounce = setTimeout(async () => {
            let q = input.value.trim().toLowerCase();
            if (controller) controller.abort();
            controller = new AbortController();
            if (!q) {
                suggestions_user.innerHTML = "";
                return;
            }
            try {
                let res = await fetch(`/conversation/search?q=${q}`, {
                    signal: controller.signal
                });
                let data = await res.json();
                if (!input.value.trim()) return;
                suggestions_user.innerHTML = data.map(t => {
                    document.getElementById('chatName').innerHTML = t.display_name;
                    const avatar = t.profile?.avatar
                        ? `/storage/${t.profile.avatar}`
                        : `/storage/default-avatar.png`;
                    return `
                        <a href="/message?chat=${t.id}"
                           class="d-flex align-items-center gap-3 p-2 text-decoration-none text-dark rounded-3 suggest-item">
                            <img src="${avatar}"
                                 onerror="this.src='/storage/default-avatar.png'"
                                 class="rounded-circle"
                                 style="width: 40px; height: 40px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">
                                    ${t.display_name ?? t.name}
                                </div>
                                <div class="text-muted small">
                                    @${t.name}
                                </div>
                            </div>
                        </a>
                    `;
                }).join("");
            } catch (err) {
                if (err.name === "AbortError") return;
                console.error("Search lỗi:", err);
            }
        }, 300);
    });
}
// XỬ LÝ WEBSOCKET ĐÃ CHUYỂN SANG MESSAGE.JS
//nút icon đã chuyển sang message.js
// ==========================================================
// ====== LOGIC GLOBAL WEBSOCKET: UPDATE SỐ Ở THANH NAV ======
// Đoạn này nằm ngoài msgPage để trang nào tải lên nó cũng chạy
// ==========================================================
setTimeout(() => {
    const metaAuth = document.querySelector('meta[name="auth-user-id"]');
    const authUserId = metaAuth ? metaAuth.content : null;

    if (window.Echo && authUserId) {
        window.Echo.private(`chat.${authUserId}`)
            .listen('MessageSent', (e) => {
                const incomingMsg = e.message;

                // Nếu tin nhắn gửi đến mình (từ người khác)
                if (incomingMsg.sender_id != authUserId) {
                    const globalBadge = document.getElementById('global-mess-badge');
                    if (globalBadge) {
                        // Nếu đang ở TRONG web nhắn tin và ĐANG XEM đúng ông đó -> Ko cộng số báo
                        if (typeof currentUserId !== 'undefined' && currentUserId == incomingMsg.sender_id) {
                            return;
                        }

                        // Nhảy số
                        let count = parseInt(globalBadge.textContent) || 0;
                        count += 1;

                        globalBadge.textContent = count;
                        globalBadge.classList.remove('d-none'); // Xóa thẻ ẩn dòng đi

                        // Animation thu hút
                        globalBadge.style.transform = 'scale(1.4)';
                        setTimeout(() => {
                            globalBadge.style.transform = 'scale(1)';
                        }, 300);
                    }
                }
            });
    }
}, 3000);
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.btn-delete-conversation');
    if (btn) {
        const postId = btn.dataset.id;
        if (!confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')) {
            return;
        }
        fetch(`/conversation/destroy/${postId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest(".conversation-item");
                    if (row) {
                        // Hiệu ứng mượt
                        row.style.transition = "all 0.3s ease";
                        row.style.opacity = "0";
                        setTimeout(() => {
                            row.remove();
                            document.querySelector(".count-conversation").innerText =
                                `Tổng hộp thoại: ${data.count}`;
                            updateSTT();
                        }, 300);
                    }
                    // Thông báo
                    console.log(data.message || "Xóa thành công");
                }
            })
            .catch((err) => {
                alert(err.message);
            })
            .finally(() => {
                btn.disabled = false;
                finishLoading();
            });
    }
});

// ===== GROUP CHAT UI LOGIC =====
let selectedGroupUsers = [];

const groupUserSearch = document.getElementById('groupUserSearch');
const groupUserSuggestions = document.getElementById('groupUserSuggestions');
const selectedUsersContainer = document.getElementById('selectedUsers');
const btnCreateGroup = document.getElementById('btnCreateGroup');

if (groupUserSearch) {
    let debounce;
    groupUserSearch.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(async () => {
            const q = groupUserSearch.value.trim().toLowerCase();
            if (!q) {
                groupUserSuggestions.innerHTML = '';
                return;
            }
            try {
                const res = await fetch(`/conversation/search?q=${q}`);
                const data = await res.json();
                groupUserSuggestions.innerHTML = data.map(u => `
                    <div class="list-group-item list-group-item-action d-flex align-items-center gap-2 cursor-pointer"
                         onclick="window.selectUserForGroup(${u.id}, '${(u.profile?.display_name || u.name).replace(/'/g, "\\'")}')">
                        <img src="${u.profile?.avatar ? '/storage/' + u.profile.avatar : '/storage/default-avatar.png'}" 
                             class="rounded-circle" style="width:30px;height:30px;object-fit:cover;">
                        <span class="small">${u.profile?.display_name ?? u.name}</span>
                    </div>
                `).join('');
            } catch (e) {
                console.error("Lỗi tim kiếm", e);
            }
        }, 300);
    });
}

window.selectUserForGroup = function (id, name) {
    if (selectedGroupUsers.includes(id)) return;
    selectedGroupUsers.push(id);

    const badge = document.createElement('span');
    badge.className = 'badge bg-light text-dark border p-2 d-flex align-items-center gap-2 m-1';
    badge.innerHTML = `
        ${name} 
        <i class="bi bi-x-circle cursor-pointer text-danger" onclick="window.removeUserFromGroup(${id}, this)"></i>
    `;
    selectedUsersContainer.appendChild(badge);

    groupUserSearch.value = '';
    groupUserSuggestions.innerHTML = '';
};

window.removeUserFromGroup = function (id, element) {
    selectedGroupUsers = selectedGroupUsers.filter(uid => uid !== id);
    element.parentElement.remove();
};

if (btnCreateGroup) {
    btnCreateGroup.addEventListener('click', async function () {
        const form = document.getElementById('newGroupForm');
        const formData = new FormData(form);
        if (selectedGroupUsers.length < 1) {
            alert('Vui lòng chọn ít nhất 1 thành viên!');
            return;
        }
        selectedGroupUsers.forEach(id => formData.append('user_ids[]', id));
        const csrfTag = document.querySelector('meta[name="csrf-token"]');
        startLoading();
        try {
            const res = await fetch('/conversation/group/create', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfTag.content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                // ===== LOGIC ĐƯA LÊN ĐẦU DANH SÁCH (GIỐNG SENDMESSAGE) =====
                const convo = data.conversation;
                const convoList = document.getElementById('msgConvoList');

                if (convoList) {
                    // Tạo phần tử hội thoại mới
                    const convoItem = document.createElement('div');
                    convoItem.className = 'd-flex align-items-center px-3 py-2 gap-2 convo-item';
                    convoItem.dataset.convoId = convo.id;
                    convoItem.dataset.userId = convo.id;
                    convoItem.dataset.name = convo.name;
                    convoItem.dataset.status = '';
                    convoItem.dataset.online = 'false';
                    convoItem.dataset.isGroup = 'true';

                    const avatar = convo.avatar ? `/storage/${convo.avatar}` : '/storage/default-avatar.png';

                    convoItem.innerHTML = `
                        <img src="${avatar}" class="rounded-circle flex-shrink-0" width="50" height="50" style="object-fit: cover;">
                        <div class="flex-grow-1 text-truncate">
                            <div class="fw-semibold msg-convo-name">${convo.name}</div>
                            <small class="text-muted">Nhóm mới tạo</small>
                        </div>
                        <small class="text-muted">Vừa xong</small>
                    `;

                    // 1. Đưa lên đầu danh sách
                    convoList.prepend(convoItem);

                    // 2. Mở luôn khung chat (Sử dụng window.openChat có sẵn)
                    if (typeof window.openChat === 'function') {
                        window.openChat(convoItem, convo.name, '', false);
                    }
                }
                // 3. Đóng modal
                const modalEl = document.getElementById('newGroupModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                // 4. Reset form
                form.reset();
                selectedGroupUsers = [];
                const selectedContainer = document.getElementById('selectedUsers');
                if (selectedContainer) selectedContainer.innerHTML = '';
                const previewImg = document.getElementById('groupAvatarPreview');
                if (previewImg) previewImg.src = '/storage/default-avatar.png';
            } else {
                alert(data.error || data.message || 'Lỗi server');
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi khởi tạo nhóm.');
        } finally {
            finishLoading();
        }
    });
}
const groupAvatarInput = document.getElementById('groupAvatarInput');
if (groupAvatarInput) {
    groupAvatarInput.addEventListener('change', function (e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function (ex) {
                document.getElementById('groupAvatarPreview').src = ex.target.result;
                if (document.getElementById('dicebearUrlInput')) {
                    document.getElementById('dicebearUrlInput').value = '';
                }
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
}
// ===== DiceBear Avatar Library Logic =====
const btnShowAvatarLibrary = document.getElementById('btnShowAvatarLibrary');
const avatarLibraryModal = document.getElementById('avatarLibraryModal');
const btnRefreshLibrary = document.getElementById('btnRefreshLibrary');
const avatarGrid = document.getElementById('avatarGrid');
const dicebearUrlInput = document.getElementById('dicebearUrlInput');
const groupAvatarPreview = document.getElementById('groupAvatarPreview');

if (btnShowAvatarLibrary) {
    const bsAvatarModal = new bootstrap.Modal(avatarLibraryModal);

    btnShowAvatarLibrary.addEventListener('click', () => {
        generateLibraryAvatars();
        bsAvatarModal.show();
    });

    if (btnRefreshLibrary) {
        btnRefreshLibrary.addEventListener('click', generateLibraryAvatars);
    }
}

function generateLibraryAvatars() {
    if (!avatarGrid) return;
    avatarGrid.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    const styles = ['shapes', 'identicon', 'bottts', 'avataaars', 'pixel-art'];
    let html = '';

    for (let i = 0; i < 12; i++) {
        const randomStyle = styles[Math.floor(Math.random() * styles.length)];
        const randomSeed = Math.random().toString(36).substring(7);
        const url = 'https://api.dicebear.com/9.x/' + randomStyle + '/svg?seed=' + randomSeed;

        html += `
            <div class="col-3 text-center">
                <div class="avatar-item" onclick="selectLibraryAvatar('${url}')">
                    <img src="${url}" class="img-fluid" style="width: 60px; height: 60px; background: #f8f9fa;">
                </div>
            </div>
        `;
    }

    avatarGrid.innerHTML = html;
}

window.selectLibraryAvatar = function (url) {
    if (groupAvatarPreview) groupAvatarPreview.src = url;
    if (dicebearUrlInput) dicebearUrlInput.value = url;
    const groupAvatarInput = document.getElementById('groupAvatarInput');
    if (groupAvatarInput) groupAvatarInput.value = '';

    const modal = bootstrap.Modal.getInstance(avatarLibraryModal);
    if (modal) modal.hide();
};

// ===== INFO PANEL LOGIC (Messenger Style) =====
window.openInfoPanel = function () {
    const panel = document.getElementById('infoPanel');
    const overlay = document.getElementById('infoOverlay');
    if (!panel || !overlay) return;

    // Populate data based on current open conversation
    if (typeof window.currentItem !== 'undefined' && window.currentItem) {
        const isGroup = window.currentItem.dataset.isGroup === 'true';
        const name = window.currentItem.dataset.name;
        const avatar = window.currentItem.querySelector('img')?.src;

        document.getElementById('infoName').textContent = name || 'Chưa rõ';
        if (avatar) document.getElementById('infoAvatar').src = avatar;

        const groupSection = document.getElementById('infoGroupSection');
        const btnEditGroup = document.getElementById('btnEditGroup');
        const btnLeaveGroup = document.getElementById('btnLeaveGroup');
        const btnBlockUser = document.getElementById('btnBlockUser');
        const btnReportUser = document.getElementById('btnReportUser');
        const infoStatus = document.getElementById('infoStatus');
        const btnInfoProfile = document.getElementById('btnInfoProfile');

        if (isGroup) {
            groupSection.classList.remove('d-none');
            btnEditGroup.classList.remove('d-none');
            btnLeaveGroup.classList.remove('d-none');
            btnBlockUser.classList.add('d-none');
            btnInfoProfile.classList.add('d-none');
            btnReportUser.classList.add('d-none');
            infoStatus.textContent = 'Nhóm trò chuyện';

            document.getElementById('infoGroupMembers').innerHTML = '<div class="text-center small text-muted py-2">Chức năng tải thành viên đang hoàn thiện</div>';
        } else {
            groupSection.classList.add('d-none');
            btnEditGroup.classList.add('d-none');
            btnLeaveGroup.classList.add('d-none');
            btnBlockUser.classList.remove('d-none');
            btnInfoProfile.classList.remove('d-none');
            btnReportUser.classList.remove('d-none');
            infoStatus.textContent = 'Người dùng riêng tư';
            const userId = window.currentItem.dataset.userId;
            if (userId && btnInfoProfile) {
                btnInfoProfile.href = `/profile/detail/${userId}`;
            }
        }
    }

    overlay.classList.remove('d-none');
    panel.style.visibility = 'visible';
    setTimeout(() => {
        panel.style.transform = 'translateX(0)';
    }, 10); // Đợi browser render để hiệu ứng trượt hoạt động
};

window.closeInfoPanel = function () {
    const panel = document.getElementById('infoPanel');
    const overlay = document.getElementById('infoOverlay');
    if (panel) panel.style.transform = 'translateX(105%)';
    if (overlay) {
        setTimeout(() => {
            overlay.classList.add('d-none');
            if (panel) panel.style.visibility = 'hidden';
        }, 300); // Khớp với transition time
    }
};

// ===== SEARCH PANEL LOGIC =====
window.openSearchPanel = function () {
    const panel = document.getElementById('searchPanel');
    const overlay = document.getElementById('infoOverlay');
    if (!panel || !overlay) return;

    // Đóng info panel nếu đang mở
    window.closeInfoPanel();

    overlay.classList.remove('d-none');
    panel.style.visibility = 'visible';
    setTimeout(() => {
        panel.style.transform = 'translateX(0)';
    }, 10);

    // Focus vào input
    setTimeout(() => {
        const input = document.getElementById('msgSearchInput');
        if (input) input.focus();
    }, 300);
};

window.closeSearchPanel = function () {
    const panel = document.getElementById('searchPanel');
    const overlay = document.getElementById('infoOverlay');
    if (panel) panel.style.transform = 'translateX(105%)';
    if (overlay) {
        setTimeout(() => {
            overlay.classList.add('d-none');
            if (panel) panel.style.visibility = 'hidden';

            // Xóa kết quả khi đóng
            const input = document.getElementById('msgSearchInput');
            if (input) input.value = '';
            document.getElementById('msgSearchList').innerHTML = '';
            document.getElementById('msgSearchEmptyState').classList.remove('d-none');
        }, 300);
    }
};

let searchTimeout = null;
document.addEventListener('DOMContentLoaded', () => {
    const infoOverlay = document.getElementById('infoOverlay');
    if (infoOverlay) {
        infoOverlay.addEventListener('click', () => {
            window.closeInfoPanel();
            window.closeSearchPanel();
        });
    }

    const btnInfoSearch = document.getElementById('btnInfoSearch');
    if (btnInfoSearch) {
        btnInfoSearch.addEventListener('click', window.openSearchPanel);
    }

    const searchInput = document.getElementById('msgSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            const list = document.getElementById('msgSearchList');
            const emptyState = document.getElementById('msgSearchEmptyState');
            const loading = document.getElementById('msgSearchLoading');

            if (!query) {
                list.innerHTML = '';
                emptyState.classList.remove('d-none');
                loading.classList.add('d-none');
                return;
            }

            emptyState.classList.add('d-none');
            loading.classList.remove('d-none');
            list.innerHTML = '';

            searchTimeout = setTimeout(async () => {
                try {
                    const convoId = window.currentItem ? window.currentItem.dataset.convoId : null;
                    if (!convoId) return;

                    const res = await fetch(`/message/search?q=${encodeURIComponent(query)}&conversation_id=${convoId}`);
                    const data = await res.json();

                    loading.classList.add('d-none');
                    list.innerHTML = '';

                    if (data.length === 0) {
                        list.innerHTML = '<div class="text-center text-muted py-3 small">Không tìm thấy tin nhắn nào</div>';
                        return;
                    }

                    data.forEach(msg => {
                        const avatar = msg.sender.profile?.avatar ? `/storage/${msg.sender.profile.avatar}` : '/storage/default-avatar.png';
                        const name = msg.sender.profile?.display_name || msg.sender.name;

                        let contentHtml = msg.content;
                        if (msg.media && msg.media.length > 0) {
                            contentHtml += `<br><small class="text-primary"><i class="bi bi-image"></i> Đính kèm ${msg.media.length} file</small>`;
                        }

                        // Regex to highlight
                        const regex = new RegExp(`(${query})`, 'gi');
                        contentHtml = contentHtml.replace(regex, '<mark class="bg-warning p-0">$1</mark>');

                        const item = document.createElement('div');
                        item.className = 'd-flex align-items-start gap-2 p-2 border-bottom hover-bg-light cursor-pointer';
                        item.innerHTML = `
                            <img src="${avatar}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold small">${name}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">${msg.time_ago}</small>
                                </div>
                                <div class="small text-muted text-break">${contentHtml}</div>
                            </div>
                        `;
                        item.onclick = () => {
                            const msgId = msg.id;
                            const target = document.getElementById(`message-${msgId}`);
                            window.closeSearchPanel();
                            if (target) {
                                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                target.style.transition = 'background-color 0.5s';
                                target.style.backgroundColor = 'rgba(0, 0, 0, 0.05)';
                                setTimeout(() => {
                                    target.style.backgroundColor = 'transparent';
                                }, 2000);
                            } else {
                                alert('Tin nhắn này ở khá xa, hãy cuộn lên để tải thêm tin nhắn cũ!');
                            }
                        };
                        list.appendChild(item);
                    });
                } catch (error) {
                    loading.classList.add('d-none');
                    list.innerHTML = '<div class="text-center text-danger py-3 small">Lỗi kết nối</div>';
                }
            }, 300);
        });
    }
});
