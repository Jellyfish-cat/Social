@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    {{-- 
        LƯU Ý: Nếu bạn đang chạy localhost, Gmail sẽ không thấy được ảnh từ asset().
        Bạn nên thay đường dẫn dưới đây bằng một link ảnh công khai (ví dụ trên Imgur) 
        để kiểm tra hiển thị chính xác nhất.
    --}}
    <img src="{{ asset('storage/logo.png') }}" class="logo" alt="{{ config('app.name') }}" style="width: 80px; height: auto;">
</a>
</td>
</tr>
