document.addEventListener("click", function (e) {
    if (window.Fancybox && Fancybox.getInstance()) return;
    const btn = e.target.closest(".open-list-interaction");
    const type = btn ? btn.dataset.type : null;
    if (!btn) return;
    const ID = btn.dataset.id;
    let url;
    if (type === "follower" || type === "following") {
        url = '/follows/detail/' + ID;
    } else if (type === "post") {
        url = '/posts/like_list/' + ID;
    }
    else if (type === "comment") {
        url = '/comments/like_list/' + ID;
    }

    if (window.matchMedia("(max-width: 992px)").matches) {
        window.location.href = url;
        return;
    }
    startLoading();
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-type': type,
        }
    })
        .then(res => res.text())
        .then(html => {
            document.getElementById("InteractionListContent").innerHTML = html;
            const modalEl = document.getElementById("InteractionListModal");
            const modal = new bootstrap.Modal(modalEl);
            // modal.show(); (đã gọi bên dưới)
            modal.show();
        })
        .finally(() => {
            finishLoading();
        });
});
window.closeLikeModal = function () {
    const modalEl = document.getElementById("InteractionListModal");
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    }
};