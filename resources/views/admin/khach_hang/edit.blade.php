@extends('layouts.admin')

@section('title', 'Sửa khách hàng')
@section('page-title', 'Sửa khách hàng')
@section('page-subtitle', 'Cập nhật hồ sơ, liên hệ và trạng thái tài khoản khách hàng')

@section('content')
@php
$isActive = (bool) old('trang_thai_hoat_dong', $khachHang->trang_thai_hoat_dong);
$displayName = old('ho_ten', $khachHang->ho_ten) ?: 'Khách hàng';
$displayEmail = old('email', $khachHang->email) ?: 'Chưa có email';
$displayPhone = old('so_dien_thoai', $khachHang->so_dien_thoai) ?: 'Chưa có số điện thoại';
$displayBirthday = old('ngay_sinh', $khachHang->ngay_sinh?->format('Y-m-d'));
try {
$displayBirthdayText = $displayBirthday ? \Carbon\Carbon::parse($displayBirthday)->format('d/m/Y') : 'Chưa nhập ngày sinh';
} catch (\Throwable $e) {
$displayBirthdayText = $displayBirthday ?: 'Chưa nhập ngày sinh';
}
$memberCard = $khachHang->thanhVien;
@endphp

<div class="staff-create-page customer-create-page customer-edit-page">
    @include('admin.partials.flash')

    <section class="staff-create-hero">
        <div>
            <span class="staff-create-kicker">
                <i class="fa-solid fa-user-pen"></i>
                Hồ sơ khách hàng
            </span>
            <h2>Cập nhật khách hàng</h2>
            <p>Điều chỉnh thông tin liên hệ, ngày sinh và trạng thái tài khoản. Các thay đổi nên được kiểm tra kỹ để tránh ảnh hưởng đến đặt vé, voucher sinh nhật và chăm sóc khách hàng.</p>
        </div>

        <div class="customer-edit-hero-actions">
            <a href="{{ route('admin.khach-hang.show', $khachHang) }}" class="staff-create-soft-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Chi tiết khách hàng
            </a>
            <a href="{{ route('admin.khach-hang.index') }}" class="staff-create-soft-btn">
                <i class="fa-solid fa-list"></i>
                Danh sách
            </a>
        </div>
    </section>

    @if ($errors->any())
    <section class="staff-create-alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div>
            <strong>Thông tin cập nhật chưa hợp lệ</strong>
            <p>Vui lòng kiểm tra lại các trường đang báo lỗi rồi lưu lại hồ sơ khách hàng.</p>
        </div>
    </section>
    @endif

    <form method="POST" action="{{ route('admin.khach-hang.update', $khachHang) }}" class="staff-create-layout">
        @csrf
        @method('PATCH')

        <main class="staff-create-panel">
            <div class="staff-create-panel-head">
                <span class="staff-create-panel-icon">
                    <i class="fa-solid fa-address-card"></i>
                </span>
                <div>
                    <span class="staff-create-kicker">Thông tin tài khoản</span>
                    <h3>Hồ sơ khách hàng</h3>
                    <p>Cập nhật tên, email và số điện thoại để nhân viên dễ tìm khách khi bán vé tại quầy hoặc hỗ trợ sau giao dịch.</p>
                </div>
            </div>

            <div class="staff-create-fields">
                <label class="staff-create-field">
                    <span>Họ và tên</span>
                    <div class="staff-create-control @error('ho_ten') is-invalid @enderror">
                        <i class="fa-solid fa-user"></i>
                        <input
                            type="text"
                            name="ho_ten"
                            value="{{ old('ho_ten', $khachHang->ho_ten) }}"
                            placeholder="VD: Nguyễn Văn A"
                            autocomplete="name"
                            required
                            autofocus>
                    </div>
                    @error('ho_ten')
                    <small class="staff-create-error">{{ $message }}</small>
                    @enderror
                </label>

                <label class="staff-create-field">
                    <span>Email đăng nhập</span>
                    <div class="staff-create-control @error('email') is-invalid @enderror">
                        <i class="fa-solid fa-envelope"></i>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $khachHang->email) }}"
                            placeholder="khachhang@cinehome.vn"
                            autocomplete="email"
                            disabled
                            class="bg-white/5 text-gray-400 border border-white/10 opacity-50 cursor-not-allowed">
                    </div>
                </label>

                <label class="staff-create-field">
                    <span>Số điện thoại</span>
                    <div class="staff-create-control @error('so_dien_thoai') is-invalid @enderror">
                        <i class="fa-solid fa-phone"></i>
                        <input
                            type="tel"
                            name="so_dien_thoai"
                            value="{{ old('so_dien_thoai', $khachHang->so_dien_thoai) }}"
                            placeholder="VD: 0987654321"
                            autocomplete="tel">
                    </div>
                    @error('so_dien_thoai')
                    <small class="staff-create-error">{{ $message }}</small>
                    @else
                    <small class="staff-create-hint">Nên nhập số điện thoại để tìm khách nhanh tại quầy vé.</small>
                    @enderror
                </label>

                <label class="staff-create-field">
                    <span>Trạng thái tài khoản</span>
                    <div class="staff-create-control @error('trang_thai_hoat_dong') is-invalid @enderror">
                        <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-lock' }}"></i>
                        <select name="trang_thai_hoat_dong" required>
                            <option value="1" @selected($isActive)>Đang hoạt động</option>
                            <option value="0" @selected(! $isActive)>Bị khóa</option>
                        </select>
                    </div>
                    @error('trang_thai_hoat_dong')
                    <small class="staff-create-error">{{ $message }}</small>
                    @else
                    <small class="staff-create-hint">Khóa tài khoản sẽ ngăn khách sử dụng một số thao tác trên website.</small>
                    @enderror
                </label>

                <label class="staff-create-field is-wide">
                    <span>Ngày sinh</span>

                    <div class="staff-create-control is-disabled">
                        <i class="fa-solid fa-cake-candles"></i>

                        <input
                            type="date"
                            value="{{ $khachHang->ngay_sinh?->format('Y-m-d') }}"
                            disabled>
                    </div>

                    <small class="staff-create-hint is-warning">
                        Ngày sinh do khách hàng cung cấp và không cho phép quản trị viên thay đổi.
                    </small>
                </label>
            </div>

            <div class="staff-create-status-card {{ $isActive ? 'is-active' : 'is-locked' }}">
                <span>
                    <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-lock' }}"></i>
                </span>
                <div>
                    <strong>{{ $isActive ? 'Tài khoản đang hoạt động' : 'Tài khoản đang bị khóa' }}</strong>
                    <p>{{ $isActive ? 'Khách hàng có thể đăng nhập, đặt vé và sử dụng voucher hợp lệ.' : 'Khách hàng bị hạn chế thao tác cho đến khi tài khoản được mở lại.' }}</p>
                </div>
            </div>

            <div class="staff-create-actions">
                <button type="submit" class="staff-create-primary-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu thay đổi
                </button>
                <a href="{{ route('admin.khach-hang.show', $khachHang) }}" class="staff-create-soft-btn">
                    Hủy
                </a>
            </div>
        </main>

        <aside class="staff-create-side">
            <section class="staff-create-preview">
                <span class="staff-create-avatar">
                    <i class="fa-solid fa-user"></i>
                </span>
                <div>
                    <small>Xem nhanh hồ sơ</small>
                    <h3>{{ $displayName }}</h3>
                    <p>{{ $displayEmail }}</p>
                </div>

                <div class="customer-create-preview-list">
                    <span>
                        <i class="fa-solid fa-phone"></i>
                        {{ $displayPhone }}
                    </span>
                    <span>
                        <i class="fa-solid fa-cake-candles"></i>
                        {{ $displayBirthdayText }}
                    </span>
                    <span>
                        <i class="fa-solid fa-id-card"></i>
                        {{ $memberCard?->ma_thanh_vien ?? 'Chưa có thẻ thành viên' }}
                    </span>
                </div>

                <span class="staff-create-status {{ $isActive ? 'is-active' : 'is-locked' }}">
                    <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-lock' }}"></i>
                    {{ $isActive ? 'Đang hoạt động' : 'Bị khóa' }}
                </span>
            </section>

            <section class="staff-create-note">
                <h3>
                    <i class="fa-solid fa-shield-halved"></i>
                    Lưu ý khi chỉnh sửa
                </h3>
                <ul>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Email và số điện thoại không được trùng với tài khoản khác.
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Ngày sinh chỉ dùng để xét voucher sinh nhật nên cần giữ ổn định.
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Không chỉnh điểm tại đây, hãy dùng trang Thẻ thành viên & Điểm.
                    </li>
                </ul>
            </section>
        </aside>
    </form>
</div>
@endsection