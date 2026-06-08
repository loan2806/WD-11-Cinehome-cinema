@extends('layouts.admin')

@section('page-title', 'Tao thong bao')
@section('page-title', 'Tao thong bao')
@section('page-subtitle', 'Gui thong bao chung theo vai tro')

@section('content')
<form method="POST" action="{{ route('admin.notifications.store') }}" class="admin-panel max-w-3xl">
    @csrf
    <div class="grid gap-4">
        <div>
            <label class="mb-2 block text-sm font-bold text-gray-300">Tieu de</label>
            <input name="title" value="{{ old('title') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>
            @error('title')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold text-gray-300">Noi dung</label>
            <textarea name="message" rows="5" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>{{ old('message') }}</textarea>
            @error('message')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Loai</label>
                <select name="type" class="w-full rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white">
                    @foreach(['info', 'success', 'warning', 'danger'] as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Vai tro nhan</label>
                <select name="audience" class="w-full rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white">
                    @foreach(['all' => 'Tat ca', 'user' => 'user', 'staff' => 'staff', 'admin' => 'admin'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('audience', 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-3">
            <button class="btn-admin">Gui thong bao</button>
            <a href="{{ route('admin.notifications.index') }}" class="btn-admin-outline">Huy</a>
        </div>
    </div>
</form>
@endsection

