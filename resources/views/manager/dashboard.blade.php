@extends('layouts.manager')

@section('title', 'Quản lý phòng chiếu - CineHome')
@section('page-title', 'Quản lý phòng chiếu')
@section('page-subtitle', 'Điều hành phòng chiếu, suất chiếu và theo dõi doanh thu')

@section('content')
    <div class="rounded-3xl border border-white/10 bg-gradient-to-r from-[#1a0b04] to-[#2b1208] p-6 shadow-xl">
        <div class="max-w-3xl">
            <h4 class="text-lg font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-[#d99a32]"></i> Xin chào, {{ Auth::user()->ho_ten }}!
            </h4>
            <p class="text-sm text-gray-300 mt-1.5 leading-relaxed">
                Đây là khu vực <strong>Quản lý phòng chiếu</strong> của <strong>CineHome</strong>.
                Bạn có thể theo dõi lịch chiếu, phòng chiếu và báo cáo doanh thu.
            </p>
        </div>
    </div>
@endsection
