@extends('layouts.user')

@section('title', 'Chọn phim và suất chiếu')

@section('content')
<div class="min-h-screen bg-[#080808] pt-28 pb-12 text-white">

    <div class="mx-auto max-w-7xl px-6">

        {{-- Header --}}
        <div class="mb-6 overflow-hidden rounded-[28px] border border-white/10 bg-[linear-gradient(135deg,rgba(255,255,255,0.06),rgba(255,255,255,0.02))] p-6 shadow-[0_20px_80px_rgba(0,0,0,0.35)]">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-yellow-400/20 bg-yellow-400/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-yellow-300">
                        <span class="h-2 w-2 rounded-full bg-yellow-400"></span>
                        Chọn lịch chiếu
                    </div>
                    <h1 class="mt-4 text-3xl font-black tracking-[0.08em] text-white sm:text-4xl">
                        Lịch chiếu phim {{ $rap->ten_rap }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm text-gray-400 sm:text-base">
                        {{ $rap->dia_chi }}
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-gray-300">
                    <div class="font-semibold text-white">🎬 Chọn ngày xem</div>
                    <div class="mt-1 text-xs text-gray-400">Nhấn vào ngày để đổi suất chiếu nhanh chóng</div>
                </div>
            </div>

            <form
                id="dateForm"
                action="{{ request()->url() }}"
                method="GET"
                class="sticky top-28 z-[9999] mt-6 rounded-[24px] border border-white/10 bg-[#0f0f0f]/95 p-3 backdrop-blur">

                <input type="hidden" name="ngay_chieu" id="selectedDateInput" value="{{ $selectedDate->toDateString() }}">

                <div class="flex items-center gap-2 overflow-hidden rounded-[20px] border border-white/10 bg-[#151515] p-2">
                    <button
                        type="button"
                        id="prevDate"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-white transition hover:bg-white/10">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <div id="dateList" class="flex flex-1 flex-nowrap gap-2 overflow-x-auto px-1 py-1 scrollbar-hide">
                        @foreach($dateOptions as $dateOption)
                            @php
                                $dateCarbon = \Carbon\Carbon::parse($dateOption['date']);
                            @endphp
                            <button
                                type="button"
                                data-date="{{ $dateOption['date'] }}"
                                class="date-chip min-w-[76px] shrink-0 rounded-2xl border px-2 py-3 text-center transition duration-200 {{ $dateOption['active'] ? 'date-chip-active border-yellow-400 bg-yellow-500/90 text-white shadow-lg shadow-yellow-500/20' : 'border-white/10 bg-white/5 text-gray-200 hover:border-yellow-400/40 hover:bg-white/10' }}">
                                <div class="text-[11px] uppercase tracking-[0.25em] text-gray-400 {{ $dateOption['active'] ? 'text-white/80' : '' }}">
                                    {{ $dateOption['label'] }}
                                </div>
                                <div class="mt-1 text-xl font-black leading-none">
                                    {{ $dateCarbon->format('d') }}
                                </div>
                                <div class="mt-1 text-[10px] uppercase tracking-[0.2em] {{ $dateOption['active'] ? 'text-white/80' : 'text-gray-500' }}">
                                    {{ $dateCarbon->translatedFormat('D') }}
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        id="nextDate"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-white transition hover:bg-white/10">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </form>
        </div>


        {{-- Danh sách phim --}}

        <div class="space-y-5">

            @forelse($suatChieuTheoPhim as $suatChieus)

                @php
                    $phim = $suatChieus->first()->phim;
                    $showtimes = $suatChieus;
                @endphp

                <div class="rounded-[24px] border border-white/10 bg-[#141414]/90 p-5 shadow-[0_10px_40px_rgba(0,0,0,0.25)]">
                    <div class="flex flex-col gap-5 md:flex-row">
                        <div class="w-full shrink-0 md:w-[140px]">
                            <img
                                src="{{ $phim->poster ? asset('storage/movies/' . $phim->poster) : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=600' }}"
                                class="h-48 w-full rounded-[18px] object-cover shadow-lg shadow-black/30">
                        </div>

                        <div class="flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-2xl font-black text-white">
                                        {{ $phim->ten_phim }}
                                    </h2>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if($phim->do_tuoi)
                                            <span class="rounded-full bg-red-500/90 px-3 py-1 text-[11px] font-black uppercase tracking-[0.25em] text-white">
                                                {{ $phim->do_tuoi }}
                                            </span>
                                        @endif
                                        <span class="rounded-full border border-yellow-400/30 bg-yellow-400/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.25em] text-yellow-300">
                                            2D
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-4 text-sm text-gray-400">
                                @if($phim->genres->isNotEmpty())
                                    {{ $phim->genres->pluck('ten_the_loai')->join(', ') }}
                                @endif
                            </div>

                            <div class="mt-6">
                                <div class="mb-3 text-[11px] font-black uppercase tracking-[0.35em] text-gray-500">
                                    Suất chiếu
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @foreach($showtimes as $suat)
                                        @if($suat->ghe_trong > 0)
                                            <a
                                                href="{{ route('dat_ve.chon_ghe', ['suat_chieu_id' => $suat->id]) }}"
                                                class="rounded-2xl border border-yellow-400/30 bg-gradient-to-r from-yellow-500/90 to-amber-500/90 px-4 py-3 text-center text-sm font-black text-black transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-yellow-500/20">
                                                <div class="text-lg leading-none">{{ $suat->thoi_gian_chieu->format('H:i') }}</div>
                                                <div class="mt-1 text-[10px] font-semibold text-black/70">
                                                    {{ $suat->ghe_trong }}/{{ $suat->tong_ghe }} ghế
                                                </div>

                                            </a>
                                        @else
                                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center text-sm font-semibold text-gray-500 opacity-60">
                                                <div class="text-lg leading-none">{{ $suat->thoi_gian_chieu->format('H:i') }}</div>
                                                <div class="mt-1 text-[11px] uppercase tracking-[0.2em]">Hết vé</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @empty

                <div class="rounded-[24px] border border-dashed border-white/10 bg-[#141414]/80 py-16 text-center shadow-[0_10px_40px_rgba(0,0,0,0.2)]">
                    <div class="mb-4 text-5xl">🎬</div>
                    <h2 class="text-2xl font-black text-white">Chưa có suất chiếu</h2>
                    <p class="mt-3 text-gray-400">
                        Hiện chưa có suất chiếu nào cho ngày này. Hãy chọn một ngày khác.
                    </p>
                </div>

            @endforelse

        </div>

    </div>

</div>
@endsection

@push('styles')
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .date-chip-active {
            background: linear-gradient(135deg, #facc15, #d97706);
            border-color: #facc15;
            color: #fff;
            transform: translateY(-1px);
        }

        .date-chip {
            scroll-snap-align: center;
        }

        .date-chip:hover {
            transform: translateY(-1px);
        }
    </style>
@endpush

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var dateForm = document.getElementById('dateForm');
            var dateInput = document.getElementById('selectedDateInput');
            var dateButtons = Array.from(document.querySelectorAll('[data-date]'));
            var prevDate = document.getElementById('prevDate');
            var nextDate = document.getElementById('nextDate');
            var isSubmitting = false;

            if (!dateForm || !dateInput) {
                console.warn('Date form or input missing');
                return;
            }

            if (!dateButtons.length) {
                console.warn('No date buttons found');
            }

            var activeIndex = dateButtons.findIndex(function (button) {
                return button.dataset.date === dateInput.value;
            });
            if (activeIndex === -1) {
                activeIndex = 0;
            }

            function submitOnce() {
                if (isSubmitting) return;
                isSubmitting = true;
                // small timeout to allow UI update before navigation
                setTimeout(function () { dateForm.submit(); }, 50);
            }

            // index: number, doSubmit: boolean
            function setActiveIndex(index, doSubmit) {
                if (!dateButtons.length) return;
                if (index < 0 || index >= dateButtons.length) {
                    return;
                }

                activeIndex = index;
                dateInput.value = dateButtons[activeIndex].dataset.date;
                dateButtons.forEach(function (button, idx) {
                    button.classList.toggle('date-chip-active', idx === activeIndex);
                    button.setAttribute('aria-pressed', idx === activeIndex ? 'true' : 'false');
                });

                // If caller wants to submit (click on a date), submit.
                if (doSubmit) {
                    submitOnce();
                } else {
                    // Otherwise just scroll the list to show the active date
                    try {
                        dateButtons[activeIndex].scrollIntoView({ inline: 'center', behavior: 'smooth', block: 'nearest' });
                    } catch (e) {
                        // ignore
                    }
                }
            }

            dateButtons.forEach(function (button, index) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    setActiveIndex(index, true);
                });
            });

            if (prevDate) {
                prevDate.addEventListener('click', function (e) {
                    e.preventDefault();
                    setActiveIndex(Math.max(0, activeIndex - 1), false);
                });
            } else {
                console.warn('prevDate button not found');
            }

            if (nextDate) {
                nextDate.addEventListener('click', function (e) {
                    e.preventDefault();
                    setActiveIndex(Math.min(dateButtons.length - 1, activeIndex + 1), false);
                });
            } else {
                console.warn('nextDate button not found');
            }
        });
    </script>
@endsection