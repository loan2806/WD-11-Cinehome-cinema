@extends('layouts.admin')

@section('title','Bán vé tại quầy')
@section('page-title','Bán vé tại quầy')

@section('content')

<div class="sell-page">

    @php
        $movies = collect($showtimes)->groupBy('phim_id');

        $dates = collect($showtimes)
            ->map(function($item){
                return $item->thoi_gian_chieu->format('Y-m-d');
            })
            ->unique()
            ->sort()
            ->values();

        $selectedDate = request('ngay_chieu') ?? $dates->first();

    @endphp


    <div class="sell-header">

        <div>
            <h2>Bán vé tại quầy</h2>

            <p>
                Chọn ngày, phim và suất chiếu để bán vé trực tiếp cho khách hàng.
            </p>
        </div>


        <div class="sell-icon">
            <i class="fa-solid fa-ticket"></i>
        </div>

    </div>



    {{-- Chọn ngày --}}

    <div class="date-box">

        <div class="date-title">
            <i class="fa-solid fa-calendar-days"></i>
            Chọn ngày xem
        </div>


        <div class="date-list">

            @foreach($dates as $date)

                @php
                    $carbonDate = \Carbon\Carbon::parse($date);
                @endphp


                <a href="{{ request()->fullUrlWithQuery(['ngay_chieu'=>$date]) }}"
                   class="date-item {{ $selectedDate == $date ? 'active' : '' }}">

                    <span>
                        {{ $carbonDate->translatedFormat('D') }}
                    </span>

                    <strong>
                        {{ $carbonDate->format('d') }}
                    </strong>

                    <small>
                        {{ $carbonDate->format('m/Y') }}
                    </small>

                </a>

            @endforeach

        </div>

    </div>




    {{-- Danh sách phim --}}

    <div class="movie-list">


        @forelse($movies as $movieId => $movieShowtimes)


            @php
                $movie = $movieShowtimes->first()->phim;

                $showtimesByDate = $movieShowtimes
                    ->filter(function($item) use ($selectedDate){
                        return $item->thoi_gian_chieu->format('Y-m-d') == $selectedDate;
                    });

            @endphp



            @if($showtimesByDate->count())


            <div class="movie-card">


                <div class="movie-poster-box">

                    <img src="{{ $movie->poster ? asset('storage/movies/'.$movie->poster) : asset('images/no-poster.jpg') }}"
                         class="movie-poster">


                </div>



                <div class="movie-content">


                    <h3>
                        {{ $movie->ten_phim }}
                    </h3>



                    <div class="movie-tags">

                        @if($movie->do_tuoi)

                            <span class="age-tag">
                                {{ $movie->do_tuoi }}
                            </span>

                        @endif


                        <span class="type-tag">
                            2D
                        </span>

                    </div>



                    <div class="showtime-title">

                        <i class="fa-solid fa-clock"></i>

                        Suất chiếu

                    </div>



                    <div class="showtime-list">


                        @foreach($showtimesByDate as $showtime)


                            <a href="{{ route('staff.ban-ve.show',$showtime->id) }}"
                               class="showtime-item">


                                <strong>
                                    {{ $showtime->thoi_gian_chieu->format('H:i') }}
                                </strong>


                                <span>

                                    {{ $showtime->phongChieu->ten_phong ?? 'Phòng chiếu' }}

                                </span>


                            </a>


                        @endforeach


                    </div>


                </div>


            </div>


            @endif


        @empty


            <div class="empty-box">

                <i class="fa-solid fa-calendar-xmark"></i>

                <p>
                    Chưa có suất chiếu.
                </p>

            </div>


        @endforelse


    </div>


</div>

@endsection
@push('styles')

<style>

.sell-page{
    animation:fadeIn .35s ease;
}



.sell-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}



.sell-header h2{
    margin:0;
    color:#fff;
    font-size:32px;
    font-weight:900;
}



.sell-header p{
    margin-top:8px;
    color:#999;
}



.sell-icon{

    width:64px;
    height:64px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:22px;

    color:#f4c56a;

    background:rgba(244,197,106,.12);

    box-shadow:0 0 35px rgba(244,197,106,.2);

}



