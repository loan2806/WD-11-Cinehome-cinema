@extends('layouts.admin')

@section('title', 'Thông báo nhân viên - CineHome')
@section('page-title', 'Thông báo của tôi')
@section('page-subtitle', 'Theo dõi các thông báo dành riêng cho tài khoản nhân viên')

@section('content')
@php
    $typeLabels = [
        'he_thong' => 'Hệ thống',
        've' => 'Vé',
        'diem' => 'Điểm',
        'voucher' => 'Voucher',
        'hang_thanh_vien' => 'Hạng thành viên',
        'tai_khoan' => 'Tài khoản',
    ];

    $typeIcons = [
        'he_thong' => 'fa-bell',
        've' => 'fa-ticket',
        'diem' => 'fa-star',
        'voucher' => 'fa-gift',
        'hang_thanh_vien' => 'fa-ranking-star',
        'tai_khoan' => 'fa-user-gear',
    ];
@endphp

<div class="mx-auto max-w-6xl">

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
            <div class="text-sm font-semibold text-gray-400">
                Tổng thông báo
            </div>

            <div class="mt-2 text-3xl font-black text-white">
                {{ $notificationStats['total'] }}
            </div>
        </div>

        <div class="rounded-3xl border border-[#d99a32]/20 bg-[#d99a32]/10 p-5">
            <div class="text-sm font-semibold text-[#f4c56a]">
                Chưa đọc
            </div>

            <div class="mt-2 text-3xl font-black text-[#f4c56a]">
                {{ $notificationStats['unread'] }}
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
            <div class="text-sm font-semibold text-gray-400">
                Đã đọc
            </div>

            <div class="mt-2 text-3xl font-black text-white">
                {{ $notificationStats['read'] }}
            </div>
        </div>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('staff.notifications.index') }}"
            class="rounded-xl px-4 py-2 text-sm font-bold no-underline transition
            {{ $activeType === null
                ? 'bg-[#d99a32] text-[#2b1208]'
                : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
            Tất cả
        </a>

        @foreach ($typeLabels as $value => $label)
            <a href="{{ route('staff.notifications.index', ['loai' => $value]) }}"
                class="rounded-xl px-4 py-2 text-sm font-bold no-underline transition
                {{ $activeType === $value
                    ? 'bg-[#d99a32] text-[#2b1208]'
                    : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#111111]">

        @forelse ($thongBaos as $notification)

            <a
                href="{{ $notification->duong_dan ?: route('staff.notifications.index') }}"
                class="flex gap-4 border-b border-white/5 px-5 py-5 no-underline transition hover:bg-white/5">

                <div class="flex h-12 w-12 flex-none items-center justify-center rounded-2xl bg-[#d99a32]/10 text-[#f4c56a]">
                    <i class="fa-solid {{ $typeIcons[$notification->loai_thong_bao] ?? 'fa-bell' }}"></i>
                </div>

                <div class="min-w-0 flex-1">

                    <div class="flex flex-wrap items-start justify-between gap-3">

                        <div>
                            <div class="font-black text-white">
                                {{ $notification->tieu_de }}
                            </div>

                            <div class="mt-1 text-sm leading-6 text-gray-400">
                                {{ $notification->noi_dung }}
                            </div>
                        </div>

                        <span class="rounded-full bg-white/5 px-3 py-1 text-xs font-bold text-gray-400">
                            {{ $typeLabels[$notification->loai_thong_bao] ?? 'Thông báo' }}
                        </span>

                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        {{ $notification->created_at->format('d/m/Y H:i') }}
                        ·
                        {{ $notification->created_at->diffForHumans() }}
                    </div>

                </div>

            </a>

        @empty

            <div class="px-6 py-16 text-center">

                <i class="fa-regular fa-bell-slash text-4xl text-[#d99a32]"></i>

                <h3 class="mt-4 text-xl font-black text-white">
                    Chưa có thông báo
                </h3>

                <p class="mt-2 text-sm text-gray-500">
                    Hiện chưa có thông báo phù hợp với bộ lọc.
                </p>

            </div>

        @endforelse

    </div>

    @if ($thongBaos->hasPages())
        <div class="mt-5">
            {{ $thongBaos->links() }}
        </div>
    @endif

</div>
@endsection