@extends('layouts.admin')

@section('title','Chọn ghế bán vé')
@section('page-title','Chọn ghế bán vé')

@section('content')
<div class="dat-ve-page">
    <div class="booking-layout">

        <aside class="movie-info-card">

            <img src="{{asset('storage/movies/'.$suatChieu->phim->poster)}}" class="movie-poster">

            <div class="movie-detail">

                <h2>{{$suatChieu->phim->ten_phim}}</h2>

                <div class="info-line">
                    <span>Rạp</span>
                    <strong>{{$suatChieu->rapChieuPhim->ten_rap ?? 'Không rõ'}}</strong>
                </div>

                <div class="info-line">
                    <span>Phòng</span>
                    <strong>{{$suatChieu->phongChieu->ten_phong ?? 'Không rõ'}}</strong>
                </div>

                <div class="info-line">
                    <span>Suất chiếu</span>
                    <strong>{{$suatChieu->thoi_gian_chieu->format('H:i d/m/Y')}}</strong>
                </div>

                <div class="info-line">
                    <span>Giá từ</span>
                    <strong>{{number_format($suatChieu->gia_ve,0,',','.')}}đ</strong>
                </div>

            </div>

        </aside>


        <section class="seat-container">

            <div class="screen-area">
                <div class="screen-line"></div>
                <p>MÀN HÌNH</p>
            </div>


            <div class="seat-map">

                @foreach($seatsByRow as $hang=>$cacGhe)

                <div class="seat-row">

                    <span class="row-label">{{$hang}}</span>

                    <div class="seat-list">

                        @foreach($cacGhe as $ghe)

                        @php
                        $seatCode=strtoupper(trim($ghe->ma_ghe));
                        $isBooked=$soldSeatCodes->contains($seatCode);
                        $isMaintenance=in_array($seatCode,$maintenanceSeatCodes,true);
                        $seatPrice=$ghe->gia ?? $suatChieu->gia_ve;
                        $seatType=strtolower($ghe->loaiGhe->ten_loai ?? '');
                        @endphp


                        <button type="button" class="seat-button {{$isBooked?'booked':''}} {{$isMaintenance?'maintenance':''}}" data-seat="{{$seatCode}}" data-price="{{$seatPrice}}" data-type="{{$seatType}}" {{($isBooked||$isMaintenance)?'disabled':''}}>
                            {{$seatCode}}
                        </button>


                        @endforeach

                    </div>

                    <span class="row-label">{{$hang}}</span>

                </div>

                @endforeach

            </div>


            <div class="seat-note">

                <div>
                    <span class="seat-demo empty"></span>
                    Ghế trống
                </div>

                <div>
                    <span class="seat-demo selected"></span>
                    Đang chọn
                </div>

                <div>
                    <span class="seat-demo booked"></span>
                    Đã bán
                </div>

                <div>
                    <span class="seat-demo maintenance"></span>
                    Bảo trì
                </div>

            </div>

        </section>



        <aside class="ticket-summary">


            <div class="summary-box">

                <p class="summary-title">
                    Loại ghế
                </p>


                @php
                $types=$seatsByRow->flatten()->groupBy(function($seat){
                return $seat->loaiGhe->ten_loai ?? 'Thường';
                });
                @endphp


                @foreach($types as $type=>$items)

                <div class="seat-type-item">

                    <span>
                        <i></i>
                        {{ucfirst($type)}}
                    </span>

                    <strong>
                        {{number_format($items->first()->gia ?? $suatChieu->gia_ve,0,',','.')}}đ
                    </strong>

                </div>

                @endforeach

            </div>



            <div class="summary-box">

                <p class="summary-title">
                    Vé đã chọn
                </p>


                <div class="summary-row">
                    <span>Số ghế</span>
                    <strong id="seatCount">0 ghế</strong>
                </div>


                <div class="summary-row">
                    <span>Vị trí</span>
                    <strong id="selectedSeatText">Chưa chọn</strong>
                </div>


                <div id="selectedList" class="selected-list"></div>


            </div>


            <div class="total-box">

                <p>
                    Tổng tiền ghế
                </p>

                <strong id="totalPrice">
                    0đ
                </strong>

            </div>


            <form id="seatSaleForm" action="{{route('staff.ban-ve.food',$suatChieu->id)}}" method="POST">

                @csrf


                <div id="selectedSeatsContainer"></div>


                <button type="submit" class="btn-food">

                    TIẾP TỤC CHỌN ĐỒ ĂN

                </button>


            </form>


        </aside>


    </div>
</div>
@endsection


