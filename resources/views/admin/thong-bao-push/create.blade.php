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
                <label class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-300">
                    <i class="fa-solid fa-tag text-[#d99a32]"></i>
                    Loại thông báo <span class="text-red-400">*</span>
                </label>

                {{-- CUSTOM DROPDOWN LOẠI THÔNG BÁO --}}
                <div class="relative" id="loaiDropdownWrapper">
                    <input type="hidden" name="loai" id="loai" value="{{ old('loai', 'info') }}">

                    <button type="button" id="loaiDropdownBtn"
                        class="dropdown-trigger flex h-12 w-full items-center justify-between rounded-xl border border-white/10 bg-[#151515] px-4 text-left transition-all focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <span id="loaiIcon" class="dropdown-icon flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/20 text-blue-400">
                                <i class="fa-solid fa-circle-info"></i>
                            </span>
                            <span id="loaiLabel" class="dropdown-label text-white font-medium">Thông tin</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-gray-500 transition-transform duration-300 dropdown-arrow"></i>
                    </button>

                    <div id="loaiDropdownMenu"
                        class="dropdown-menu absolute left-0 right-0 top-[calc(100%+8px)] z-50 hidden max-h-64 overflow-y-auto rounded-xl border border-white/10 bg-[#1a1a1a] p-1.5 shadow-2xl shadow-black/50 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                        <div class="mb-2 px-3 py-2 border-b border-white/5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Chọn loại thông báo</span>
                        </div>
                        @foreach ($loaiOptions as $value => $label)
                            <button type="button"
                                class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-all hover:bg-white/5"
                                data-value="{{ $value }}" data-label="{{ $label }}">
                                @switch($value)
                                    @case('info')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/20 text-blue-400 group-hover:bg-blue-500/30 transition-colors">
                                            <i class="fa-solid fa-circle-info"></i>
                                        </span>
                                        @break
                                    @case('success')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500/20 text-green-400 group-hover:bg-green-500/30 transition-colors">
                                            <i class="fa-solid fa-check-circle"></i>
                                        </span>
                                        @break
                                    @case('warning')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-yellow-500/20 text-yellow-400 group-hover:bg-yellow-500/30 transition-colors">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        </span>
                                        @break
                                    @case('promo')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/20 text-purple-400 group-hover:bg-purple-500/30 transition-colors">
                                            <i class="fa-solid fa-gift"></i>
                                        </span>
                                        @break
                                    @case('system')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-500/20 text-gray-400 group-hover:bg-gray-500/30 transition-colors">
                                            <i class="fa-solid fa-gear"></i>
                                        </span>
                                        @break
                                    @default
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#d99a32]/20 text-[#d99a32] group-hover:bg-[#d99a32]/30 transition-colors">
                                            <i class="fa-solid fa-bell"></i>
                                        </span>
                                @endswitch
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-white group-hover:text-[#d99a32] transition-colors">{{ $label }}</span>
                                </div>
                                <i class="fa-solid fa-check text-[#d99a32] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-300">
                    <i class="fa-solid fa-users text-[#d99a32]"></i>
                    Đối tượng nhận <span class="text-red-400">*</span>
                </label>

                {{-- CUSTOM DROPDOWN ĐỐI TƯỢNG NHẬN --}}
                <div class="relative" id="doiTuongDropdownWrapper">
                    <input type="hidden" name="doi_tuong_nhan" id="doi_tuong_nhan" value="{{ old('doi_tuong_nhan', 'all') }}">

                    <button type="button" id="doiTuongDropdownBtn"
                        class="dropdown-trigger flex h-12 w-full items-center justify-between rounded-xl border border-white/10 bg-[#151515] px-4 text-left transition-all focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <span id="doiTuongIcon" class="dropdown-icon flex h-8 w-8 items-center justify-center rounded-lg bg-[#d99a32]/20 text-[#d99a32]">
                                <i class="fa-solid fa-users"></i>
                            </span>
                            <span id="doiTuongLabel" class="dropdown-label text-white font-medium">Tất cả người dùng</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-gray-500 transition-transform duration-300 dropdown-arrow"></i>
                    </button>

                    <div id="doiTuongDropdownMenu"
                        class="dropdown-menu absolute left-0 right-0 top-[calc(100%+8px)] z-50 hidden max-h-64 overflow-y-auto rounded-xl border border-white/10 bg-[#1a1a1a] p-1.5 shadow-2xl shadow-black/50 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                        <div class="mb-2 px-3 py-2 border-b border-white/5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Chọn đối tượng nhận</span>
                        </div>
                        @foreach ($doiTuongOptions as $value => $label)
                            <button type="button"
                                class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-all hover:bg-white/5"
                                data-value="{{ $value }}" data-label="{{ $label }}">
                                @switch($value)
                                    @case('all')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#d99a32]/20 text-[#d99a32] group-hover:bg-[#d99a32]/30 transition-colors">
                                            <i class="fa-solid fa-globe"></i>
                                        </span>
                                        @break
                                    @case('user')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/20 text-blue-400 group-hover:bg-blue-500/30 transition-colors">
                                            <i class="fa-solid fa-user"></i>
                                        </span>
                                        @break
                                    @case('staff')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500/20 text-green-400 group-hover:bg-green-500/30 transition-colors">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </span>
                                        @break
                                    @case('admin')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/20 text-purple-400 group-hover:bg-purple-500/30 transition-colors">
                                            <i class="fa-solid fa-user-shield"></i>
                                        </span>
                                        @break
                                    @case('vip')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-yellow-500/20 text-yellow-400 group-hover:bg-yellow-500/30 transition-colors">
                                            <i class="fa-solid fa-crown"></i>
                                        </span>
                                        @break
                                    @case('nguoi_dung_cu_the')
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-pink-500/20 text-pink-400 group-hover:bg-pink-500/30 transition-colors">
                                            <i class="fa-solid fa-user-pen"></i>
                                        </span>
                                        @break
                                    @default
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-500/20 text-gray-400 group-hover:bg-gray-500/30 transition-colors">
                                            <i class="fa-solid fa-users"></i>
                                        </span>
                                @endswitch
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-white group-hover:text-[#d99a32] transition-colors">{{ $label }}</span>
                                </div>
                                <i class="fa-solid fa-check text-[#d99a32] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Người dùng cụ thể (ẩn/hiện theo đối tượng) --}}
        <div id="nguoi_dung_cu_the_wrapper" class="{{ old('doi_tuong_nhan', 'all') === 'nguoi_dung_cu_the' ? '' : 'hidden' }}">
            <label class="mb-2 flex items-center gap-2 text-sm font-bold text-gray-300">
                <i class="fa-solid fa-user text-[#d99a32]"></i>
                Chọn người dùng <span class="text-red-400">*</span>
            </label>

            {{-- CUSTOM DROPDOWN NGƯỜI DÙNG CỤ THỂ --}}
            <div class="relative" id="nguoiDungCuTheDropdownWrapper">
                <input type="hidden" name="nguoi_dung_cu_the" id="nguoi_dung_cu_the" value="{{ old('nguoi_dung_cu_the', '') }}">

                <button type="button" id="nguoiDungCuTheDropdownBtn"
                    class="dropdown-trigger flex h-12 w-full items-center justify-between rounded-xl border border-white/10 bg-[#151515] px-4 text-left transition-all focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <span id="nguoiDungCuTheIcon" class="dropdown-icon flex h-8 w-8 items-center justify-center rounded-lg bg-pink-500/20 text-pink-400">
                            <i class="fa-solid fa-user-pen"></i>
                        </span>
                        <span id="nguoiDungCuTheLabel" class="dropdown-label text-gray-400">-- Chọn người dùng --</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-gray-500 transition-transform duration-300 dropdown-arrow"></i>
                </button>

                <div id="nguoiDungCuTheDropdownMenu"
                    class="dropdown-menu absolute left-0 right-0 top-[calc(100%+8px)] z-50 hidden max-h-64 overflow-y-auto rounded-xl border border-white/10 bg-[#1a1a1a] p-1.5 shadow-2xl shadow-black/50 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                    <div class="mb-2 px-3 py-2 border-b border-white/5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Danh sách người dùng</span>
                    </div>
                    <div id="nguoiDungCuTheList" class="space-y-1">
                        {{-- Users will be loaded dynamically --}}
                    </div>
                </div>
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
<style>
    /* Custom Dropdown Styles */
    .dropdown-menu {
        animation: dropdownFadeIn 0.2s ease-out;
    }

    @keyframes dropdownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu.active {
        display: block !important;
    }

    .dropdown-arrow.rotate-180 {
        transform: rotate(180deg);
    }

    .dropdown-option.selected {
        background: rgba(217, 154, 50, 0.15) !important;
        border: 1px solid rgba(217, 154, 50, 0.3);
    }

    .dropdown-option.selected .dropdown-label {
        color: #d99a32 !important;
    }

    .dropdown-option.selected .dropdown-icon {
        border: 1px solid rgba(217, 154, 50, 0.5);
    }

    .dropdown-option.selected .fa-check {
        opacity: 1 !important;
    }

    /* Scrollbar styles */
    .scrollbar-thin::-webkit-scrollbar {
        width: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
        background: transparent;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.2);
    }
</style>

<script>
    // Custom Dropdown Logic
    function initCustomDropdown(wrapperId, hiddenInputId, iconId, labelId, defaultIcon, defaultLabel) {
        const wrapper = document.getElementById(wrapperId);
        const trigger = wrapper.querySelector('.dropdown-trigger');
        const menu = wrapper.querySelector('.dropdown-menu');
        const arrow = trigger.querySelector('.dropdown-arrow');
        const hiddenInput = document.getElementById(hiddenInputId);
        const iconEl = document.getElementById(iconId);
        const labelEl = document.getElementById(labelId);
        const currentValue = hiddenInput.value;

        // Set initial selected state
        const selectedOption = wrapper.querySelector(`[data-value="${currentValue}"]`);
        if (selectedOption) {
            selectOption(wrapper, selectedOption, iconEl, labelEl);
        }

        // Toggle dropdown
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            closeAllDropdowns();
            menu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });

        // Handle option selection
        wrapper.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                selectOption(wrapper, this, iconEl, labelEl);
                hiddenInput.value = this.dataset.value;
                menu.classList.add('hidden');
                arrow.classList.remove('rotate-180');

                // Trigger change event for dependent logic
                if (wrapperId === 'doiTuongDropdownWrapper') {
                    handleDoiTuongChange(this.dataset.value);
                }
            });
        });
    }

    function selectOption(wrapper, option, iconEl, labelEl) {
        // Remove selected from all
        wrapper.querySelectorAll('.dropdown-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        // Add selected to clicked
        option.classList.add('selected');
        // Update display
        labelEl.textContent = option.dataset.label;
        iconEl.innerHTML = option.querySelector('span').innerHTML;
        iconEl.className = option.querySelector('span').className.replace('group-hover:', '');
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
        document.querySelectorAll('.dropdown-arrow').forEach(arrow => {
            arrow.classList.remove('rotate-180');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', closeAllDropdowns);

    // Initialize dropdowns
    document.addEventListener('DOMContentLoaded', function() {
        initCustomDropdown(
            'loaiDropdownWrapper',
            'loai',
            'loaiIcon',
            'loaiLabel',
            'fa-circle-info',
            'Thông tin'
        );

        initCustomDropdown(
            'doiTuongDropdownWrapper',
            'doi_tuong_nhan',
            'doiTuongIcon',
            'doiTuongLabel',
            'fa-users',
            'Tất cả người dùng'
        );

        // Handle người dùng cụ thể visibility
        const doiTuongValue = document.getElementById('doi_tuong_nhan').value;
        handleDoiTuongChange(doiTuongValue);
    });

    function handleDoiTuongChange(value) {
        const nguoiDungCuTheWrapper = document.getElementById('nguoi_dung_cu_the_wrapper');
        const nguoiDungList = document.getElementById('nguoiDungCuTheList');

        if (value === 'nguoi_dung_cu_the') {
            nguoiDungCuTheWrapper.classList.remove('hidden');

            // Fetch users
            fetch(`{{ route('admin.thong-bao-push.users-by-role') }}?role=${value}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    nguoiDungList.innerHTML = '';

                    if (data.length === 0) {
                        nguoiDungList.innerHTML = '<div class="px-3 py-4 text-center text-gray-500 text-sm">Không có người dùng nào</div>';
                        return;
                    }

                    data.forEach((user, index) => {
                        const isSelected = index === 0;
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = `dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-all hover:bg-white/5 ${isSelected ? 'selected' : ''}`;
                        button.dataset.value = user.id;
                        button.dataset.label = `${user.ho_ten} (${user.email})`;
                        button.innerHTML = `
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-pink-500/20 text-pink-400 group-hover:bg-pink-500/30 transition-colors">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <span class="block text-sm font-medium text-white group-hover:text-[#d99a32] transition-colors truncate">${user.ho_ten}</span>
                                <span class="block text-xs text-gray-500 truncate">${user.email}</span>
                            </div>
                            <i class="fa-solid fa-check text-[#d99a32] opacity-0 group-hover:opacity-100 transition-opacity ${isSelected ? '!opacity-100' : ''}"></i>
                        `;

                        button.addEventListener('click', function(e) {
                            e.stopPropagation();
                            selectOption(document.getElementById('nguoiDungCuTheDropdownWrapper'), this,
                                document.getElementById('nguoiDungCuTheIcon'),
                                document.getElementById('nguoiDungCuTheLabel'));
                            document.getElementById('nguoi_dung_cu_the').value = this.dataset.value;
                            document.getElementById('nguoiDungCuTheDropdownMenu').classList.add('hidden');
                            document.getElementById('nguoiDungCuTheDropdownBtn').querySelector('.dropdown-arrow').classList.remove('rotate-180');
                        });

                        nguoiDungList.appendChild(button);
                    });

                    // Initialize the third dropdown
                    initCustomDropdown(
                        'nguoiDungCuTheDropdownWrapper',
                        'nguoi_dung_cu_the',
                        'nguoiDungCuTheIcon',
                        'nguoiDungCuTheLabel',
                        'fa-user-pen',
                        '-- Chọn người dùng --'
                    );
                });
        } else {
            nguoiDungCuTheWrapper.classList.add('hidden');
        }
    }

    // Preview khi nhập
    const tieuDeInput = document.getElementById('tieu_de');
    const noiDungInput = document.getElementById('noi_dung');
    const previewTieuDe = document.querySelector('.preview-tieu-de');
    const previewNoiDung = document.querySelector('.preview-noi-dung');

    if (tieuDeInput) {
        tieuDeInput.addEventListener('input', function() {
            previewTieuDe.textContent = this.value || 'Tiêu đề thông báo của bạn';
        });
    }

    if (noiDungInput) {
        noiDungInput.addEventListener('input', function() {
            previewNoiDung.textContent = this.value || 'Nội dung thông báo sẽ hiển thị ở đây...';
        });
    }
</script>
@endpush
