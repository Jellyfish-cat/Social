@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-file-earmark-text"></i> Quản lý lịch sử tìm kiếm
        </h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            Danh sách tìm kiếm
             <span class="badge bg-white text-primary count-search">Tổng: {{ $searchHistorys->total() }}</span>

        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 post-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="40%">Nội dung</th>
                            <th width="40%">Người gửi</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($searchHistorys as $value)
                    <tr class="search-item">
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td class="text-start" >
                            {{ Str::limit($value->keyword, 120) }}
                        </td>
                        <td>
                            {{ $value->user->profile->display_name ?? $value->user->email }}
                        </td>
                       
                        <td class="text-center">
                            <a 
                               class="btn btn-info btn-sm" href = "/search?q={{$value->keyword}}">
                                <i class="bi bi-eye"></i>
                            </a>
                                  <a  class="btn btn-danger btn-sm btn-delete-search"
                                data-id="{{ $value->id }}">
                                    <i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4 count-search">
                            Chưa có từ khóa
                        </td>
                    </tr> 
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
                    <div class="p-3 d-flex justify-content-center">
             {{ $searchHistorys->links() }}
        </div>

            
            <!-- Hiện thẻ phân trang của Bootstrap -->

        </div>

    </div>
</div>
@endsection 