@extends('layouts.admin')

@section('title', 'Chi tiết vé - CineHome')
@section('page-title', 'Chi tiết vé')
@section('page-subtitle', 'Thông tin chi tiết vé xem phim')

@section('content')

<div class="rounded-3xl border border-white/10 bg-[#101010] shadow-xl">

    <div class="flex flex-col gap-4 border-b border-white/10 p-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h5 class="text-2xl font-black text-white">Chi tiết vé</h5>
            <p class="mt-1 text-sm text-gray-400">
                Mã vé:
                <span class="font-bold text-[#d99a32]">{{ $veXemPhim->ma_ve }}</span>
            </p>
        </div>

        <a href="{{ route('admin.ve-xem-phims.index') }}"
            class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Quay lại
        </a>
    </div>

    <div class="grid grid-cols-1 gap-5 p-6 xl:grid-cols-2">

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-ticket text-[#d99a32]"></i>
                Thông tin vé
            </h6>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Mã vé</span>
                    <span class="font-bold text-[#d99a32]">{{ $veXemPhim->ma_ve }}</span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Loại vé</span>
                    <span class="font-bold text-white">
                        {{ $veXemPhim->loai_ve === 'tai_quay' ? 'Tại quầy' : 'Trực tuyến' }}
                    </span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Trạng thái</span>

                    @if ($veXemPhim->trang_thai === 'da_thanh_toan')
                        <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-300">Đã thanh toán</span>
                    @elseif ($veXemPhim->trang_thai === 'da_su_dung')
                        <span class="rounded-full bg-gray-500/15 px-3 py-1 text-xs font-bold text-gray-300">Đã sử dụng</span>
                    @else
                        <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-bold text-red-300">Đã hủy</span>
                    @endif
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Tổng tiền</span>
                    <span class="font-bold text-white">{{ number_format($veXemPhim->tong_tien) }}đ</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-400">Tiền hoàn</span>
                    <span class="font-bold text-white">{{ number_format($veXemPhim->tien_hoan) }}đ</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-user text-[#d99a32]"></i>
                Người mua
            </h6>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Khách hàng</span>
                    <span class="font-bold text-white">
                        {{ $veXemPhim->nguoiDung->ho_ten ?? 'Khách mua tại quầy' }}
                    </span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Email</span>
                    <span class="text-white">{{ $veXemPhim->nguoiDung->email ?? '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-400">Nhân viên bán</span>
                    <span class="font-bold text-white">{{ $veXemPhim->nhanVien->ho_ten ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-film text-[#d99a32]"></i>
                Phim & ghế
            </h6>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Tên phim</span>
                    <span class="font-bold text-white">{{ $veXemPhim->ten_phim }}</span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Rạp</span>
                    <span class="text-white">{{ $veXemPhim->ten_rap ?? '-' }}</span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Phòng</span>
                    <span class="text-white">{{ $veXemPhim->ten_phong ?? '-' }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-400">Ghế</span>
                    <span class="font-bold text-[#d99a32]">{{ $veXemPhim->ma_ghe ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-clock text-[#d99a32]"></i>
                Thời gian
            </h6>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Thời gian chiếu</span>
                    <span class="font-bold text-white">
                        {{ $veXemPhim->thoi_gian_chieu?->format('d/m/Y H:i') ?? '-' }}
                    </span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Ngày tạo vé</span>
                    <span class="text-white">{{ $veXemPhim->created_at?->format('d/m/Y H:i') }}</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-400">Cập nhật cuối</span>
                    <span class="text-white">{{ $veXemPhim->updated_at?->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

    </div>

    @if ($veXemPhim->trang_thai === 'da_thanh_toan')
        <div class="flex flex-wrap gap-3 border-t border-white/10 p-6">
            <form method="POST" action="{{ route('admin.ve-xem-phims.su-dung', $veXemPhim) }}">
                @csrf
                @method('PATCH')

                <button type="submit"
                    onclick="return confirm('Xác nhận vé này đã sử dụng?')"
                    class="rounded-2xl bg-green-500/15 px-5 py-3 font-bold text-green-300 transition hover:-translate-y-0.5 hover:bg-green-500/25">
                    <i class="fa-solid fa-check mr-2"></i>
                    Đánh dấu đã sử dụng
                </button>
            </form>

            <form method="POST" action="{{ route('admin.ve-xem-phims.huy', $veXemPhim) }}">
                @csrf
                @method('PATCH')

                <button type="submit"
                    onclick="return confirm('Bạn có chắc muốn hủy vé này?')"
                    class="rounded-2xl bg-red-500/15 px-5 py-3 font-bold text-red-300 transition hover:-translate-y-0.5 hover:bg-red-500/25">
                    <i class="fa-solid fa-ban mr-2"></i>
                    Hủy vé
                </button>
            </form>
        </div>
    @endif

</div>

@endsection