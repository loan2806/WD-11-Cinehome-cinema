@extends('layouts.admin')

@section('title', 'Thùng rác nhân viên')
@section('page-title', 'Thùng rác nhân viên')
@section('page-subtitle', 'Quản lý các nhân viên đã xóa mềm')

@section('content')

<section class="staff-list-hero">
    <div class="staff-list-hero-copy">
        <span class="staff-list-kicker">
            <i class="fa-solid fa-trash"></i>
            Thùng rác
        </span>

        <h2>Thùng rác nhân viên</h2>

        <p>
            Danh sách các nhân viên đã được xóa mềm.
            Bạn có thể khôi phục hoặc xóa vĩnh viễn nhân viên.
        </p>

        <div class="staff-list-hero-meta">
            <span>
                <i class="fa-solid fa-trash-can"></i>
                {{ $nhanViens->total() }} nhân viên trong thùng rác
            </span>
        </div>
    </div>

    <a href="{{ route('admin.nhanviens.index') }}"
        class="staff-list-primary-btn">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại danh sách
    </a>
</section>

<section class="staff-list-panel">

    <div class="staff-list-panel-head">
        <div>
            <span class="staff-list-kicker">
                Danh sách
            </span>

            <h3>Nhân viên đã xóa</h3>

            <p>
                Đang hiển thị
                {{ $nhanViens->count() }}
                /
                {{ $nhanViens->total() }}
                nhân viên.
            </p>
        </div>
    </div>

    <form method="GET"
        action="{{ route('admin.nhanviens.trash') }}"
        class="staff-list-filter">

        <label class="staff-list-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
                type="text"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="Tìm theo tên hoặc email...">
        </label>

        <input
            type="date"
            name="deleted_from"
            class="admin-input"
            value="{{ request('deleted_from') }}">

        <input
            type="date"
            name="deleted_to"
            class="admin-input"
            value="{{ request('deleted_to') }}">

        <div class="trash-filter-actions">
            <button class="staff-list-filter-btn">
                <i class="fa-solid fa-filter"></i>
                Lọc
            </button>

            @if(request()->filled('keyword') || request()->filled('deleted_from') || request()->filled('deleted_to'))
            <a href="{{ route('admin.nhanviens.trash') }}"
                class="staff-list-reset-btn">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
            @endif
        </div>

    </form>

    <div class="staff-list-table-wrap">
        <table class="staff-list-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Nhân viên</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Ngày xóa</th>
                    <th class="is-right">Thao tác</th>
                </tr>
            </thead>

            <tbody>

                @forelse($nhanViens as $nhanVien)

                <tr>

                    <td>
                        <span class="staff-id-badge">
                            #{{ $nhanVien->id }}
                        </span>
                    </td>

                    <td>
                        <div class="staff-profile-cell">
                            <span class="staff-avatar">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>

                            <div>
                                <strong>{{ $nhanVien->ho_ten }}</strong>

                                <small>
                                    <i class="fa-solid fa-briefcase"></i>
                                    Nhân viên hệ thống
                                </small>
                            </div>
                        </div>
                    </td>

                    <td>
                        <span class="staff-email">
                            {{ $nhanVien->email }}
                        </span>
                    </td>

                    <td>
                        <span class="staff-status is-locked">
                            <i class="fa-solid fa-trash"></i>
                            Xóa mềm
                        </span>
                    </td>

                    <td>
                        <span class="staff-date">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            {{ optional($nhanVien->deleted_at)->format('d/m/Y H:i') }}
                        </span>
                    </td>

                    <td>
                        <div class="staff-actions">

                            <form method="POST" action="{{ route('admin.nhanviens.restore', $nhanVien->id) }}" onsubmit="return confirm('Bạn có chắc muốn khôi phục nhân viên {{ addslashes($nhanVien->ho_ten) }}?')"> @csrf <button type="submit" class="staff-action-btn is-unlock" title="Khôi phục"> <i class="fa-solid fa-rotate-left"></i> </button> </form>

                            {{-- Xóa vĩnh viễn --}}
                            <form method="POST"
                                action="{{ route('admin.nhanviens.forceDelete', $nhanVien->id) }}"
                                onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn nhân viên này?')">

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="staff-action-btn is-delete"
                                    title="Xóa vĩnh viễn">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>

                            </form>

                        </div>
                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="6">

                        <div class="staff-list-empty">
                            <i class="fa-solid fa-trash-can"></i>

                            <h3>Thùng rác đang trống</h3>

                            <p>
                                Hiện chưa có nhân viên nào bị xóa.
                            </p>

                            <a href="{{ route('admin.nhanviens.index') }}"
                                class="staff-list-primary-btn">
                                <i class="fa-solid fa-arrow-left"></i>
                                Quay lại danh sách
                            </a>
                        </div>

                    </td>
                </tr>

                @endforelse

            </tbody>
        </table>
    </div>

    <div class="staff-list-pagination">
        {{ $nhanViens->links() }}
    </div>

</section>

@endsection