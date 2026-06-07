@extends('layouts.admin')

@section('page-title', 'Cấu hình hệ thống')
@section('page-title', 'Cấu hình hệ thống')
@section('page-subtitle', 'Thiết lập thông tin chung, đặt vé và vận hành')

@section('content')
@include('admin.partials.flash')

<form method="POST" action="{{ route('admin.system-settings.update') }}" class="space-y-6">
    @csrf
    @method('PATCH')

    @foreach ($settings as $group => $items)
        <div class="admin-panel">
            <div class="panel-header"><h5>{{ ucfirst($group) }}</h5></div>
            <div class="panel-body grid gap-4 md:grid-cols-2">
                @foreach ($items as $setting)
                    <label class="block">
                        <span class="mb-2 block text-sm font-bold text-[#f4c56a]">{{ $setting->label }}</span>
                        @if ($setting->type === 'textarea')
                            <textarea name="settings[{{ $setting->key }}]" class="admin-input min-h-[110px]">{{ $setting->value }}</textarea>
                        @else
                            <input type="{{ $setting->type }}" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="admin-input">
                        @endif
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <button class="btn-admin" type="submit">Lưu cấu hình</button>
</form>
@endsection

