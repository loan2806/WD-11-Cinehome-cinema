@extends('layouts.admin')

@section('title', 'Tặng & thu hồi điểm')
@section('page-title', 'Tặng & thu hồi điểm')
@section('page-subtitle', 'Quản lý điểm cho toàn bộ thành viên')

@section('content')

<style>
    .all-point-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 28px;
        margin-bottom: 22px;
        border-radius: 22px;
        background: linear-gradient(135deg, #171b24, #202633);
        border: 1px solid rgba(255, 255, 255, .07);
    }

    .all-point-hero-copy {
        flex: 1;
    }

    .all-point-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #f5b942;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .all-point-hero h2 {
        margin: 0 0 8px;
        color: #fff;
        font-size: 26px;
        font-weight: 900;
    }

    .all-point-hero p {
        margin: 0;
        color: #9ca3af;
    }

    .all-point-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 16px;
        border-radius: 12px;
        color: #fff;
        background: rgba(255, 255, 255, .07);
        text-decoration: none;
        font-weight: 800;
        white-space: nowrap;
    }

    .all-point-back:hover {
        color: #fff;
        background: rgba(255, 255, 255, .12);
    }

    .all-point-count {
        margin-bottom: 22px;
        padding: 18px 22px;
        border-radius: 18px;
        background: #171b24;
        border: 1px solid rgba(255, 255, 255, .07);
        color: #cbd5e1;
    }

    .all-point-count strong {
        color: #f5b942;
        font-size: 20px;
    }

    .all-point-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
    }

    .all-point-card {
        padding: 26px;
        border-radius: 22px;
        background: #171b24;
        border: 1px solid rgba(255, 255, 255, .07);
    }

    .all-point-card-head {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }

    .all-point-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        font-size: 20px;
    }

    .all-point-icon.gift {
        color: #22c55e;
        background: rgba(34, 197, 94, .12);
    }

    .all-point-icon.withdraw {
        color: #ef4444;
        background: rgba(239, 68, 68, .12);
    }

    .all-point-card-head h3 {
        margin: 0;
        color: #fff;
        font-size: 19px;
        font-weight: 900;
    }

    .all-point-card-head p {
        margin: 3px 0 0;
        color: #8b93a1;
        font-size: 13px;
    }

    .all-point-field {
        margin-bottom: 17px;
    }

    .all-point-field label {
        display: block;
        margin-bottom: 8px;
        color: #e5e7eb;
        font-size: 14px;
        font-weight: 800;
    }

    .all-point-field input,
    .all-point-field select,
    .all-point-field textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 12px 14px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 12px;
        outline: none;
        background: #10141b;
        color: #fff;
        font-size: 14px;
    }

    .all-point-field input:focus,
    .all-point-field select:focus,
    .all-point-field textarea:focus {
        border-color: #f5b942;
    }

    .all-point-field select option {
        background: #171b24;
        color: #fff;
    }

    .all-point-field textarea {
        min-height: 100px;
        resize: vertical;
    }

    .all-point-help {
        margin-top: 7px;
        color: #7f8795;
        font-size: 12px;
        line-height: 1.5;
    }

    .all-point-warning {
        display: flex;
        gap: 10px;
        padding: 13px 14px;
        margin-bottom: 18px;
        border-radius: 12px;
        color: #fbbf24;
        background: rgba(245, 158, 11, .08);
        border: 1px solid rgba(245, 158, 11, .15);
        font-size: 13px;
        line-height: 1.5;
    }

    .all-point-submit {
        width: 100%;
        min-height: 48px;
        border: 0;
        border-radius: 13px;
        color: #fff;
        font-weight: 900;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .all-point-submit.gift {
        background: linear-gradient(135deg, #16a34a, #22c55e);
    }

    .all-point-submit.withdraw {
        background: linear-gradient(135deg, #dc2626, #ef4444);
    }

    @media (max-width: 900px) {
        .all-point-grid {
            grid-template-columns: 1fr;
        }

        .all-point-hero {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>


{{-- HEADER --}}
<section class="all-point-hero">

    <div class="all-point-hero-copy">

        <span class="all-point-kicker">
            <i class="fa-solid fa-star"></i>
            Quản lý điểm thành viên
        </span>

        <h2>Tặng & thu hồi điểm</h2>

        <p>
            Thực hiện cộng hoặc thu hồi điểm cho toàn bộ thành viên
            trong một lần.
        </p>

    </div>

    <a href="{{ route('admin.thanh-vien.index') }}"
        class="all-point-back">

        <i class="fa-solid fa-arrow-left"></i>

        Danh sách thành viên

    </a>

</section>


{{-- SỐ LƯỢNG --}}
<section class="all-point-count">

    <i class="fa-solid fa-users"></i>

    Hiện có

    <strong>
        {{ number_format($soLuongThanhVien) }}
    </strong>

    thành viên trong hệ thống.

</section>


{{-- 2 FORM --}}
<div class="all-point-grid">


    {{-- ========================= --}}
    {{-- TẶNG ĐIỂM --}}
    {{-- ========================= --}}

    <section class="all-point-card">

        <div class="all-point-card-head">

            <span class="all-point-icon gift">
                <i class="fa-solid fa-gift"></i>
            </span>

            <div>
                <h3>Tặng điểm tất cả</h3>

                <p>
                    Cộng điểm cho toàn bộ thành viên
                </p>
            </div>

        </div>


        <div class="all-point-warning">

            <i class="fa-solid fa-triangle-exclamation"></i>

            <span>
                Điểm sẽ được cộng cho
                <strong>tất cả thành viên</strong>.
            </span>

        </div>


        <form
            method="POST"
            action="{{ route('admin.thanh-vien.xu-ly-diem-hang-loat') }}"
            onsubmit="return confirm('Bạn có chắc chắn muốn tặng điểm cho TẤT CẢ thành viên không?')">

            @csrf

            <input type="hidden" name="loai" value="tang">
            <div class="all-point-field">
                <label>Hạng thành viên được tặng</label>

                <div class="all-point-rank-dropdown" data-point-dropdown>

                    <input
                        type="hidden"
                        name="hang_thanh_vien"
                        value="{{ old('loai') === 'tang' ? old('hang_thanh_vien') : '' }}"
                        data-point-dropdown-value>

                    <button
                        type="button"
                        class="all-point-rank-trigger"
                        data-point-dropdown-trigger>

                        <span class="all-point-rank-current">

                            @if(old('loai') === 'tang' && old('hang_thanh_vien') === 'member')
                            <i class="fa-solid fa-user member-icon"></i>
                            <span>Member</span>

                            @elseif(old('loai') === 'tang' && old('hang_thanh_vien') === 'silver')
                            <i class="fa-solid fa-medal silver-icon"></i>
                            <span>Silver</span>

                            @elseif(old('loai') === 'tang' && old('hang_thanh_vien') === 'gold')
                            <i class="fa-solid fa-crown gold-icon"></i>
                            <span>Gold</span>

                            @elseif(old('loai') === 'tang' && old('hang_thanh_vien') === 'platinum')
                            <i class="fa-solid fa-gem platinum-icon"></i>
                            <span>Platinum</span>

                            @else
                            <i class="fa-solid fa-layer-group all-icon"></i>
                            <span>-- Chọn hạng --</span>
                            @endif

                        </span>

                        <i class="fa-solid fa-chevron-down all-point-rank-arrow"></i>
                    </button>

                    <div class="all-point-rank-menu" data-point-dropdown-menu>

                        <button type="button"
                            class="all-point-rank-option {{ !old('hang_thanh_vien') ? 'is-selected' : '' }}"
                            data-value="">
                            <i class="fa-solid fa-layer-group all-icon"></i>
                            <span>-- Chọn hạng --</span>
                        </button>

                        <button type="button"
                            class="all-point-rank-option {{ old('hang_thanh_vien') === 'member' ? 'is-selected' : '' }}"
                            data-value="member">
                            <i class="fa-solid fa-user member-icon"></i>
                            <span>Member</span>
                        </button>

                        <button type="button"
                            class="all-point-rank-option {{ old('hang_thanh_vien') === 'silver' ? 'is-selected' : '' }}"
                            data-value="silver">
                            <i class="fa-solid fa-medal silver-icon"></i>
                            <span>Silver</span>
                        </button>

                        <button type="button"
                            class="all-point-rank-option {{ old('hang_thanh_vien') === 'gold' ? 'is-selected' : '' }}"
                            data-value="gold">
                            <i class="fa-solid fa-crown gold-icon"></i>
                            <span>Gold</span>
                        </button>

                        <button type="button"
                            class="all-point-rank-option {{ old('hang_thanh_vien') === 'platinum' ? 'is-selected' : '' }}"
                            data-value="platinum">
                            <i class="fa-solid fa-gem platinum-icon"></i>
                            <span>Platinum</span>
                        </button>

                    </div>
                </div>

                <div class="all-point-help">
                    Chỉ thành viên thuộc hạng được chọn mới nhận điểm và thông báo.
                </div>
            </div>

            {{-- SỐ ĐIỂM --}}
            <div class="all-point-field">

                <label>
                    Số điểm tặng
                </label>

                <input
                    type="number"
                    name="so_diem"
                    min="1"
                    max="10000"
                    value="{{ old('loai') === 'tang' ? old('so_diem') : '' }}"
                    placeholder="Ví dụ: 100">
                @if ($errors->has('so_diem') && old('loai') === 'tang')
                <small style="color: #dc2626; display: block; margin-top: 5px;">
                    {{ $errors->first('so_diem') }}
                </small>
                @endif

            </div>


            <div class="all-point-field">
                <label>Tính vào hạng thành viên</label>

                <div class="all-point-rank-dropdown" data-point-dropdown>

                    <input
                        type="hidden"
                        name="tinh_vao_hang"
                        value="{{ old('loai') === 'tang' ? old('tinh_vao_hang', '1') : '1' }}"
                        data-point-dropdown-value>

                    <button
                        type="button"
                        class="all-point-rank-trigger"
                        data-point-dropdown-trigger>

                        <span class="all-point-rank-current">

                            @if(old('loai') === 'tang' && old('tinh_vao_hang') === '0')

                            <i class="fa-solid fa-ban point-no-icon"></i>
                            <span class="point-no-text">
                                Không - Không tính vào hạng
                            </span>

                            @else

                            <i class="fa-solid fa-ranking-star point-yes-icon"></i>
                            <span class="point-yes-text">
                                Có - Tính vào hạng
                            </span>

                            @endif

                        </span>

                        <i class="fa-solid fa-chevron-down all-point-rank-arrow"></i>
                    </button>


                    <div class="all-point-rank-menu" data-point-dropdown-menu>

                        {{-- CÓ --}}
                        <button
                            type="button"
                            class="all-point-rank-option {{ old('tinh_vao_hang', '1') === '1' ? 'is-selected' : '' }}"
                            data-value="1">

                            <i class="fa-solid fa-ranking-star point-yes-icon"></i>

                            <span class="point-yes-text">
                                Có - Tính vào hạng
                            </span>

                        </button>


                        {{-- KHÔNG --}}
                        <button
                            type="button"
                            class="all-point-rank-option {{ old('tinh_vao_hang') === '0' ? 'is-selected' : '' }}"
                            data-value="0">

                            <i class="fa-solid fa-ban point-no-icon"></i>

                            <span class="point-no-text">
                                Không - Không tính vào hạng
                            </span>

                        </button>

                    </div>
                </div>

                <div class="all-point-help">
                    Nếu chọn "Có", điểm sẽ được cộng vào tổng tích lũy
                    và có thể làm thay đổi hạng.
                </div>
            </div>


            {{-- NỘI DUNG --}}
            <div class="all-point-field">

                <label>
                    Nội dung
                </label>

                <textarea
                    name="noi_dung"
                    maxlength="255"
                    placeholder="Ví dụ: Tặng 100 điểm nhân dịp sinh nhật CineHome...">{{ old('loai') === 'tang' ? old('noi_dung') : '' }}</textarea>
                @if ($errors->has('noi_dung') && old('loai') === 'tang')
                <small style="color: #dc2626; display: block; margin-top: 5px;">
                    {{ $errors->first('noi_dung') }}
                </small>
                @endif
            </div>


            <button
                type="submit"
                class="all-point-submit gift">

                <i class="fa-solid fa-gift"></i>

                Tặng điểm cho tất cả

            </button>

        </form>

    </section>


    {{-- ========================= --}}
    {{-- THU HỒI ĐIỂM --}}
    {{-- ========================= --}}

    <section class="all-point-card">

        <div class="all-point-card-head">

            <span class="all-point-icon withdraw">
                <i class="fa-solid fa-arrow-rotate-left"></i>
            </span>

            <div>

                <h3>Thu hồi điểm tất cả</h3>

                <p>
                    Trừ điểm của toàn bộ thành viên
                </p>

            </div>

        </div>


        <div class="all-point-warning">

            <i class="fa-solid fa-triangle-exclamation"></i>

            <span>
                Điểm sẽ được thu hồi khỏi
                <strong>tất cả thành viên</strong>.
                Không để điểm bị âm.
            </span>

        </div>


        <form
            method="POST"
            action="{{ route('admin.thanh-vien.xu-ly-diem-hang-loat') }}"
            onsubmit="return confirm('Bạn có chắc chắn muốn THU HỒI điểm của TẤT CẢ thành viên không?')">

            @csrf

            <input type="hidden" name="loai" value="thu_hoi">

            <div class="all-point-field">
                <label>Hạng thành viên được thu hồi</label>

                <div class="all-point-rank-dropdown" data-point-dropdown>

                    <input
                        type="hidden"
                        name="hang_thanh_vien"
                        value="{{ old('loai') === 'tang' ? old('hang_thanh_vien') : '' }}"
                        data-point-dropdown-value>

                    <button
                        type="button"
                        class="all-point-rank-trigger"
                        data-point-dropdown-trigger>

                        <span class="all-point-rank-current">

                            @if(old('loai') === 'tang' && old('hang_thanh_vien') === 'member')
                            <i class="fa-solid fa-user member-icon"></i>
                            <span>Member</span>

                            @elseif(old('loai') === 'tang' && old('hang_thanh_vien') === 'silver')
                            <i class="fa-solid fa-medal silver-icon"></i>
                            <span>Silver</span>

                            @elseif(old('loai') === 'tang' && old('hang_thanh_vien') === 'gold')
                            <i class="fa-solid fa-crown gold-icon"></i>
                            <span>Gold</span>

                            @elseif(old('loai') === 'tang' && old('hang_thanh_vien') === 'platinum')
                            <i class="fa-solid fa-gem platinum-icon"></i>
                            <span>Platinum</span>

                            @else
                            <i class="fa-solid fa-layer-group all-icon"></i>
                            <span>-- Chọn hạng --</span>
                            @endif

                        </span>

                        <i class="fa-solid fa-chevron-down all-point-rank-arrow"></i>
                    </button>

                    <div class="all-point-rank-menu" data-point-dropdown-menu>

                        <button type="button"
                            class="all-point-rank-option {{ !old('hang_thanh_vien') ? 'is-selected' : '' }}"
                            data-value="">
                            <i class="fa-solid fa-layer-group all-icon"></i>
                            <span>-- Chọn hạng --</span>
                        </button>

                        <button type="button"
                            class="all-point-rank-option {{ old('hang_thanh_vien') === 'member' ? 'is-selected' : '' }}"
                            data-value="member">
                            <i class="fa-solid fa-user member-icon"></i>
                            <span>Member</span>
                        </button>

                        <button type="button"
                            class="all-point-rank-option {{ old('hang_thanh_vien') === 'silver' ? 'is-selected' : '' }}"
                            data-value="silver">
                            <i class="fa-solid fa-medal silver-icon"></i>
                            <span>Silver</span>
                        </button>

                        <button type="button"
                            class="all-point-rank-option {{ old('hang_thanh_vien') === 'gold' ? 'is-selected' : '' }}"
                            data-value="gold">
                            <i class="fa-solid fa-crown gold-icon"></i>
                            <span>Gold</span>
                        </button>

                        <button type="button"
                            class="all-point-rank-option {{ old('hang_thanh_vien') === 'platinum' ? 'is-selected' : '' }}"
                            data-value="platinum">
                            <i class="fa-solid fa-gem platinum-icon"></i>
                            <span>Platinum</span>
                        </button>

                    </div>
                </div>

                <div class="all-point-help">
                    Chỉ thành viên thuộc hạng được chọn mới nhận điểm và thông báo.
                </div>
            </div>

            {{-- SỐ ĐIỂM --}}
            <div class="all-point-field">

                <label>
                    Số điểm thu hồi
                </label>

                <input
                    type="number"
                    name="so_diem"
                    value="{{ old('loai') === 'thu_hoi' ? old('so_diem') : '' }}"
                    placeholder="Ví dụ: 50">

                @if ($errors->has('so_diem') && old('loai') === 'thu_hoi')
                <small style="color: #dc2626; display: block; margin-top: 5px;">
                    {{ $errors->first('so_diem') }}
                </small>
                @endif
            </div>


            {{-- NỘI DUNG --}}
            <div class="all-point-field">

                <label>
                    Nội dung
                </label>

                <textarea
                    name="noi_dung"
                    maxlength="255"
                    placeholder="Ví dụ: Thu hồi điểm tặng nhầm...">{{ old('loai') === 'thu_hoi' ? old('noi_dung') : '' }}</textarea>
                @if ($errors->has('noi_dung') && old('loai') === 'thu_hoi')
                <small style="color: #dc2626; display: block; margin-top: 5px;">
                    {{ $errors->first('noi_dung') }}
                </small>
                @endif

            </div>


            <button
                type="submit"
                class="all-point-submit withdraw">

                <i class="fa-solid fa-arrow-rotate-left"></i>

                Thu hồi điểm tất cả

            </button>

        </form>

    </section>

</div>

@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('[data-point-dropdown]').forEach(function(dropdown) {

            const trigger = dropdown.querySelector('[data-point-dropdown-trigger]');
            const menu = dropdown.querySelector('[data-point-dropdown-menu]');
            const hiddenInput = dropdown.querySelector('[data-point-dropdown-value]');

            if (!trigger || !menu || !hiddenInput) return;


            function openDropdown() {

                document
                    .querySelectorAll('[data-point-dropdown].is-open')
                    .forEach(function(other) {

                        if (other !== dropdown) {
                            other.classList.remove('is-open');
                        }

                    });

                dropdown.classList.add('is-open');
            }


            function closeDropdown() {
                dropdown.classList.remove('is-open');
            }


            trigger.addEventListener('click', function(e) {

                e.stopPropagation();

                if (dropdown.classList.contains('is-open')) {
                    closeDropdown();
                } else {
                    openDropdown();
                }

            });


            menu.querySelectorAll('[data-value]').forEach(function(option) {

                option.addEventListener('click', function(e) {

                    e.stopPropagation();

                    const value = this.dataset.value;

                    const icon = this.querySelector('i') ?
                        this.querySelector('i').outerHTML :
                        '';

                    const text = this.querySelector('span') ?
                        this.querySelector('span').textContent.trim() :
                        '';

                    hiddenInput.value = value;


                    dropdown.querySelector(
                            '.all-point-rank-current'
                        ).innerHTML =
                        icon +
                        '<span>' +
                        text +
                        '</span>';


                    menu.querySelectorAll(
                        '.all-point-rank-option'
                    ).forEach(function(item) {

                        item.classList.remove('is-selected');

                    });


                    this.classList.add('is-selected');

                    closeDropdown();

                });

            });


            document.addEventListener('click', function(e) {

                if (!dropdown.contains(e.target)) {
                    closeDropdown();
                }

            });

        });

    });
</script>