.sell-icon i{
    font-size:28px;
}




/* DATE */

.date-box{

    background:#121212;

    border:1px solid rgba(255,255,255,.1);

    border-radius:24px;

    padding:20px;

    margin-bottom:25px;

}



.date-title{

    color:#f4c56a;

    font-weight:900;

    margin-bottom:15px;

    display:flex;

    align-items:center;

    gap:10px;

}



.date-list{

    display:flex;

    gap:12px;

    overflow-x:auto;

}



.date-item{

    min-width:90px;

    padding:14px;

    border-radius:18px;

    background:#1a1a1a;

    border:1px solid rgba(255,255,255,.1);

    color:#aaa;

    text-decoration:none;

    text-align:center;

    transition:.25s;

}



.date-item span{

    display:block;

    font-size:12px;

    text-transform:uppercase;

}



.date-item strong{

    display:block;

    font-size:28px;

    color:#fff;

}



.date-item small{

    font-size:12px;

}



.date-item:hover,
.date-item.active{

    background:linear-gradient(135deg,#f4c56a,#d99a32);

    color:#222;

    transform:translateY(-3px);

}



.date-item.active strong,
.date-item.active span,
.date-item.active small{

    color:#222;

}



/* MOVIE */

.movie-list{

    display:flex;

    flex-direction:column;

    gap:22px;

}



.movie-card{

    display:flex;

    gap:22px;

    padding:22px;

    border-radius:26px;

    background:#121212;

    border:1px solid rgba(255,255,255,.1);

    box-shadow:0 20px 50px rgba(0,0,0,.3);

}



.movie-poster-box{

    width:150px;

    flex-shrink:0;

}



.movie-poster{

    width:100%;

    height:220px;

    object-fit:cover;

    border-radius:18px;

}



.movie-content{

    flex:1;

}



.movie-content h3{

    margin:0;

    color:#fff;

    font-size:26px;

    font-weight:900;

}



.movie-tags{

    display:flex;

    gap:10px;

    margin-top:15px;

}



.age-tag,
.type-tag{

    padding:6px 12px;

    border-radius:999px;

    font-size:11px;

    font-weight:900;

    text-transform:uppercase;

}



.age-tag{

    background:#ef4444;

    color:#fff;

}



.type-tag{

    background:rgba(244,197,106,.15);

    border:1px solid rgba(244,197,106,.4);

    color:#f4c56a;

}



.showtime-title{

    margin-top:30px;

    margin-bottom:12px;

    color:#888;

    font-size:12px;

    font-weight:900;

    text-transform:uppercase;

    letter-spacing:2px;

}



.showtime-list{

    display:flex;

    flex-wrap:wrap;

    gap:12px;

}



.showtime-item{

    min-width:110px;

    padding:14px;

    border-radius:18px;

    text-align:center;

    text-decoration:none;

    color:#111;

    background:linear-gradient(135deg,#f4c56a,#d99a32);

    transition:.25s;

}



.showtime-item strong{

    display:block;

    font-size:20px;

}



.showtime-item span{

    display:block;

    margin-top:5px;

    font-size:12px;

    color:#333;

}



.showtime-item:hover{

    transform:translateY(-4px);

    box-shadow:0 15px 35px rgba(244,197,106,.25);

}



.empty-box{

    height:250px;

    border-radius:25px;

    background:#121212;

    border:1px dashed rgba(255,255,255,.2);

    display:flex;

    flex-direction:column;

    align-items:center;

    justify-content:center;

    color:#777;

}



.empty-box i{

    font-size:45px;

    color:#d99a32;

    margin-bottom:15px;

}



@keyframes fadeIn{

    from{

        opacity:0;

        transform:translateY(10px);

    }

    to{

        opacity:1;

        transform:translateY(0);

    }

}



@media(max-width:900px){

    .movie-card{

        flex-direction:column;

    }


    .movie-poster-box{

        width:100%;

    }


    .movie-poster{

        height:300px;

    }

}


</style>

<script>
localStorage.removeItem("staff_food_cart");
</script>

@endpush
