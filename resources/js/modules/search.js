document.addEventListener("click", function(e){
    const btn = e.target.closest(".btn-cancel-Search");
    if(!btn) return;
    const form = btn.closest(".search-form");
    const input = form.querySelector(".search-input");
    input.value = "";
    input.focus(); // focus lại cho user gõ tiếp
});
document.addEventListener("click", function(e){
    const btn = e.target.closest(".btn-Search");
    if(!btn) return;
    e.preventDefault();
    const form = btn.closest("form");
    const input = form.querySelector("input[name='q']");
    const keyword = input.value;
    if (!keyword) return;
    startLoading();
    window.location.href = `/search?q=${encodeURIComponent(keyword)}`;
    finishLoading();
});
/*let currentTab = "post";
window.addEventListener("DOMContentLoaded", () => {
    const savedTab = sessionStorage.getItem("currentProfileTab");
    if (savedTab) {
        let btnClass =
            savedTab === "people" ? "people-tab" :
                        "post-tab";
        currentTab = savedTab;
        const tabBtn = document.querySelector(`.${btnClass}`);
        if (tabBtn) setActiveTab(tabBtn);
        loadProfilePosts(savedTab); // load nội dung tab lưu trước
    }
});*/
document.addEventListener("click", function (e) {
    const postTab = e.target.closest("#post-tab");
    if (postTab) {
        loadProfilePosts("post");
        setActiveTab(postTab);
        return;
    }
    const peopleTab = e.target.closest("#people-tab");
    if (peopleTab) {
        loadProfilePosts("people");
        setActiveTab(peopleTab);
        return;
    }


});
function loadProfilePosts(type) {
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
        })
        .finally(() => {
            finishLoading();
        });
}
function setActiveTab(activeTab) {
    document.querySelectorAll('#searchTabs .nav-link').forEach(tab => {
        tab.classList.remove('active');
        tab.classList.add('text-dark', 'hover-bg-light');
    });
    // Bật active cho tab vừa click
    activeTab.classList.add('active');
    activeTab.classList.remove('text-dark', 'hover-bg-light');
}