@extends('layouts.user')

@section('title', 'Hệ thống rạp - CineHome')

@section('content')

<section class="min-h-screen bg-[#0b0705] text-white pt-28 pb-10">
    <div class="max-w-[1800px] mx-auto px-8">

        <h1 class="text-4xl font-extrabold mb-2">
            Hệ thống <span class="text-[#f5a623]">rạp chiếu</span>
        </h1>

        <p class="text-gray-400 mb-8">
            Chọn rạp gần bạn để xem lịch chiếu và đặt vé nhanh chóng.
            <a href="{{ route('user.cinemas.map') }}" class="text-[#f5a623] font-bold ms-1 hover:underline">
                Xem bản đồ rạp
            </a>
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
            @forelse ($cinemas ?? [] as $cinema)
                <div
                    class="bg-[#151515] border border-white/10 rounded-2xl overflow-hidden shadow-lg transition duration-300 hover:-translate-y-2 hover:shadow-2xl flex flex-col min-h-[390px]">

                    <img
                        src="{{ $cinema->hinh_anh ?? 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1200' }}"
                        alt="{{ $cinema->ten_rap }}"
                        class="w-full h-[180px] object-cover shrink-0"
                    >

                    <div class="p-4 flex flex-col flex-1">
                        <h2 class="text-xl font-extrabold mb-3 text-[#f5a623] min-h-[52px]">
                            {{ $cinema->ten_rap }}
                        </h2>

                        <p class="text-gray-300 mb-2 text-sm">
                            <i class="fa-solid fa-location-dot text-[#f5a623] mr-1"></i>
                            {{ $cinema->dia_chi }}
                        </p>

                        <p class="text-gray-400 mb-4 text-sm">
                            <i class="fa-solid fa-phone text-[#f5a623] mr-1"></i>
                            {{ $cinema->so_dien_thoai ?? 'Đang cập nhật' }}
                        </p>

                        <div class="flex gap-3 mt-auto">
                            <a
                                href="{{ route('user.showtimes.index', ['rap_chieu_phim_id' => $cinema->id]) }}"
                                class="flex-1 text-center bg-[#f5a623] text-black font-extrabold py-2 rounded-xl hover:bg-[#ffc04d] transition text-sm"
                            >
                                Xem lịch chiếu
                            </a>

                            <a
                                href="{{ route('user.cinemas.show', $cinema->id) }}"
                                class="flex-1 text-center bg-white/10 text-white font-bold py-2 rounded-xl hover:bg-white/20 transition text-sm"
                            >
                                Chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="xl:col-span-5 text-center py-20 text-gray-400">
                    Chưa có dữ liệu rạp.
                </div>
            @endforelse
        </div>

    </div>
</section>

@endsection
