@extends('layouts.admin')

@section('title','Thanh toán tại quầy')
@section('page-title','Thanh toán tại quầy')

@section('content')

<div class="min-h-screen bg-black text-white pt-32 pb-20 px-8">

    <div class="max-w-5xl mx-auto">


        <h1 class="text-4xl font-black">

            XÁC NHẬN
            <span class="text-yellow-400">
                BÁN VÉ
            </span>

        </h1>



        <div class="mt-8 bg-zinc-900 border border-white/10 rounded-2xl p-8">



            <h2 class="text-yellow-400 font-black text-xl mb-6">

                Thông tin suất chiếu

            </h2>




            <div class="space-y-3">


                <p>

                    <span class="text-gray-400">
                        Phim:
                    </span>

                    <strong>
                        {{ $suatChieu->phim->ten_phim }}
                    </strong>

                </p>



                <p>

                    <span class="text-gray-400">
                        Rạp:
                    </span>

                    <strong>
                        {{ $suatChieu->rapChieuPhim->ten_rap }}
                    </strong>

                </p>



                <p>

                    <span class="text-gray-400">
                        Phòng:
                    </span>

                    <strong>
                        {{ $suatChieu->phongChieu->ten_phong }}
                    </strong>

                </p>



                <p>

                    <span class="text-gray-400">
                        Ghế:
                    </span>

                    <strong>
                        {{ $seats->implode(', ') }}
                    </strong>

                </p>



                <p>

                    <span class="text-gray-400">
                        Suất chiếu:
                    </span>

                    <strong>

                        {{ $suatChieu->thoi_gian_chieu->format('H:i d/m/Y') }}

                    </strong>

                </p>



            </div>





            <hr class="border-white/10 my-6">





            <h2 class="text-yellow-400 font-black text-xl mb-4">

                Đồ ăn

            </h2>




            @if($foodItems->count())


            @foreach($foodItems as $item)


            <div class="flex justify-between py-2">


                <span>

                    {{ $item['name'] }}

                    x{{ $item['qty'] }}

                </span>



                <span>

                    {{ number_format(
                    $item['price']*$item['qty'],
                    0,
                    ',',
                    '.'
                    )}}đ

                </span>


            </div>



            @endforeach



            @else


            <p class="text-gray-500">

                Không có đồ ăn

            </p>



            @endif





            <hr class="border-white/10 my-6">





            <div class="space-y-3">



                <div class="flex justify-between">


                    <span>

                        Tiền ghế

                    </span>


                    <strong>

                        {{number_format($seatTotal,0,',','.')}}đ

                    </strong>


                </div>





                <div class="flex justify-between">


                    <span>

                        Tiền đồ ăn

                    </span>


                    <strong>

                        {{number_format($foodTotal,0,',','.')}}đ

                    </strong>


                </div>





                <div class="flex justify-between text-2xl font-black border-t border-white/10 pt-4">


                    <span>

                        Tổng thanh toán

                    </span>



                    <span class="text-yellow-400">

                        {{number_format($total,0,',','.')}}đ

                    </span>


                </div>



            </div>







            <form action="{{route('staff.ban-ve.store',$suatChieu->id)}}" method="POST" class="mt-8">

                @csrf

                <input type="hidden" name="seats" value="{{$seats->implode(',')}}">


                <input type="hidden" name="food_cart" value="{{json_encode($foodItems->values())}}">


                <input type="hidden" name="clear_cart_key" value="staff_food_cart_{{auth()->id()}}_{{$suatChieu->id}}">


                <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-300 text-black py-4 rounded-xl font-black transition">

                    XÁC NHẬN BÁN VÉ

                </button>

            </form>




        </div>


    </div>


</div>


@endsection
