@extends('layouts.admin')

@section('title', 'Thùng rác khách hàng')
@section('page-title', 'Thùng rác khách hàng')
@section('page-subtitle', 'Quản lý các khách hàng đã xóa mềm')

@section('content')

<section class="staff-list-hero">
    <div class="staff-list-hero-copy">
        <span class="staff-list-kicker">
            <i class="fa-solid fa-trash"></i>
            Thùng rác
        </span>

        <h2>Thùng rác khách hàng</h2>

        <p>
            Danh sách các khách hàng đã được xóa mềm.
            Bạn có thể khôi phục hoặc xóa vĩnh viễn khách hàng.
        </p>

        <div class="staff-list-hero-meta">
            <span>
                <i class="fa-solid fa-trash-can"></i>
                {{ $khachHangs->total() }} khách hàng trong thùng rác
            </span>
        </div>
    </div>

    <a href="{{ route('admin.khach-hang.index') }}"
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

            <h3>Khách hàng đã xóa</h3>

            <p>
                Đang hiển thị
                {{ $khachHangs->count() }}
                /
                {{ $khachHangs->total() }}
                khách hàng.
            </p>
        </div>
    </div>


    <form method="GET"
          action="{{ route('admin.khach-hang.trash') }}"
          class="staff-list-filter">

        <label class="staff-list-search">
            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="Tìm theo tên, email hoặc số điện thoại...">
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

            <button type="submit" class="staff-list-filter-btn">
                <i class="fa-solid fa-filter"></i>
                Lọc
            </button>


            @if(
                request()->filled('keyword') ||
                request()->filled('deleted_from') ||
                request()->filled('deleted_to')
            )

                <a href="{{ route('admin.khach-hang.trash') }}"
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
                    <th>Khách hàng</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Trạng thái</th>
                    <th>Ngày xóa</th>
                    <th class="is-right">Thao tác</th>
                </tr>
            </thead>


            <tbody>

                @forelse($khachHangs as $khachHang)

                    <tr>

                        {{-- Mã --}}
                        <td>
                            <span class="staff-id-badge">
                                #{{ $khachHang->id }}
                            </span>
                        </td>


                        {{-- Khách hàng --}}
                        <td>

                            <div class="staff-profile-cell">

                                <span class="staff-avatar">
                                    <i class="fa-solid fa-user"></i>
                                </span>

                                <div>

                                    <strong>
                                        {{ $khachHang->ho_ten }}
                                    </strong>

                                    <small>
                                        <i class="fa-solid fa-user"></i>
                                        Khách hàng
                                    </small>

                                </div>

                            </div>

                        </td>


                        {{-- Email --}}
                        <td>

                            <span class="staff-email">
                                {{ $khachHang->email }}
                            </span>

                        </td>


                        {{-- Số điện thoại --}}
                        <td>

                            <span class="staff-email">

                                <i class="fa-solid fa-phone"></i>

                                {{ $khachHang->so_dien_thoai ?: 'Chưa có SĐT' }}

                            </span>

                        </td>


                        {{-- Trạng thái --}}
                        <td>

                            <span class="staff-status is-locked">

                                <i class="fa-solid fa-trash"></i>

                                Xóa mềm

                            </span>

                        </td>


                        {{-- Ngày xóa --}}
                        <td>

                            <span class="staff-date">

                                <i class="fa-regular fa-calendar-xmark"></i>

                                {{ optional($khachHang->deleted_at)->format('d/m/Y H:i') }}

                            </span>

                        </td>


                        {{-- Thao tác --}}
                        <td>

                            <div class="staff-actions">


                                {{-- KHÔI PHỤC --}}
                                <form method="POST"
                                      action="{{ route('admin.khach-hang.restore', $khachHang->id) }}"
                                      onsubmit="return confirm('Bạn có chắc muốn khôi phục khách hàng này?');">

                                    @csrf

                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="staff-action-btn is-unlock"
                                        title="Khôi phục">

                                        <i class="fa-solid fa-rotate-left"></i>

                                    </button>

                                </form>


                                {{-- XÓA VĨNH VIỄN --}}
                                <form method="POST"
                                      action="{{ route('admin.khach-hang.force-delete', $khachHang->id) }}"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn khách hàng này?')">

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

                        <td colspan="7">

                            <div class="staff-list-empty">

                                <i class="fa-solid fa-trash-can"></i>

                                <h3>Thùng rác đang trống</h3>

                                <p>
                                    Hiện chưa có khách hàng nào bị xóa.
                                </p>

                                <a href="{{ route('admin.khach-hang.index') }}"
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

        {{ $khachHangs->links() }}

    </div>

</section>

@endsection