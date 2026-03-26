let currentTabsearch = "post";
window.addEventListener("DOMContentLoaded", () => {
    const savedTab = sessionStorage.getItem("currentSearchTab");
    if (savedTab) {
        let btnClass =
            savedTab === "people" ? "people-tab" :
                savedTab === "topic" ? "topic-tab" :
                    "post-tab";
        currentTabsearch = savedTab;
        const tabBtn = document.querySelector(`.${btnClass}`);
        if (tabBtn) setActiveSearchTab(tabBtn);
        loadSearchPosts(savedTab); // load nội dung tab lưu trước
    }
});
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btn-cancel-Search");
    if (!btn) return;
    const form = btn.closest(".search-form");
    const input = form.querySelector(".search-input");
    input.value = "";
    input.focus(); // focus lại cho user gõ tiếp
});
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btn-Search");
    if (!btn) return;
    e.preventDefault();
    const form = btn.closest("form");
    const input = form.querySelector("input[name='q']");
    const keyword = input.value;
    if (!keyword) return;
    startLoading();
    window.location.href = `/search?q=${encodeURIComponent(keyword)}`;
    finishLoading();
});

document.addEventListener("click", function (e) {
    const postTab = e.target.closest("#post-tab");
    if (postTab) {
        loadSearchPosts("post");
        setActiveSearchTab(postTab);
        updateHistory("post");
        return;
    }
    const peopleTab = e.target.closest("#people-tab");
    if (peopleTab) {
        loadSearchPosts("people");
        setActiveSearchTab(peopleTab);
        updateHistory("people");

        return;
    }
    const topicTab = e.target.closest("#topic-tab");
    if (topicTab) {
        loadSearchPosts("topic");
        setActiveSearchTab(topicTab);
        updateHistory("topic");

        return;
    }

});
function loadSearchPosts(type) {
    const container = document.getElementById("search-results-container");
    if (!container) return;
    const urlParams = new URLSearchParams(window.location.search);
    const keyword = urlParams.get('q') || '';
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Đang tải...</div>
        </div>
    `;
    startLoading();
    fetch(`/search/tab/${type}?q=${encodeURIComponent(keyword)}`)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
            container.className = "";
            currentTabsearch = type;
            sessionStorage.setItem("currentSearchTab", type);
        })
        .finally(() => {
            finishLoading();
        });
}
function setActiveSearchTab(activeTab) {
    document.querySelectorAll('#searchTabs .nav-link').forEach(tab => {
        tab.classList.remove('active');
        tab.classList.add('text-dark', 'hover-bg-light');
    });
    // Bật active cho tab vừa click
    activeTab.classList.add('active');
    activeTab.classList.remove('text-dark', 'hover-bg-light');
}
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".topic-show");
    if (!btn) return;
    const topicId = btn.dataset.id;
    const container = document.getElementById("search-results-container");
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Đang tải...</div>
        </div>
    `;
    startLoading();
    fetch(`/topics/show/${topicId}`)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
        })
        .finally(() => {
            finishLoading();
        });
    updateHistory("topic");
});
//cập nhật lịch sử để back
function updateHistory(type) {
    const url = new URL(window.location);
    url.searchParams.set('tab', type);
    window.history.pushState({ tab: type }, '', url);
}
//bắt click back
window.addEventListener("popstate", function (e) {
    if (e.state && e.state.tab) {
        let type = e.state.tab;
        if (type !== currentTab) {
            let btnClass =
                type === "topic" ? "topic-tab" :
                    type === "people" ? "people-tab" :
                        "post-tab";

            const tabBtn = document.querySelector(`#${btnClass}`);
            if (tabBtn) setActiveSearchTab(tabBtn);
            loadSearchPosts(type);
        }
    }
    else { }
});
window.addEventListener("DOMContentLoaded", () => {
    if (!document.getElementById("search-results-container")) return;
    const urlParams = new URLSearchParams(window.location.search);
    let currentType = urlParams.get('tab') || sessionStorage.getItem("currentSearchTab") || "post";

    let btnClass =
        currentType === "topic" ? "topic-tab" :
            currentType === "people" ? "people-tab" :
                "post-tab";
    currentTabsearch = currentType;
    const tabBtn = document.querySelector(`#${btnClass}`);
    if (tabBtn) setActiveSearchTab(tabBtn);
    loadSearchPosts(currentType);
    const url = new URL(window.location);
    url.searchParams.set('tab', currentType);
    window.history.replaceState({ tab: currentType }, '', url);
});