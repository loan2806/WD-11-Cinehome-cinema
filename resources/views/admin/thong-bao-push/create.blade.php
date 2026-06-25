@extends('layouts.admin')

@section('page-title', 'Tạo thông báo đẩy mới')

@section('content')

<div class="admin-panel max-w-3xl">

    {{-- HEADER --}}
    <div class="panel-header">
        <div>
            <h5 class="text-2xl font-black text-white">
                Tạo thông báo đẩy mới
            </h5>
            <small class="text-gray-400">
                Nhập thông tin thông báo đẩy để gửi đến người dùng
            </small>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-4">
            <div class="flex items-center gap-2 text-red-400">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span class="font-bold">Vui lòng kiểm tra lại các trường sau:</span>
            </div>
            <ul class="mt-2 list-disc list-inside text-sm text-red-300">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST" action="{{ route('admin.thong-bao-push.store') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <label for="tieu_de" class="mb-2 block text-sm font-bold text-gray-300">
                Tiêu đề thông báo <span class="text-red-400">*</span>
            </label>
            <input type="text" name="tieu_de" id="tieu_de" value="{{ old('tieu_de') }}"
                class="h-12 w-full rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]"
                placeholder="Nhập tiêu đề thông báo...">
            @error('tieu_de')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="noi_dung" class="mb-2 block text-sm font-bold text-gray-300">
                Nội dung thông báo <span class="text-red-400">*</span>
            </label>
            <textarea name="noi_dung" id="noi_dung" rows="6"
                class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]"
                placeholder="Nhập nội dung thông báo...">{{ old('noi_dung') }}</textarea>
            @error('noi_dung')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="loai" class="mb-2 block text-sm font-bold text-gray-300">
                Loại thông báo <span class="text-red-400">*</span>
            </label>
            <select name="loai" id="loai"
                class="h-12 w-full rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">
                @foreach ($loaiOptions as $value => $label)
                    <option value="{{ $value }}" {{ old('loai', 'info') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('loai')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="doi_tuong_nhan" class="mb-2 block text-sm font-bold text-gray-300">
                Đối tượng nhận <span class="text-red-400">*</span>
            </label>
            <select name="doi_tuong_nhan" id="doi_tuong_nhan"
                class="h-12 w-full rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">
                @foreach ($doiTuongOptions as $value => $label)
                    <option value="{{ $value }}" {{ old('doi_tuong_nhan', 'all') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('doi_tuong_nhan')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div id="nguoi_dung_cu_the_wrapper" class="{{ old('doi_tuong_nhan', 'all') === 'nguoi_dung_cu_the' ? '' : 'hidden' }}">
            <label for="nguoi_dung_cu_the" class="mb-2 block text-sm font-bold text-gray-300">
                Chọn người dùng <span class="text-red-400">*</span>
            </label>
            <select name="nguoi_dung_cu_the" id="nguoi_dung_cu_the"
                class="h-12 w-full rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">
                <option value="">-- Chọn người dùng --</option>
            </select>
            @error('nguoi_dung_cu_the')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">
                Vui lòng chọn đối tượng nhận trước để tải danh sách người dùng.
            </p>
        </div>

        <div class="flex items-center gap-3 pt-4">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">
                <i class="fa-solid fa-paper-plane"></i>
                Tạo và gửi thông báo
            </button>
            <a href="{{ route('admin.thong-bao-push.index') }}"
                class="rounded-2xl border border-white/10 bg-white/5 px-6 py-3 text-sm font-bold text-gray-300 transition hover:bg-white/10">
                Hủy bỏ
            </a>
        </div>

    </form>

</div>

@endsection

@push('scripts')
<script>
    const doiTuongNhanSelect = document.getElementById('doi_tuong_nhan');
    const nguoiDungCuTheWrapper = document.getElementById('nguoi_dung_cu_the_wrapper');
    const nguoiDungCuTheSelect = document.getElementById('nguoi_dung_cu_the');

    doiTuongNhanSelect.addEventListener('change', function () {
        if (this.value === 'nguoi_dung_cu_the') {
            nguoiDungCuTheWrapper.classList.remove('hidden');
        } else {
            nguoiDungCuTheWrapper.classList.add('hidden');
        }
    });

    doiTuongNhanSelect.addEventListener('change', function () {
        if (this.value === 'nguoi_dung_cu_the') {
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
        }
    });
</script>
@endpush
