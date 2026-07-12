@extends('layouts.staff')

@section('title', 'Chấm công của tôi - CineHome')
@section('page-title', 'Chấm công của tôi')

@section('content')

{{-- THÔNG BÁO --}}
@if(session('success'))
<div class="mb-6 rounded-xl border border-green-500/20 bg-green-500/10 p-4 text-green-400">
    <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-red-400">
    <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- CỘT TRÁI: CHECK IN / OUT HÔM NAY --}}
    <div class="lg:col-span-1 space-y-6">
        
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md text-center">
            <h2 class="text-xl font-black text-white mb-2">Hôm nay</h2>
            <p class="text-[#d99a32] text-lg font-bold mb-6">
                {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}
            </p>

            <div class="space-y-4">
                @if(!$chamCongHomNay)
                    {{-- Chưa chấm công --}}
                    <div class="rounded-2xl border border-dashed border-gray-600 bg-gray-800/30 p-4">
                        <p class="text-gray-400 mb-4">Bạn chưa bắt đầu ca làm việc</p>
                        <form method="POST" action="{{ route('staff.cham-congs.check-in') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-green-500 to-green-600 py-4 font-black text-white shadow-lg transition hover:scale-105">
                                <i class="fa-solid fa-fingerprint text-2xl mb-1 block"></i>
                                VÀO CA (CHECK IN)
                            </button>
                        </form>
                    </div>
                @elseif(!$chamCongHomNay->gio_ra)
                    {{-- Đã vào ca, chưa ra ca --}}
                    <div class="rounded-2xl border border-[#d99a32]/30 bg-[#d99a32]/10 p-4">
                        <p class="text-green-400 font-bold mb-1">
                            <i class="fa-solid fa-clock"></i> Đã vào ca lúc: {{ $chamCongHomNay->gio_vao }}
                        </p>
                        <p class="text-gray-400 mb-4 text-sm">Hãy nhớ ra ca khi kết thúc công việc</p>

                        <form method="POST" action="{{ route('staff.cham-congs.check-out') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-red-500 to-red-600 py-4 font-black text-white shadow-lg transition hover:scale-105">
                                <i class="fa-solid fa-door-open text-2xl mb-1 block"></i>
                                RA CA (CHECK OUT)
                            </button>
                        </form>
                    </div>
                @else
                    {{-- Đã hoàn thành ca --}}
                    <div class="rounded-2xl border border-green-500/30 bg-green-500/10 p-4 text-left">
                        <div class="text-center mb-4">
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-green-500/20 text-3xl text-green-400 mb-2">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <h3 class="text-lg font-bold text-green-400">Đã hoàn thành ca</h3>
                        </div>
                        
                        <div class="space-y-2 text-sm text-gray-300">
                            <div class="flex justify-between">
                                <span>Giờ vào:</span>
                                <span class="font-bold text-white">{{ $chamCongHomNay->gio_vao }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Giờ ra:</span>
                                <span class="font-bold text-white">{{ $chamCongHomNay->gio_ra }}</span>
                            </div>
                            <div class="flex justify-between border-t border-white/10 pt-2">
                                <span>Giờ làm:</span>
                                <span class="font-bold text-[#d99a32]">{{ $chamCongHomNay->so_gio_lam }}h</span>
                            </div>
                            @if($chamCongHomNay->so_gio_tang_ca > 0)
                            <div class="flex justify-between">
                                <span>Tăng ca:</span>
                                <span class="font-bold text-yellow-400">{{ $chamCongHomNay->so_gio_tang_ca }}h</span>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 text-xs text-gray-500 text-left space-y-1">
                <p><strong>Quy định giờ giấc:</strong></p>
                <p>- Giờ hành chính: 08:00 - 17:00</p>
                <p>- Nghỉ trưa: 12:00 - 13:00</p>
                <p>- Sau 08:05 tính là đi muộn.</p>
                <p>- Trước 17:00 tính là về sớm.</p>
            </div>
        </div>

    </div>

    {{-- CỘT PHẢI: LỊCH SỬ CHẤM CÔNG THÁNG --}}
    <div class="lg:col-span-2">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
            
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-black text-white">Lịch sử chấm công</h2>
                
                {{-- BỘ LỌC --}}
                <form method="GET" action="{{ route('staff.cham-congs.index') }}" class="flex gap-2">
                    <select name="thang" class="rounded-xl border border-white/10 bg-[#101010] px-4 py-2 text-white focus:border-[#d99a32] outline-none">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $thang == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                    
                    <select name="nam" class="rounded-xl border border-white/10 bg-[#101010] px-4 py-2 text-white focus:border-[#d99a32] outline-none">
                        @for($y = date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $nam == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                        @endfor
                    </select>
                    
                    <button type="submit" class="rounded-xl bg-white/10 px-4 py-2 font-bold text-white transition hover:bg-white/20">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                </form>
            </div>

            {{-- BẢNG LỊCH SỬ --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-sm text-left">
                    <thead class="bg-[#1f1f1f] text-gray-300">
                        <tr>
                            <th class="px-4 py-3">Ngày</th>
                            <th class="px-4 py-3 text-center">Giờ vào</th>
                            <th class="px-4 py-3 text-center">Giờ ra</th>
                            <th class="px-4 py-3 text-center">Giờ làm</th>
                            <th class="px-4 py-3 text-center">Tăng ca</th>
                            <th class="px-4 py-3 text-center">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-white">
                        @forelse($chamCongs as $cc)
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-3 font-bold text-[#d99a32]">
                                    {{ \Carbon\Carbon::parse($cc->ngay)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ $cc->gio_vao ?? '--:--' }}
                                    @if($cc->di_muon)
                                        <div class="text-[10px] text-red-400 mt-1">Đi muộn</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ $cc->gio_ra ?? '--:--' }}
                                    @if($cc->ve_som)
                                        <div class="text-[10px] text-red-400 mt-1">Về sớm</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-bold">
                                    {{ $cc->so_gio_lam }}h
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-yellow-400">
                                    {{ $cc->so_gio_tang_ca > 0 ? $cc->so_gio_tang_ca . 'h' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($cc->nghi_khong_phep)
                                        <span class="rounded-full bg-red-500/20 px-2 py-1 text-xs font-bold text-red-400">Nghỉ không phép</span>
                                    @elseif($cc->nghi_phep)
                                        <span class="rounded-full bg-blue-500/20 px-2 py-1 text-xs font-bold text-blue-400">Nghỉ phép</span>
                                    @elseif(!$cc->gio_ra)
                                        <span class="rounded-full bg-yellow-500/20 px-2 py-1 text-xs font-bold text-yellow-400">Chưa ra ca</span>
                                    @else
                                        <span class="rounded-full bg-green-500/20 px-2 py-1 text-xs font-bold text-green-400">Hoàn thành</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Chưa có dữ liệu chấm công trong tháng này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- THỐNG KÊ NHANH THÁNG NÀY --}}
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 border-t border-white/10 pt-6">
                <div class="text-center">
                    <p class="text-sm text-gray-400">Tổng ngày công</p>
                    <p class="text-2xl font-black text-green-400">
                        {{ $chamCongs->where('nghi_phep', false)->where('nghi_khong_phep', false)->count() }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-400">Tổng giờ làm</p>
                    <p class="text-2xl font-black text-white">
                        {{ $chamCongs->sum('so_gio_lam') }}h
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-400">Tổng giờ tăng ca</p>
                    <p class="text-2xl font-black text-yellow-400">
                        {{ $chamCongs->sum('so_gio_tang_ca') }}h
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-400">Đi muộn / Về sớm</p>
                    <p class="text-2xl font-black text-red-400">
                        {{ $chamCongs->where('di_muon', true)->count() }} / {{ $chamCongs->where('ve_som', true)->count() }}
                    </p>
                </div>
            </div>

        </div>
    </div>

</div>

@endsection
