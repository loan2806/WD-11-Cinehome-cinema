@extends('layouts.admin')

@section('title', 'Khuyến mãi & Voucher')
@section('page-title', 'Khuyến mãi & Voucher')
@section('page-subtitle', 'Thiết lập voucher mẫu, điểm đổi, hạn dùng và cấp ưu đãi trực tiếp cho khách hàng')

@section('content')
@php
    $activeFilters = collect([
        request('q'),
        request('loai_voucher'),
        request('trang_thai'),
    ])->filter()->count();

    $issuedFilters = collect([
        request('issued_q'),
        request('issued_status'),
    ])->filter()->count();

    $typeIcons = [
        'giam_gia_ve' => 'fa-ticket',
        'giam_gia_do_an' => 'fa-burger',
        'giam_gia_ghe_vip' => 'fa-couch',
        'sinh_nhat' => 'fa-cake-candles',
        'khach_hang_than_thiet' => 'fa-crown',
    ];
@endphp

<div class="voucher-admin-page">
    @include('admin.partials.flash')

    <section class="voucher-hero">
        <div>
            <span class="voucher-eyebrow">
                <i class="fa-solid fa-gift"></i>
                Ưu đãi khách hàng
            </span>
            <h2>Quản lý khuyến mãi & voucher</h2>
            <p>Tạo voucher mẫu, cấu hình điểm đổi, cấp mã cá nhân cho khách và theo dõi trạng thái sử dụng.</p>
        </div>
    </section>

    <section class="voucher-stats">
        <div class="voucher-stat">
            <span>Voucher mẫu</span>
            <strong>{{ $summary['total'] }}</strong>
        </div>
        <div class="voucher-stat is-good">
            <span>Đang hiệu lực</span>
            <strong>{{ $summary['active'] }}</strong>
        </div>
        <div class="voucher-stat is-warn">
            <span>Hết hạn</span>
            <strong>{{ $summary['expired'] }}</strong>
        </div>
        <div class="voucher-stat">
            <span>Đã cấp</span>
            <strong>{{ $summary['issued'] }}</strong>
        </div>
        <div class="voucher-stat is-muted">
            <span>Đã dùng</span>
            <strong>{{ $summary['used'] }}</strong>
        </div>
    </section>

    <div class="voucher-workspace">
        <aside class="voucher-sidebar">
            <form method="POST" action="{{ route('admin.vouchers.store') }}" class="voucher-panel">
                @csrf
                <div class="voucher-panel-head">
                    <div>
                        <span class="voucher-eyebrow">Tạo mẫu</span>
                        <h3>Voucher mới</h3>
                        <p>Dùng cho đổi điểm hoặc cấp thủ công.</p>
                    </div>
                    <span class="voucher-panel-icon"><i class="fa-solid fa-ticket-simple"></i></span>
                </div>

                <div class="voucher-form-grid">
                    <label class="voucher-field is-wide">
                        <span>Mã voucher</span>
                        <input name="ma_voucher" value="{{ old('ma_voucher') }}" class="admin-input" placeholder="VD: FOOD20K" required>
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Tên chương trình</span>
                        <input name="ten_voucher" value="{{ old('ten_voucher') }}" class="admin-input" placeholder="VD: Giảm 20.000đ combo bắp nước" required>
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Loại voucher</span>
                        <select name="loai_voucher" class="admin-input" required>
                            @foreach ($voucherTypeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('loai_voucher', 'giam_gia_ve') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="voucher-field">
                        <span>Giá trị giảm</span>
                        <input name="gia_tri_giam" type="number" min="0" value="{{ old('gia_tri_giam', 0) }}" class="admin-input" placeholder="0" required>
                    </label>
                    <label class="voucher-field">
                        <span>Điểm đổi</span>
                        <input name="diem_can_doi" type="number" min="0" value="{{ old('diem_can_doi', 0) }}" class="admin-input" placeholder="0" required>
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Hạn dùng</span>
                        <input name="ngay_het_han" type="date" value="{{ old('ngay_het_han', now()->addMonth()->format('Y-m-d')) }}" class="admin-input" required>
                    </label>
                    <label class="voucher-switch is-wide">
                        <input type="checkbox" name="trang_thai" value="1" checked>
                        <span></span>
                        <div>
                            <strong>Bật voucher ngay</strong>
                            <small>Khách có thể đổi hoặc admin có thể cấp mã.</small>
                        </div>
                    </label>
                </div>

                <button type="submit" class="voucher-primary-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu voucher
                </button>
            </form>

            <form method="POST" action="{{ route('admin.vouchers.issue') }}" class="voucher-panel">
                @csrf
                <div class="voucher-panel-head">
                    <div>
                        <span class="voucher-eyebrow">Cấp phát</span>
                        <h3>Tặng voucher</h3>
                        <p>Cấp voucher cá nhân, không trừ điểm khách hàng.</p>
                    </div>
                    <span class="voucher-panel-icon"><i class="fa-solid fa-hand-holding-heart"></i></span>
                </div>

                <div class="voucher-form-grid">
                    <label class="voucher-field is-wide">
                        <span>Voucher mẫu</span>
                        <select name="voucher_id" class="admin-input" required>
                            <option value="">Chọn voucher đang hiệu lực</option>
                            @foreach ($activeVouchers as $voucher)
                                <option value="{{ $voucher->id }}" @selected(old('voucher_id') == $voucher->id)>
                                    {{ $voucher->ma_voucher }} - {{ $voucher->ten_voucher }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Khách hàng</span>
                        <select name="nguoi_dung_id" class="admin-input" required>
                            <option value="">Chọn khách hàng</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('nguoi_dung_id') == $customer->id)>
                                    {{ $customer->ho_ten }} - {{ $customer->email }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="voucher-field">
                        <span>Số lượng</span>
                        <input name="quantity" type="number" min="1" max="20" value="{{ old('quantity', 1) }}" class="admin-input" required>
                    </label>
                    <label class="voucher-field">
                        <span>Hạn riêng</span>
                        <input name="ngay_het_han" type="date" value="{{ old('ngay_het_han') }}" class="admin-input">
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Lý do cấp</span>
                        <select name="loai_cap_phat" class="admin-input" required>
                            <option value="admin_tang" @selected(old('loai_cap_phat', 'admin_tang') === 'admin_tang')>Admin tặng</option>
                            <option value="khach_hang_than_thiet" @selected(old('loai_cap_phat') === 'khach_hang_than_thiet')>Khách hàng thân thiết</option>
                        </select>
                    </label>
                </div>

                <button type="submit" class="voucher-primary-btn">
                    <i class="fa-solid fa-gift"></i>
                    Cấp voucher
                </button>
            </form>
        </aside>

        <main class="voucher-main">
            <section class="voucher-panel">
                <div class="voucher-panel-head">
                    <div>
                        <span class="voucher-eyebrow">Voucher mẫu</span>
                        <h3>Danh sách chương trình</h3>
                        <p>{{ $vouchers->total() }} voucher theo bộ lọc hiện tại.</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.vouchers.index') }}" class="voucher-filter">
                    <label class="voucher-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input name="q" value="{{ request('q') }}" placeholder="Tìm mã hoặc tên voucher...">
                    </label>
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
                    <button class="voucher-filter-btn" type="submit">
                        <i class="fa-solid fa-filter"></i>
                        Lọc
                        @if ($activeFilters)
                            <span>{{ $activeFilters }}</span>
                        @endif
                    </button>
                    @if ($activeFilters)
                        <a href="{{ route('admin.vouchers.index') }}" class="voucher-reset-btn" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </form>

                <div class="voucher-list">
                    @forelse ($vouchers as $voucher)
                        @php
                            $expired = $voucher->ngay_het_han->lt(today());
                            $statusClass = $expired ? 'is-expired' : ($voucher->trang_thai ? 'is-active' : 'is-inactive');
                            $statusLabel = $expired ? 'Hết hạn' : ($voucher->trang_thai ? 'Đang hiệu lực' : 'Đang tắt');
                            $typeIcon = $typeIcons[$voucher->loai_voucher] ?? 'fa-ticket';
                        @endphp

                        <article class="voucher-card">
                            <div class="voucher-card-top">
                                <div class="voucher-code-badge">
                                    <i class="fa-solid {{ $typeIcon }}"></i>
                                    <strong>{{ $voucher->ma_voucher }}</strong>
                                </div>
                                <span class="voucher-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>

                            <div class="voucher-card-body">
                                <div class="voucher-card-title">
                                    <span>{{ $voucherTypeLabels[$voucher->loai_voucher] ?? $voucher->loai_voucher }}</span>
                                    <h3>{{ $voucher->ten_voucher }}</h3>
                                </div>

                                <div class="voucher-metrics">
                                    <div>
                                        <span>Giảm</span>
                                        <strong>{{ number_format((float) $voucher->gia_tri_giam, 0, ',', '.') }}đ</strong>
                                    </div>
                                    <div>
                                        <span>Điểm đổi</span>
                                        <strong>{{ number_format($voucher->diem_can_doi) }}</strong>
                                    </div>
                                    <div>
                                        <span>Đã cấp</span>
                                        <strong>{{ $voucher->nguoi_dung_vouchers_count }}</strong>
                                    </div>
                                    <div>
                                        <span>Đã dùng</span>
                                        <strong>{{ $voucher->used_count }}</strong>
                                    </div>
                                </div>

                                <div class="voucher-expire">
                                    <i class="fa-solid fa-calendar-day"></i>
                                    Hết hạn {{ $voucher->ngay_het_han->format('d/m/Y') }}
                                </div>
                            </div>

                            <div class="voucher-card-actions">
                                <form method="POST" action="{{ route('admin.vouchers.toggle-status', $voucher) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="voucher-soft-btn" type="submit">
                                        <i class="fa-solid {{ $voucher->trang_thai ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                        {{ $voucher->trang_thai ? 'Tắt' : 'Bật' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Xóa voucher {{ $voucher->ma_voucher }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="voucher-delete-btn" type="submit" @disabled($voucher->nguoi_dung_vouchers_count > 0)>
                                        <i class="fa-solid fa-trash"></i>
                                        Xóa
                                    </button>
                                </form>
                            </div>

                            <details class="voucher-edit-box">
                                <summary>
                                    <span>Sửa voucher</span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </summary>

                                <form method="POST" action="{{ route('admin.vouchers.update', $voucher) }}" class="voucher-edit-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="voucher-field">
                                        <span>Mã voucher</span>
                                        <input name="ma_voucher" value="{{ old('ma_voucher', $voucher->ma_voucher) }}" class="admin-input" required>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Tên voucher</span>
                                        <input name="ten_voucher" value="{{ old('ten_voucher', $voucher->ten_voucher) }}" class="admin-input" required>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Loại voucher</span>
                                        <select name="loai_voucher" class="admin-input">
                                            @foreach ($voucherTypeLabels as $value => $label)
                                                <option value="{{ $value }}" @selected(old('loai_voucher', $voucher->loai_voucher) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Hạn dùng</span>
                                        <input name="ngay_het_han" type="date" value="{{ old('ngay_het_han', $voucher->ngay_het_han->format('Y-m-d')) }}" class="admin-input" required>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Giá trị giảm</span>
                                        <input name="gia_tri_giam" type="number" min="0" value="{{ old('gia_tri_giam', (float) $voucher->gia_tri_giam) }}" class="admin-input" required>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Điểm cần đổi</span>
                                        <input name="diem_can_doi" type="number" min="0" value="{{ old('diem_can_doi', $voucher->diem_can_doi) }}" class="admin-input" required>
                                    </label>
                                    <label class="voucher-switch is-wide">
                                        <input type="checkbox" name="trang_thai" value="1" @checked($voucher->trang_thai)>
                                        <span></span>
                                        <div>
                                            <strong>Bật voucher</strong>
                                            <small>Cho phép khách đổi hoặc admin cấp mã.</small>
                                        </div>
                                    </label>
                                    <button class="voucher-primary-btn is-wide" type="submit">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Cập nhật voucher
                                    </button>
                                </form>
                            </details>
                        </article>
                    @empty
                        <div class="voucher-empty">
                            <i class="fa-solid fa-ticket-simple"></i>
                            <h3>Chưa có voucher phù hợp</h3>
                            <p>Thử đổi bộ lọc hoặc tạo một voucher mẫu mới ở khung bên trái.</p>
                        </div>
                    @endforelse
                </div>

                <div class="voucher-pagination">
                    {{ $vouchers->links() }}
                </div>
            </section>

            <section class="voucher-panel">
                <div class="voucher-panel-head">
                    <div>
                        <span class="voucher-eyebrow">Đã cấp</span>
                        <h3>Voucher cá nhân</h3>
                        <p>Theo dõi mã cá nhân và thu hồi khi khách chưa sử dụng.</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.vouchers.index') }}" class="voucher-issued-filter">
                    <label class="voucher-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input name="issued_q" value="{{ request('issued_q') }}" placeholder="Tìm mã cá nhân, khách hàng...">
                    </label>
                    <select name="issued_status" class="admin-input">
                        <option value="">Tất cả trạng thái</option>
                        <option value="unused" @selected(request('issued_status') === 'unused')>Chưa dùng</option>
                        <option value="used" @selected(request('issued_status') === 'used')>Đã dùng</option>
                        <option value="expired" @selected(request('issued_status') === 'expired')>Hết hạn</option>
                    </select>
                    <button class="voucher-filter-btn" type="submit">
                        <i class="fa-solid fa-filter"></i>
                        Lọc
                        @if ($issuedFilters)
                            <span>{{ $issuedFilters }}</span>
                        @endif
                    </button>
                </form>

                <div class="issued-voucher-list">
                    @forelse ($issuedVouchers as $item)
                        @php
                            $issuedExpired = $item->ngay_het_han && $item->ngay_het_han->lt(now());
                            $issuedStatusClass = $item->da_su_dung ? 'is-used' : ($issuedExpired ? 'is-expired' : 'is-unused');
                            $issuedStatusLabel = $item->da_su_dung ? 'Đã dùng' : ($issuedExpired ? 'Hết hạn' : 'Chưa dùng');
                        @endphp

                        <article class="issued-voucher-card">
                            <div>
                                <span>Mã cá nhân</span>
                                <strong>{{ $item->ma_voucher_ca_nhan }}</strong>
                                <small>Nhận {{ $item->ngay_nhan?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div>
                                <span>Khách hàng</span>
                                <strong>{{ $item->nguoiDung?->ho_ten ?? 'Không rõ' }}</strong>
                                <small>{{ $item->nguoiDung?->email }}</small>
                            </div>
                            <div>
                                <span>Voucher mẫu</span>
                                <strong>{{ $item->voucher?->ma_voucher ?? 'Không rõ' }}</strong>
                                <small>{{ $item->voucher?->ten_voucher }}</small>
                            </div>
                            <div>
                                <span>Hạn dùng</span>
                                <strong>{{ $item->ngay_het_han?->format('d/m/Y') ?? 'Theo voucher mẫu' }}</strong>
                            </div>
                            <span class="issued-status {{ $issuedStatusClass }}">{{ $issuedStatusLabel }}</span>

                            @if (! $item->da_su_dung)
                                <form method="POST" action="{{ route('admin.vouchers.issued.destroy', $item) }}" onsubmit="return confirm('Thu hồi voucher {{ $item->ma_voucher_ca_nhan }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="voucher-delete-btn" type="submit">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Thu hồi
                                    </button>
                                </form>
                            @else
                                <span class="voucher-no-action">Đã sử dụng</span>
                            @endif
                        </article>
                    @empty
                        <div class="voucher-empty">
                            <i class="fa-solid fa-user-tag"></i>
                            <h3>Chưa có voucher cá nhân phù hợp</h3>
                            <p>Thử đổi bộ lọc hoặc cấp voucher cho khách hàng ở khung bên trái.</p>
                        </div>
                    @endforelse
                </div>

                <div class="voucher-pagination">
                    {{ $issuedVouchers->links() }}
                </div>
            </section>
        </main>
    </div>
</div>
@endsection
