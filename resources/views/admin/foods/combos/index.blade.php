@extends('layouts.admin')

@section('title', 'Quản lý combo')
@section('page-title', 'Quản lý combo')
@section('page-subtitle', 'Thiết lập combo đồ ăn, giá bán, ảnh và thành phần')

@section('content')
<div class="admin-panel">
    @include('admin.partials.flash')

    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h5 class="text-2xl font-black text-white">Quản lý combo</h5>
            <small class="text-gray-400">Danh sách combo, giá, ảnh và thành phần kèm theo.</small>
        </div>
        <a href="{{ route('admin.foods.combos.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">
            <i class="fa-solid fa-plus"></i>
            Thêm combo
        </a>
    </div>

    <form action="{{ route('admin.foods.combos.index') }}" method="GET" class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end">
        <div class="flex-1">
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Tìm kiếm combo</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="VD: Combo gia đình, Combo nước..." class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
        </div>
        <button type="submit" class="rounded-xl bg-[#d99a32] px-6 py-3 font-semibold text-black transition hover:bg-[#e6a940]"><i class="fa-solid fa-search mr-2"></i>Tìm kiếm</button>
        <a href="{{ route('admin.foods.combos.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-6 py-3 font-semibold text-white transition hover:bg-white/10"><i class="fa-solid fa-rotate-right mr-2"></i>Đặt lại</a>
    </form>

    <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left">
                <thead class="bg-white/4 text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-4">#</th>
                        <th class="px-5 py-4">Ảnh</th>
                        <th class="px-5 py-4">Combo</th>
                        <th class="px-5 py-4">Giá</th>
                        <th class="px-5 py-4">Tồn kho</th>
                        <th class="px-5 py-4">Thành phần</th>
                        <th class="px-5 py-4 text-center">Trạng thái</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($combos as $combo)
                        <tr class="bg-[#0f0f0f] transition hover:bg-white/5">
                            <td class="px-5 py-5 text-sm font-medium text-gray-200">{{ $loop->iteration + ($combos->currentPage() - 1) * $combos->perPage() }}</td>
                            <td class="px-5 py-5">
                                @if($combo->image)
                                    @php
                                        $comboImage = str_starts_with($combo->image, 'foods/') ? $combo->image : 'foods/' . $combo->image;
                                    @endphp
                                    <img src="{{ asset('storage/' . $comboImage) }}" alt="{{ $combo->name }}" class="h-16 w-16 rounded-xl object-cover">
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-white/5 text-gray-400"><i class="fa-solid fa-image"></i></div>
                                @endif
                            </td>
                            <td class="px-5 py-5">
                                <div class="font-bold text-white">{{ $combo->name }}</div>
                                @if($combo->description)
                                    <div class="mt-2 text-sm text-gray-400">{{ \Illuminate\Support\Str::limit($combo->description, 80) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-5 text-white">{{ number_format((float) $combo->price, 0, ',', '.') }}đ</td>
                            @php
                                $stock = (int) $combo->stock_quantity;
                                $isLow = $stock <= ($combo->min_stock_quantity ?? 10);
                            @endphp
                            <td class="px-5 py-5 {{ $isLow ? 'text-yellow-300' : 'text-white' }}">
                                <strong>{{ $stock }}</strong>
                            </td>
                            <td class="px-5 py-5 text-sm text-gray-300">
                                @if($combo->comboItems->isNotEmpty())
                                    @foreach($combo->comboItems as $item)
                                        <div>{{ $item->variant?->doAn->name ?? '---' }} x{{ $item->quantity }}</div>
                                    @endforeach
                                @else
                                    <span class="text-gray-500">Chưa có</span>
                                @endif
                            </td>
                            <td class="px-5 py-5 text-center">
                                <span class="rounded-full bg-green-500/20 px-3 py-1 text-xs font-bold text-green-300">{{ $combo->is_active ? 'Đang hoạt động' : 'Tạm ẩn' }}</span>
                            </td>
                            <td class="px-5 py-5 text-right">
                                <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                    <a href="{{ route('admin.foods.combos.edit', $combo) }}" class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25" title="Chỉnh sửa"><i class="fa-solid fa-pen"></i></a>
                                    <form action="{{ route('admin.foods.combos.destroy', $combo) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Bạn có chắc muốn xóa combo này?')" class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 transition hover:bg-red-500/25" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-500">
                                <i class="fa-solid fa-inbox mr-2"></i>
                                Chưa có combo nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('components.admin-pagination', ['paginator' => $combos])
</div>
@endsection
