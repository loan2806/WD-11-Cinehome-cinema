@extends('layouts.user')

@section('title', 'Chọn Ghế')

@push('styles')
<style>
    .screen-thumb {
        background: #ccc;
        height: 30px;
        width: 80%;
        margin: 0 auto 30px;
        border-radius: 5px 5px 0 0;
        box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
        transform: perspective(200px) rotateX(-5deg);
    }
    .seat {
        width: 35px;
        height: 35px;
        margin: 5px;
        background-color: #444;
        border-radius: 5px 5px 0 0;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        color: transparent;
        transition: all 0.2s;
        border: 1px solid #666;
    }
    .seat:hover:not(.booked) {
        background-color: #666;
        color: #fff;
    }
    .seat.selected {
        background-color: #e50914; /* Màu đỏ cinehome */
        color: #fff;
        border-color: #e50914;
    }
    .seat.booked {
        background-color: #222;
        cursor: not-allowed;
        border-color: #111;
        opacity: 0.5;
    }
    .seat-row-label {
        width: 30px;
        display: inline-block;
        text-align: center;
        font-weight: bold;
        color: #aaa;
    }
    .seat-legend .seat {
        cursor: default;
    }
</style>
@endpush

@section('content')
<div class="container py-5 mt-5">
    <div class="row">
        <!-- Thông tin phim -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-white border-secondary h-100">
                <img src="{{ $suatChieu->movie->poster ?? 'https://via.placeholder.com/300x450?text=Poster' }}" class="card-img-top" alt="Poster" style="height: 300px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title text-warning font-bold text-xl">{{ $suatChieu->movie->title }}</h5>
                    <ul class="list-unstyled text-gray-300 mt-3">
                        <li class="mb-2"><i class="fa fa-map-marker-alt text-danger w-20"></i> Rạp: <strong>{{ $suatChieu->cinema->name }}</strong></li>
                        <li class="mb-2"><i class="fa fa-door-open text-info w-20"></i> Phòng chiếu: <strong>{{ $suatChieu->room_name }}</strong></li>
                        <li class="mb-2"><i class="fa fa-calendar-alt text-primary w-20"></i> Ngày: <strong>{{ \Carbon\Carbon::parse($suatChieu->show_date)->format('d/m/Y') }}</strong></li>
                        <li class="mb-2"><i class="fa fa-clock text-success w-20"></i> Giờ chiếu: <strong>{{ \Carbon\Carbon::parse($suatChieu->show_time)->format('H:i') }}</strong></li>
                        <li class="mb-2"><i class="fa fa-money-bill-wave text-warning w-20"></i> Giá vé: <strong>{{ number_format($suatChieu->price, 0, ',', '.') }} VNĐ</strong></li>
                    </ul>
                    <hr class="border-secondary">
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span>Số ghế chọn: <strong id="count-seats">0</strong></span>
                        <span>Tổng tiền: <strong id="total-price" class="text-warning text-xl">0</strong> <span class="text-warning">VNĐ</span></span>
                    </div>
                    
                    <form id="booking-form" action="#" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="suat_chieu_id" value="{{ $suatChieu->id }}">
                        <input type="hidden" name="ghe_duoc_chon" id="ghe_duoc_chon" value="">
                        <button type="button" id="btn-dat-ve" class="btn btn-danger w-100 font-bold py-2 disabled">TIẾP TỤC ĐẶT VÉ</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sơ đồ ghế -->
        <div class="col-md-8">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h4 class="text-white mb-4 font-bold">MÀN HÌNH CHÍNH</h4>
                    <div class="screen-thumb text-dark text-center fw-bold" style="font-size: 12px; line-height: 30px;">MÀN HÌNH</div>
                    
                    <div class="seat-map-container overflow-auto pb-4">
                        <div class="d-inline-block text-left">
                            @foreach($hangGhe as $hang)
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <span class="seat-row-label">{{ $hang }}</span>
                                    @for($i = 1; $i <= $soCot; $i++)
                                        @php
                                            $maGhe = $hang . $i;
                                            $daDat = in_array($maGhe, $gheDaDat);
                                        @endphp
                                        <div class="seat {{ $daDat ? 'booked' : '' }}" data-ghe="{{ $maGhe }}">{{ $i }}</div>
                                    @endfor
                                    <span class="seat-row-label">{{ $hang }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="seat-legend d-flex justify-content-center gap-4 mt-4 pt-3 border-top border-secondary">
                        <div class="d-flex align-items-center">
                            <div class="seat m-0 me-2" style="background-color: #444; border-color: #666;"></div> <span class="text-white">Ghế trống</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="seat m-0 me-2 selected" style="background-color: #e50914; border-color: #e50914;"></div> <span class="text-white">Ghế đang chọn</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="seat m-0 me-2 booked" style="background-color: #222; border-color: #111;"></div> <span class="text-white">Ghế đã đặt</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 text-start">
                <a href="{{ route('dat_ve.chon_phim', ['rap_id' => $suatChieu->cinema_id]) }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Quay lại chọn phim</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const seats = document.querySelectorAll('.seat:not(.booked):not(.seat-legend .seat)');
        const countSeatsEl = document.getElementById('count-seats');
        const totalPriceEl = document.getElementById('total-price');
        const btnDatVe = document.getElementById('btn-dat-ve');
        const inputGheDuocChon = document.getElementById('ghe_duoc_chon');
        
        const pricePerSeat = {{ $suatChieu->price }};
        let selectedSeats = [];

        seats.forEach(seat => {
            seat.addEventListener('click', function() {
                const maGhe = this.getAttribute('data-ghe');
                
                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    selectedSeats = selectedSeats.filter(ghe => ghe !== maGhe);
                } else {
                    this.classList.add('selected');
                    selectedSeats.push(maGhe);
                }
                
                updateBookingInfo();
            });
        });

        function updateBookingInfo() {
            const count = selectedSeats.length;
            countSeatsEl.innerText = count;
            
            const total = count * pricePerSeat;
            totalPriceEl.innerText = total.toLocaleString('vi-VN');
            
            inputGheDuocChon.value = selectedSeats.join(',');
            
            if (count > 0) {
                btnDatVe.classList.remove('disabled');
            } else {
                btnDatVe.classList.add('disabled');
            }
        }
        
        btnDatVe.addEventListener('click', function() {
            if (selectedSeats.length > 0) {
                alert('Chức năng thanh toán đang được phát triển. Bạn đã chọn ghế: ' + selectedSeats.join(', '));
                // document.getElementById('booking-form').submit();
            }
        });
    });
</script>
@endsection
