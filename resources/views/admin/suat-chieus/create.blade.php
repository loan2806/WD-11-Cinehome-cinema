@extends('layouts.admin')

@section('page-title', 'Thêm suất chiếu')
@section('page-subtitle', 'Lên lịch chiếu đơn lẻ hoặc tạo chuỗi suất chiếu hàng loạt')

@php
    $selectedLoaiTao = old('loai_tao', 'don_le');
    $selectedCheDoHangLoat = old('che_do_hang_loat', 'tu_dong');
    $selectedPhimId = old('phim_id', request('phim_id'));
    $selectedPhongId = old('phong_chieu_id', $phongChieuId ?? request('phong_chieu_id'));
    $selectedKhungGio = old('khung_gio', []);
    $selectedKhungGio = is_array($selectedKhungGio) ? $selectedKhungGio : [];
    $khungGioMacDinh = ['08:30', '11:00', '13:30', '16:00', '18:30', '21:00', '23:30'];
    $valDonPhong = old('thoi_gian_don_phong', $thoiGianDonPhong ?? 15);
@endphp

@push('styles')
<style>
    .showtime-form-layout,
    .showtime-main,
    .showtime-panel,
    .showtime-grid,
    .showtime-field {
        overflow: visible !important;
        position: relative !important;
    }

    .showtime-panel {
        z-index: 10;
    }

    .showtime-panel:has(.cine-select-wrapper.open) {
        z-index: 900 !important;
    }

    .cine-select-wrapper {
        position: relative !important;
        width: 100% !important;
        user-select: none !important;
        z-index: 20 !important;
    }

    .cine-select-wrapper.open {
        z-index: 99999 !important;
    }

    .cine-select-trigger {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: #18181c !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
        color: #f3f4f6 !important;
        font-size: 14px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        outline: none !important;
    }

    .cine-select-trigger:hover,
    .cine-select-wrapper.open .cine-select-trigger {
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.2) !important;
    }

    .cine-select-trigger i {
        color: #facc15 !important;
        font-size: 12px !important;
        transition: transform 0.2s ease !important;
    }

    .cine-select-wrapper.open .cine-select-trigger i {
        transform: rotate(180deg) !important;
    }

    .cine-select-menu {
        position: absolute !important;
        top: calc(100% + 6px) !important;
        left: 0 !important;
        right: 0 !important;
        min-width: 100% !important;
        background: #18181c !important;
        border: 1px solid rgba(250, 204, 21, 0.35) !important;
        border-radius: 16px !important;
        padding: 8px !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.95), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
        z-index: 999999 !important;
        max-height: 260px !important;
        overflow-y: auto !important;
        display: none !important;
    }

    .cine-select-wrapper.open .cine-select-menu {
        display: block !important;
    }

    .cine-select-option {
        padding: 10px 12px !important;
        border-radius: 10px !important;
        color: #d1d5db !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        margin-bottom: 2px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .cine-select-option:hover {
        background: rgba(250, 204, 21, 0.15) !important;
        color: #facc15 !important;
    }

    .cine-select-option.selected {
        background: rgba(250, 204, 21, 0.25) !important;
        color: #facc15 !important;
        font-weight: 700 !important;
    }

    .showtime-time-list {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        margin-top: 10px !important;
    }

    .showtime-time-chip {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        background: #18181c !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        border-radius: 10px !important;
        padding: 8px 14px !important;
        color: #fff !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        user-select: none !important;
    }

    .showtime-time-chip:has(input:checked) {
        border-color: #facc15 !important;
        background: rgba(250, 204, 21, 0.15) !important;
        color: #facc15 !important;
    }

    /* Form Cảnh báo trùng */
    .showtime-conflict-box {
        background: rgba(239, 68, 68, 0.1) !important;
        border: 1px solid rgba(239, 68, 68, 0.4) !important;
        border-radius: 16px !important;
        padding: 20px !important;
        margin-bottom: 24px !important;
    }
    .showtime-conflict-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        color: #f87171;
        margin-bottom: 16px;
    }
    .showtime-conflict-head i {
        font-size: 24px;
        margin-top: 2px;
    }
    .showtime-conflict-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }
    .btn-conflict-continue {
        background: #ef4444;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-conflict-continue:hover {
        background: #dc2626;
    }
    .btn-conflict-cancel {
        background: #27272a;
        color: #d4d4d8;
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-conflict-cancel:hover {
        background: #3f3f46;
    }
</style>
@endpush

@section('content')
<div class="showtime-create-page">
    @include('admin.partials.flash')


    <section class="showtime-hero-panel">
        <div>
            <span class="showtime-kicker">
                <i class="fa-solid fa-calendar-plus"></i> Trung tâm lên lịch
            </span>
            <h2>Thêm suất chiếu mới</h2>
            <p>Cấu hình lịch chiếu đơn lẻ hoặc rải chuỗi suất chiếu hàng loạt.</p>
        </div>

        <div class="showtime-hero-actions">
            <a href="{{ route('admin.suat-chieus.index') }}" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-arrow-left"></i> Danh sách suất chiếu
            </a>
            <button type="submit" form="showtimeCreateForm" class="movie-action-btn is-primary">
                <i class="fa-solid fa-floppy-disk"></i> Xác nhận lên lịch
            </button>
        </div>
    </section>

    <!-- FORM BÁO CẢNH BÁO TRÙNG LỊCH KHI TẠO HÀNG LOẠT -->
    @if (session('suat_chieu_trung_danh_sach') || session('khung_gio_trung_noibo'))
        <section class="showtime-conflict-box">
            <div class="showtime-conflict-head">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #f87171;">Phát hiện trùng lịch suất chiếu!</h3>
                    <p style="margin: 0; font-size: 14px; color: #fca5a5;">
                        Thời lượng phim là <strong>{{ session('thoi_luong_phim_phut', 90) }} phút</strong> (+{{ session('thoi_gian_don_phong_phut', 15) }}p dọn phòng). Một số khung giờ bạn chọn bị chồng chéo thời gian:
                    </p>
                </div>
            </div>

            @if (session('khung_gio_trung_noibo') && count(session('khung_gio_trung_noibo')) > 0)
                <div style="background: rgba(0,0,0,0.3); border-radius: 10px; padding: 12px; margin-bottom: 12px; color: #fecaca; font-size: 13.5px;">
                    <strong><i class="fa-solid fa-clock"></i> Khung giờ chọn thủ công bị trùng nhau:</strong>
                    <span style="color: #facc15; font-weight: 700;">{{ implode(', ', session('khung_gio_trung_noibo')) }}</span>
                    <br><small style="color: #a1a1aa;">Các mốc giờ này quá gần nhau so với thời lượng phim nên không thể chiếu cùng phòng.</small>
                </div>
            @endif

            @if (session('suat_chieu_trung_danh_sach') && count(session('suat_chieu_trung_danh_sach')) > 0)
                <div class="showtime-conflict-table" style="margin-bottom: 12px;">
                    <strong style="color: #fecaca; display: block; margin-bottom: 8px;"><i class="fa-solid fa-database"></i> Trùng với các suất chiếu đã có sẵn trong phòng:</strong>
                    <table>
                        <thead>
                            <tr>
                                <th>ID suất</th>
                                <th>Phim đang chiếm chỗ</th>
                                <th>Phòng</th>
                                <th>Thời gian chiếu</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (session('suat_chieu_trung_danh_sach') as $scTrung)
                                <tr>
                                    <td>#{{ $scTrung->id }}</td>
                                    <td>{{ $scTrung->phim->ten_phim ?? 'Không rõ' }}</td>
                                    <td>{{ $scTrung->phongChieu->ten_phong ?? 'Không rõ' }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($scTrung->thoi_gian_chieu)->format('H:i d/m/Y') }} - {{ \Carbon\Carbon::parse($scTrung->thoi_gian_ket_thuc)->format('H:i d/m/Y') }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.suat-chieus.edit', $scTrung->id) }}" target="_blank">
                                            Sửa suất này <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="showtime-conflict-actions">
                <button type="button" class="btn-conflict-continue" onclick="submitIgnoreConflicts()">
                    <i class="fa-solid fa-forward"></i> Tiếp tục (Loại bỏ suất trùng & Thêm suất hợp lệ)
                </button>

                <button type="button" class="btn-conflict-cancel" onclick="cancelConflictWarning()">
                    <i class="fa-solid fa-pen-to-square"></i> Hủy & Sửa lại dữ liệu
                </button>
            </div>
        </section>
    @endif

    <form id="showtimeCreateForm" action="{{ route('admin.suat-chieus.store') }}" method="POST" class="showtime-form">
        @csrf
        <input type="hidden" name="rap_chieu_phim_id" value="{{ $rapMacDinh->id ?? 1 }}">
        <input type="hidden" name="bo_qua_trung" id="bo_qua_trung" value="0">

        <div class="showtime-form-layout">
            <main class="showtime-main">
                <section class="showtime-panel">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-clapperboard"></i></span>
                        <div>
                            <h3>1. Chọn phim và phòng chiếu</h3>
                            <p>Xác định phim và phòng chiếu trước khi chọn khung giờ.</p>
                        </div>
                    </div>

                    <div class="showtime-grid" style="grid-template-columns: 1fr 1fr !important; gap: 20px;">
                        <div class="showtime-field">
                            <span>Phim trình chiếu <b>*</b></span>
                            <div class="cine-select-wrapper" id="wrap_phim">
                                <input type="hidden" name="phim_id" id="phim_id" value="{{ $selectedPhimId }}" required>
                                <div class="cine-select-trigger" tabindex="0">
                                    <span class="cine-select-value">Chọn phim</span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                                <div class="cine-select-menu">
                                    <div class="cine-select-option {{ empty($selectedPhimId) ? 'selected' : '' }}" data-value="" data-thoi-luong="90">Chọn phim</div>
                                    @foreach ($phims as $phim)
                                        <div class="cine-select-option {{ (string)$selectedPhimId === (string)$phim->id ? 'selected' : '' }}" 
                                             data-value="{{ $phim->id }}"
                                             data-thoi-luong="{{ $phim->thoi_luong ?? 90 }}">
                                            {{ $phim->ten_phim }} ({{ $phim->thoi_luong ?? 90 }} phút)
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="showtime-field">
                            <span>Phòng chiếu <b>*</b></span>
                            <div class="cine-select-wrapper" id="wrap_phong">
                                <input type="hidden" name="phong_chieu_id" id="phong_chieu_id" value="{{ $selectedPhongId }}" required>
                                <div class="cine-select-trigger" tabindex="0">
                                    <span class="cine-select-value">Chọn phòng</span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                                <div class="cine-select-menu">
                                    <div class="cine-select-option {{ empty($selectedPhongId) ? 'selected' : '' }}" data-value="">Chọn phòng</div>
                                    @foreach ($phongChieus ?? [] as $phong)
                                        <div class="cine-select-option {{ (string)$selectedPhongId === (string)$phong->id ? 'selected' : '' }}" 
                                             data-value="{{ $phong->id }}"
                                             data-room-type="{{ strtoupper($phong->loai_phong) }}">
                                            {{ $phong->ten_phong }} ({{ strtoupper($phong->loai_phong) }})
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="showtime-panel">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-sliders"></i></span>
                        <div>
                            <h3>2. Phương thức lên lịch</h3>
                            <p>Tạo một suất cụ thể hoặc rải nhiều suất theo khoảng ngày.</p>
                        </div>
                    </div>

                    <div class="showtime-mode-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; margin-bottom: 20px;">
                        <div class="showtime-field">
                            <span>Chế độ tạo lịch <b>*</b></span>
                            <div class="cine-select-wrapper">
                                <input type="hidden" name="loai_tao" id="loai_tao" value="{{ $selectedLoaiTao }}" required>
                                <div class="cine-select-trigger" tabindex="0">
                                    <span class="cine-select-value">{{ $selectedLoaiTao === 'hang_loat' ? 'Tạo chuỗi suất chiếu hàng loạt' : 'Tạo 1 suất chiếu đơn lẻ' }}</span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                                <div class="cine-select-menu">
                                    <div class="cine-select-option {{ $selectedLoaiTao === 'don_le' ? 'selected' : '' }}" data-value="don_le">
                                        Tạo 1 suất chiếu đơn lẻ
                                    </div>
                                    <div class="cine-select-option {{ $selectedLoaiTao === 'hang_loat' ? 'selected' : '' }}" data-value="hang_loat">
                                        Tạo chuỗi suất chiếu hàng loạt
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ô THỜI GIAN DỌN PHÒNG -->
                        <div class="showtime-field">
                            <span>Thời gian dọn phòng (Phút) <b>*</b></span>
                            <input type="number" name="thoi_gian_don_phong" id="thoi_gian_don_phong" min="15" max="30" value="{{ $valDonPhong }}" style="color-scheme: dark; background: #18181c; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 12px; border-radius: 12px;">
                            <small style="color: #a1a1aa; margin-top: 4px;">Tối thiểu 15 phút - Tối đa 30 phút.</small>
                        </div>
                    </div>

                    <!-- KHU VỰC TẠO ĐƠN LẺ -->
                    <div id="khu_don_le" class="showtime-mode-block">
                        <div class="showtime-grid two-cols">
                            <label class="showtime-field">
                                <span>Ngày chiếu <b>*</b></span>
                                <input type="date" name="ngay_chieu_don_le" id="ngay_chieu_don_le" min="{{ date('Y-m-d') }}" value="{{ old('ngay_chieu_don_le') }}" style="color-scheme: dark; background: #18181c; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 12px; border-radius: 12px;">
                            </label>

                            <label class="showtime-field">
                                <span>Giờ khởi chiếu <b>*</b></span>
                                <input type="time" name="gio_chieu_don_le" id="gio_chieu_don_le" value="{{ old('gio_chieu_don_le') }}" style="color-scheme: dark; background: #18181c; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 12px; border-radius: 12px;">
                            </label>
                        </div>
                    </div>

                    <!-- KHU VỰC TẠO HÀNG LOẠT -->
                    <div id="khu_hang_loat" class="showtime-mode-block" style="display: none;">
                        <div class="showtime-grid two-cols" style="margin-bottom: 20px;">
                            <label class="showtime-field">
                                <span>Từ ngày <b>*</b></span>
                                <input type="date" name="ngay_bat_dau" id="ngay_bat_dau" min="{{ date('Y-m-d') }}" value="{{ old('ngay_bat_dau') }}" style="color-scheme: dark; background: #18181c; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 12px; border-radius: 12px;">
                            </label>

                            <label class="showtime-field">
                                <span>Đến hết ngày <b>*</b></span>
                                <input type="date" name="ngay_ket_thuc" id="ngay_ket_thuc" min="{{ date('Y-m-d') }}" value="{{ old('ngay_ket_thuc') }}" style="color-scheme: dark; background: #18181c; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 12px; border-radius: 12px;">
                            </label>
                        </div>

                        <!-- LỰA CHỌN 2 CHẾ ĐỘ TẠO HÀNG LOẠT -->
                        <div style="background: rgba(250, 204, 21, 0.05); border: 1px solid rgba(250, 204, 21, 0.2); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                            <span style="font-size: 14px; font-weight: 700; color: #facc15; display: block; margin-bottom: 10px;">
                                <i class="fa-solid fa-gears"></i> Lựa chọn kiểu sinh suất chiếu hàng loạt:
                            </span>

                            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                                <label style="display: flex; align-items: center; gap: 8px; color: #fff; font-size: 14px; cursor: pointer; font-weight: 600;">
                                    <input type="radio" name="che_do_hang_loat" value="tu_dong" @checked($selectedCheDoHangLoat === 'tu_dong') onchange="window.switchSubBatchMode()" style="accent-color: #facc15;">
                                    <span>Chế độ 1: Tự động tính theo khung giờ Đầu - Cuối</span>
                                </label>

                                <label style="display: flex; align-items: center; gap: 8px; color: #fff; font-size: 14px; cursor: pointer; font-weight: 600;">
                                    <input type="radio" name="che_do_hang_loat" value="thu_cong" @checked($selectedCheDoHangLoat === 'thu_cong') onchange="window.switchSubBatchMode()" style="accent-color: #facc15;">
                                    <span>Chế độ 2: Chọn các khung giờ chiếu thủ công</span>
                                </label>
                            </div>
                        </div>

                        <!-- CHẾ ĐỘ 1: TỰ ĐỘNG TÍNH THEO GIỜ ĐẦU - CUỐI -->
                        <div id="sub_che_do_tu_dong" class="showtime-grid two-cols" style="background: #18181c; padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
                            <label class="showtime-field">
                                <span>Giờ bắt đầu (Suất chiếu đầu) <b>*</b></span>
                                <input type="time" name="gio_bat_dau_tu_dong" id="gio_bat_dau_tu_dong" value="{{ old('gio_bat_dau_tu_dong', '08:00') }}" style="color-scheme: dark; background: #25252b; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 12px; border-radius: 12px;">
                                <small style="color: #a1a1aa; margin-top: 4px;">Ví dụ: 08:00</small>
                            </label>

                            <label class="showtime-field">
                                <span>Giờ kết thúc (Giờ muộn nhất) <b>*</b></span>
                                <input type="time" name="gio_ket_thuc_tu_dong" id="gio_ket_thuc_tu_dong" value="{{ old('gio_ket_thuc_tu_dong', '23:30') }}" style="color-scheme: dark; background: #25252b; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 12px; border-radius: 12px;">
                                <small style="color: #a1a1aa; margin-top: 4px;" id="sub_tu_dong_note">Hệ thống tự tính thời lượng + {{ $valDonPhong }}p dọn phòng để tạo suất tiếp theo.</small>
                            </label>
                        </div>

                        <!-- CHẾ ĐỘ 2: CHỌN KHUNG GIỜ THỦ CÔNG (GIỐNG HỆT ẢNH 2) -->
                        <div id="sub_che_do_thu_cong" style="background: #18181c; padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: none;">
                            <div style="margin-bottom: 12px;">
                                <strong style="color: #fff; font-size: 15px; display: block;">Khung giờ trong ngày <b style="color: #ef4444;">*</b></strong>
                                <span style="color: #a1a1aa; font-size: 13px;">Chọn một hoặc nhiều mốc giờ để tạo lịch hàng loạt.</span>
                            </div>

                            <div class="showtime-time-list" id="khung_gio_checkboxes" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                                @foreach ($khungGioMacDinh as $gio)
                                    <label class="showtime-time-chip">
                                        <input type="checkbox" name="khung_gio[]" value="{{ $gio }}" @checked(in_array($gio, $selectedKhungGio))>
                                        <span>{{ $gio }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <!-- KHU VỰC CHÈN GIỜ KHÁC -->
                            <div class="showtime-custom-time" style="display: flex; align-items: center; gap: 10px;">
                                <input type="time" id="custom_time_input" style="color-scheme: dark; background: #25252b; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 10px 14px; border-radius: 8px;">
                                <button type="button" id="btn_add_custom_time" onclick="window.executeAddCustomTime(event)" style="background: #25252b; border: 1px solid #d99a32; color: #d99a32; padding: 10px 18px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-plus"></i> Chèn giờ khác
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="showtime-panel">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-money-bill-wave"></i></span>
                        <div>
                            <h3>3. Biểu giá</h3>
                            <p>Để trống giá tùy chỉnh nếu muốn dùng ma trận giá tự động của hệ thống.</p>
                        </div>
                    </div>

                    <div id="khu_vuc_gia_ngay_le" class="showtime-holiday-price" style="display: none;">
                        <div class="showtime-holiday-head">
                            <i class="fa-solid fa-gift"></i>
                            <div>
                                <strong>Phát hiện ngày lễ: <span id="ten_ngay_le_label"></span></strong>
                                <small>Nhập giá riêng nếu muốn áp dụng biểu giá ngày lễ.</small>
                            </div>
                        </div>
                        <label class="showtime-field">
                            <span>Giá vé ngày lễ</span>
                            <div class="showtime-money-input">
                                <input type="number" name="gia_ve_ngay_le" id="gia_ve_ngay_le" value="{{ old('gia_ve_ngay_le') }}" placeholder="Ví dụ: 120000">
                                <em>VND</em>
                            </div>
                        </label>
                    </div>

                    <div class="showtime-grid two-cols">
                        <label class="showtime-field">
                            <span>Giá vé tùy chỉnh</span>
                            <div class="showtime-money-input">
                                <input type="number" name="gia_ve_tuy_chinh" value="{{ old('gia_ve_tuy_chinh') }}" placeholder="Bỏ trống để dùng giá tự động">
                                <em>VND</em>
                            </div>
                        </label>

                        <div class="showtime-price-note">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Giá tùy chỉnh sẽ ghi đè giá thường/cuối tuần.</span>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="showtime-side">
                <section class="showtime-panel showtime-monitor">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-desktop"></i></span>
                        <div>
                            <h3>Monitor lịch</h3>
                            <p>Xem nhanh thời lượng chiếm phòng trước khi tạo.</p>
                        </div>
                    </div>

                    <textarea id="thoi_luong_preview" readonly placeholder="Chọn phim và thời gian để xem phân tích lịch..."></textarea>

                    <div class="showtime-monitor-metrics">
                        <div>
                            <small>Dọn phòng</small>
                            <strong id="metric_don_phong">{{ $valDonPhong }} phút</strong>
                        </div>
                        <div>
                            <small>Chế độ</small>
                            <strong id="modePreview">Đơn lẻ</strong>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <div class="showtime-savebar">
            <div>
                <strong>Sẵn sàng tạo suất chiếu?</strong>
                <span>Hệ thống sẽ kiểm tra trùng lịch trước khi lưu.</span>
            </div>
            <div class="showtime-save-actions">
                <a href="{{ route('admin.suat-chieus.index') }}" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-xmark"></i> Hủy
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Xác nhận lên lịch
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function submitIgnoreConflicts() {
    document.getElementById('bo_qua_trung').value = '1';
    document.getElementById('showtimeCreateForm').submit();
}

function cancelConflictWarning() {
    const conflictBox = document.querySelector('.showtime-conflict-box');
    if (conflictBox) conflictBox.style.display = 'none';
    document.getElementById('bo_qua_trung').value = '0';
}

// Hàm ẩn / hiện Chế độ 1 và Chế độ 2
window.switchSubBatchMode = function() {
    const loaiInput = document.getElementById('loai_tao');
    const subTuDong = document.getElementById('sub_che_do_tu_dong');
    const subThuCong = document.getElementById('sub_che_do_thu_cong');
    const selectedRadio = document.querySelector('input[name="che_do_hang_loat"]:checked');

    if (!loaiInput || loaiInput.value !== 'hang_loat') return;

    const isTuDong = !selectedRadio || selectedRadio.value === 'tu_dong';

    if (subTuDong) {
        subTuDong.style.setProperty('display', isTuDong ? 'grid' : 'none', 'important');
    }
    if (subThuCong) {
        subThuCong.style.setProperty('display', isTuDong ? 'none' : 'block', 'important');
    }

    if (typeof updateMonitor === 'function') {
        updateMonitor();
    }
};

window.executeAddCustomTime = function(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const customTimeInput = document.getElementById('custom_time_input');
    const khungGioContainer = document.getElementById('khung_gio_checkboxes');
    if (!customTimeInput || !khungGioContainer) return false;

    const rawVal = customTimeInput.value;
    if (!rawVal) {
        alert('Vui lòng chọn đầy đủ Giờ và Phút trước khi bấm Chèn!');
        customTimeInput.focus();
        return false;
    }

    const parts = rawVal.split(':');
    const formattedVal = parts[0].padStart(2, '0') + ':' + parts[1].substring(0, 2).padStart(2, '0');

    let existingCheckbox = khungGioContainer.querySelector(`input[value="${formattedVal}"]`);
    if (existingCheckbox) {
        existingCheckbox.checked = true;
    } else {
        const newChip = document.createElement('label');
        newChip.className = 'showtime-time-chip';
        newChip.innerHTML = `<input type="checkbox" name="khung_gio[]" value="${formattedVal}" checked> <span>${formattedVal}</span>`;
        khungGioContainer.appendChild(newChip);
    }

    customTimeInput.value = '';
    if (typeof updateMonitor === 'function') updateMonitor();
    return false;
};

document.addEventListener('DOMContentLoaded', function() {
    const mainForm = document.getElementById('showtimeCreateForm');
    if (mainForm) {
        mainForm.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const target = e.target;
                if (target && target.id === 'custom_time_input') {
                    e.preventDefault();
                    e.stopPropagation();
                    window.executeAddCustomTime(e);
                    return false;
                }
                if (target && target.tagName === 'INPUT' && target.type !== 'submit') {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }

    // Dropdown Custom
    document.querySelectorAll('.cine-select-wrapper').forEach(function(wrapper) {
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const trigger = wrapper.querySelector('.cine-select-trigger');
        const triggerText = wrapper.querySelector('.cine-select-value');
        const options = wrapper.querySelectorAll('.cine-select-option');

        options.forEach(function(opt) {
            if (opt.classList.contains('selected')) {
                triggerText.textContent = opt.textContent.trim();
            }
        });

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            document.querySelectorAll('.cine-select-wrapper').forEach(function(w) {
                if (w !== wrapper) w.classList.remove('open');
            });
            wrapper.classList.toggle('open');
        });

        options.forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                hiddenInput.value = opt.dataset.value;
                triggerText.textContent = opt.textContent.trim();

                options.forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');

                wrapper.classList.remove('open');
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.cine-select-wrapper').forEach(w => w.classList.remove('open'));
    });

    // Ép dọn phòng 15 - 30p
    const donPhongInput = document.getElementById('thoi_gian_don_phong');
    if (donPhongInput) {
        donPhongInput.addEventListener('change', function() {
            let val = parseInt(this.value) || 15;
            if (val < 15) val = 15;
            if (val > 30) val = 30;
            this.value = val;

            const metricDonPhong = document.getElementById('metric_don_phong');
            if (metricDonPhong) metricDonPhong.textContent = val + ' phút';
            const subNote = document.getElementById('sub_tu_dong_note');
            if (subNote) subNote.textContent = `Hệ thống tự tính thời lượng + ${val}p dọn phòng để tạo suất tiếp theo.`;

            updateMonitor();
        });
    }

    // Chuyển đổi giữa Đơn Lẻ & Hàng Loạt
    const loaiInput = document.getElementById('loai_tao');
    const khuDonLe = document.getElementById('khu_don_le');
    const khuHangLoat = document.getElementById('khu_hang_loat');
    const modePreview = document.getElementById('modePreview');

    function switchFormMode() {
        if (!loaiInput) return;
        const isSingle = loaiInput.value === 'don_le';
        if (khuDonLe) khuDonLe.style.setProperty('display', isSingle ? 'block' : 'none', 'important');
        if (khuHangLoat) khuHangLoat.style.setProperty('display', isSingle ? 'none' : 'block', 'important');
        if (modePreview) modePreview.textContent = isSingle ? 'Đơn lẻ' : 'Hàng loạt';
        window.switchSubBatchMode();
    }

    if (loaiInput) {
        loaiInput.addEventListener('change', switchFormMode);
        switchFormMode();
    }

    // Monitor Phân Tích Lịch
    const phimInput = document.getElementById('phim_id');
    const ngayChieuInput = document.getElementById('ngay_chieu_don_le');
    const gioChieuInput = document.getElementById('gio_chieu_don_le');
    const previewArea = document.getElementById('thoi_luong_preview');
    const khungGioContainer = document.getElementById('khung_gio_checkboxes');

    window.updateMonitor = function() {
        if (!previewArea) return;

        const selectedPhimOpt = document.querySelector('#wrap_phim .cine-select-option.selected');
        const tenPhim = selectedPhimOpt ? selectedPhimOpt.textContent.trim() : 'Chưa chọn phim';
        const thoiLuong = selectedPhimOpt ? (parseInt(selectedPhimOpt.dataset.thoiLuong) || 90) : 90;
        const curDonPhong = parseInt(donPhongInput?.value) || 15;

        if (!phimInput.value) {
            previewArea.value = "Vui lòng chọn Phim và Khung giờ để xem phân tích...";
            return;
        }

        if (loaiInput.value === 'don_le') {
            const ngay = ngayChieuInput.value || 'YYYY-MM-DD';
            const gio = gioChieuInput.value || 'HH:MM';
            previewArea.value = `[CHIẾM PHÒNG CHIẾU]\n- Phim: ${tenPhim}\n- Thời lượng: ${thoiLuong} phút (+${curDonPhong}p dọn phòng)\n- Khởi chiếu: ${gio} ngày ${ngay}`;
        } else {
            const selectedRadio = document.querySelector('input[name="che_do_hang_loat"]:checked');
            const subMode = selectedRadio ? selectedRadio.value : 'tu_dong';

            if (subMode === 'tu_dong') {
                const gStart = document.getElementById('gio_bat_dau_tu_dong')?.value || '08:00';
                const gEnd = document.getElementById('gio_ket_thuc_tu_dong')?.value || '23:30';
                previewArea.value = `[TỰ ĐỘNG TÍNH SUẤT CHIẾU]\n- Phim: ${tenPhim}\n- Thời lượng: ${thoiLuong} phút (+${curDonPhong}p dọn phòng)\n- Suất đầu: ${gStart}\n- Giờ kết thúc tối đa: ${gEnd}`;
            } else {
                const checkedBoxes = document.querySelectorAll('input[name="khung_gio[]"]:checked');
                const selectedGios = Array.from(checkedBoxes).map(cb => cb.value).join(', ');
                previewArea.value = `[THỦ CÔNG CÓ VALIDATE TRÙNG]\n- Phim: ${tenPhim}\n- Thời lượng: ${thoiLuong} phút (+${curDonPhong}p dọn phòng)\n- Mốc giờ đã chọn: ${selectedGios || 'Chưa chọn mốc giờ nào'}`;
            }
        }
    };

    if (phimInput) phimInput.addEventListener('change', updateMonitor);
    if (ngayChieuInput) ngayChieuInput.addEventListener('change', updateMonitor);
    if (gioChieuInput) gioChieuInput.addEventListener('change', updateMonitor);
    document.getElementById('gio_bat_dau_tu_dong')?.addEventListener('change', updateMonitor);
    document.getElementById('gio_ket_thuc_tu_dong')?.addEventListener('change', updateMonitor);

    if (khungGioContainer) {
        khungGioContainer.addEventListener('change', function(e) {
            if (e.target.name === 'khung_gio[]') updateMonitor();
        });
    }

    if (mainForm) {
        mainForm.addEventListener('submit', function(e) {
            if (loaiInput && loaiInput.value === 'hang_loat') {
                const selectedRadio = document.querySelector('input[name="che_do_hang_loat"]:checked');
                if (selectedRadio && selectedRadio.value === 'thu_cong') {
                    const checkedBoxes = document.querySelectorAll('input[name="khung_gio[]"]:checked');
                    if (checkedBoxes.length === 0) {
                        e.preventDefault();
                        alert('Vui lòng chọn hoặc chèn ít nhất MỘT khung giờ chiếu trước khi khởi tạo!');
                        return false;
                    }
                }
            }
        });
    }
});
</script>
@endpush