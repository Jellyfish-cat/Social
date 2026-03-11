<div class="card shadow-sm">
    <div class="card-header fw-bold">
        Bình luận
    </div>

    <div class="card-body" style="max-height:500px; overflow-y:auto;">

        @forelse($post->comments as $comment)
            <div class="d-flex mb-3">
                <img src="{{ asset('storage/' . ($comment->user->profile->avatar ?? 'default.jpg')) }}"
                     class="rounded-circle me-2"
                     style="width:35px;height:35px;object-fit:cover;">

                <div>
                    <div class="fw-bold small">
                        {{ $comment->user->profile->display_name ?? $comment->user->name }}
                    </div>
                    <div class="small">
                        {{ $comment->content }}
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted small">Chưa có bình luận nào.</p>
        @endforelse

    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {

    let commentPanel = document.getElementById("comment-panel");
    let currentPostId = null;

    const observer = new IntersectionObserver((entries) => {

        entries.forEach(entry => {

            if (entry.isIntersecting && entry.intersectionRatio >= 0.8) {

                let postId = entry.target.dataset.id;

                if (currentPostId !== postId) {

                    currentPostId = postId;

                    fetch(`/posts/comments/${postId}`)
                        .then(response => response.text())
                        .then(html => {

                            commentPanel.innerHTML = html;

                        });
                }
            }

        });

    }, {
        threshold: 0.8
    });
    document.querySelectorAll(".post-item").forEach(post => {
        observer.observe(post);
    });

});
</script>