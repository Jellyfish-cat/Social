<div class="card shadow-sm border-0 rounded-4 p-3 mb-4">
    <h5 class="fw-bold mb-3 px-2">Kết quả Chủ đề</h5>
    @php
        $post_count = App\Models\Post::whereHas('topics', function ($q) use ($topics) {
            $q->whereIn('topics.id', $topics->pluck('id'));
        })->count();
    @endphp
    @if(isset($topics) && $topics->count() > 0)
        <!-- Giao diện hiển thị danh sách dạng lưới (Grid) -->
        <div class="row g-3 px-2">
            @foreach($topics as $topic)
            <div class="col-12 col-md-6 col-lg-12">
                <button class="text-decoration-none text-dark d-block topic-show" data-id="{{ $topic->id }}">
                    <div class="d-flex align-items-center p-3 border rounded-4 hover-bg-light transition-all cursor-pointer h-100">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-hash fs-4"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold mb-1 text-truncate">
                                {{ $topic->name }}
                            </h6>
                            <span class="text-muted small">
                                {{ number_format($post_count ?? 0) }} bài viết
                            </span>
                        </div>
                    </div>
                </button>
            </div>
            @endforeach
        </div>
    @else
        <!-- Giao diện Skeleton khi không tìm thấy Topic nào -->
        <div class="text-center py-5">
            <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                <i class="bi bi-tags fs-1 text-muted"></i>
            </div>
            <h6 class="fw-bold text-dark">Chưa có chủ đề nào phù hợp với: "{{ $keyword ?? 'từ khóa này' }}"</h6>
            <p class="text-muted small">Hãy thử một từ khóa khác ngắn gọn hơn.</p>
        </div>
    @endif
</div>
