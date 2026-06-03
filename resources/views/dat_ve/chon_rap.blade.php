@extends('layouts.user')

@section('title', 'Chọn Rạp Chiếu Phim')

@section('content')
<div class="container py-5 mt-5">
    <h2 class="text-center text-white mb-5 font-bold text-3xl">CHỌN RẠP CHIẾU PHIM</h2>
    
    <div class="row">
        @forelse($danhSachRap as $rap)
            <div class="col-md-4 mb-4">
                <div class="card bg-dark text-white shadow">
                    <img src="{{ $rap->image ?? 'https://via.placeholder.com/400x200?text=Cinema' }}" class="card-img-top" alt="{{ $rap->name }}" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title text-warning font-bold text-xl">{{ $rap->name }}</h5>
                        <p class="card-text text-gray-300"><i class="fa fa-map-marker-alt text-danger mr-2"></i> {{ $rap->address }}</p>
                        <a href="{{ route('dat_ve.chon_phim', ['rap_id' => $rap->id]) }}" class="btn btn-danger w-100 mt-3 font-bold">CHỌN RẠP NÀY</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-white text-center">Hiện chưa có rạp chiếu phim nào.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
