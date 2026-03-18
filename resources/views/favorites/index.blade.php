@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h3 class="mb-4">Bài viết yêu thích</h3>
    @if($saved->count() > 0)
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            @foreach($saved as $savedItem)
                @php $item = $savedItem->news; @endphp
                @if($item && $item->status == 1)
                    <div class="col">
    <div class="card h-100 border-0 shadow-sm hover-shadow transition-card">
        <div class="ratio ratio-4x3">
            <a href="{{ route('news.chitiet', $item->id) }}">
                <img src="{{ asset('storage/upload/' . $item->image) }}" class="card-img-top object-fit-cover" alt="{{ $item->title }}">
            </a>
        </div>
        <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold">
                <a href="{{ route('news.chitiet', $item->id) }}" class="text-decoration-none text-dark">
                    {{ Str::limit($item->title, 60) }}
                </a>
            </h5>
            <p class="card-text text-muted small flex-grow-1">
                {{ Str::limit($item->description, 80) }}
            </p>
        </div>
        <div class="card-footer bg-transparent border-top-0 d-flex justify-content-between align-items-center pb-3">
            <small class="text-muted"><i class="bi bi-calendar3"></i> {{ $item->created_at->format('d/m/Y') }}</small>
            <button class="btn btn-sm btn-warning rounded-pill px-2 save-btn" data-news-id="{{ $item->id }}">
                <i class="bi bi-bookmark-fill me-1"></i>
                <span>Bỏ lưu</span>
            </button>
        </div>
    </div>
</div>

                @endif
            @endforeach
        </div>
    @else
        <p class="text-muted">Bạn chưa lưu bài viết nào.</p>
    @endif
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.transition-card {
    transition: all 0.3s ease;
}
.card-img-top {
    object-fit: cover;
}
</style>

<script>
document.querySelectorAll('.save-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        const newsId = this.dataset.newsId;
        const icon = this.querySelector('i');
        const text = this.querySelector('span');
        const token = document.querySelector('input[name="_token"]').value;
        this.classList.add('disabled');
        fetch(`/news/saved/${newsId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({})
        })
        .then(res => {
            if (!res.ok) throw new Error('Lỗi server: ' + res.status);
            return res.json();
        })
        .then(data => {
            this.classList.remove('disabled');
            if (data.status === 'unsaved') {
                const card = this.closest('.col');
                if (card) card.remove();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Có lỗi xảy ra, vui lòng thử lại!');
            this.classList.remove('disabled');
        });
    });
});
</script>

@endsection
