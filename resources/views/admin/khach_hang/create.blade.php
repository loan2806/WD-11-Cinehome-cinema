@extends('layouts.admin')

@section('title', 'Thêm khách hàng')
@section('page-title', 'Thêm khách hàng')
@section('page-subtitle', 'Tạo tài khoản khách hàng mới để hỗ trợ đặt vé, tích điểm và nhận voucher')

@section('content')
<div class="staff-create-page customer-create-page">
    @include('admin.partials.flash')

    <section class="staff-create-hero">
        <div>
            <span class="staff-create-kicker">
                <i class="fa-solid fa-user-plus"></i>
                Tài khoản khách hàng
            </span>
            <h2>Thêm khách hàng mới</h2>
            <p>Tạo hồ sơ khách hàng cho quầy vé hoặc chăm sóc khách hàng. Sau khi lưu, tài khoản được kích hoạt ngay và có thể dùng để đặt vé, theo dõi lịch sử mua vé, nhận điểm thưởng và voucher.</p>
        </div>

        <a href="{{ route('admin.khach-hang.index') }}" class="staff-create-soft-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Danh sách khách hàng
        </a>
    </section>

    @if ($errors->any())
        <section class="staff-create-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Thông tin khách hàng chưa hợp lệ</strong>
                <p>Vui lòng kiểm tra lại các trường đang báo lỗi rồi lưu lại tài khoản.</p>
            </div>
        </section>
    @endif

    <form method="POST" action="{{ route('admin.khach-hang.store') }}" class="staff-create-layout">
        @csrf

        <main class="staff-create-panel">
            <div class="staff-create-panel-head">
                <span class="staff-create-panel-icon">
                    <i class="fa-solid fa-address-card"></i>
                </span>
                <div>
                    <span class="staff-create-kicker">Thông tin đăng ký</span>
                    <h3>Hồ sơ khách hàng</h3>
                    <p>Nhập chính xác email và số điện thoại để dễ tìm khách khi bán vé tại quầy hoặc hỗ trợ sau giao dịch.</p>
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
                            value="{{ old('ho_ten') }}"
                            placeholder="VD: Nguyễn Văn A"
                            autocomplete="name"
                            autofocus
                        >
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
                            value="{{ old('email') }}"
                            placeholder="khachhang@cinehome.vn"
                            autocomplete="email"
                            
                        >
                    </div>
                    @error('email')
                        <small class="staff-create-error">{{ $message }}</small>
                    @enderror
                </label>

                <label class="staff-create-field">
                    <span>Số điện thoại</span>
                    <div class="staff-create-control @error('so_dien_thoai') is-invalid @enderror">
                        <i class="fa-solid fa-phone"></i>
                        <input
                            type="tel"
                            name="so_dien_thoai"
                            value="{{ old('so_dien_thoai') }}"
                            placeholder="VD: 0987654321"
                            autocomplete="tel"
                        >
                    </div>
                    @error('so_dien_thoai')
                        <small class="staff-create-error">{{ $message }}</small>
                    @else
                        <small class="staff-create-hint">Có thể bỏ trống, nhưng nên nhập để tìm khách nhanh hơn tại quầy.</small>
                    @enderror
                </label>

                <label class="staff-create-field">
                    <span>Ngày sinh</span>
                    <div class="staff-create-control @error('ngay_sinh') is-invalid @enderror">
                        <i class="fa-solid fa-cake-candles"></i>
                        <input
                            type="date"
                            name="ngay_sinh"
                            value="{{ old('ngay_sinh') }}"
                            autocomplete="bday"
                        >
                    </div>
                    @error('ngay_sinh')
                        <small class="staff-create-error">{{ $message }}</small>
                    @else
                        <small class="staff-create-hint">Ngày sinh giúp hệ thống áp dụng ưu đãi sinh nhật chính xác.</small>
                    @enderror
                </label>

                <label class="staff-create-field is-wide">
                    <span>Mật khẩu ban đầu</span>
                    <div class="staff-create-control @error('mat_khau') is-invalid @enderror">
                        <i class="fa-solid fa-key"></i>
                        <input
                            type="password"
                            name="mat_khau"
                            placeholder="Tối thiểu 6 ký tự"
                            autocomplete="new-password"
                            minlength="6"
                        
                        >
                    </div>
                    @error('mat_khau')
                        <small class="staff-create-error">{{ $message }}</small>
                    @else
                        <small class="staff-create-hint">Mật khẩu sẽ được mã hóa khi lưu. Nên đặt mật khẩu tạm và hướng dẫn khách đổi sau lần đăng nhập đầu.</small>
                    @enderror
                </label>
            </div>

            <div class="staff-create-status-card">
                <span>
                    <i class="fa-solid fa-circle-check"></i>
                </span>
                <div>
                    <strong>Kích hoạt tài khoản ngay sau khi tạo</strong>
                    <p>Hệ thống sẽ gán vai trò khách hàng và trạng thái hoạt động cho tài khoản mới.</p>
                </div>
            </div>

            <div class="staff-create-actions">
                <button type="submit" class="staff-create-primary-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu khách hàng
                </button>
                <a href="{{ route('admin.khach-hang.index') }}" class="staff-create-soft-btn">
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
                    <h3>{{ old('ho_ten') ?: 'Khách hàng mới' }}</h3>
                    <p>{{ old('email') ?: 'Email đăng nhập sẽ hiển thị tại đây' }}</p>
                </div>

                <div class="customer-create-preview-list">
                    <span>
                        <i class="fa-solid fa-phone"></i>
                        {{ old('so_dien_thoai') ?: 'Chưa có số điện thoại' }}
                    </span>
                    <span>
                        <i class="fa-solid fa-cake-candles"></i>
                        {{ old('ngay_sinh') ?: 'Chưa nhập ngày sinh' }}
                    </span>
                </div>

                <span class="staff-create-status">
                    <i class="fa-solid fa-circle-check"></i>
                    Hoạt động
                </span>
            </section>

            <section class="staff-create-note">
                <h3>
                    <i class="fa-solid fa-shield-halved"></i>
                    Lưu ý khi tạo khách
                </h3>
                <ul>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Email và số điện thoại không được trùng với tài khoản đã có.
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Ngày sinh nên nhập đúng để tránh sai ưu đãi sinh nhật và voucher.
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Sau khi tạo, có thể xem lịch sử vé và trạng thái tài khoản ở trang chi tiết khách hàng.
                    </li>
                </ul>
            </section>
        </aside>
    </form>
</div>
@endsection
