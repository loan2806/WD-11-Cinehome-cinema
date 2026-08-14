@extends('layouts.admin')

@section('title', 'Giá theo phòng chiếu - CineHome')
@section('page-title', 'Giá theo phòng chiếu')

@section('content')

@php
    // Cùng bảng màu/icon đang dùng cho badge "Loại phòng" ở trang Quản lý
    // phòng chiếu, để hai trang nhất quán với nhau.
    $kieuTheoLoai = [
        '2d' => ['mau' => 'slate', 'icon' => 'fa-clapperboard', 'hex' => '#94a3b8'],
        '3d' => ['mau' => 'purple', 'icon' => 'fa-cube', 'hex' => '#c084fc'],
        'imax' => ['mau' => 'cyan', 'icon' => 'fa-expand', 'hex' => '#22d3ee'],
        '4dx' => ['mau' => 'pink', 'icon' => 'fa-bolt', 'hex' => '#f472b6'],
    ];
@endphp

<div class="admin-panel space-y-6">

    <div class="panel-header flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-white/5 pb-6">
        <div>
            <h5 class="text-3xl font-black text-white tracking-wide">
                Giá vé theo loại phòng chiếu
            </h5>
            <p class="text-sm text-gray-400 mt-1">
                Mỗi loại phòng một mức phụ thu duy nhất — áp dụng cho TẤT CẢ phòng cùng loại. Thêm phòng mới với loại nào sẽ tự nhận đúng mức phụ thu của loại đó.
            </p>
        </div>

        <a href="{{ route('admin.phong-chieus.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white hover:bg-white/10 transition duration-200">
            <i class="fa-solid fa-door-open"></i>
            Quản lý phòng chiếu
        </a>
    </div>

    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 text-xs text-amber-200 flex items-start gap-2.5">
        <i class="fa-solid fa-circle-info mt-0.5"></i>
        <span>Giá vé cuối cùng = Giá vé ngày thường/cuối tuần (cấu hình ở "Cài đặt hệ thống") + Phụ thu theo loại phòng bên dưới. Nếu các phòng cùng loại trước đây có phụ thu khác nhau, lưu ở đây sẽ đồng bộ tất cả về cùng một mức.</span>
    </div>

    <form method="POST" action="{{ route('admin.loai-phong-gia.update') }}" id="formGiaLoaiPhong">
        @csrf
        @method('PUT')

        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($danhSach as $loai)
                @php $kieu = $kieuTheoLoai[$loai['ma']] ?? ['mau' => 'gray', 'icon' => 'fa-door-open', 'hex' => '#9ca3af']; @endphp

                <div class="loai-phong-gia-card" style="--accent: {{ $kieu['hex'] }}">
                    <div class="loai-phong-gia-card__head">
                        <span class="loai-phong-gia-card__icon">
                            <i class="fa-solid {{ $kieu['icon'] }}"></i>
                        </span>
                        <div class="loai-phong-gia-card__title">
                            <h6>{{ $loai['ten'] }}</h6>
                            <span class="loai-phong-gia-card__count">
                                <i class="fa-solid fa-door-open"></i>
                                {{ $loai['so_phong'] > 0 ? $loai['so_phong'] . ' phòng' : 'Chưa có phòng nào' }}
                            </span>
                        </div>
                    </div>

                    <label class="loai-phong-gia-card__label" for="phu_thu_{{ $loai['ma'] }}">Phụ thu vé</label>
                    <div class="loai-phong-gia-card__input">
                        <span>+</span>
                        <input
                            type="number"
                            name="phu_thu[{{ $loai['ma'] }}]"
                            id="phu_thu_{{ $loai['ma'] }}"
                            data-preview-target="preview_{{ $loai['ma'] }}"
                            value="{{ old('phu_thu.' . $loai['ma'], (int) $loai['phu_thu']) }}"
                            min="0"
                            step="1000"
                            required
                        >
                        <span>đ</span>
                    </div>
                    @error('phu_thu.' . $loai['ma'])
                        <small class="loai-phong-gia-card__error">{{ $message }}</small>
                    @enderror

                    <div class="loai-phong-gia-card__preview">
                        <div>
                            <span>Ngày thường</span>
                            <strong id="preview_{{ $loai['ma'] }}_thuong">{{ number_format($giaNgayThuong + $loai['phu_thu']) }}đ</strong>
                        </div>
                        <div>
                            <span>Cuối tuần</span>
                            <strong id="preview_{{ $loai['ma'] }}_cuoituan">{{ number_format($giaCuoiTuan + $loai['phu_thu']) }}đ</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#e50914] to-[#ff3b46] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-500/10 hover:shadow-red-500/20 hover:scale-[1.02] active:scale-[0.98] transition duration-200">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu giá theo loại phòng chiếu
            </button>
        </div>
    </form>