@push('styles')
<style>
    .dat-ve-page {
        color: #fff;
        animation: fadeIn .3s ease;
    }

    .booking-layout {
        display: grid;
        grid-template-columns: 320px 1fr 350px;
        gap: 24px;
    }

    .movie-info-card,
    .seat-container,
    .ticket-summary {
        background: #121212;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 24px;
        padding: 20px;
    }

    .movie-poster {
        width: 100%;
        border-radius: 16px;
    }

    .movie-detail {
        margin-top: 18px;
        background: #1a1a1a;
        padding: 18px;
        border-radius: 18px;
    }

    .movie-detail h2 {
        color: #f4c56a;
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 16px;
    }

    .info-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .info-line span {
        color: #888;
    }

    .screen-area {
        text-align: center;
        margin-bottom: 35px;
    }

    .screen-line {
        width: 80%;
        height: 35px;
        margin: auto;
        border-radius: 50% 50% 0 0;
        background: linear-gradient(180deg, #f4c56a, rgba(217, 154, 50, .15));
    }

    .screen-area p {
        color: #f4c56a;
        font-weight: 900;
        letter-spacing: 4px;
    }

    .seat-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .row-label {
        width: 30px;
        text-align: center;
        color: #d99a32;
        font-weight: 900;
    }

    .seat-list {
        display: flex;
        gap: 8px;
    }

    .seat-button {
        width: 46px;
        height: 42px;
        border: none;
        border-radius: 9px 9px 5px 5px;
        background: #2a2a2a;
        color: #fff;
        font-weight: 900;
        font-size: 11px;
        cursor: pointer;
        transition: .2s;
    }

    .seat-button:hover:not(:disabled) {
        transform: translateY(-3px);
        border: 1px solid #f4c56a;
    }

    .seat-button.selected {
        background: linear-gradient(135deg, #f4c56a, #d99a32);
        color: #222;
    }

    .seat-button.booked,
    .seat-button.maintenance {
        background: #080808;
        color: #555;
        cursor: not-allowed;
    }

    .seat-note {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 35px;
        flex-wrap: wrap;
    }

    .seat-note div {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #aaa;
    }

    .seat-demo {
        width: 18px;
        height: 18px;
        border-radius: 5px;
    }

    .seat-demo.empty {
        background: #2a2a2a;
    }

    .seat-demo.selected {
        background: #d99a32;
    }

    .seat-demo.booked {
        background: #111;
    }

    .seat-demo.maintenance {
        background: #666;
    }

</style>
@endpush
@push('styles')
<style>
    .summary-box {
        background: #1a1a1a;
        border-radius: 18px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .summary-title {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        font-weight: 900;
        margin-bottom: 15px;
    }

    .seat-type-item,
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .seat-type-item i {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #d99a32;
        margin-right: 8px;
    }

    .selected-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }

    .selected-seat {
        background: #d99a32;
        color: #222;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 900;
    }

    .total-box {
        background: rgba(217, 154, 50, .12);
        border: 1px solid rgba(217, 154, 50, .5);
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        margin-bottom: 18px;
    }

    .total-box p {
        color: #aaa;
        font-size: 12px;
    }

    .total-box strong {
        font-size: 30px;
        color: #f4c56a;
    }

    .btn-food,
    .btn-confirm {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 14px;
        font-weight: 900;
        cursor: pointer;
        margin-bottom: 10px;
    }

    .btn-food {
        background: #f4c56a;
        color: #222;
    }

    .btn-confirm {
        background: linear-gradient(135deg, #d99a32, #f4c56a);
        color: #222;
    }

    .btn-food:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    @media(max-width:1200px) {

        .booking-layout {
            grid-template-columns: 1fr;
        }

    }

</style>
@endpush


@push('scripts')
<script>
document.addEventListener("DOMContentLoaded",function(){

    const form=document.getElementById("seatSaleForm");
    const selectedContainer=document.getElementById("selectedSeatsContainer");
    const selectedText=document.getElementById("selectedSeatText");
    const seatCount=document.getElementById("seatCount");
    const totalPrice=document.getElementById("totalPrice");
    const selectedList=document.getElementById("selectedList");

    const seatButtons=document.querySelectorAll(".seat-button:not(:disabled)");

    let selectedSeats=new Map();


    console.log("JS CHON GHE DA CHAY");
    console.log("So ghe tim thay:",seatButtons.length);



    function formatMoney(value){

        return Number(value).toLocaleString("vi-VN")+"đ";

    }



    function render(){

        selectedContainer.innerHTML="";

        let seats=[];
        let total=0;


        selectedSeats.forEach((price,seat)=>{

            seats.push(seat);

            total+=price;

        });



        if(seats.length>0){

            let input=document.createElement("input");

            input.type="hidden";

            input.name="seats";

            input.value=seats.join(",");


            selectedContainer.appendChild(input);

        }



        seatCount.innerText=
            seats.length+" ghế";



        selectedText.innerText=
            seats.length
            ?
            seats.join(", ")
            :
            "Chưa chọn";



        totalPrice.innerText=
            formatMoney(total);



        selectedList.innerHTML=
            seats.length
            ?
            seats.map(seat=>`

                <span class="selected-seat">
                    ${seat}
                </span>

            `).join("")
            :
            "Chưa chọn ghế nào";


    }





    seatButtons.forEach(button=>{


        button.addEventListener("click",function(){


            let seat=this.dataset.seat;


            let price=Number(this.dataset.price);



            console.log("Chon ghe:",seat,"Gia:",price);



            if(selectedSeats.has(seat)){


                selectedSeats.delete(seat);


                this.classList.remove("selected");


            }
            else{


                selectedSeats.set(seat,price);


                this.classList.add("selected");


            }



            render();


        });


    });




    form.addEventListener("submit",function(e){


        if(selectedSeats.size===0){


            e.preventDefault();


            alert("Vui lòng chọn ít nhất một ghế.");


            return;


        }



    });




    render();



});
</script>
@endpush
