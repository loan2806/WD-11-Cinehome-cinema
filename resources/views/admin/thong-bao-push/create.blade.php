@extends('layouts.admin')

@section('page-title', 'Tạo thông báo đẩy mới')

@section('content')

<div class="admin-panel">

    {{-- HEADER --}}
    <div class="panel-header flex items-center gap-4">
        <a href="{{ route('admin.thong-bao-push.index') }}"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-gray-400 transition-all hover:bg-white/10 hover:text-white">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="flex-1 text-center">
            <h5 class="text-xl font-bold text-white">
                Tạo thông báo đẩy mới
            </h5>
            <p class="mt-1 text-sm text-gray-500">
                Nhập thông tin thông báo đẩy để gửi đến người dùng
            </p>
        </div>
        <div class="h-10 w-10 shrink-0"></div>
    </div>

    {{-- FORM CONTAINER --}}
    <div class="mt-5 rounded-2xl border border-white/10 bg-[#0f0f0f] p-6">

        {{-- ERROR ALERT --}}
        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 p-4">
                <div class="flex items-center gap-2 text-red-400">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <span class="font-bold">Vui lòng kiểm tra lại các trường sau:</span>
                </div>
                <ul class="mt-2 space-y-1 text-sm text-red-300">
                    @foreach ($errors->all() as $error)
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle text-[6px] text-red-500"></i>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM --}}
        <form method="POST" action="{{ route('admin.thong-bao-push.store') }}" class="space-y-5">
        @csrf

        {{-- Tiêu đề --}}
        <div>
            <label for="tieu_de" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-300">
                <i class="fa-solid fa-heading text-[#d99a32]"></i>
                Tiêu đề thông báo <span class="text-red-400">*</span>
            </label>
            <input type="text" name="tieu_de" id="tieu_de" value="{{ old('tieu_de') }}"
                class="h-12 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-white outline-none transition-all focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20"
                placeholder="Nhập tiêu đề thông báo...">
        </div>

        {{-- Nội dung --}}
        <div>
            <label for="noi_dung" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-300">
                <i class="fa-solid fa-align-left text-[#d99a32]"></i>
                Nội dung thông báo <span class="text-red-400">*</span>
            </label>
            <textarea name="noi_dung" id="noi_dung" rows="5"
                class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition-all focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20 resize-none"
                placeholder="Nhập nội dung thông báo...">{{ old('noi_dung') }}</textarea>
        </div>

        {{-- 2 Cột: Loại & Đối tượng --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="loai" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-300">
                    <i class="fa-solid fa-tag text-[#d99a32]"></i>
                    Loại thông báo <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <select name="loai" id="loai"
                        class="h-12 w-full appearance-none rounded-xl border border-white/10 bg-[#151515] px-4 pr-10 text-white outline-none transition-all focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20">
                        @foreach ($loaiOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('loai', 'info') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                </div>
            </div>

            <div>
                <label for="doi_tuong_nhan" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-300">
                    <i class="fa-solid fa-users text-[#d99a32]"></i>
                    Đối tượng nhận <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <select name="doi_tuong_nhan" id="doi_tuong_nhan"
                        class="h-12 w-full appearance-none rounded-xl border border-white/10 bg-[#151515] px-4 pr-10 text-white outline-none transition-all focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20">
                        @foreach ($doiTuongOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('doi_tuong_nhan', 'all') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                </div>
            </div>
        </div>

        {{-- Người dùng cụ thể (ẩn/hiện theo đối tượng) --}}
        <div id="nguoi_dung_cu_the_wrapper" class="{{ old('doi_tuong_nhan', 'all') === 'nguoi_dung_cu_the' ? '' : 'hidden' }}">
            <label for="nguoi_dung_cu_the" class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-300">
                <i class="fa-solid fa-user text-[#d99a32]"></i>
                Chọn người dùng <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <select name="nguoi_dung_cu_the" id="nguoi_dung_cu_the"
                    class="h-12 w-full appearance-none rounded-xl border border-white/10 bg-[#151515] px-4 pr-10 text-white outline-none transition-all focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20">
                    <option value="">-- Chọn người dùng --</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Vui lòng chọn đối tượng nhận trước để tải danh sách người dùng.
            </p>
        </div>

        {{-- Preview Card --}}
        <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-[#1a1a1a] to-[#151515] p-5">
            <h6 class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-400">
                <i class="fa-solid fa-mobile-screen text-[#d99a32]"></i>
                Xem trước thông báo
            </h6>
            <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-[#1e1e1e] p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#8a4a21] to-[#d99a32]">
                    <i class="fa-solid fa-bell text-white text-sm"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-white">CineHome</span>
                        <span class="text-xs text-gray-500">vừa xong</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-300 preview-tieu-de">Tiêu đề thông báo của bạn</p>
                    <p class="mt-0.5 text-xs text-gray-500 preview-noi-dung">Nội dung thông báo sẽ hiển thị ở đây...</p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all hover:scale-[1.02] hover:shadow-[#d99a32]/30">
                <i class="fa-solid fa-paper-plane"></i>
                Tạo và gửi thông báo
            </button>
            <a href="{{ route('admin.thong-bao-push.index') }}"
                class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-semibold text-gray-400 transition-all hover:bg-white/10 hover:text-white">
                Hủy bỏ
            </a>
        </div>
    </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    // Toggle người dùng cụ thể
    const doiTuongNhanSelect = document.getElementById('doi_tuong_nhan');
    const nguoiDungCuTheWrapper = document.getElementById('nguoi_dung_cu_the_wrapper');
    const nguoiDungCuTheSelect = document.getElementById('nguoi_dung_cu_the');

    doiTuongNhanSelect.addEventListener('change', function () {
        if (this.value === 'nguoi_dung_cu_the') {
            nguoiDungCuTheWrapper.classList.remove('hidden');
            // Fetch users
            fetch(`{{ route('admin.thong-bao-push.users-by-role') }}?role=${this.value}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    nguoiDungCuTheSelect.innerHTML = '<option value="">-- Chọn người dùng --</option>';
                    data.forEach((user) => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.ho_ten} (${user.email})`;
                        nguoiDungCuTheSelect.appendChild(option);
                    });
                });
        } else {
            nguoiDungCuTheWrapper.classList.add('hidden');
        }
    });

    // Preview khi nhập
    const tieuDeInput = document.getElementById('tieu_de');
    const noiDungInput = document.getElementById('noi_dung');
    const previewTieuDe = document.querySelector('.preview-tieu-de');
    const previewNoiDung = document.querySelector('.preview-noi-dung');

    tieuDeInput.addEventListener('input', function() {
        previewTieuDe.textContent = this.value || 'Tiêu đề thông báo của bạn';
    });

    noiDungInput.addEventListener('input', function() {
        previewNoiDung.textContent = this.value || 'Nội dung thông báo sẽ hiển thị ở đây...';
    });
</script>
@endpush
