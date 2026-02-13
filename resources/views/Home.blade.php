@extends('layouts.app')

@section('content')

@foreach ($posts as $post)
    <div class="card mb-3 p-3">

        <h5>{{ $post->user->profile->display_name }}</h5>
        <p>{{ $post->content }}</p>

        <button onclick="likePost({{ $post->id }})">
            ❤️
        </button>

    </div>
@endforeach

{{ $posts->links() }}

@endsection
<script>
function likePost(postId) {
    fetch('/api/likes/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ post_id: postId })
    });
}
</script>
