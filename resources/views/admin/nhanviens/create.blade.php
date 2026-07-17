@extends('layouts.admin')

@section('title', 'Thêm nhân viên')
@section('page-title', 'Thêm nhân viên')
@section('page-subtitle', 'Tạo tài khoản nhân viên mới cho hệ thống CineHome')

@section('content')
<div class="staff-create-page">
    @include('admin.partials.flash')

    <section class="staff-create-hero">
        <div>
            <span class="staff-create-kicker">
                <i class="fa-solid fa-user-plus"></i>
                Tài khoản mới
            </span>
            <h2>Thêm nhân viên vận hành</h2>
            <p>Tạo tài khoản cho nhân sự bán vé, soát vé hoặc thao tác nghiệp vụ tại rạp. Tài khoản sẽ được kích hoạt ngay sau khi lưu.</p>
        </div>

        <a href="{{ route('admin.nhanviens.index') }}" class="staff-create-soft-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Danh sách nhân viên
        </a>
    </section>

    @if ($errors->any())
        <section class="staff-create-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Thông tin chưa hợp lệ</strong>
                <p>Vui lòng kiểm tra lại các trường được đánh dấu bên dưới trước khi lưu nhân viên.</p>
            </div>
        </section>
    @endif

    <form action="{{ route('admin.nhanviens.store') }}" method="POST" class="staff-create-layout">
        @csrf

        <main class="staff-create-panel">
            <div class="staff-create-panel-head">
                <span class="staff-create-panel-icon">
                    <i class="fa-solid fa-id-card"></i>
                </span>
                <div>
                    <span class="staff-create-kicker">Thông tin đăng nhập</span>
                    <h3>Hồ sơ nhân viên</h3>
                    <p>Nhập đúng email để nhân viên có thể đăng nhập và nhận thông báo hệ thống.</p>
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
                            required
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
                            placeholder="nhanvien@cinehome.vn"
                            autocomplete="email"
                            required
                        >
                    </div>
                    @error('email')
                        <small class="staff-create-error">{{ $message }}</small>
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
                            required
                        >
                    </div>
                    @error('mat_khau')
                        <small class="staff-create-error">{{ $message }}</small>
                    @else
                        <small class="staff-create-hint">Nên đặt mật khẩu tạm đủ mạnh và yêu cầu nhân viên đổi sau lần đăng nhập đầu.</small>
                    @enderror
                </label>
            </div>

            <div class="staff-create-status-card">
                <span>
                    <i class="fa-solid fa-circle-check"></i>
                </span>
                <div>
                    <strong>Kích hoạt ngay sau khi tạo</strong>
                    <p>Controller hiện tự gán vai trò nhân viên và trạng thái hoạt động cho tài khoản mới.</p>
                </div>
            </div>

            <div class="staff-create-actions">
                <button type="submit" class="staff-create-primary-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu nhân viên
                </button>
                <a href="{{ route('admin.nhanviens.index') }}" class="staff-create-soft-btn">
                    Hủy
                </a>
            </div>
        </main>

        <aside class="staff-create-side">
            <section class="staff-create-preview">
                <span class="staff-create-avatar">
                    <i class="fa-solid fa-user-tie"></i>
                </span>
                <div>
                    <small>Xem nhanh</small>
                    <h3>{{ old('ho_ten') ?: 'Nhân viên mới' }}</h3>
                    <p>{{ old('email') ?: 'email đăng nhập sẽ hiển thị tại đây' }}</p>
                </div>
                <span class="staff-create-status">
                    <i class="fa-solid fa-circle-check"></i>
                    Hoạt động
                </span>
            </section>

            <section class="staff-create-note">
                <h3>
                    <i class="fa-solid fa-shield-halved"></i>
                    Lưu ý trước khi tạo
                </h3>
                <ul>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Email không được trùng với tài khoản đã có trong hệ thống.
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Nhân viên mới được tạo với vai trò vận hành cơ bản.
                    </li>
                    <li>
                        <i class="fa-solid fa-check"></i>
                        Có thể khóa hoặc chỉnh sửa tài khoản ở danh sách nhân viên.
                    </li>
                </ul>
            </section>
        </aside>
    </form>
</div>
@endsection
