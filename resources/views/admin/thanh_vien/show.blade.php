@extends('layouts.admin')

@section('title', 'Chi tiết thành viên')
@section('page-title', 'Chi tiết thành viên')
@section('page-subtitle', 'Theo dõi hồ sơ, điểm, vé và voucher của khách hàng')

@section('content')
<div class="space-y-6">

    <a href="{{ route('admin.thanh-vien.index') }}"
        class="inline-flex items-center gap-2 text-sm font-bold text-[#d99a32] no-underline transition hover:translate-x-1">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại danh sách
    </a>

    {{-- HỒ SƠ THÀNH VIÊN --}}
    <div
        class="rounded-3xl border border-[#d99a32]/30 bg-gradient-to-br from-[#1b0d05] via-[#121212] to-[#070707] p-6 shadow-2xl">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div>
                <p class="text-sm text-gray-400">Mã thành viên</p>
                <h2 class="mt-2 text-3xl font-black text-[#f4c56a]">
                    {{ $thanhVien->ma_thanh_vien }}
                </h2>

                <div class="mt-5 inline-flex rounded-full bg-[#d99a32]/15 px-4 py-2 text-sm font-black text-[#f4c56a]">
                    <i class="fa-solid fa-crown mr-2"></i>
                    {{ strtoupper($thanhVien->ten_hang) }}
                </div>
            </div>

            <div class="lg:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-gray-400">Điểm hiện tại</p>
                    <h3 class="mt-2 text-2xl font-black text-white">{{ number_format($thanhVien->diem_hien_tai) }}</h3>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-gray-400">Tổng điểm</p>
                    <h3 class="mt-2 text-2xl font-black text-white">{{ number_format($thanhVien->tong_diem_tich_luy) }}
                    </h3>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-gray-400">Số vé đã mua</p>
                    <h3 class="mt-2 text-2xl font-black text-white">{{ number_format($tongVe) }}</h3>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-gray-400">Tổng chi tiêu</p>
                    <h3 class="mt-2 text-2xl font-black text-white">{{ number_format($tongChiTieu, 0, ',', '.') }}đ</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- THÔNG TIN KHÁCH HÀNG --}}
    <div class="rounded-3xl border border-white/10 bg-[#121212] p-6">
        <h2 class="mb-5 text-xl font-black">Thông tin khách hàng</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <p class="text-sm text-gray-400">Họ tên</p>
                <p class="mt-1 font-bold text-white">{{ $nguoiDung->ho_ten ?? '---' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Email</p>
                <p class="mt-1 font-bold text-white">{{ $nguoiDung->email ?? '---' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Ngày sinh</p>
                <p class="mt-1 font-bold text-white">{{ $nguoiDung->ngay_sinh?->format('d/m/Y') ?? '---' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Ngày tham gia</p>
                <p class="mt-1 font-bold text-white">{{ $thanhVien->ngay_tham_gia?->format('d/m/Y') ?? '---' }}</p>
            </div>
        </div>
    </div>

    {{-- LỊCH SỬ ĐIỂM --}}
    <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#121212]">
        <div class="border-b border-white/10 px-6 py-5">
            <h2 class="text-xl font-black">Lịch sử điểm</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left">
                <thead class="bg-white/[0.04] text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-6 py-4">Ngày</th>
                        <th class="px-6 py-4">Loại</th>
                        <th class="px-6 py-4">Điểm</th>
                        <th class="px-6 py-4">Nội dung</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                    @forelse($lichSuDiems as $item)
                    <tr class="hover:bg-white/[0.04]">
                        <td class="px-6 py-4 text-gray-400">
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-6 py-4">
                            @if($item->loai_giao_dich === 'cong_diem')
                            <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400">Cộng
                                điểm</span>
                            @else
                            <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-bold text-red-400">Trừ
                                điểm</span>
                            @endif
                        </td>

                        <td
                            class="px-6 py-4 font-black {{ $item->loai_giao_dich === 'cong_diem' ? 'text-green-400' : 'text-red-400' }}">
                            {{ $item->loai_giao_dich === 'cong_diem' ? '+' : '-' }}{{ $item->so_diem }}
                        </td>

                        <td class="px-6 py-4 text-gray-300">
                            {{ $item->noi_dung }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                            Chưa có lịch sử điểm.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-white/10 px-6 py-4">
            {{ $lichSuDiems->links() }}
        </div>
    </div>

    {{-- ADMIN TẶNG / TRỪ ĐIỂM THỦ CÔNG --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Form tặng điểm --}}
        <div class="rounded-3xl border border-green-500/20 bg-[#121212] p-6 shadow-xl">
            <h2 class="mb-2 text-xl font-black text-green-400">
                <i class="fa-solid fa-plus-circle mr-2"></i>
                Tặng điểm thành viên
            </h2>

            <p class="mb-5 text-sm text-gray-400">
                Dùng để đền bù sự cố, tri ân khách hàng hoặc tặng điểm theo chương trình khuyến mãi.
            </p>

            <form method="POST" action="{{ route('admin.thanh-vien.tang-diem', $thanhVien) }}" class="space-y-4">
                @csrf

                <input type="number" name="so_diem" min="1" max="10000" placeholder="Số điểm muốn tặng"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none transition focus:border-green-400">

                <textarea name="noi_dung" rows="3" placeholder="Lý do tặng điểm, ví dụ: Đền bù lỗi thanh toán"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none transition focus:border-green-400"></textarea>

                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-300">
                        Loại cộng điểm
                    </label>

                    <select name="tinh_vao_hang"
                        class="w-full rounded-2xl border border-white/10 bg-[#1a1a1a] px-4 py-3 text-white outline-none transition focus:border-green-400">
                        <option value="0">
                            Chỉ cộng điểm sử dụng, không xét hạng
                        </option>

                        <option value="1">
                            Cộng điểm và tính vào hạng thành viên
                        </option>
                    </select>

                    <p class="mt-2 text-xs text-gray-500">
                        Đền bù sự cố nên chọn không xét hạng. Tri ân khách VIP hoặc chương trình khuyến mãi có thể chọn
                        tính vào hạng.
                    </p>
                </div>
                <button type="submit"
                    class="w-full rounded-2xl bg-green-500 px-5 py-3 text-sm font-black text-white transition duration-300 hover:-translate-y-1 hover:bg-green-400">
                    <i class="fa-solid fa-gift mr-2"></i>
                    Tặng điểm
                </button>
            </form>
        </div>

        {{-- Form trừ điểm --}}
        <div class="rounded-3xl border border-red-500/20 bg-[#121212] p-6 shadow-xl">
            <h2 class="mb-2 text-xl font-black text-red-400">
                <i class="fa-solid fa-minus-circle mr-2"></i>
                Trừ điểm thành viên
            </h2>

            <p class="mb-5 text-sm text-gray-400">
                Dùng khi cần thu hồi điểm cộng nhầm, xử lý gian lận hoặc điều chỉnh sai lệch điểm.
            </p>

            <form method="POST" action="{{ route('admin.thanh-vien.tru-diem', $thanhVien) }}" class="space-y-4"
                onsubmit="return confirm('Bạn có chắc muốn trừ điểm thành viên này?')">
                @csrf

                <input type="number" name="so_diem" min="1" max="10000" placeholder="Số điểm muốn trừ"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none transition focus:border-red-400">

                <textarea name="noi_dung" rows="3" placeholder="Lý do trừ điểm, ví dụ: Thu hồi điểm cấp sai"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none transition focus:border-red-400"></textarea>

                <button type="submit"
                    class="w-full rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white transition duration-300 hover:-translate-y-1 hover:bg-red-400">
                    <i class="fa-solid fa-rotate-left mr-2"></i>
                    Trừ điểm
                </button>
            </form>
        </div>

    </div>

    {{-- VOUCHER KHÁCH HÀNG --}}
    <div class="rounded-3xl border border-white/10 bg-[#121212] p-6">
        <h2 class="mb-5 text-xl font-black">Voucher khách đang sở hữu</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @forelse($vouchers as $voucherCaNhan)
            <div
                class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 transition hover:-translate-y-1 hover:border-[#d99a32]/50">
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
            <p class="text-gray-500">Khách hàng chưa có voucher nào.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection