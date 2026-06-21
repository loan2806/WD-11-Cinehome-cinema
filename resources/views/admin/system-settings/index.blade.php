@extends('layouts.admin')

@section('page-title', 'Cấu hình hệ thống')
@section('page-subtitle', 'Thiết lập thông tin chung, đặt vé, thanh toán và vận hành')

@section('content')
@include('admin.partials.flash')

<form method="POST" action="{{ route('admin.system-settings.update') }}" class="space-y-6">
    @csrf
    @method('PATCH')

    @foreach ($settings as $group => $items)
        <div class="admin-panel">
            <div class="panel-header">
                <h5>{{ ucfirst($group) }}</h5>
            </div>
            <div class="panel-body grid gap-4 md:grid-cols-2">
                @foreach ($items as $setting)
                    <label class="block rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                        <span class="mb-2 block text-sm font-bold text-[#f4c56a]">{{ $setting->nhan }}</span>

                        @if ($setting->loai === 'boolean')
                            <input type="hidden" name="{{ $setting->khoa }}" value="0">
                            <label class="inline-flex cursor-pointer items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="{{ $setting->khoa }}"
                                    value="1"
                                    class="h-5 w-5 rounded border-white/20 bg-[#111]"
                                    @checked((string) $setting->gia_tri === '1')
                                >
                                <span class="text-sm text-gray-300">Bật</span>
                            </label>
                        @elseif ($setting->loai === 'textarea')
                            <textarea name="{{ $setting->khoa }}" class="admin-input min-h-[110px]">{{ $setting->gia_tri }}</textarea>
                        @else
                            <input
                                type="{{ $setting->loai }}"
                                name="{{ $setting->khoa }}"
                                value="{{ $setting->gia_tri }}"
                                class="admin-input"
                            >
                        @endif
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <button class="btn-admin" type="submit">Lưu cấu hình</button>
</form>
@endsection
