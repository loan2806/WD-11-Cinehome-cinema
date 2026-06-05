@extends('layouts.user')

@section('title', 'Chọn Phim và Suất Chiếu')

@section('content')
<div class="container py-5 mt-5">
    <h2 class="text-center text-white mb-2 font-bold text-3xl">PHIM ĐANG CHIẾU TẠI RẠP</h2>
    <h4 class="text-center text-warning mb-5">{{ mb_strtoupper($rap->name) }}</h4>
    
    <div class="row">
        @forelse($suatChieuTheoPhim as $movieId => $suatChieus)
            @php 
                $phim = $suatChieus->first()->movie; 
            @endphp
            <div class="col-12 mb-5">
                <div class="card bg-dark text-white border-secondary">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <img src="{{ $phim->poster ?? 'https://via.placeholder.com/300x450?text=Poster' }}" class="img-fluid rounded-start h-100" alt="{{ $phim->title }}" style="object-fit: cover;">
                        </div>
                        <div class="col-md-9">
                            <div class="card-body">
                                <h4 class="card-title text-warning font-bold">{{ $phim->ten_phim }}</h4>
                                <p class="card-text text-gray-300 mb-2"><strong>Thời lượng:</strong> {{ $phim->thoi_luong }} phút</p>
                                <p class="card-text text-gray-300 mb-4">{{ Str::limit($phim->mo_ta, 150) }}</p>
                                
                                <h5 class="text-white mb-3 border-bottom border-secondary pb-2">Các suất chiếu:</h5>
                                @php
                                    $suatChieuTheoNgay = $suatChieus->groupBy('show_date');
                                @endphp
                                
                                @foreach($suatChieuTheoNgay as $ngay => $cacSuatChieu)
                                    <div class="mb-3">
                                        <strong class="d-block mb-2 text-info">{{ \Carbon\Carbon::parse($ngay)->format('d/m/Y') }}</strong>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($cacSuatChieu as $sc)
                                                <a href="{{ route('dat_ve.chon_ghe', ['suat_chieu_id' => $sc->id]) }}" class="btn btn-outline-light">
                                                    {{ \Carbon\Carbon::parse($sc->show_time)->format('H:i') }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-white text-center">Hiện chưa có lịch chiếu phim nào tại rạp này.</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-4 text-center">
        <a href="{{ route('dat_ve.chon_rap') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Quay lại Chọn Rạp</a>
    </div>
</div>
@endsection
