@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng')
@section('page-title', 'Chi tiết khách hàng')
@section('page-subtitle', 'Hồ sơ, trạng thái, vé và voucher của khách hàng')

@section('content')
<div class="space-y-6">

    <a href="{{ route('admin.khach-hang.index') }}"
        class="inline-flex items-center gap-2 text-sm font-bold text-[#d99a32] no-underline transition hover:translate-x-1">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại danh sách
    </a>
    <a href="{{ route('admin.khach-hang.edit', $khachHang) }}"
        class="inline-flex items-center gap-2 rounded-2xl bg-[#d99a32] px-5 py-3 text-sm font-black text-black no-underline transition hover:bg-[#f4c56a]">
        <i class="fa-solid fa-pen-to-square"></i>
        Sửa hồ sơ
    </a>

    {{-- HỒ SƠ KHÁCH HÀNG --}}
    <div
        class="rounded-3xl border border-[#d99a32]/30 bg-gradient-to-br from-[#1b0d05] via-[#121212] to-[#070707] p-6 shadow-2xl">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm text-gray-400">Khách hàng</p>
                <h2 class="mt-2 text-3xl font-black text-white">
                    {{ $khachHang->ho_ten }}
                </h2>
                <p class="mt-1 text-gray-400">{{ $khachHang->email }}</p>
            </div>

            <div>
                @if($khachHang->trang_thai_hoat_dong)
                <span class="rounded-full bg-green-500/15 px-4 py-2 text-sm font-black text-green-400">
                    Đang hoạt động
                </span>
                @else
                <span class="rounded-full bg-red-500/15 px-4 py-2 text-sm font-black text-red-400">
                    Tài khoản bị khóa
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- THỐNG KÊ KHÁCH HÀNG --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
        <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
            <p class="text-sm text-gray-400">Tổng vé</p>
            <h3 class="mt-2 text-3xl font-black text-white">{{ number_format($tongVe) }}</h3>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
            <p class="text-sm text-gray-400">Tổng chi tiêu</p>
            <h3 class="mt-2 text-3xl font-black text-white">
                {{ number_format($tongChiTieu, 0, ',', '.') }}đ
            </h3>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
            <p class="text-sm text-gray-400">Hạng thành viên</p>
            <h3 class="mt-2 text-3xl font-black text-[#f4c56a]">
                {{ strtoupper($khachHang->thanhVien->ten_hang ?? '---') }}
            </h3>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
            <p class="text-sm text-gray-400">Điểm hiện tại</p>
            <h3 class="mt-2 text-3xl font-black text-white">
                {{ number_format($khachHang->thanhVien->diem_hien_tai ?? 0) }}
            </h3>
        </div>

        @if($khachHang->thanhVien)
        <a href="{{ route('admin.thanh-vien.show', $khachHang->thanhVien) }}"
            class="inline-flex items-center gap-2 rounded-2xl bg-[#d99a32] px-5 py-3 text-sm font-black text-black no-underline transition hover:bg-[#f4c56a]">
            <i class="fa-solid fa-crown"></i>
            Xem thẻ thành viên & điểm
        </a>
        @endif
    </div>



    {{-- THÔNG TIN CÁ NHÂN --}}
    <div class="rounded-3xl border border-white/10 bg-[#121212] p-6">
        <h2 class="mb-5 text-xl font-black">Thông tin cá nhân</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <p class="text-sm text-gray-400">Ngày sinh</p>
                <p class="mt-1 font-bold text-white">{{ $khachHang->ngay_sinh?->format('d/m/Y') ?? '---' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Ngày tạo tài khoản</p>
                <p class="mt-1 font-bold text-white">{{ $khachHang->created_at?->format('d/m/Y') ?? '---' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Trạng thái email</p>
                <p class="mt-1 font-bold text-white">
                    {{ $khachHang->email_verified_at ? 'Đã xác minh' : 'Chưa xác minh' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Số điện thoại</p>
                <p class="mt-1 font-bold text-white">
                    {{ $khachHang->so_dien_thoai ?? '---' }}
                </p>
            </div>
        </div>
    </div>

    {{-- VÉ GẦN ĐÂY --}}
    <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#121212]">
        <div class="border-b border-white/10 px-6 py-5">
            <h2 class="text-xl font-black">Vé gần đây</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left">
                <thead class="bg-white/[0.04] text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-6 py-4">Mã vé</th>
                        <th class="px-6 py-4">Phim</th>
                        <th class="px-6 py-4">Ghế</th>
                        <th class="px-6 py-4">Tiền</th>
                        <th class="px-6 py-4">Trạng thái</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                    @forelse($veGanDay as $ve)
                    <tr class="hover:bg-white/[0.04]">
                        <td class="px-6 py-4 font-black text-[#d99a32]">{{ $ve->ma_ve }}</td>
                        <td class="px-6 py-4 text-white">{{ $ve->ten_phim }}</td>
                        <td class="px-6 py-4 text-gray-400">{{ $ve->ma_ghe }}</td>
                        <td class="px-6 py-4 text-white">{{ number_format($ve->tong_tien, 0, ',', '.') }}đ</td>
                        <td class="px-6 py-4 text-gray-400">{{ $ve->trang_thai }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            Khách hàng chưa mua vé nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- VOUCHER KHÁCH HÀNG --}}
    <div class="rounded-3xl border border-white/10 bg-[#121212] p-6">
        <h2 class="mb-5 text-xl font-black">Voucher khách đang có</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @forelse($khachHang->vouchersCaNhan as $voucherCaNhan)
            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                <p class="text-sm text-gray-400">Mã voucher</p>
                <h3 class="mt-2 break-all text-xl font-black text-[#f4c56a]">
                    {{ $voucherCaNhan->ma_voucher_ca_nhan }}
                </h3>

                <p class="mt-4 text-sm text-gray-400">Giá trị</p>
                <p class="text-2xl font-black text-white">
                    {{ number_format($voucherCaNhan->voucher->gia_tri_giam ?? 0, 0, ',', '.') }}đ
                </p>

                <div class="mt-4">
                    @if($voucherCaNhan->da_su_dung)
                    <span class="rounded-full bg-gray-500/15 px-3 py-1 text-xs font-bold text-gray-400">Đã dùng</span>
                    @else
                    <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400">Chưa
                        dùng</span>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-gray-500">Khách chưa có voucher nào.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection