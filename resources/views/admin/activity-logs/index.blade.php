@extends('layouts.admin')

@section('page-title', 'Nhật ký hệ thống')
@section('page-title', 'Nhật ký hoạt động hệ thống')
@section('page-subtitle', 'Theo dõi thao tác người dùng và quản trị')

@section('content')
<div class="admin-panel">
    <div class="panel-header">
        <div><h5>Nhật ký</h5><small>{{ $logs->total() }} bản ghi</small></div>
    </div>

    <form class="mb-4 grid gap-3 md:grid-cols-3" method="GET">
        <input name="keyword" value="{{ request('keyword') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" placeholder="Hành động, mô tả">
        
        <select name="chuc_nang" class="rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white">
            <option value="">Tất cả chức năng</option>
            @foreach($modules as $module)
                <option value="{{ $module }}" @selected(request('chuc_nang') === $module)>{{ $module }}</option>
            @endforeach
        </select>
        <button class="btn-admin"><i class="fa-solid fa-filter"></i> Lọc</button>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Thời gian</th>
                <th>Người dùng</th>
                <th>Chức năng</th>
                <th>Hành động / Mô tả</th>
                <th>Địa chỉ IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    
                    <td>{{ $log->nguoiDung?->ho_ten ?? 'Hệ thống' }}</td>
                    
                    <td>{{ $log->chuc_nang ?? '-' }}</td>
                    
                    <td><strong>{{ $log->hanh_dong }}</strong><br><small>{{ $log->mo_ta }}</small></td>
                    
                    <td>{{ $log->dia_chi_ip }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-gray-400">Chưa có nhật ký hoạt động.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
