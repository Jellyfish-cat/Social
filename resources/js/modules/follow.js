
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".follow-btn");
    if (!btn) return; // Nếu không phải nút follow thì thoát
    const userId = btn.dataset.id;
    const authid = btn.dataset.authid;
    startLoading();
    fetch(`/follows/store/${userId}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.error("Lỗi:", data.message || "Không thể thực hiện follow");
                return;
            }

            // Xóa cache tìm kiếm để cập nhật lại thứ tự ưu tiên
            if (typeof window.clearSearchCache === 'function') {
                window.clearSearchCache();
            }
            const followCounts = document.querySelectorAll(`.follow-count[data-id="${userId}"]`);
            followCounts.forEach(el => {
                el.innerText = data.following_count + " đang theo dõi";
            });
            const followingcount = document.querySelectorAll(`.following-count[data-authid="${authid}"]`);
            followingcount.forEach(el => {
                el.innerText = data.follower_count +  " người theo dõi";
            });
            if (btn.classList.contains("btn-primary")) {
                btn.classList.replace("btn-primary", "btn-light");
                btn.innerText = "Đang theo dõi";
            } else {
                btn.classList.replace("btn-light", "btn-primary");
                btn.innerText = "Theo dõi";
            }
        })
        .catch(error => {
            console.error("Lỗi khi xử lý follow:", error);
        })
        .finally(() => {
            finishLoading();
        });
});

document.addEventListener("click", function (e) {
    if (window.Fancybox && Fancybox.getInstance()) return;
    const btn = e.target.closest(".open-follow");
    if (!btn) return;
    const userId = btn.dataset.id;
    const type = btn.dataset.type;
    if (window.matchMedia("(max-width: 992px)").matches) {
        window.location.href = `/follows/detail/${userId}`;
        return;
    }
    const action = btn.dataset.action;
    startLoading();
    fetch(`/follows/detail/${userId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-type': type,
        }
    })
        .then(res => res.text())
        .then(html => {
            document.getElementById("followDetailContent").innerHTML = html;
            const modalEl = document.getElementById("followDetailModal");
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        })
        .finally(() => {
            finishLoading();
        });
});