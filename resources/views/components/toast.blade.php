@props(['type' => 'success', 'message' => null])

@php
    $variant = match($type) {
        'success' => [
            'bg' => 'bg-gradient-to-r from-[#5a2d14] to-[#d99a32]',
            'border' => 'border-[#d99a32]/50',
            'icon' => 'fa-circle-check',
            'shadow' => 'shadow-[#d99a32]/30',
        ],
        'error' => [
            'bg' => 'bg-gradient-to-r from-[#5a2d14] to-[#ef4444]',
            'border' => 'border-red-500/50',
            'icon' => 'fa-circle-exclamation',
            'shadow' => 'shadow-red-500/30',
        ],
        'warning' => [
            'bg' => 'bg-gradient-to-r from-[#5a2d14] to-[#f59e0b]',
            'border' => 'border-amber-500/50',
            'icon' => 'fa-triangle-exclamation',
            'shadow' => 'shadow-amber-500/30',
        ],
        default => [
            'bg' => 'bg-gradient-to-r from-[#5a2d14] to-[#d99a32]',
            'border' => 'border-[#d99a32]/50',
            'icon' => 'fa-circle-info',
            'shadow' => 'shadow-[#d99a32]/30',
        ],
    };
@endphp

<div
    x-data="{
        show: true,
        init() {
            setTimeout(() => { this.show = false; }, 3500);
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
    class="fixed inset-0 z-[99999] flex items-center justify-center p-4 pointer-events-none"
>
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" @click="show = false"></div>

    <div class="relative flex items-center gap-4 rounded-2xl border {{ $variant['border'] }} {{ $variant['bg'] }} px-6 py-4 shadow-2xl {{ $variant['shadow'] }}">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/20">
            <i class="fa-solid {{ $variant['icon'] }} text-2xl leading-none text-white"></i>
        </div>

        <div class="pr-2">
            <p class="text-base font-bold leading-snug text-white">
                {{ $message ?? ($type === 'success' ? 'Thao tác thành công' : ($type === 'error' ? 'Có lỗi xảy ra' : 'Thông báo')) }}
            </p>
        </div>

        <button @click="show = false" class="ml-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 text-white/80 transition hover:bg-white/25 hover:text-white">
            <i class="fa-solid fa-xmark text-sm leading-none"></i>
        </button>
    </div>
</div>
