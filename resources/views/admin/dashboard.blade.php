@extends('layouts.app_admin')

@section('content')
    <h1>Dashboard Admin</h1>

    <div class="row">
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body">
                    Tổng user: {{ \App\Models\User::count() }}
                </div>
            </div>
        </div>
    </div>
@endsection