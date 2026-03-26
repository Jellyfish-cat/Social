let currentTab = "posts";
window.addEventListener("DOMContentLoaded", () => {
    const savedTab = sessionStorage.getItem("currentProfileTab");
    if (savedTab) {
        let btnClass =
            savedTab === "comments" ? "comment-profile" :
                savedTab === "favorites" ? "fav-profile" :
                    savedTab === "likes" ? "like-profile" :
                        "post-profile";
        currentTab = savedTab;
        const tabBtn = document.querySelector(`.${btnClass}`);
        if (tabBtn) setActiveTab(tabBtn);
        loadProfilePosts(savedTab); // load nội dung tab lưu trước
    }
});
document.addEventListener("click", function (e) {
    const postTab = e.target.closest(".post-profile");
    if (postTab) {
        loadProfilePosts("posts");
        setActiveTab(postTab);
        updateHistory("posts");
        return;
    }
    const favTab = e.target.closest(".fav-profile");
    if (favTab) {
        loadProfilePosts("favorites");
        setActiveTab(favTab);
        updateHistory("favorites");
        return;
    }
    const likeTab = e.target.closest(".like-profile");
    if (likeTab) {
        loadProfilePosts("likes");
        setActiveTab(likeTab);
        updateHistory("likes");

        return;
    }
    const commentTab = e.target.closest(".comment-profile");
    if (commentTab) {
        loadProfilePosts("comments");
        setActiveTab(commentTab);
        updateHistory("comments");
        return;
    }

});
function loadProfilePosts(type) {
    const container = document.getElementById("post-list");
    if (!container) return;
    const userId = window.location.pathname.split('/').pop();
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Đang tải...</div>
        </div>
    `;
    startLoading();
    fetch(`/profile/${type}/${userId}`)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
            container.className = "";
            currentTab = type;
            sessionStorage.setItem("currentProfileTab", type);
        })
        .finally(() => {
            finishLoading();
        });
}

function setActiveTab(activeBtn) {
    document.querySelectorAll(".post-profile, .fav-profile, .comment-profile, .like-profile").forEach(btn => {
        btn.classList.remove("fw-semibold", "active-tab");
        btn.classList.add("text-muted");
    });

    activeBtn.classList.add("fw-semibold", "active-tab");
    activeBtn.classList.remove("text-muted");
}
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
                type === "comments" ? "comment-profile" :
                    type === "favorites" ? "fav-profile" :
                        type === "likes" ? "like-profile" :
                            "post-profile";

            const tabBtn = document.querySelector(`.${btnClass}`);
            if (tabBtn) setActiveTab(tabBtn);
            loadProfilePosts(type);
        }
    }
    else { }
});
window.addEventListener("DOMContentLoaded", () => {
    if (!document.getElementById("post-list")) return;
    const urlParams = new URLSearchParams(window.location.search);
    let currentType = urlParams.get('tab') || sessionStorage.getItem("currentProfileTab") || "posts";

    let btnClass =
        currentType === "comments" ? "comment-profile" :
            currentType === "favorites" ? "fav-profile" :
                currentType === "likes" ? "like-profile" :
                    "post-profile";
    currentTab = currentType;
    const tabBtn = document.querySelector(`.${btnClass}`);
    if (tabBtn) setActiveTab(tabBtn);
    loadProfilePosts(currentType);
    const url = new URL(window.location);
    url.searchParams.set('tab', currentType);
    window.history.replaceState({ tab: currentType }, '', url);
});

