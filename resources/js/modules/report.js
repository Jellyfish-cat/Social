let currentReportTab = "post";
let tab = null;
window.addEventListener("DOMContentLoaded", () => {
    const resultsContainer = document.getElementById("report-results-container");
    if (!resultsContainer) return;
    tab = resultsContainer.dataset.tab;
    const urlParams = new URLSearchParams(window.location.search);
    let currentType = urlParams.get('tab') || sessionStorage.getItem("currentReportTab") || "post";

    let btnId = currentType === "people" ? "people-tab" :
        currentType === "comment" ? "comment-tab" :
            "post-tab";

    currentReportTab = currentType;
    const tabBtn = document.getElementById(btnId);

    if (tabBtn) {
        setActiveReportTab(tabBtn);
        loadReportTab(currentType);
    }

    // Sync state with history
    const url = new URL(window.location);
    url.searchParams.set('tab', currentType);
    window.history.replaceState({ tab: currentType }, '', url);
});

document.addEventListener("change", function (e) {
    const statusFilter = e.target.closest("#filter-status");
    if (statusFilter) {
        tab = statusFilter.value;
        loadReportTab(currentReportTab);
    }
});

document.addEventListener("click", function (e) {
    const postTab = e.target.closest("#post-tab");
    if (postTab) {
        loadReportTab("post");
        setActiveReportTab(postTab);
        updateReportHistory("post");
        return;
    }

    const peopleTab = e.target.closest("#people-tab");
    if (peopleTab) {
        loadReportTab("people");
        setActiveReportTab(peopleTab);
        updateReportHistory("people");
        return;
    }

    const commentTab = e.target.closest("#comment-tab");
    if (commentTab) {
        loadReportTab("comment");
        setActiveReportTab(commentTab);
        updateReportHistory("comment");
        return;
    }
});

function loadReportTab(type) {
    const container = document.getElementById("report-results-container");
    if (!container) return;

    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Đang tải báo cáo...</div>
        </div>
    `;

    if (typeof startLoading === 'function') startLoading();
    
    // Luôn ưu tiên lấy status từ dropdown nếu có
    const statusFilter = document.getElementById("filter-status");
    if (statusFilter) tab = statusFilter.value;

    fetch(`/admin/reports/tab/${type}/${tab}?status=${tab}`)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
            currentReportTab = type;
            sessionStorage.setItem("currentReportTab", type);
        })
        .catch(err => {
            container.innerHTML = `<div class="alert alert-danger m-3">Lỗi khi tải dữ liệu: ${err.message}</div>`;
        })
        .finally(() => {
            if (typeof finishLoading === 'function') finishLoading();
        });
}

function setActiveReportTab(activeTab) {
    document.querySelectorAll('#reportTabs .nav-link').forEach(tab => {
        tab.classList.remove('active');
        tab.classList.add('text-dark', 'hover-bg-light');
    });
    // Bật active cho tab vừa click
    activeTab.classList.add('active');
    activeTab.classList.remove('text-dark', 'hover-bg-light');
}

function updateReportHistory(type) {
    const url = new URL(window.location);
    url.searchParams.set('tab', type);
    window.history.pushState({ tab: type }, '', url);
}

// Bắt sự kiện back/forward browser
window.addEventListener("popstate", function (e) {
    if (e.state && e.state.tab) {
        let type = e.state.tab;
        if (type !== currentReportTab) {
            let btnId = type === "people" ? "people-tab" :
                type === "comment" ? "comment-tab" :
                    "post-tab";

            const tabBtn = document.getElementById(btnId);
            if (tabBtn) setActiveReportTab(tabBtn);
            loadReportTab(type);
        }
    }
});

// Xử lý sự kiện phân trang AJAX (nếu cần)
document.addEventListener('click', function (e) {
    const paginationLink = e.target.closest('#report-results-container .pagination a');
    if (paginationLink) {
        e.preventDefault();
        const url = new URL(paginationLink.href);
        const type = url.searchParams.get('tab') || currentReportTab;
        const page = url.searchParams.get('page');

        loadReportPage(type, page);
    }
});

function loadReportPage(type, page) {
    const container = document.getElementById("report-results-container");
    if (typeof startLoading === 'function') startLoading();

    const statusFilter = document.getElementById("filter-status");
    if (statusFilter) tab = statusFilter.value;

    fetch(`/admin/reports/tab/${type}/${tab}?page=${page}&status=${tab}`)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .finally(() => {
            if (typeof finishLoading === 'function') finishLoading();
        });
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete-report');
    if (btn) {
        e.stopPropagation();
        e.preventDefault();
        const postId = btn.dataset.id;
        if (!confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/reports/destroy/${postId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest(".report-item");
                    if (row) {
                        // Hiệu ứng mượt
                        row.style.transition = "all 0.3s ease";
                        row.style.opacity = "0";
                        setTimeout(() => {
                            row.remove();
                            document.querySelector(".count-report").innerText =
                                `Tổng: ${data.count}`;
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

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-check-report');
    if (btn) {
        e.stopPropagation();
        e.preventDefault(); // In case of 'a' tag
        const postId = btn.dataset.id;
        const action = btn.dataset.action || 'hide';

        let confirmMsg = 'Duyệt xử lý tin này. Bạn chắc chắn chứ?';
        if (action === 'hide') confirmMsg = 'Ẩn toàn bộ nội dung này. Bạn chắc chứ?';
        if (action === 'dismiss') confirmMsg = 'Bỏ qua báo cáo do nội dung không vi phạm?';
        if (action === 'restore') confirmMsg = 'Khôi phục lại nội dung và trạng thái báo cáo?';
        if (!confirm(confirmMsg)) {
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (typeof startLoading === 'function') startLoading();
        fetch(`/reports/check/${postId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: action })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // If it's the dropdown link, we need to hide the row
                    const row = btn.closest(".report-item");
                    if (row) {
                        // Hiệu ứng mượt
                        row.style.transition = "all 0.3s ease";
                        row.style.opacity = "0";
                        setTimeout(() => {
                            row.remove();
                            document.querySelector(".count-report").innerText =
                                `Tổng: ${data.count}`;
                            updateSTT();
                        }, 300);
                    }
                    // Thông báo
                    console.log(data.message || "Duyệt thành công");
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