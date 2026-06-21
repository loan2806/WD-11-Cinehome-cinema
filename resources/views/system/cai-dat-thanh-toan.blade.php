@extends('layouts.system')

@section('title', 'Cài đặt cổng thanh toán - CineHome')
@section('page-title', 'Cài đặt cổng thanh toán')

@section('content')
@if (session('success'))
    <div class="mb-5 rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-4 text-green-200">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('system.payments.update') }}" class="space-y-6">
    @csrf
    @method('PATCH')

    <section class="rounded-2xl border border-white/10 bg-[#101010] p-6">
        <div class="mb-5">
            <h2 class="m-0 text-xl font-black text-white">Phương thức khả dụng</h2>
            <p class="mt-1 text-sm text-gray-400">Luồng đặt vé sẽ chỉ hiển thị các phương thức đang bật tại đây.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ([
                'payment_cash_enabled' => 'Thanh toán tại quầy',
                'payment_demo_enabled' => 'Thanh toán giả lập online',
                'payment_vnpay_enabled' => 'VNPAY',
                'payment_momo_enabled' => 'MoMo',
            ] as $key => $label)
                <label class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-4">
                    <span>
                        <span class="block font-bold text-white">{{ $label }}</span>
                        <span class="text-xs text-gray-500">{{ (string) ($settings[$key]->gia_tri ?? '0') === '1' ? 'Đang bật' : 'Đang tắt' }}</span>
                    </span>
                    <input type="hidden" name="{{ $key }}" value="0">
                    <input
                        type="checkbox"
                        name="{{ $key }}"
                        value="1"
                        class="h-5 w-5"
                        @checked((string) ($settings[$key]->gia_tri ?? '0') === '1')
                    >
                </label>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-[#101010] p-6">
            <h2 class="m-0 text-xl font-black text-white">Thông tin VNPAY</h2>
            <div class="mt-5 grid gap-4">
                <label>
                    <span class="mb-2 block text-sm font-bold text-[#f4c56a]">TMN Code</span>
                    <input name="payment_vnpay_tmn_code" value="{{ $settings['payment_vnpay_tmn_code']->gia_tri ?? '' }}" class="admin-input">
                </label>
                <label>
                    <span class="mb-2 block text-sm font-bold text-[#f4c56a]">Hash Secret</span>
                    <input type="password" name="payment_vnpay_hash_secret" value="{{ $settings['payment_vnpay_hash_secret']->gia_tri ?? '' }}" class="admin-input">
                </label>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-[#101010] p-6">
            <h2 class="m-0 text-xl font-black text-white">Thông tin MoMo</h2>
            <div class="mt-5 grid gap-4">
                <label>
                    <span class="mb-2 block text-sm font-bold text-[#f4c56a]">Partner Code</span>
                    <input name="payment_momo_partner_code" value="{{ $settings['payment_momo_partner_code']->gia_tri ?? '' }}" class="admin-input">
                </label>
                <label>
                    <span class="mb-2 block text-sm font-bold text-[#f4c56a]">Access Key</span>
                    <input name="payment_momo_access_key" value="{{ $settings['payment_momo_access_key']->gia_tri ?? '' }}" class="admin-input">
                </label>
                <label>
                    <span class="mb-2 block text-sm font-bold text-[#f4c56a]">Secret Key</span>
                    <input type="password" name="payment_momo_secret_key" value="{{ $settings['payment_momo_secret_key']->gia_tri ?? '' }}" class="admin-input">
                </label>
            </div>
        </div>
    </section>

    <button class="rounded-xl bg-[#d99a32] px-5 py-3 font-black text-[#2b1208] hover:bg-[#f4c56a]" type="submit">
        Lưu cấu hình thanh toán
    </button>
</form>
@endsection
