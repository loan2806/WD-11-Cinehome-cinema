@if ($paginator->hasPages())

<div class="mt-8 flex items-center justify-center">

    <div class="flex items-center gap-2 rounded-2xl bg-white/5 p-2 backdrop-blur">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-4 py-2 text-gray-500">← Trước</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                ← Trước
            </a>
        @endif

        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();

            $start = max($current - 2, 1);
            $end = min($current + 2, $last);
        @endphp

        {{-- LEFT: first page + dots --}}
        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}"
               class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                1
            </a>

            @if ($start > 2)
                <span class="px-2 text-gray-500">...</span>
            @endif
        @endif

        {{-- MIDDLE: current range --}}
        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $current)
                <span class="px-4 py-2 rounded-xl bg-[#d99a32] text-black font-bold">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $paginator->url($page) }}"
                   class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                    {{ $page }}
                </a>
            @endif
        @endfor

        {{-- RIGHT: dots + last page --}}
        @if ($end < $last)
            @if ($end < $last - 1)
                <span class="px-2 text-gray-500">...</span>
            @endif

            <a href="{{ $paginator->url($last) }}"
               class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                {{ $last }}
            </a>
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                Sau →
            </a>
        @else
            <span class="px-4 py-2 text-gray-500">Sau →</span>
        @endif

    </div>

</div>

@endif