</div>

<style>
.loai-phong-gia-card {
    position: relative;
    border-radius: 1.5rem;
    border: 1px solid rgba(255,255,255,.08);
    background: linear-gradient(160deg, rgba(255,255,255,.04), rgba(255,255,255,.01));
    padding: 1.5rem;
    overflow: hidden;
}
.loai-phong-gia-card::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: var(--accent);
}
.loai-phong-gia-card__head {
    display: flex;
    align-items: center;
    gap: .9rem;
    margin-bottom: 1.25rem;
}
.loai-phong-gia-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 14px;
    background: color-mix(in srgb, var(--accent) 16%, transparent);
    color: var(--accent);
    font-size: 1.1rem;
}
.loai-phong-gia-card__title h6 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 900;
    color: #fff;
    letter-spacing: .02em;
}
.loai-phong-gia-card__count {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    margin-top: .25rem;
    font-size: .72rem;
    font-weight: 700;
    color: #9ca3af;
}
.loai-phong-gia-card__label {
    display: block;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #9ca3af;
    margin-bottom: .5rem;
}
.loai-phong-gia-card__input {
    display: flex;
    align-items: center;
    gap: .5rem;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.1);
    background: #151515;
    padding: 0 1rem;
    height: 52px;
    transition: border-color .15s ease;
}
.loai-phong-gia-card__input:focus-within {
    border-color: var(--accent);
}
.loai-phong-gia-card__input > span:first-child {
    color: #6b7280;
    font-weight: 800;
}
.loai-phong-gia-card__input > span:last-child {
    color: #6b7280;
    font-weight: 700;
    font-size: .85rem;
}
.loai-phong-gia-card__input input {
    flex: 1;
    min-width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: var(--accent);
    font-weight: 900;
    font-size: 1.05rem;
    -moz-appearance: textfield;
}
.loai-phong-gia-card__input input::-webkit-outer-spin-button,
.loai-phong-gia-card__input input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.loai-phong-gia-card__error {
    display: block;
    margin-top: .4rem;
    color: #f87171;
    font-size: .75rem;
}
.loai-phong-gia-card__preview {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .75rem;
    margin-top: 1.1rem;
    padding-top: 1.1rem;
    border-top: 1px dashed rgba(255,255,255,.08);
}
.loai-phong-gia-card__preview > div {
    display: flex;
    flex-direction: column;
    gap: .2rem;
}
.loai-phong-gia-card__preview span {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6b7280;
}
.loai-phong-gia-card__preview strong {
    font-size: .95rem;
    font-weight: 900;
    color: #e5e7eb;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const giaNgayThuong = {{ (int) $giaNgayThuong }};
    const giaCuoiTuan = {{ (int) $giaCuoiTuan }};

    document.querySelectorAll('.loai-phong-gia-card__input input[data-preview-target]').forEach(function (input) {
        input.addEventListener('input', function () {
            const phuThu = Number(input.value) || 0;
            const key = input.dataset.previewTarget;
            const thuongEl = document.getElementById(key + '_thuong');
            const cuoiTuanEl = document.getElementById(key + '_cuoituan');
            if (thuongEl) thuongEl.textContent = (giaNgayThuong + phuThu).toLocaleString('vi-VN') + 'đ';
            if (cuoiTuanEl) cuoiTuanEl.textContent = (giaCuoiTuan + phuThu).toLocaleString('vi-VN') + 'đ';
        });
    });
});
</script>
@endpush

@endsection
