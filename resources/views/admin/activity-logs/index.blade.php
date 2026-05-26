@extends('layouts.admin')

@section('title', 'Nhat ky he thong')
@section('page-title', 'Nhat ky hoat dong he thong')
@section('page-subtitle', 'Theo doi thao tac nguoi dung va quan tri')

@section('content')
<div class="admin-panel">
    <div class="panel-header">
        <div><h5>Nhat ky</h5><small>{{ $logs->total() }} ban ghi</small></div>
    </div>

    <form class="mb-4 grid gap-3 md:grid-cols-3" method="GET">
        <input name="keyword" value="{{ request('keyword') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" placeholder="Hanh dong, mo ta">
        <select name="module" class="rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white">
            <option value="">Tat ca module</option>
            @foreach($modules as $module)
                <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
            @endforeach
        </select>
        <button class="btn-admin"><i class="fa-solid fa-filter"></i> Loc</button>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Thoi gian</th>
                <th>Nguoi dung</th>
                <th>Module</th>
                <th>Hanh dong</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $log->user?->name ?? 'He thong' }}</td>
                    <td>{{ $log->module ?? '-' }}</td>
                    <td><strong>{{ $log->action }}</strong><br><small>{{ $log->description }}</small></td>
                    <td>{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-gray-400">Chua co nhat ky.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
