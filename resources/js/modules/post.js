document.addEventListener("click", function(e){
    const btn = e.target.closest(".open-post");
    if(!btn) return;
    const postId = btn.dataset.id;
    const commentIcons = document.querySelectorAll(`.open-post[data-id="${postId}"] i`);
    fetch(`/posts/detail/${postId}`)
    .then(res => res.text())
    .then(html => {
        commentIcons.forEach(icon=>{
            icon.classList.remove("any-pop");
            void icon.offsetWidth;
            icon.classList.add("any-pop");
        });
        document.getElementById("postDetailContent").innerHTML = html;
        const modalEl = document.getElementById("postDetailModal");
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

    });

});