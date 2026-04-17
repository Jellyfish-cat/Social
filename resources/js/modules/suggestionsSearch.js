let cacheSearch = {};
let searchTimeout = null;

// Hàm để các module khác (như Follow) có thể xóa cache khi dữ liệu thay đổi
window.clearSearchCache = function () {
    cacheSearch = {};
};

document.addEventListener("DOMContentLoaded", function () {
    const inputSearch = document.querySelector(".search-input");
    const suggestionsContainer = document.querySelector(".search-wrapper #suggestions");

    if (inputSearch && suggestionsContainer) {
        // Hàm gọi API và vẽ kết quả
        const fetchSuggestions = async (q = "") => {
            if (q && cacheSearch[q]) {
                renderSuggestions(cacheSearch[q]);
                return;
            }

            suggestionsContainer.innerHTML = `
                <div class="list-group-item text-center text-muted py-3 border-0">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div> 
                    <span class="ms-2">Đang tải...</span>
                </div>
            `;
            suggestionsContainer.style.display = 'block';

            try {
                let res = await fetch(`/search/suggestions?q=${encodeURIComponent(q)}`);
                if (!res.ok) throw new Error("Mạng lỗi!");
                let data = await res.json();
                if (q) cacheSearch[q] = data;
                renderSuggestions(data);
            } catch (error) {
                console.error("Lỗi hệ thống Search:", error);
                suggestionsContainer.innerHTML = `
                    <div class="list-group-item text-danger text-center border-0">
                        <i class="bi bi-exclamation-circle"></i> Kết nối gián đoạn!
                    </div>
                `;
            }
        };

        // 1. Lắng nghe khi gõ phím
        inputSearch.addEventListener("input", () => {
            let q = inputSearch.value.trim().toLowerCase();
            clearTimeout(searchTimeout);

            if (!q) {
                fetchSuggestions("");
                return;
            }

            searchTimeout = setTimeout(() => fetchSuggestions(q), 300);
        });

        // 2. Lắng nghe khi focus vào ô tìm kiếm
        inputSearch.addEventListener("focus", () => {
            let q = inputSearch.value.trim().toLowerCase();
            fetchSuggestions(q);
        });

        // 3. Đóng bảng khi click ra ngoài
        document.addEventListener("click", function (event) {
            if (!inputSearch.contains(event.target) && !suggestionsContainer.contains(event.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });

        // 4. Xử lý xóa lịch sử tìm kiếm (Event Delegation)
        suggestionsContainer.addEventListener("click", async function (event) {
            const deleteBtn = event.target.closest(".btn-delete-search");
            if (deleteBtn) {
                event.preventDefault();
                event.stopPropagation();

                const id = deleteBtn.getAttribute("data-id");
                try {
                    const response = await fetch(`/search/destroy/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    if (response.ok) {
                        // Xóa sạch cache để đảm bảo lần sau lấy dữ liệu mới
                        cacheSearch = {};
                        // Cập nhật lại danh sách gợi ý
                        let q = inputSearch.value.trim().toLowerCase();
                        fetchSuggestions(q);
                    } else {
                        console.error("Xóa thất bại");
                    }
                } catch (error) {
                    console.error("Lỗi xóa history:", error);
                }
            }
        });
    }

    function renderSuggestions(data) {
        const hasHistory = data.history && data.history.length > 0;
        const hasTopics = data.topics && data.topics.length > 0;
        const hasUsers = data.users && data.users.length > 0;
        const hasPosts = data.posts && data.posts.length > 0;
        const hasComments = data.comments && data.comments.length > 0;
        const hasMessages = data.messages && data.messages.length > 0;
        const hasConversations = data.conversations && data.conversations.length > 0;
        const hasReports = data.reports && data.reports.length > 0;
        const hasAdminHistory = data.admin_history && data.admin_history.length > 0;

        if (!hasHistory && !hasTopics && !hasUsers && !hasPosts && !hasComments && !hasMessages && !hasConversations && !hasReports && !hasAdminHistory) {
            suggestionsContainer.innerHTML = `
                <div class="list-group-item text-center text-muted border-0">
                    Không có gợi ý nào.
                </div>
            `;
            suggestionsContainer.style.display = 'block';
            return;
        }

        let html = '';

        // 1. Gợi ý (Người dùng + Bài viết + Admin Types)
        let suggestions = [];
        // thêm users
        if (hasUsers) {
            data.users.forEach(u => {
                const dName = (u.profile && u.profile.display_name) ? u.profile.display_name : (u.display_name || "");
                const displayText = dName ? `${dName} (@${u.name})` : u.name;
                suggestions.push({
                    type: 'user',
                    text: displayText,
                    action: `selectSearchItem(null, '${u.name.replace(/'/g, "\\'")}')`
                });
            });
        }

        // thêm posts
        if (hasPosts) {
            data.posts.forEach(p => {
                const uName = p.user?.profile?.display_name || p.user?.name || "Ẩn danh";
                let contentSnippet = p.content ? p.content.substring(0, 50) + "..." : "Bài viết";
                suggestions.push({
                    type: 'post',
                    text: `Bài viết [${uName}]: ${contentSnippet}`,
                    action: `window.location.href='/posts/detail/${p.id}'`
                });
            });
        }

        // thêm comments
        if (hasComments) {
            data.comments.forEach(c => {
                const uName = c.user?.profile?.display_name || c.user?.name || "Ẩn danh";
                let contentSnippet = c.content ? c.content.substring(0, 50) + "..." : "Bình luận";
                suggestions.push({
                    type: 'comment',
                    text: `Bình luận [${uName}]: ${contentSnippet}`,
                    action: `selectSearchItem(null, '${c.content.replace(/'/g, "\\'")}')`
                });
            });
        }

        // thêm messages
        if (hasMessages) {
            data.messages.forEach(m => {
                const uName = m.sender?.profile?.display_name || m.sender?.name || "Ẩn danh";
                let contentSnippet = m.content ? m.content.substring(0, 50) + "..." : "Tin nhắn";
                suggestions.push({
                    type: 'message',
                    text: `Tin nhắn [${uName}]: ${contentSnippet}`,
                    action: `selectSearchItem(null, '${m.content.replace(/'/g, "\\'")}')`
                });
            });
        }

        // thêm conversations
        if (hasConversations) {
            data.conversations.forEach(c => {
                const names = c.users.map(u => u.profile?.display_name || u.name).join(', ');
                suggestions.push({
                    type: 'conversation',
                    text: `Hộp thoại: ${names}`,
                    action: `window.location.href='/admin/conversations/${c.id}'`
                });
            });
        }

        // thêm reports
        if (hasReports) {
            data.reports.forEach(r => {
                suggestions.push({
                    type: 'report',
                    text: `Báo cáo [${r.category}]: ${r.reason.substring(0, 30)}...`,
                    action: `selectSearchItem(null, '${r.reason.replace(/'/g, "\\'")}')`
                });
            });
        }

        // thêm admin_history
        if (hasAdminHistory) {
            data.admin_history.forEach(h => {
                suggestions.push({
                    type: 'history',
                    text: `Lịch sử: ${h.keyword} (${h.user?.profile?.display_name || 'User'})`,
                    action: `selectSearchItem(null, '${h.keyword.replace(/'/g, "\\'")}')`
                });
            });
        }
        if (suggestions.length > 0) {
            html += `<div class="list-group-item disabled bg-light fw-bold py-1 small text-uppercase">Gợi ý</div>`;

            suggestions.forEach(item => {
                html += `
                    <button type="button"
                        class="list-group-item list-group-item-action d-flex align-items-center border-0"
                        onclick="${item.action}">
                        
                        <i class="bi bi-search me-3 text-muted"></i>
                        <span class="text-truncate">${item.text}</span>

                    </button>
                `;
            });
        }
        // 2. Chủ đề
        if (hasTopics) {
            html += `<div class="list-group-item disabled bg-light fw-bold py-1 mt-2 small text-uppercase">Chủ đề</div>`;
            data.topics.forEach(t => {
                html += `
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center border-0"
                        onclick="selectSearchItem(null, '${t.name.replace(/'/g, "\\'")}')">
                        <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width:30px;height:30px">
                             <i class="bi bi-hash text-primary"></i>
                        </div>
                        <span class="fw-semibold">${t.name}</span>
                    </button>
                `;
            });
        }

        // 3. Lịch sử tìm kiếm (Dưới cùng)
        if (hasHistory) {
            html += `<div class="list-group-item disabled bg-light fw-bold py-1 mt-2 small text-uppercase">Tìm kiếm gần đây</div>`;
            data.history.forEach(item => {
                const id = item.id;
                const keyword = item.keyword;
                html += `
                    <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between border-0">
                    <div class="d-flex align-items-center flex-grow-1"
                        onclick="selectSearchItem(null, '${keyword.replace(/'/g, "\\'")}')"
                        style="cursor: pointer;">
                        <i class="bi bi-clock-history me-3 text-muted"></i>
                        <span class="text-truncate">${keyword}</span>
                    </div>
                    <a class="btn btn-sm btn-delete-search ms-2 text-muted"
                    data-id="${id}"
                    style="font-size: 18px; line-height: 1; cursor: pointer;">
                        &times;
                    </a>
                </div>
                `;
            });
        }

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';
    }

    window.selectSearchItem = function (id, keyword) {
        const inputSearch = document.querySelector(".search-input");
        const suggestionsContainer = document.querySelector(".search-wrapper #suggestions");
        if (inputSearch) inputSearch.value = keyword;
        if (suggestionsContainer) suggestionsContainer.style.display = 'none';
        const form = document.querySelector(".search-wrapper.search-form");
        if (form) form.submit();
    }
});