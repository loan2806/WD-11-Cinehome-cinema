@extends('layouts.admin')

@section('page-title', 'Khuyến mãi & Voucher')
@section('page-subtitle', 'Thiết lập voucher mẫu, điểm đổi, hạn dùng và cấp ưu đãi trực tiếp cho khách hàng')

@section('content')
@php
    $typeClasses = [
        'giam_gia_ve' => 'bg-blue-500/15 text-blue-300',
        'giam_gia_do_an' => 'bg-orange-500/15 text-orange-300',
        'giam_gia_ghe_vip' => 'bg-purple-500/15 text-purple-300',
        'sinh_nhat' => 'bg-pink-500/15 text-pink-300',
        'khach_hang_than_thiet' => 'bg-green-500/15 text-green-300',
    ];
@endphp

@include('admin.partials.flash')

<div class="mb-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
    <div class="stat-card">
        <div class="stat-label">Voucher mẫu</div>
        <div class="stat-value">{{ $summary['total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Đang hiệu lực</div>
        <div class="stat-value">{{ $summary['active'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Hết hạn</div>
        <div class="stat-value">{{ $summary['expired'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Đã cấp</div>
        <div class="stat-value">{{ $summary['issued'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Đã dùng</div>
        <div class="stat-value">{{ $summary['used'] }}</div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-[430px_1fr]">
    <div class="space-y-6">
        <form method="POST" action="{{ route('admin.vouchers.store') }}" class="admin-panel">
            @csrf
            <div class="panel-header">
                <div>
                    <h5>Tạo voucher mẫu</h5>
                    <small>Voucher mẫu dùng để khách đổi điểm hoặc admin cấp thủ công</small>
                </div>
            </div>

            <div class="panel-body space-y-4">
                <input name="ma_voucher" value="{{ old('ma_voucher') }}" class="admin-input" placeholder="Mã voucher, ví dụ FOOD20K" required>
                <input name="ten_voucher" value="{{ old('ten_voucher') }}" class="admin-input" placeholder="Tên chương trình" required>

                <select name="loai_voucher" class="admin-input" required>
                    @foreach ($voucherTypeLabels as $value => $label)
                        <option value="{{ $value }}" @selected(old('loai_voucher', 'giam_gia_ve') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="grid gap-3 md:grid-cols-2">
                    <input name="gia_tri_giam" type="number" min="0" value="{{ old('gia_tri_giam', 0) }}" class="admin-input" placeholder="Giá trị giảm" required>
                    <input name="diem_can_doi" type="number" min="0" value="{{ old('diem_can_doi', 0) }}" class="admin-input" placeholder="Điểm cần đổi" required>
                </div>

                <input name="ngay_het_han" type="date" value="{{ old('ngay_het_han', now()->addMonth()->format('Y-m-d')) }}" class="admin-input" required>

                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-bold text-gray-200">
                    <input type="checkbox" name="trang_thai" value="1" class="h-4 w-4 accent-[#d99a32]" checked>
                    Bật voucher ngay
                </label>

                <button type="submit" class="btn-admin w-full">
                    <i class="fa-solid fa-ticket-simple"></i>
                    Lưu voucher
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.vouchers.issue') }}" class="admin-panel">
            @csrf
            <div class="panel-header">
                <div>
                    <h5>Cấp voucher cho khách</h5>
                    <small>Tặng voucher cá nhân, không trừ điểm khách hàng</small>
                </div>
            </div>

            <div class="panel-body space-y-4">
                <select name="voucher_id" class="admin-input" required>
                    <option value="">Chọn voucher đang hiệu lực</option>
                    @foreach ($activeVouchers as $voucher)
                        <option value="{{ $voucher->id }}" @selected(old('voucher_id') == $voucher->id)>
                            {{ $voucher->ma_voucher }} - {{ $voucher->ten_voucher }}
                        </option>
                    @endforeach
                </select>

                <select name="nguoi_dung_id" class="admin-input" required>
                    <option value="">Chọn khách hàng</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('nguoi_dung_id') == $customer->id)>
                            {{ $customer->ho_ten }} - {{ $customer->email }}
                        </option>
                    @endforeach
                </select>

                <div class="grid gap-3 md:grid-cols-2">
                    <input name="quantity" type="number" min="1" max="20" value="{{ old('quantity', 1) }}" class="admin-input" placeholder="Số lượng" required>
                    <input name="ngay_het_han" type="date" value="{{ old('ngay_het_han') }}" class="admin-input" placeholder="Hạn riêng">
                </div>

                <select name="loai_cap_phat" class="admin-input" required>
                    <option value="admin_tang" @selected(old('loai_cap_phat', 'admin_tang') === 'admin_tang')>Admin tặng</option>
                    <option value="khach_hang_than_thiet" @selected(old('loai_cap_phat') === 'khach_hang_than_thiet')>Khách hàng thân thiết</option>
                </select>

                <button type="submit" class="btn-admin w-full">
                    <i class="fa-solid fa-gift"></i>
                    Cấp voucher
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-6">
        <div class="admin-panel">
            <div class="panel-header flex-col items-start gap-4 lg:flex-row lg:items-center">
                <div>
                    <h5>Danh sách voucher mẫu</h5>
                    <small>Lọc, bật/tắt và chỉnh chính sách đổi điểm</small>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.vouchers.index') }}" class="panel-body grid gap-3 border-b border-white/10 lg:grid-cols-[1fr_180px_170px_auto_auto]">
                <input name="q" value="{{ request('q') }}" class="admin-input" placeholder="Tìm mã hoặc tên voucher...">
                <select name="loai_voucher" class="admin-input">
                    <option value="">Tất cả loại</option>
                    @foreach ($voucherTypeLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('loai_voucher') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="trang_thai" class="admin-input">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected(request('trang_thai') === 'active')>Đang hiệu lực</option>
                    <option value="inactive" @selected(request('trang_thai') === 'inactive')>Đang tắt</option>
                    <option value="expired" @selected(request('trang_thai') === 'expired')>Hết hạn</option>
                </select>
                <button class="btn-admin" type="submit">Lọc</button>
                <a href="{{ route('admin.vouchers.index') }}" class="btn-admin-outline text-center no-underline">Reset</a>
            </form>

            <div class="space-y-4 p-4">
                @forelse ($vouchers as $voucher)
                    @php
                        $expired = $voucher->ngay_het_han->lt(today());
                    @endphp
                    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-[#d99a32]/15 px-3 py-1 text-xs font-black text-[#f4c56a]">{{ $voucher->ma_voucher }}</span>
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $typeClasses[$voucher->loai_voucher] ?? 'bg-white/10 text-gray-300' }}">
                                        {{ $voucherTypeLabels[$voucher->loai_voucher] ?? $voucher->loai_voucher }}
                                    </span>
                                    @if ($voucher->trang_thai && ! $expired)
                                        <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-black text-green-300">Đang hiệu lực</span>
                                    @elseif ($expired)
                                        <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-black text-red-300">Hết hạn</span>
                                    @else
                                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black text-gray-300">Đang tắt</span>
                                    @endif
                                </div>

                                <h3 class="m-0 text-lg font-black text-white">{{ $voucher->ten_voucher }}</h3>
                                <div class="mt-2 grid gap-2 text-sm text-gray-300 md:grid-cols-4">
                                    <div>Giảm: <strong class="text-white">{{ number_format((float) $voucher->gia_tri_giam, 0, ',', '.') }}đ</strong></div>
                                    <div>Điểm đổi: <strong class="text-white">{{ number_format($voucher->diem_can_doi) }}</strong></div>
                                    <div>Đã cấp: <strong class="text-white">{{ $voucher->nguoi_dung_vouchers_count }}</strong></div>
                                    <div>Đã dùng: <strong class="text-white">{{ $voucher->used_count }}</strong></div>
                                </div>
                                <div class="mt-2 text-sm text-gray-500">Hết hạn: {{ $voucher->ngay_het_han->format('d/m/Y') }}</div>
                            </div>

                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <form method="POST" action="{{ route('admin.vouchers.toggle-status', $voucher) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-black text-gray-200 transition hover:bg-white/10" type="submit">
                                        {{ $voucher->trang_thai ? 'Tắt' : 'Bật' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Xóa voucher {{ $voucher->ma_voucher }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-btn action-delete" type="submit" @disabled($voucher->nguoi_dung_vouchers_count > 0)>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <details class="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                            <summary class="cursor-pointer text-sm font-black text-[#f4c56a]">Sửa voucher</summary>
                            <form method="POST" action="{{ route('admin.vouchers.update', $voucher) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                                @csrf
                                @method('PATCH')
                                <input name="ma_voucher" value="{{ old('ma_voucher', $voucher->ma_voucher) }}" class="admin-input" placeholder="Mã voucher" required>
                                <input name="ten_voucher" value="{{ old('ten_voucher', $voucher->ten_voucher) }}" class="admin-input" placeholder="Tên voucher" required>
                                <select name="loai_voucher" class="admin-input">
                                    @foreach ($voucherTypeLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('loai_voucher', $voucher->loai_voucher) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input name="ngay_het_han" type="date" value="{{ old('ngay_het_han', $voucher->ngay_het_han->format('Y-m-d')) }}" class="admin-input" required>
                                <input name="gia_tri_giam" type="number" min="0" value="{{ old('gia_tri_giam', (float) $voucher->gia_tri_giam) }}" class="admin-input" required>
                                <input name="diem_can_doi" type="number" min="0" value="{{ old('diem_can_doi', $voucher->diem_can_doi) }}" class="admin-input" required>
                                <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-bold text-gray-200 md:col-span-2">
                                    <input type="checkbox" name="trang_thai" value="1" class="h-4 w-4 accent-[#d99a32]" @checked($voucher->trang_thai)>
                                    Bật voucher
                                </label>
                                <button class="btn-admin md:col-span-2" type="submit">Cập nhật voucher</button>
                            </form>
                        </details>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/15 p-10 text-center text-gray-400">
                        Chưa có voucher phù hợp bộ lọc.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-white/10 p-4">
                {{ $vouchers->links() }}
            </div>
        </div>

        <div class="admin-panel">
            <div class="panel-header flex-col items-start gap-4 lg:flex-row lg:items-center">
                <div>
                    <h5>Voucher đã cấp cho khách</h5>
                    <small>Theo dõi mã cá nhân, trạng thái dùng và thu hồi khi chưa sử dụng</small>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.vouchers.index') }}" class="panel-body grid gap-3 border-b border-white/10 lg:grid-cols-[1fr_180px_auto]">
                <input name="issued_q" value="{{ request('issued_q') }}" class="admin-input" placeholder="Tìm mã cá nhân, khách hàng...">
                <select name="issued_status" class="admin-input">
                    <option value="">Tất cả</option>
                    <option value="unused" @selected(request('issued_status') === 'unused')>Chưa dùng</option>
                    <option value="used" @selected(request('issued_status') === 'used')>Đã dùng</option>
                    <option value="expired" @selected(request('issued_status') === 'expired')>Hết hạn</option>
                </select>
                <button class="btn-admin" type="submit">Lọc cấp phát</button>
            </form>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mã cá nhân</th>
                            <th>Khách hàng</th>
                            <th>Voucher mẫu</th>
                            <th>Hạn dùng</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($issuedVouchers as $item)
                            @php
                                $issuedExpired = $item->ngay_het_han && $item->ngay_het_han->lt(now());
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $item->ma_voucher_ca_nhan }}</strong>
                                    <div class="text-xs text-gray-500">{{ $item->ngay_nhan?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <strong>{{ $item->nguoiDung?->ho_ten ?? 'Không rõ' }}</strong>
                                    <div class="text-xs text-gray-500">{{ $item->nguoiDung?->email }}</div>
                                </td>
                                <td>
                                    {{ $item->voucher?->ma_voucher }}
                                    <div class="text-xs text-gray-500">{{ $item->voucher?->ten_voucher }}</div>
                                </td>
                                <td>{{ $item->ngay_het_han?->format('d/m/Y') ?? 'Theo voucher mẫu' }}</td>
                                <td>
                                    @if ($item->da_su_dung)
                                        <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-black text-green-300">Đã dùng</span>
                                    @elseif ($issuedExpired)
                                        <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-black text-red-300">Hết hạn</span>
                                    @else
                                        <span class="rounded-full bg-yellow-500/15 px-3 py-1 text-xs font-black text-yellow-200">Chưa dùng</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if (! $item->da_su_dung)
                                        <form method="POST" action="{{ route('admin.vouchers.issued.destroy', $item) }}" onsubmit="return confirm('Thu hồi voucher {{ $item->ma_voucher_ca_nhan }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="action-btn action-delete" type="submit" title="Thu hồi">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-400">Chưa có voucher cá nhân phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-white/10 p-4">
                {{ $issuedVouchers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
