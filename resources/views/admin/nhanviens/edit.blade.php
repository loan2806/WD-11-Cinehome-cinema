@extends('layouts.admin')

@section('title', 'Chỉnh sửa nhân viên')
@section('page-title', 'Chỉnh sửa nhân viên')
@section('page-subtitle', 'Cập nhật thông tin đăng nhập và trạng thái tài khoản nhân viên')

@section('content')
@php
    $isActive = (bool) $nhanvien->trang_thai_hoat_dong;
    $isSelf = auth()->id() === $nhanvien->id;
@endphp

<div class="staff-create-page staff-edit-page">
    @include('admin.partials.flash')

    <section class="staff-create-hero">
        <div>
            <span class="staff-create-kicker">
                <i class="fa-solid fa-user-pen"></i>
                Hồ sơ nhân viên
            </span>
            <h2>Chỉnh sửa {{ $nhanvien->ho_ten }}</h2>
            <p>Cập nhật họ tên, email đăng nhập và kiểm tra nhanh trạng thái hoạt động của tài khoản nhân viên trong hệ thống.</p>
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
                <p>Vui lòng kiểm tra lại họ tên hoặc email trước khi cập nhật nhân viên.</p>
            </div>
        </section>
    @endif

    <div class="staff-create-layout">
        <form action="{{ route('admin.nhanviens.update', $nhanvien) }}" method="POST" class="staff-create-panel">
            @csrf
            @method('PUT')

            <div class="staff-create-panel-head">
                <span class="staff-create-panel-icon">
                    <i class="fa-solid fa-id-card-clip"></i>
                </span>
                <div>
                    <span class="staff-create-kicker">Thông tin tài khoản</span>
                    <h3>Cập nhật hồ sơ</h3>
                    <p>Những thông tin này dùng để nhận diện nhân viên trong nhật ký hệ thống và các thao tác nghiệp vụ.</p>
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
                            value="{{ old('ho_ten', $nhanvien->ho_ten) }}"
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
                            value="{{ old('email', $nhanvien->email) }}"
                            placeholder="nhanvien@cinehome.vn"
                            autocomplete="email"
                            required
                        >
                    </div>
                    @error('email')
                        <small class="staff-create-error">{{ $message }}</small>
                    @enderror
                </label>
            </div>

            <div class="staff-edit-status-card {{ $isActive ? 'is-active' : 'is-locked' }}">
                <span>
                    <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-lock' }}"></i>
                </span>
                <div>
                    <strong>{{ $isActive ? 'Tài khoản đang hoạt động' : 'Tài khoản đang bị khóa' }}</strong>
                    <p>
                        {{ $isActive
                            ? 'Nhân viên có thể đăng nhập và thực hiện các nghiệp vụ được phân quyền.'
                            : 'Nhân viên tạm thời không thể sử dụng tài khoản để thao tác hệ thống.' }}
                    </p>
                </div>
            </div>

            <div class="staff-create-actions">
                <button type="submit" class="staff-create-primary-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Cập nhật nhân viên
                </button>
                <a href="{{ route('admin.nhanviens.index') }}" class="staff-create-soft-btn">
                    Hủy
                </a>
            </div>
        </form>

        <aside class="staff-create-side">
            <section class="staff-create-preview">
                <span class="staff-create-avatar">
                    <i class="fa-solid fa-user-tie"></i>
                </span>
                <div>
                    <small>Xem nhanh</small>
                    <h3>{{ old('ho_ten', $nhanvien->ho_ten) }}</h3>
                    <p>{{ old('email', $nhanvien->email) }}</p>
                </div>
                <span class="staff-create-status {{ $isActive ? '' : 'is-locked' }}">
                    <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-lock' }}"></i>
                    {{ $isActive ? 'Hoạt động' : 'Đã khóa' }}
                </span>
            </section>

            <section class="staff-edit-meta">
                <h3>
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Thông tin hệ thống
                </h3>
                <div class="staff-edit-meta-list">
                    <div>
                        <span>Mã nhân viên</span>
                        <strong>#{{ $nhanvien->id }}</strong>
                    </div>
                    <div>
                        <span>Ngày tạo</span>
                        <strong>{{ $nhanvien->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Cập nhật gần nhất</span>
                        <strong>{{ $nhanvien->updated_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                    </div>
                </div>
            </section>

            <section class="staff-edit-danger-zone">
                <h3>
                    <i class="fa-solid fa-shield-halved"></i>
                    Trạng thái tài khoản
                </h3>
                <p>
                    {{ $isSelf
                        ? 'Bạn đang xem chính tài khoản của mình nên hệ thống không cho phép tự khóa tài khoản.'
                        : 'Dùng thao tác này khi nhân viên nghỉ tạm thời hoặc cần mở lại quyền đăng nhập.' }}
                </p>

                <form method="POST" action="{{ route('admin.nhanviens.toggle-status', $nhanvien) }}">
                    @csrf
                    @method('PATCH')
                    <button
                        type="submit"
                        class="staff-edit-toggle-btn {{ $isActive ? 'is-lock' : 'is-unlock' }}"
                        @disabled($isSelf)
                    >
                        <i class="fa-solid {{ $isActive ? 'fa-lock' : 'fa-lock-open' }}"></i>
                        {{ $isActive ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}
                    </button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
