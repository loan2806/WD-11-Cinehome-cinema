@extends('layouts.admin')

@section('page-title', 'Thêm Ghế Ngồi')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex items-center justify-between">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Thêm ghế ngồi mới
                </h5>

                <small class="text-gray-400">
                    Chọn phòng → chọn hàng → nhập thông tin ghế. Có thể tạo nhiều ghế liên tiếp cho cùng một hàng.
                </small>

            </div>

            <a href="{{ route('admin.ghe-ngois.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                <i class="fa-solid fa-arrow-left"></i>

                Quay lại

            </a>

        </div>

        @if (session('success'))
            <div class="mt-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-300">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-4">
                <ul class="list-inside list-disc text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.ghe-ngois.store') }}" method="POST" class="mt-6 space-y-6">

            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Phòng Chiếu <span class="text-red-400">*</span>
                    </label>

                    <select name="phong_chieu_id" id="phong_chieu_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        <option value="">-- Chọn Phòng --</option>

                        @foreach ($phongChieus as $phong)
                            <option value="{{ $phong->id }}"
                                {{ old('phong_chieu_id', $phongChieuId) == $phong->id ? 'selected' : '' }}>
                                {{ $phong->ten_phong }} - {{ $phong->rapChieuPhim->ten_rap ?? '' }}
                            </option>
                        @endforeach

                    </select>

                    <small class="text-xs text-gray-500">Đổi phòng sẽ tự động tải lại danh sách hàng ghế.</small>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Hàng Ghế <span class="text-red-400">*</span>
                    </label>

                    <select name="hang_ghe_id" id="hang_ghe_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        <option value="">-- Chọn Hàng --</option>

                        @foreach ($hangGhes as $hang)
                            <option value="{{ $hang->id }}"
                                data-la-couple="{{ $hang->la_hang_couple ? 1 : 0 }}"
                                {{ old('hang_ghe_id', $hangGheId) == $hang->id ? 'selected' : '' }}>
                                {{ $hang->ten_hang }}{{ $hang->la_hang_couple ? ' (Couple)' : '' }}
                            </option>
                        @endforeach

                    </select>

                    <small class="text-xs text-gray-500">
                        Chưa có hàng nào?
                        <a href="{{ route('admin.hang-ghes.create', ['phong_chieu_id' => old('phong_chieu_id', $phongChieuId)]) }}"
                            class="text-[#d99a32] hover:underline">+ Tạo hàng ghế mới</a>
                    </small>

                    <div id="couple-hint" class="hidden rounded-xl border border-pink-500/30 bg-pink-500/10 px-3 py-2 text-xs text-pink-200">
                        <i class="fa-solid fa-heart mr-1"></i>
                        Hàng này đang bật chế độ Couple. Hệ thống sẽ tự ghép 2 ghế liền kề thành 1 cặp.
                    </div>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Loại Ghế <span class="text-red-400">*</span>
                    </label>

                    <select name="loai_ghe_id" id="loai_ghe_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        <option value="">-- Chọn Loại --</option>

                        @foreach ($loaiGhes as $loai)
                            <option value="{{ $loai->id }}"
                                {{ old('loai_ghe_id') == $loai->id ? 'selected' : '' }}>
                                {{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Trạng Thái <span class="text-red-400">*</span>
                    </label>

                    <select name="trang_thai" id="trang_thai" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        <option value="hoat_dong"
                            {{ old('trang_thai', 'hoat_dong') == 'hoat_dong' ? 'selected' : '' }}>
                            Hoạt động
                        </option>

                        <option value="bao_tri"
                            {{ old('trang_thai') == 'bao_tri' ? 'selected' : '' }}>
                            Bảo trì
                        </option>

                    </select>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Mã Ghế <span class="text-red-400">*</span>
                    </label>

                    <input type="text" name="ma_ghe" id="ma_ghe"
                        value="{{ old('ma_ghe', $goiYMaGhe) }}"
                        placeholder="VD: A1, B2" maxlength="10" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                    <small class="text-xs text-gray-500">Hệ thống sẽ gợi ý mã ghế = Tên hàng + số cột kế tiếp.</small>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Cột <span class="text-red-400">*</span>
                    </label>

                    <input type="number" name="cot" id="cot"
                        value="{{ old('cot', $goiYCot) }}" min="1" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                    <small class="text-xs text-gray-500">Số thứ tự cột trong hàng (mỗi hàng phải duy nhất).</small>

                </div>

            </div>

            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-4">
                <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-300">
                    <input type="checkbox" name="tiep_tuc_tao" value="1"
                        {{ old('tiep_tuc_tao', request('tiep_tuc_tao')) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-white/20 bg-[#151515] text-[#d99a32] focus:ring-[#d99a32]">
                    <span>Tiếp tục thêm ghế cho cùng hàng này (mã ghế & cột tự tăng)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-4 border-t border-white/10 pt-6">

                <a href="{{ route('admin.ghe-ngois.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/5 px-6 py-3 font-medium text-white transition hover:bg-white/10">

                    Hủy

                </a>

                <button type="submit"
                    class="rounded-2xl bg-gradient-to-r from-[#6b3a1e] via-[#a66a2b] to-[#d9a441] px-8 py-3 font-semibold text-white shadow-lg shadow-amber-900/30">

                    <i class="fa-solid fa-save mr-2"></i>

                    Lưu

                </button>

            </div>

        </form>

    </div>

@endsection

@prepend('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const phongSelect = document.getElementById('phong_chieu_id');
    const hangSelect = document.getElementById('hang_ghe_id');
    const cotInput = document.getElementById('cot');
    const maGheInput = document.getElementById('ma_ghe');
    const baseUrl = @json(route('phong-chieus.hang-ghes.index', ['phong_chieu' => 0]));
    const urlTemplate = baseUrl.replace(/\/0$/, '/');

    const initialPhong = @json(old('phong_chieu_id', $phongChieuId));
    const initialHang = @json(old('hang_ghe_id', $hangGheId));
    const initialCot = @json(old('cot', $goiYCot));
    const initialMaGhe = @json(old('ma_ghe', $goiYMaGhe));
    const userTouchedCot = @json(old()->has('cot'));
    const userTouchedMaGhe = @json(old()->has('ma_ghe'));

    const coupleHint = document.getElementById('couple-hint');

    function updateCoupleHint() {
        const opt = hangSelect.querySelector('option[value="' + hangSelect.value + '"]');
        if (opt && opt.dataset.laCouple === '1') {
            coupleHint.classList.remove('hidden');
        } else {
            coupleHint.classList.add('hidden');
        }
    }

    async function loadHangs(phongId, preselect) {
        hangSelect.innerHTML = '<option value="">-- Chọn Hàng --</option>';
        if (!phongId) return;

        try {
            const res = await fetch(urlTemplate + phongId, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            (json.data || []).forEach(h => {
                const opt = document.createElement('option');
                opt.value = h.id;
                opt.textContent = h.ten_hang + ' (' + h.so_ghe + ' ghế)' + (h.la_hang_couple ? ' - Couple' : '');
                opt.dataset.laCouple = h.la_hang_couple ? '1' : '0';
                if (String(preselect) === String(h.id)) opt.selected = true;
                hangSelect.appendChild(opt);
            });
            updateCoupleHint();
        } catch (e) {
            console.error('Không tải được danh sách hàng:', e);
        }
    }

    async function suggestNextSeat(hangId) {
        if (!hangId) return;
        const selectedOption = hangSelect.querySelector('option[value="' + hangId + '"]');
        const tenHang = selectedOption ? selectedOption.textContent.split(' ')[0] : '';
        const res = await fetch(urlTemplate + phongSelect.value);
        const json = await res.json();
        const hang = (json.data || []).find(h => String(h.id) === String(hangId));
        if (!hang) return;
        const nextCot = (hang.so_ghe || 0) + 1;
        if (!userTouchedCot) cotInput.value = nextCot;
        if (!userTouchedMaGhe) maGheInput.value = tenHang + nextCot;
    }

    phongSelect.addEventListener('change', function () {
        loadHangs(this.value, null);
        cotInput.value = 1;
        maGheInput.value = '';
        coupleHint.classList.add('hidden');
    });

    hangSelect.addEventListener('change', function () {
        suggestNextSeat(this.value);
        updateCoupleHint();
    });

    if (initialPhong) {
        loadHangs(initialPhong, initialHang).then(() => {
            updateCoupleHint();
            if (initialHang && !userTouchedCot) {
                suggestNextSeat(initialHang);
            }
        });
    }
});
</script>
@endprepend
