@extends('layouts.admin')

@section('title','Chọn đồ ăn tại quầy - '.$suatChieu->phim->ten_phim)

@section('content')

<div class="min-h-screen bg-black text-white pt-28 pb-20 px-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-4xl font-black uppercase">
                Chọn <span class="text-yellow-400">Đồ Ăn</span>
            </h1>
            <p class="text-gray-400 mt-2">
                {{ $suatChieu->phim->ten_phim }} -
                {{ $suatChieu->rapChieuPhim->ten_rap }}
            </p>
        </div>


        <div class="grid lg:grid-cols-[1fr_300px] gap-4 items-start">

            {{-- MENU ĐỒ ĂN --}}
            <section>

                @foreach($menu as $category)

                <h2 class="text-xl font-black text-yellow-400 uppercase mb-4 mt-6">
                    {{ $category['category'] }}
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">

                    @foreach($category['foods'] as $food)

                    <div class="food-card bg-zinc-900 border border-white/10 rounded-2xl p-4 hover:border-yellow-400 transition">

                        <div class="h-32 bg-black rounded-xl flex items-center justify-center overflow-hidden">
                            @if(!empty($food['image']))
                            <img src="{{ asset('storage/'.$food['image']) }}" class="h-full object-contain hover:scale-110 transition">
                            @endif
                        </div>

                        <h3 class="mt-3 text-center font-bold text-sm">
                            {{ $food['name'] }}
                        </h3>

                        <p class="text-center text-yellow-400 font-black mt-2">
                            {{ number_format($food['price']) }}đ
                        </p>

                        <div class="flex gap-2 mt-3">
                            <input type="number" min="1" value="1" class="qty w-14 bg-black border border-white/10 rounded-lg text-center">

                            <button type="button" class="add-food flex-1 bg-yellow-400 text-black rounded-xl font-black text-sm" data-id="{{ $food['id'] }}" data-name="{{ $food['name'] }}" data-price="{{ $food['price'] }}">
                                THÊM
                            </button>
                        </div>

                    </div>

                    @endforeach

                </div>

                @endforeach

            </section>


            {{-- GIỎ HÀNG --}}
            <aside>

                <div class="sticky top-28 bg-zinc-900 border border-white/10 rounded-3xl p-5">

                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-black text-yellow-400">
                            🛒 Đơn hàng
                        </h2>

                        <span id="cartCount" class="bg-yellow-400 text-black px-3 py-1 rounded-full font-black text-sm">
                            0
                        </span>
                    </div>


                    <div id="cart" class="mt-5 space-y-3 max-h-[420px] overflow-y-auto">
                        <p class="text-gray-500 text-sm">
                            Chưa có món
                        </p>
                    </div>


                    <div class="border-t border-white/10 mt-5 pt-4 space-y-3">

                        <div class="flex justify-between">
                            <span>Số lượng</span>
                            <b id="totalQty">0</b>
                        </div>

                        <div class="flex justify-between">
                            <span>Tổng đồ ăn</span>
                            <b id="total" class="text-yellow-400">
                                0đ
                            </b>
                        </div>

                    </div>


                    <form id="checkoutForm" action="{{ route('staff.ban-ve.checkout',$suatChieu->id) }}" method="POST">

                        @csrf

                        <input type="hidden" name="seats" value="{{ $selectedSeats->implode(',') }}">

                        <input type="hidden" id="foodCartInput" name="food_cart">

                        <input type="hidden" name="clear_cart_key" value="staff_food_cart_{{auth()->id()}}_{{$suatChieu->id}}">

                        <button class="w-full mt-5 bg-yellow-400 text-black py-3 rounded-xl font-black hover:bg-yellow-300 transition">
                            TIẾP TỤC THANH TOÁN →
                        </button>

                    </form>

                </div>

            </aside>

        </div>

    </div>
</div>

@endsection
@push('scripts')
<script>
    const cartKey = 'staff_food_cart_{{auth()->id()}}_{{$suatChieu->id}}';

    let cart = JSON.parse(localStorage.getItem(cartKey) || '{}');



    function renderCart() {

        let html = '';

        let total = 0;

        let totalQty = 0;



        Object.values(cart).forEach(item => {


            let price = Number(item.price) || 0;

            let qty = Number(item.qty) || 0;



            if (qty <= 0) {
                return;
            }



            total += price * qty;

            totalQty += qty;



            html += `

<div class="bg-black border border-white/10 rounded-xl p-3">

<div class="font-bold text-sm mb-3">

${item.name}

</div>


<div class="flex justify-between items-center">


<div class="flex items-center gap-2">


<button type="button"
class="minus-food bg-red-500 w-7 h-7 rounded-lg font-bold"
data-id="${item.id}">
-
</button>


<span class="font-bold">
${qty}
</span>


<button type="button"
class="plus-food bg-green-500 w-7 h-7 rounded-lg font-bold"
data-id="${item.id}">
+
</button>


</div>


<span class="text-yellow-400 font-bold text-sm">

${(price*qty).toLocaleString('vi-VN')}đ

</span>


</div>

</div>

`;

        });



        document.getElementById('cart').innerHTML =
            html || '<p class="text-gray-500 text-sm">Chưa có món</p>';



        document.getElementById('total').innerText =
            total.toLocaleString('vi-VN') + 'đ';



        document.getElementById('totalQty').innerText =
            totalQty;



        document.getElementById('cartCount').innerText =
            totalQty;



        localStorage.setItem(
            cartKey
            , JSON.stringify(cart)
        );


    }





    document.addEventListener('click', function(e) {



        let addBtn = e.target.closest('.add-food');



        if (addBtn) {


            let card = addBtn.closest('.food-card');


            let id = addBtn.dataset.id;


            let qty = Number(
                card.querySelector('.qty').value
            ) || 1;



            if (!cart[id]) {


                cart[id] = {

                    id: id,

                    name: addBtn.dataset.name,

                    price: Number(addBtn.dataset.price) || 0,

                    qty: 0

                };


            }



            cart[id].qty += qty;


            renderCart();


            return;


        }




        let plusBtn = e.target.closest('.plus-food');


        if (plusBtn) {


            let id = plusBtn.dataset.id;



            if (cart[id]) {


                cart[id].qty++;


                renderCart();


            }


            return;


        }




        let minusBtn = e.target.closest('.minus-food');


        if (minusBtn) {


            let id = minusBtn.dataset.id;



            if (cart[id]) {


                cart[id].qty--;



                if (cart[id].qty <= 0) {

                    delete cart[id];

                }


                renderCart();


            }


        }



    });





    document
        .getElementById('checkoutForm')
        .addEventListener('submit', function() {


            document
                .getElementById('foodCartInput')
                .value = JSON.stringify(
                    Object.values(cart)
                );



        });



    renderCart();

</script>
@endpush