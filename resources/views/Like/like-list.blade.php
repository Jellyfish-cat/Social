@extends($layout)

@section('content')

@php
    $userid = $item->user->id ?? null;
@endphp

<style>
#followDetailModal .modal-dialog { border-radius: 12px; }
#followDetailModal .modal-content { border-radius: 12px; border: none; }
.follower-list::-webkit-scrollbar { width: 6px; }
.follower-list::-webkit-scrollbar-thumb { background: #dbdbdb; border-radius: 6px; }
.follower-list::-webkit-scrollbar-thumb:hover { background: #c7c7c7; }
</style>

<div class="d-flex flex-column w-100 bg-white " style="border-radius: 12px; height: 100%; max-height: 480px;">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
        <div style="width: 38px;"></div>
        <h6 class="fw-bold m-0 text-center flex-grow-1">Người đã thích</h6>
        <button type="button" class="btn border-0 shadow-none p-2" data-bs-dismiss="modal">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Search -->
    <div class="p-3 pb-2 border-bottom">
        <div class="input-group" style="background:#efefef;border-radius:8px;height:36px;">
            <span class="ps-3 pe-2 text-muted"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control border-0 shadow-none bg-transparent"
                   placeholder="Tìm kiếm" id="searchFollower">
        </div>
    </div>

    <!-- List -->
    <div class="follower-list" style="overflow-y:auto;flex-grow:1;">
        <div class="p-3">

        @forelse($values as $value)

            <div class="d-flex align-items-center justify-content-between mb-3 follower-item">

                <!-- User info -->
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('profile.detail', $value->id) }}">
                        <img src="{{ asset('storage/' . ($value->profile->avatar ?? 'default-avatar.png')) }}"
                             class="rounded-circle" width="44" height="44">
                    </a>

                    <div>
                        <a href="{{ route('profile.detail', $value->id) }}"
                           class="fw-bold text-dark text-decoration-none">
                            {{ $value->name }}
                        </a>
                        <div class="text-muted">
                            {{ $value->profile->display_name ?? $value->name }}
                        </div>
                    </div>
                </div>

                <!-- Follow button -->
                <div>
                    @if(Auth::check() && Auth::id() !== $value->id)

                        @php
                            $isFollowing = Auth::user()
                                ->following
                                ->contains('id', $value->id);
                        @endphp

                        <button
                            class="btn {{ $isFollowing ? 'btn-light' : 'btn-primary' }} btn-sm follow-btn"
                            data-id="{{ $value->id }}">
                            {{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
                        </button>

                    @endif
                </div>

            </div>

        @empty
            <div class="text-center text-muted py-4">
                Chưa có lượt thích nào.
            </div>
        @endforelse

        </div>
    </div>

</div>

<script>
const input = document.getElementById('searchFollower');

if (input) {
    input.addEventListener('input', function(e) {
        const text = e.target.value.toLowerCase();

        document.querySelectorAll('.follower-item').forEach(item => {
            const name = item.innerText.toLowerCase();
            item.style.display = name.includes(text) ? 'flex' : 'none';
        });
    });
}
</script>

@endsection