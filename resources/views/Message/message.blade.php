@foreach($messages as $index => $msg)

    @php
        $showTime = false;

        if ($index == 0) {
            $showTime = true;
        } else {
            $prev = $messages[$index - 1];
            $diff = $msg->created_at->diffInMinutes($prev->created_at);

            if ($diff > 5) {
                $showTime = true;
            }
        }
    @endphp

    @if($showTime)
        <div class="msg-bubble-time">
            {{ $msg->created_at->format('H:i d/m') }}
        </div>
    @endif

    <div class="msg-bubble-row {{ $msg->sender_id == auth()->id() ? 'mine' : '' }}">

        @if($msg->sender_id != auth()->id())
            <img src="{{ asset('storage/' . ($msg->sender->profile->avatar ?? 'default-avatar.png')) }}"
                 class="msg-bubble-avatar">
        @endif

        <div class="msg-bubble {{ $msg->sender_id == auth()->id() ? 'mine' : 'theirs' }}">
            @foreach($msg->media as $media)

                @if($media->type === 'image')
                             <a href="{{ asset('storage/' . $media->file_path) }}" 
                data-fancybox="gallery-{{Auth::id() }}">
                    <img src="{{ asset('storage/' . $media->file_path) }}" style="max-width:200px;border-radius:12px;" class="mb-1"></a>
                @elseif($media->type === 'video')
                <a href="{{ asset('storage/' . $media->file_path) }}" 
                        class="video-link"
                data-fancybox="gallery-{{Auth::id() }}">
                    <video src="{{ asset('storage/' . $media->file_path) }}" style="max-width:200px;border-radius:12px;" controls class="mb-1"></video></a>
                @endif
            @endforeach
            @if($msg->content)
                <div>{{ $msg->content }}</div>
            @endif
        </div>

    </div>

@endforeach