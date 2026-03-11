@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-collection"></i> Danh sách chủ đề
        </h3>

        <a href="{{ route('topics.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Thêm mới
        </a>
    </div>


    <!-- Card -->
    <div class="card shadow-sm">

        <!-- Card header -->
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Quản lý chủ đề</h5>
        </div>


        <!-- Card body -->
        <div class="card-body p-0">
    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle mb-0 topic-table">

            <thead class="table-light text-center">
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Tên chủ đề</th>
                    <th>Mô tả</th>
                    <th width="120">Hành động</th>
                </tr>
            </thead>

            <tbody>

            @forelse($topics as $topic)

                <tr>

                    <td class="text-center">
                        {{ $loop->iteration }}
                    </td>

                    <td class="fw-semibold">
                        {{ $topic->name }}
                    </td>

                    <td class="text-muted">
                        {{ $topic->description }}
                    </td>

                    <td class="text-center">

                        <a href="{{ route('topics.edit',$topic->id) }}"
                           class="btn btn-warning btn-sm">
                           <i class="bi bi-pencil"></i>
                        </a>

                        <form action="{{ route('topics.destroy',$topic->id) }}"
                              method="POST"
                              class="d-inline">

                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="4" class="text-center text-muted p-4">
                        Chưa có chủ đề
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>
</div>

        <div class="card-footer text-muted">
            Tổng chủ đề: {{ $topics->count() }}
        </div>

    </div>

</div>

@endsection