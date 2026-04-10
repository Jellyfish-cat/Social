          <div class="tab-pane fade show active" id="posts-content" role="tabpanel">
            @if($checktopic)
                    <h5 class="fw-bold mb-3 px-2">Bài viết chủ đề : {{$display_name}}</h5>
            @else
                    <h5 class="fw-bold mb-3 px-2">Khám phá bài viết</h5>
            @endif
                    @forelse($posts as $post)
             @include('posts.post_item', ['post' => $post])
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-camera fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Chưa có bài viết nào được đăng.</p>
                </div>
            @endforelse
          </div>