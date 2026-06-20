@extends('layouts.admin')

@section('page-title', 'Lịch sử bảo trì ghế')

@section('content')
<div class="admin-panel">
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h5 class="text-2xl font-black text-white">Lịch sử bảo trì ghế</h5>
            <small class="text-gray-400">Theo dõi lịch trình và trạng thái bảo trì các ghế</small>
        </div>
        <a href="{{ route('admin.ghe-ngois.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại danh sách ghế
        </a>
    </div>

    <div class="mt-6 rounded-2xl border border-white/10 bg-[#0f0f0f] p-6">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Ghế</th>
                    <th>Phòng</th>
                    <th>Người thực hiện</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lichBaoTriGheNgois as $lich)
                    <tr>
                        <td>{{ $lich->thoi_gian_bat_dau->format('d/m/Y H:i') }}</td>
                        <td>{{ $lich->gheNgoi->ma_ghe ?? 'N/A' }}</td>
                        <td>{{ $lich->phongChieu->ten_phong ?? 'N/A' }}</td>
                        <td>{{ $lich->nguoiDung?->ho_ten ?? 'Hệ thống' }}</td>
                        <td>{{ $lich->ly_do ?? '-' }}</td>
                        <td>
                            @php
                                $statusClass = match($lich->trang_thai) {
                                    'cho_thuc_hien' => 'bg-yellow-500/20 text-yellow-400',
                                    'dang_thuc_hien' => 'bg-blue-500/20 text-blue-400',
                                    'da_hoan_thanh' => 'bg-green-500/20 text-green-400',
                                    'da_huy' => 'bg-red-500/20 text-red-400',
                                    default => 'bg-gray-500/20 text-gray-400',
                                };
                                $statusLabel = match($lich->trang_thai) {
                                    'cho_thuc_hien' => 'Chờ thực hiện',
                                    'dang_thuc_hien' => 'Đang thực hiện',
                                    'da_hoan_thanh' => 'Đã hoàn thành',
                                    'da_huy' => 'Đã hủy',
                                    default => $lich->trang_thai,
                                };
                            @endphp
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td>
                            @if($lich->trang_thai === 'cho_thuc_hien' || $lich->trang_thai === 'dang_thuc_hien')
                                <form action="{{ route('admin.lich-bao-tri-ghe-ngois.complete', $lich) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg bg-green-500/20 px-3 py-1.5 text-xs font-bold text-green-300 transition hover:bg-green-500/30">
                                        <i class="fa-solid fa-check mr-1"></i>Kết thúc
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">Chưa có lịch sử bảo trì.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $lichBaoTriGheNgois->links() }}
        </div>
    </div>
</div>
@endsection
