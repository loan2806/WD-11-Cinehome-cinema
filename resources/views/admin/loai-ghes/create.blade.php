@extends('layouts.admin')

@section('title', 'Thêm Loại Ghế - CineHome')
@section('page-title', 'Thêm Loại Ghế')

@section('content')

    <div class="admin-panel space-y-6">

        {{-- HEADER --}}
        <div class="panel-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-white/5 pb-6">
            <div>
                <h5 class="text-3xl font-black text-white tracking-wide">
                    Thêm loại ghế mới
                </h5>
                <p class="text-sm text-gray-400 mt-1">
                    Tạo phân hạng ghế mới với giá phụ thu và màu sắc đại diện riêng trên sơ đồ
                </p>
            </div>

            <a href="{{ route('admin.loai-ghes.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10 hover:scale-[1.02] active:scale-[0.98] duration-200">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại
            </a>
        </div>

        {{-- ERROR HANDLING --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-4">
                <ul class="list-inside list-disc text-sm text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM WITH SIDE PREVIEW --}}
        <form action="{{ route('admin.loai-ghes.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Cột bên trái: Các trường nhập liệu --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                        {{-- TÊN LOẠI GHẾ --}}
                        <div class="space-y-2">
                            <label for="ten_loai" class="text-sm font-bold text-gray-400 tracking-wide block">
                                Tên Loại Ghế <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="ten_loai" id="ten_loai" value="{{ old('ten_loai') }}"
                                placeholder="VD: Thường, VIP, Couple, Sweetbox" maxlength="50" required
                                class="w-full rounded-2xl border border-white/10 bg-[#1c1c1c] px-4 py-3 text-white outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition duration-200">
                        </div>

                        {{-- MỨC PHỤ THU --}}
                        <div class="space-y-2">
                            <label for="phu_thu" class="text-sm font-bold text-gray-400 tracking-wide block">
                                Phụ Thu (VNĐ) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="phu_thu" id="phu_thu" value="{{ old('phu_thu', 0) }}" min="0" step="1000" required
                                class="w-full rounded-2xl border border-white/10 bg-[#1c1c1c] px-4 py-3 text-white outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition duration-200">
                            <small class="text-xs text-gray-500 block">Số tiền cộng thêm vào giá vé cơ bản (0 = không phụ thu)</small>
                        </div>

                        {{-- MÀU SẮC ĐẠI DIỆN --}}
                        <div class="space-y-2">
                            <label for="mau_sac" class="text-sm font-bold text-gray-400 tracking-wide block">
                                Màu sắc hiển thị trên sơ đồ
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="mau_sac" id="mau_sac" value="{{ old('mau_sac', '#666666') }}"
                                    class="h-12 w-20 cursor-pointer rounded-xl border border-white/10 bg-transparent p-1 transition duration-200 hover:border-red-500/40">
                                <input type="text" id="mau_sac_hex" value="{{ old('mau_sac', '#666666') }}"
                                    placeholder="#666666" maxlength="7"
                                    class="flex-1 rounded-2xl border border-white/10 bg-[#1c1c1c] px-4 py-3 text-white outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition duration-200">
                            </div>
                            <small class="text-xs text-gray-500 block">Mã màu nhận diện loại ghế khi đặt vé hoặc quản lý phòng</small>
                        </div>

                    </div>

                    {{-- MÔ TẢ --}}
                    <div class="space-y-2">
                        <label for="mo_ta" class="text-sm font-bold text-gray-400 tracking-wide block">
                            Mô tả loại ghế
                        </label>
                        <textarea name="mo_ta" id="mo_ta" rows="4" placeholder="Nhập một vài thông tin mô tả về loại ghế này..."
                            class="w-full rounded-2xl border border-white/10 bg-[#1c1c1c] px-4 py-3 text-white outline-none focus:border-[#e50914] focus:ring-1 focus:ring-[#e50914] transition duration-200">{{ old('mo_ta') }}</textarea>
                    </div>

                    {{-- THUỘC TÍNH COUPLE --}}
                    <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-white/10 bg-[#151515] p-4 transition hover:border-red-500/20">
                        <input type="checkbox" name="la_couple" value="1" id="la_couple"
                            {{ old('la_couple') ? 'checked' : '' }}
                            class="mt-1 h-5 w-5 rounded border-white/20 bg-[#1c1c1c] text-[#e50914] focus:ring-[#e50914] transition">
                        <div>
                            <div class="text-sm font-bold text-white">
                                Đây là loại ghế ghép đôi (Couple / Sweetbox)
                            </div>
                            <div class="mt-1 text-xs text-gray-400 leading-relaxed">
                                Ghế thuộc loại này sẽ tự động được hiển thị liền kề và đặt theo cặp 2 người trên sơ đồ phòng chiếu.
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Cột bên phải: Xem trước hiển thị động (Live Preview) --}}
                <div class="lg:col-span-1">
                    <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-[#1c1c1c] to-[#121212] p-6 flex flex-col items-center justify-center relative overflow-hidden h-full min-h-[300px] shadow-xl">
                        <div class="absolute top-4 left-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Xem trước hiển thị (Live Preview)
                        </div>

                        <!-- Ghế Giả Lập Động -->
                        <div id="preview_seat_container" class="relative flex flex-col items-center mt-6 transition-all duration-300">
                            <!-- Quầng sáng Neon mờ phía sau -->
                            <div id="preview_glow" class="absolute h-16 w-16 rounded-full blur-3xl opacity-40 transition-all duration-300" style="background-color: #666666;"></div>
                            
                            <!-- Bố cục vector ghế -->
                            <div class="flex gap-1 items-end relative" id="preview_seat_layout">
                                <!-- Ghế 1 -->
                                <div class="relative flex flex-col items-center">
                                    <!-- Tựa lưng -->
                                    <div id="preview_backrest_1" class="w-16 h-12 rounded-t-2xl border-t border-x border-white/20 relative flex items-center justify-center transition-all duration-300" style="background-color: #666666;">
                                        <span id="preview_seat_name" class="text-[9px] font-black text-white/70 drop-shadow-md select-none tracking-wider">GHẾ</span>
                                    </div>
                                    <!-- Đệm ngồi -->
                                    <div id="preview_cushion_1" class="w-18 h-5 rounded-b-lg border-b border-x border-white/25 mt-0.5" style="background-color: #666666;"></div>
                                    <!-- Tay vịn trái -->
                                    <div id="preview_armrest_left" class="absolute -left-3 top-3.5 w-2 h-9 bg-white/10 rounded-sm border border-white/10 shadow-inner"></div>
                                    <!-- Tay vịn phải (bị ẩn khi là couple ghế đôi) -->
                                    <div id="preview_armrest_right" class="absolute -right-3 top-3.5 w-2 h-9 bg-white/10 rounded-sm border border-white/10 shadow-inner"></div>
                                </div>

                                <!-- Ghế 2 (Chỉ hiện khi là Couple) -->
                                <div id="preview_second_seat" class="relative flex flex-col items-center transition-all duration-300" style="display: none;">
                                    <!-- Tựa lưng 2 -->
                                    <div id="preview_backrest_2" class="w-16 h-12 rounded-t-2xl border-t border-x border-white/20 relative flex items-center justify-center transition-all duration-300" style="background-color: #666666;">
                                        <span class="text-[9px] font-black text-white/70 drop-shadow-md select-none tracking-wider">LOVE</span>
                                    </div>
                                    <!-- Đệm ngồi 2 -->
                                    <div id="preview_cushion_2" class="w-18 h-5 rounded-b-lg border-b border-x border-white/25 mt-0.5" style="background-color: #666666;"></div>
                                    <!-- Tay vịn phải của ghế đôi -->
                                    <div id="preview_armrest_couple_right" class="absolute -right-3 top-3.5 w-2 h-9 bg-white/10 rounded-sm border border-white/10 shadow-inner"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Thẻ thông tin nhanh dưới ghế --}}
                        <div class="mt-8 text-center space-y-1">
                            <h5 id="preview_title" class="text-base font-extrabold text-white">Chưa đặt tên</h5>
                            <p id="preview_price_tag" class="text-sm font-black text-[#ff3b46]">+0đ phụ thu</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-4 border-t border-white/5 pt-6">
                <a href="{{ route('admin.loai-ghes.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/5 px-6 py-3 font-medium text-white transition hover:bg-white/10 active:scale-95 duration-200">
                    Hủy bỏ
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#e50914] to-[#ff3b46] px-8 py-3 font-bold text-white shadow-lg shadow-red-500/10 hover:shadow-red-500/20 hover:scale-[1.02] active:scale-95 transition duration-200">
                    <i class="fa-solid fa-save"></i>
                    Lưu thông tin
                </button>
            </div>

        </form>

    </div>

    {{-- SCRIPTS FOR COLOR SYNC & LIVE PREVIEW --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tenLoaiInput = document.getElementById('ten_loai');
            const phuThuInput = document.getElementById('phu_thu');
            const colorPicker = document.getElementById('mau_sac');
            const colorHex = document.getElementById('mau_sac_hex');
            const laCoupleCheckbox = document.getElementById('la_couple');
            
            // Preview elements
            const previewTitle = document.getElementById('preview_title');
            const previewSeatName = document.getElementById('preview_seat_name');
            const previewPriceTag = document.getElementById('preview_price_tag');
            const previewBackrest1 = document.getElementById('preview_backrest_1');
            const previewCushion1 = document.getElementById('preview_cushion_1');
            const previewBackrest2 = document.getElementById('preview_backrest_2');
            const previewCushion2 = document.getElementById('preview_cushion_2');
            const previewGlow = document.getElementById('preview_glow');
            const previewSecondSeat = document.getElementById('preview_second_seat');
            const previewArmrestRight = document.getElementById('preview_armrest_right');

            // Hàm cập nhật Preview động
            function updatePreview() {
                const name = tenLoaiInput.value.trim() || 'GHẾ';
                const color = colorPicker.value;
                const price = parseInt(phuThuInput.value) || 0;
                const isCouple = laCoupleCheckbox.checked;

                // Cập nhật text & tiêu đề
                previewTitle.textContent = tenLoaiInput.value.trim() || 'Chưa đặt tên';
                previewSeatName.textContent = name.substring(0, 8).toUpperCase();
                
                // Cập nhật màu nền & quầng sáng phát sáng
                previewBackrest1.style.backgroundColor = color;
                previewCushion1.style.backgroundColor = color;
                previewGlow.style.backgroundColor = color;

                // Cập nhật giá tiền phụ thu
                previewPriceTag.textContent = `+${price.toLocaleString('vi-VN')}đ phụ thu`;

                // Cập nhật trạng thái Ghế Đôi (Couple)
                if (isCouple) {
                    previewSecondSeat.style.display = 'flex';
                    previewBackrest2.style.backgroundColor = color;
                    previewCushion2.style.backgroundColor = color;
                    previewArmrestRight.style.display = 'none'; // Ẩn tay vịn ở giữa để tạo khối ghế đôi liền
                } else {
                    previewSecondSeat.style.display = 'none';
                    previewArmrestRight.style.display = 'block'; // Hiện lại tay vịn phải như ghế đơn lẻ
                }
            }

            // Sự kiện thay đổi trên các ô nhập liệu
            tenLoaiInput.addEventListener('input', updatePreview);
            phuThuInput.addEventListener('input', updatePreview);
            laCoupleCheckbox.addEventListener('change', updatePreview);

            // Đồng bộ Picker -> Text Hex
            colorPicker.addEventListener('input', function() {
                colorHex.value = colorPicker.value.toUpperCase();
                updatePreview();
            });

            // Đồng bộ Text Hex -> Picker
            colorHex.addEventListener('input', function() {
                let val = colorHex.value.trim();
                if (val && !val.startsWith('#')) {
                    val = '#' + val;
                }
                
                if (/^#[0-9A-F]{6}$/i.test(val)) {
                    colorPicker.value = val;
                    updatePreview();
                }
            });

            // Chạy cập nhật lần đầu tiên
            updatePreview();
        });
    </script>

@endsection
