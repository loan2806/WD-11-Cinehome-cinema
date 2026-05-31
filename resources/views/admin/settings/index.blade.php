@extends('layouts.admin')

@section('title', 'Cau hinh he thong')
@section('page-title', 'Cau hinh he thong')
@section('page-subtitle', 'Quan ly thong tin chung, dat ve va hoan tien')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    @foreach($settings as $group => $items)
        <div class="admin-panel">
            <div class="panel-header">
                <div><h5>{{ ucfirst($group) }}</h5><small>{{ $items->count() }} cau hinh</small></div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach($items as $setting)
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-300">{{ $setting->label }}</label>
                        @if($setting->type === 'boolean')
                            <label class="inline-flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                                <input type="checkbox" name="{{ $setting->key }}" value="1" @checked($setting->value)>
                                <span>Bat</span>
                            </label>
                        @else
                            <input type="{{ $setting->type }}" name="{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <button class="btn-admin"><i class="fa-solid fa-floppy-disk"></i> Luu cau hinh</button>
</form>
@endsection
