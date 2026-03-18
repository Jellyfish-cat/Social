document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btn-favorite");
    if (!btn) return;

    const postId = btn.dataset.id;
    const favoriteIcons = document.querySelectorAll(`.btn-favorite[data-id="${postId}"] i`);
    fetch(`/posts/favorite/${postId}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            favoriteIcons.forEach(icon => {
                icon.classList.toggle("text-warning");
                if (icon.classList.contains("text-warning")) {
                    icon.classList.replace("bi-bookmark", "bi-bookmark-fill");
                } else {
                    icon.classList.replace("bi-bookmark-fill", "bi-bookmark");
                }
            });

        });

});