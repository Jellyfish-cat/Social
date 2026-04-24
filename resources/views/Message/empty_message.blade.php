<div id="chatEmptyUI"
 class="msg-header d-flex flex-column align-items-center  py-4 border-bottom">
   <img src="{{ asset('storage/' . ($otherUser->profile->avatar ?? 'default-avatar.png')) }}"
            class="rounded-circle mb-2"
            style="width:80px;height:80px;object-fit:cover;">

        {{-- Name --}}
        <div class="fw-semibold fs-5" id="nameUser">
            {{$otherUser->profile->display_name ?? ''}}
        </div>

        {{-- Username / info --}}
        <div class="text-muted small mb-3">
            {{ $otherUser->name ?? '' }}
        </div>

        {{-- Button --}}
        <a href="{{ route('profile.detail', $otherUser->id ?? '') }}" 
        class="btn btn-light rounded-pill px-3">
            Xem trang cá nhân
        </a>
    <div class="text-muted mt-4">
        Hãy bắt đầu cuộc trò chuyện 👋
    </div>
</div>
