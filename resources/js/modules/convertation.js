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

        // Cập nhật trạng thái hiển thị của footer (input hay thông báo khóa)
        const convoStatus = el.dataset.convoStatus;
        const isGroupChat = el.dataset.isGroup === 'true';

        const previewContainer = document.getElementById('previewMediaContainer');
        const lockedText = document.getElementById('lockedMessageText');
        const footers = document.querySelectorAll('.msg-chat-footer');

        if (lockedText) {
            lockedText.textContent = isGroupChat ? 'Nhóm này đã bị giải tán' : 'Tài khoản này đã bị khóa do vi phạm';
        }

        if (previewContainer) {
            previewContainer.style.display = (convoStatus === 'hide') ? 'none' : 'block';
        }

        footers.forEach(footer => {
            if (convoStatus === 'hide') {
                if (footer.classList.contains('justify-content-center')) {
                    footer.style.display = 'flex';
                } else {
                    footer.style.display = 'none';
                }
            } else {
                if (footer.classList.contains('justify-content-center')) {
                    footer.style.display = 'none';
                } else {
                    footer.style.display = 'flex';
                }
            }
        });

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
                        let isViewing = false;
                        if (typeof window.currentUserId !== 'undefined') {
                            if (incomingMsg.is_group) {
                                isViewing = (window.currentUserId == incomingMsg.conversation_id);
                            } else {
                                isViewing = (window.currentUserId == incomingMsg.sender_id);
                            }
                        }
                        if (isViewing) return;

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

// ===== MODAL DYNAMIC LOADING (open-group) =====
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".open-group");
    if (!btn) return;
    e.preventDefault();

    const action = btn.dataset.action;
    let url = '';

    if (action === 'create') {
        url = '/conversation/group/create';
    } else if (action === 'edit') {
        const convoId = window.currentItem ? window.currentItem.dataset.convoId : null;
        if (!convoId) return;
        url = `/conversation/edit/${convoId}`;
    }

    startLoading();
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.text())
        .then(html => {
            document.getElementById("groupModalContent").innerHTML = html;
            const modalEl = document.getElementById("groupModal");
            const modal = new bootstrap.Modal(modalEl);

            window.selectedGroupUsers = [];
            window.selectedEditGroupUsers = [];

            if (action === 'edit') {
                const hiddenInputs = document.querySelectorAll('#editSelectedUsers input[name="user_ids[]"]');
                hiddenInputs.forEach(input => {
                    window.selectedEditGroupUsers.push(parseInt(input.value));
                });
            }

            modal.show();
        })
        .finally(() => {
            finishLoading();
        });
});

// ===== DYNAMIC EVENTS (Delegation) =====
let searchDebounce;
document.addEventListener('input', async function (e) {
    if (e.target.id === 'groupUserSearch' || e.target.id === 'editGroupUserSearch') {
        const isEdit = e.target.id === 'editGroupUserSearch';
        const suggestionsContainer = document.getElementById(isEdit ? 'editGroupUserSuggestions' : 'groupUserSuggestions');

        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(async () => {
            const q = e.target.value.trim().toLowerCase();
            if (!q) {
                suggestionsContainer.innerHTML = '';
                return;
            }
            try {
                const res = await fetch(`/conversation/search?q=${q}`);
                const data = await res.json();
                const fnName = isEdit ? 'window.selectUserForEditGroup' : 'window.selectUserForGroup';
                suggestionsContainer.innerHTML = data.map(u => `
                    <div class="list-group-item list-group-item-action d-flex align-items-center gap-2 cursor-pointer"
                         onclick="${fnName}(${u.id}, '${(u.profile?.display_name || u.name).replace(/'/g, "\\'")}')">
                        <img src="${u.profile?.avatar ? '/storage/' + u.profile.avatar : '/storage/default-avatar.png'}" 
                             class="rounded-circle" style="width:30px;height:30px;object-fit:cover;">
                        <span class="small">${u.profile?.display_name ?? u.name}</span>
                    </div>
                `).join('');
            } catch (err) {
                console.error("Lỗi tìm kiếm", err);
            }
        }, 300);
    }
});

