    @extends($layout)

    @section('content')
<style>
    /* Ghi đè CSS cho modal chỉ áp dụng khi load list này */
    #followDetailModal .modal-dialog {
        border-radius: 12px;
    }
    #followDetailModal .modal-content {
        border-radius: 12px;
        border: none;
    }
    /* Thanh cuộn của danh sách */
    .follower-list::-webkit-scrollbar {
        width: 6px;
    }
    .follower-list::-webkit-scrollbar-thumb {
        background: #dbdbdb; 
        border-radius: 6px;
    }
    .follower-list::-webkit-scrollbar-thumb:hover {
        background: #c7c7c7; 
    }
</style>

<div class="d-flex flex-column w-100 bg-white" style="border-radius: 12px; height: 100%; max-height: 480px;">
    <!-- Header: Người theo dõi -->
    <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
        <div style="width: 38px;"></div> <!-- placeholder để cân bằng với nút close -->
        <h6 class="fw-bold m-0 text-center flex-grow-1" style="font-size: 16px;">Người theo dõi</h6>
        <button type="button" class="btn border-0 shadow-none p-2" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg" style="font-size: 18px;"></i>
        </button>
    </div>

    <!-- Thanh tìm kiếm -->
    <div class="p-3 pb-2 border-bottom">
        <div class="input-group align-items-center" style="background-color: #efefef; border-radius: 8px; height: 36px; overflow: hidden;">
            <span class="ps-3 pe-2 bg-transparent text-muted" style="font-size: 14px;">
                <i class="bi bi-search"></i>
            </span>
            <input  autocomplete="off" type="text" class="form-control bg-transparent border-0 shadow-none p-0" placeholder="Tìm kiếm" id="searchFollower" style="font-size: 14px;">
        </div>
    </div>

    <!-- Danh sách -->
    <div class="follower-list" style="overflow-y: auto; flex-grow: 1;">
        <div class="p-3">
            @forelse($values as $value)
            <div class="d-flex align-items-center justify-content-between mb-3 follower-item">
                <!-- Avatar & Tên -->
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('profile.detail', $value->id) }}" class="text-decoration-none">
                        <img src="{{ asset('storage/' . ($value->profile->avatar ?? 'default-avatar.png')) }}" 
                             class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover; border: 1px solid #ddd;">
                    </a>
                    <div class="d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center gap-1">
                            <a href="{{ route('profile.detail', $value->id) }}" class="text-decoration-none text-dark fw-bold" style="font-size: 14px; line-height: 1;">
                                {{ $value->name }}
                            </a>
                            <!-- Nếu là đang xem bản thân thì k cần hiện thêm chữ "Theo dõi" bên cạnh tên user giống trong ảnh -->
                        </div>
                        <div class="text-muted" style="font-size: 13px; line-height: 1.2; margin-top: 4px;">
                            {{ $value->profile->display_name ?? $value->name }}
                        </div>
                    </div>
                </div>
                
                <!-- Nút hành động -->
                <div>
                    @if(Auth::check() && Auth::id() === $user->id)
                        {{--xem danh sách của chính mình --}}
                        <button 
                            class="follow-btn btn btn-sm fw-semibold px-4 rounded-pill 
                            {{ $user->following->contains($value->id) ? 'btn-light' : 'btn-primary' }}"
                            data-id="{{ $value->id }}"  data-authid="{{Auth::id()}}">
                            {{ $user->following->contains($value->id) ? 'Đang Theo dõi' : 'Theo dõi' }}
                        </button>
                                            @else
                        {{-- xem danh sách của người khác Hiện Theo dõi / Đang theo dõi --}}
                        @if(Auth::check() && Auth::id() !== $value->id)
                            @php
                                $isFollowing = Auth::user()->following->contains('id', $value->id);
                            @endphp
                            <button  data-authid="{{Auth::id()}}" class="btn {{ $isFollowing ? 'btn-light' : 'btn-primary' }} fw-semibold px-3 py-1 btn-sm follow-btn shadow-none" 
                                    data-id="{{$value->id}}" style="font-size: 14px; border-radius: 8px; {{ $isFollowing ? 'background-color: #efefef;' : '' }}">
                                {{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4 w-100" style="font-size: 14px;">
                Không có người theo dõi nào.
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    // JS logic cho ô tìm kiếm
    var searchInput = document.getElementById('searchFollower');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const text = e.target.value.toLowerCase().trim();
            const items = document.querySelectorAll('.follower-item');
            
            items.forEach(item => {
                const nameLink = item.querySelector('.fw-bold');
                const name = nameLink ? nameLink.innerText.toLowerCase() : '';
                
                const displayDiv = item.querySelector('.text-muted');
                const displayName = displayDiv ? displayDiv.innerText.toLowerCase() : '';
                
                if (name.includes(text) || displayName.includes(text)) {
                    item.style.setProperty('display', 'flex', 'important');
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }
</script>
@endSection