document.addEventListener('change', function (e) {
    if (e.target.id === 'groupAvatarInput' || e.target.id === 'editGroupAvatarInput') {
        const isEdit = e.target.id === 'editGroupAvatarInput';
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function (ex) {
                const preview = document.getElementById(isEdit ? 'editGroupAvatarPreview' : 'groupAvatarPreview');
                if (preview) preview.src = ex.target.result;
                const dicebearInput = document.getElementById(isEdit ? 'editDicebearUrlInput' : 'dicebearUrlInput');
                if (dicebearInput) dicebearInput.value = '';
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    }
});

document.addEventListener('click', async function (e) {
    if (e.target.id === 'btnShowAvatarLibrary' || e.target.id === 'btnShowEditAvatarLibrary') {
        window.avatarTargetContext = e.target.id === 'btnShowEditAvatarLibrary' ? 'edit' : 'create';
        const libraryModalEl = document.getElementById('avatarLibraryModal');
        if (libraryModalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(libraryModalEl);
            modal.show();
            loadAvatarLibrary();
        }
    }
    if (e.target.id === 'btnCreateGroup') {
        const form = document.getElementById('newGroupForm');
        const formData = new FormData(form);
        e.target.disabled = true;
        if (window.selectedGroupUsers.length < 1) {
            alert('Vui lòng chọn ít nhất 1 thành viên!');
            return;
        }
        window.selectedGroupUsers.forEach(id => formData.append('user_ids[]', id));
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
                const avatarUrl = data.conversation.avatar ? '/storage/' + data.conversation.avatar : '/storage/default-avatar.png';
                const lastMsg = data.conversation.latest_message ? data.conversation.latest_message.content : 'Chưa có tin nhắn';
                const newConvoHtml = `
                    <div class="d-flex align-items-center px-3 py-2 gap-2 convo-item"
                         data-name="${data.conversation.name}"
                         data-status=""
                         data-convo-status="show"
                         data-online="false"
                         data-is-group="true"
                         data-convo-id="${data.conversation.id}"
                         data-user-id="${data.conversation.id}">
                        <img src="${avatarUrl}"
                             class="rounded-circle flex-shrink-0" style="width: 50px; height: 50px; object-fit: cover;">
                        <div class="flex-grow-1 text-truncate">
                            <div class="fw-semibold">
                                ${data.conversation.name}
                                <i class="bi bi-people text-muted ms-1" title="Nhóm"></i>
                            </div>
                            <small class="text-muted">${lastMsg}</small>
                        </div>
                        <small class="text-muted">Vừa xong</small>
                    </div>
                `;
                const convoList = document.getElementById('msgConvoList');
                if (convoList) {
                    convoList.insertAdjacentHTML('afterbegin', newConvoHtml);
                    // Tự động click để mở đoạn chat vừa tạo
                    const newItem = convoList.querySelector(`[data-convo-id="${data.conversation.id}"]`);
                    if (newItem) newItem.click();
                }

                // Đóng modal
                const modalEl = document.getElementById('groupModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    } else {
                        // Cố gắng tạo mới nếu chưa có instance
                        new bootstrap.Modal(modalEl).hide();
                    }
                }

                // Cố gắng ẩn backdrop nếu còn sót lại (Bootstrap 5 bug sometimes)
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(bg => bg.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            } else {
                alert(data.error || data.message || 'Lỗi server');
            }
        } catch (err) {
            console.error(err);
            alert('Lỗi khởi tạo nhóm.');
        } finally {
            e.target.disabled = false;
            finishLoading();
        }
    }

    if (e.target.id === 'btnUpdateGroup') {
        const convoId = document.getElementById('editConvoId').value;
        const form = document.getElementById('editGroupForm');
        const formData = new FormData(form);

        if (window.selectedEditGroupUsers.length < 1) {
            alert('Vui lòng chọn ít nhất 1 thành viên!');
            return;
        }

        formData.delete('user_ids[]');
        window.selectedEditGroupUsers.forEach(id => formData.append('user_ids[]', id));

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const btn = e.target;
        btn.disabled = true;
        startLoading();
        try {
            const res = await fetch(`/conversation/group/update/${convoId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Lỗi cập nhật');
            }
        } catch (err) {
            console.error(err);
            alert('Lỗi cập nhật nhóm.');
        } finally {
            btn.disabled = false;
            finishLoading();
        }
    }

    if (e.target.id === 'btnDissolveGroup' || e.target.closest('#btnDissolveGroup')) {
        const convoId = window.currentItem.dataset.convoId;
        if (!convoId) return;

        if (confirm('Bạn có chắc chắn muốn giải tán nhóm này không? Tất cả tin nhắn sẽ bị xóa và không thể khôi phục.')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            startLoading();
            fetch(`/conversation/destroy/${convoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Lỗi khi giải tán nhóm');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Lỗi kết nối.');
                })
                .finally(() => finishLoading());
        }
    }

    if (e.target.id === 'btnDeleteConversation' || e.target.closest('#btnDeleteConversation')) {
        const convoId = window.currentItem.dataset.convoId;
        if (!convoId) return;

        if (confirm('Bạn có chắc chắn muốn xóa lịch sử trò chuyện này? Các tin nhắn sẽ biến mất với bạn nhưng vẫn còn với người khác.')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            startLoading();
            fetch(`/conversation/clear/${convoId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/message';
                    } else {
                        alert(data.message || 'Lỗi khi xóa đoạn chat');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Lỗi kết nối.');
                })
                .finally(() => finishLoading());
        }
    }
});

// Member Selection Logic
window.selectedGroupUsers = [];
window.selectedEditGroupUsers = [];

window.selectUserForGroup = function (id, name) {
    if (window.selectedGroupUsers.includes(id)) return;
    window.selectedGroupUsers.push(id);
    const container = document.getElementById('selectedUsers');
    const badge = document.createElement('span');
    badge.className = 'badge bg-light text-dark border p-2 d-flex align-items-center gap-2 m-1';
    badge.innerHTML = name + ' <i class="bi bi-x-circle cursor-pointer text-danger" onclick="window.removeUserFromGroup(' + id + ', this)"></i><input type="hidden" name="user_ids[]" value="' + id + '">';
    if (container) container.appendChild(badge);
    document.getElementById('groupUserSearch').value = '';
    document.getElementById('groupUserSuggestions').innerHTML = '';
};

window.removeUserFromGroup = function (id, element) {
    window.selectedGroupUsers = window.selectedGroupUsers.filter(uid => uid !== id);
    element.parentElement.remove();
};

window.selectUserForEditGroup = function (id, name) {
    if (window.selectedEditGroupUsers.includes(id)) return;
    window.selectedEditGroupUsers.push(id);
    const container = document.getElementById('editSelectedUsers');
    const badge = document.createElement('span');
    badge.className = 'badge bg-light text-dark border p-2 d-flex align-items-center gap-2 m-1';
    badge.innerHTML = name + ' <i class="bi bi-x-circle cursor-pointer text-danger" onclick="window.removeUserFromEditGroup(' + id + ', this)"></i><input type="hidden" name="user_ids[]" value="' + id + '">';
    if (container) container.appendChild(badge);
    document.getElementById('editGroupUserSearch').value = '';
    document.getElementById('editGroupUserSuggestions').innerHTML = '';
};

window.removeUserFromEditGroup = function (id, element) {
    window.selectedEditGroupUsers = window.selectedEditGroupUsers.filter(uid => uid !== id);
    element.parentElement.remove();
};

window.avatarTargetContext = 'create';
window.selectLibraryAvatar = function (url) {
    const isCreate = window.avatarTargetContext === 'create';
    const preview = document.getElementById(isCreate ? 'groupAvatarPreview' : 'editGroupAvatarPreview');
    const diceInput = document.getElementById(isCreate ? 'dicebearUrlInput' : 'editDicebearUrlInput');
    const fileInput = document.getElementById(isCreate ? 'groupAvatarInput' : 'editGroupAvatarInput');

    if (preview) preview.src = url;
    if (diceInput) diceInput.value = url;
    if (fileInput) fileInput.value = '';

    const modalEl = document.getElementById('avatarLibraryModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
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
        const btnDissolveGroup = document.getElementById('btnDissolveGroup');

        if (isGroup) {
            groupSection.classList.remove('d-none');
            btnEditGroup.classList.remove('d-none');
            btnLeaveGroup.classList.remove('d-none');
            btnDissolveGroup.classList.add('d-none'); // Ẩn mặc định, sẽ hiện nếu là trưởng nhóm
            btnBlockUser.classList.add('d-none');
            btnInfoProfile.classList.add('d-none');
            btnReportUser.classList.add('d-none');
            infoStatus.textContent = 'Nhóm trò chuyện';
            const conversationId = window.currentItem.dataset.convoId;
            const membersContainer = document.getElementById('infoGroupMembers');

            if (conversationId && membersContainer) {
                membersContainer.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

                fetch(`/conversation/members/${conversationId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            let html = '';
                            let creator = data.creator;
                            data.members.forEach(m => {
                                html += `
                                    <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                                        <img src="${m.avatar}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                        <div class="flex-grow-1 small fw-semibold text-truncate">${m.name}</div>
                                         ${creator == m.id ? '<span class="badge bg-primary">Trưởng nhóm</span>' : ''}
                                        <a href="/profile/detail/${m.id}" class="btn btn-sm btn-light rounded-pill px-3" style="font-size: 11px;">Xem</a>
                                    </div>
                                `;
                            });
                            membersContainer.innerHTML = html || '<div class="text-center text-muted small py-2">Không có thành viên</div>';

                            const authUserId = document.querySelector('meta[name="auth-user-id"]')?.content;
                            if (authUserId == creator) {
                                btnDissolveGroup.classList.remove('d-none');
                            }
                        } else {
                            membersContainer.innerHTML = '<div class="text-center text-muted small py-2">Lỗi tải thành viên</div>';
                        }
                    })
                    .catch(() => {
                        membersContainer.innerHTML = '<div class="text-center text-muted small py-2">Lỗi kết nối</div>';
                    });
            }

            if (conversationId && btnLeaveGroup) {
                btnLeaveGroup.href = `/conversation/leave/${conversationId}`;
            }
            if (conversationId && btnEditGroup) {
                btnEditGroup.href = `/conversation/edit/${conversationId}`;
            }
        } else {
            groupSection.classList.add('d-none');
            btnEditGroup.classList.add('d-none');
            btnLeaveGroup.classList.add('d-none');
            btnDissolveGroup.classList.add('d-none');
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

// ===== AVATAR LIBRARY LOGIC =====
window.loadAvatarLibrary = function () {
    const grid = document.getElementById('avatarGrid');
    if (!grid) return;
    grid.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    let html = '';
    for (let i = 0; i < 12; i++) {
        const seed = Math.random().toString(36).substring(7);
        // Using identicon/bottts/avataaars or any dicebear style
        const styles = [
            'identicon',
            'bottts',
            'avataaars',
            'initials',
            'thumbs',
            'adventurer',
            'adventurer-neutral',
            'big-ears',
            'big-ears-neutral',
            'croodles',
            'croodles-neutral',
            'fun-emoji',
            'icons',
            'lorelei',
            'lorelei-neutral',
            'micah',
            'miniavs',
            'notionists',
            'notionists-neutral',
            'open-peeps',
            'personas',
            'pixel-art',
            'pixel-art-neutral',
            'shapes'
        ];

        const style = styles[Math.floor(Math.random() * styles.length)];
        const url = `https://api.dicebear.com/7.x/${style}/svg?seed=${seed}`;
        html += `
            <div class="col-4 col-md-3 text-center">
                <img src="${url}" class="rounded-circle cursor-pointer border border-2 hover-border-primary" 
                     style="width: 70px; height: 70px; object-fit: cover; transition: all 0.2s;"
                     onclick="window.selectLibraryAvatar('${url}')">
            </div>
        `;
    }
    grid.innerHTML = html;
};

document.addEventListener('click', function (e) {
    const btn = e.target.closest('#btnRefreshLibrary');
    if (btn) {
        window.loadAvatarLibrary();
    }
});
