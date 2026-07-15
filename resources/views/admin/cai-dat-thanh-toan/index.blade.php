@extends('layouts.admin')

@section('title', 'Cài đặt cổng thanh toán - CineHome')
@section('page-title', 'Cài đặt cổng thanh toán')
@section('page-subtitle', 'Quản lý phương thức thanh toán hiển thị trong luồng đặt vé')

@section('content')
@php
    $settingValue = fn (string $key, string $default = '') => old($key, $settings[$key]->gia_tri ?? $default);
    $isEnabled = fn (string $key, string $default = '0') => (string) $settingValue($key, $default) === '1';

    $methodCards = [
        [
            'key' => 'payment_cash_enabled',
            'title' => 'Thanh toán tại quầy',
            'description' => 'Dành cho giao dịch bán vé trực tiếp tại rạp.',
            'icon' => 'fa-cash-register',
            'tag' => 'Offline',
            'default' => '1',
        ],
        [
            'key' => 'payment_demo_enabled',
            'title' => 'Giả lập online',
            'description' => 'Phục vụ kiểm thử nhanh luồng đặt vé trực tuyến.',
            'icon' => 'fa-flask',
            'tag' => 'Test',
            'default' => '1',
        ],
        [
            'key' => 'payment_vnpay_enabled',
            'title' => 'VNPAY',
            'description' => 'Thanh toán qua cổng VNPAY Sandbox/Production.',
            'icon' => 'fa-credit-card',
            'tag' => 'Gateway',
            'default' => '0',
        ],
        [
            'key' => 'payment_momo_enabled',
            'title' => 'MoMo',
            'description' => 'Bật ví MoMo khi đã có đủ thông tin đối tác.',
            'icon' => 'fa-wallet',
            'tag' => 'Wallet',
            'default' => '0',
        ],
    ];

    $enabledCount = collect($methodCards)->filter(fn ($method) => $isEnabled($method['key'], $method['default']))->count();
    $vnpayReady = filled($settingValue('payment_vnpay_tmn_code')) && filled($settingValue('payment_vnpay_hash_secret'));
    $momoReady = filled($settingValue('payment_momo_partner_code')) && filled($settingValue('payment_momo_access_key')) && filled($settingValue('payment_momo_secret_key'));
@endphp

<form method="POST" action="{{ route('admin.cai-dat-thanh-toan.update') }}" class="payment-settings payment-page">
    @csrf
    @method('PATCH')

    @if (session('success'))
        <div class="payment-alert is-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="payment-alert is-danger">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Chưa thể lưu cấu hình</strong>
                <span>Vui lòng kiểm tra lại các trường được đánh dấu trong biểu mẫu.</span>
            </div>
        </div>
    @endif

    <section class="payment-hero-panel">
        <div class="payment-hero-copy">
            <span class="payment-kicker">
                <i class="fa-solid fa-shield-halved"></i>
                Trung tâm thanh toán
            </span>
            <h2>Cấu hình cổng thanh toán CineHome</h2>
            <p>
                Chọn phương thức cho khách hàng, nhập thông tin kết nối cổng thanh toán và lưu lại để áp dụng cho luồng đặt vé.
            </p>

            <div class="payment-hero-actions">
                <a href="#payment-methods" class="payment-btn is-secondary">
                    <i class="fa-solid fa-sliders"></i>
                    Chọn phương thức
                </a>
                <button type="submit" class="payment-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu cấu hình
                </button>
            </div>
        </div>

        <aside class="payment-status-summary">
            <div class="payment-status-card">
                <span>Đang bật</span>
                <strong>{{ $enabledCount }}/{{ count($methodCards) }}</strong>
                <small>phương thức thanh toán</small>
            </div>
            <div class="payment-status-mini">
                <span class="{{ $vnpayReady ? 'is-ready' : 'is-missing' }}">
                    <i class="fa-solid {{ $vnpayReady ? 'fa-circle-check' : 'fa-circle-dot' }}"></i>
                    VNPAY {{ $vnpayReady ? 'sẵn sàng' : 'thiếu khóa' }}
                </span>
                <span class="{{ $momoReady ? 'is-ready' : 'is-missing' }}">
                    <i class="fa-solid {{ $momoReady ? 'fa-circle-check' : 'fa-circle-dot' }}"></i>
                    MoMo {{ $momoReady ? 'sẵn sàng' : 'thiếu khóa' }}
                </span>
            </div>
        </aside>
    </section>

    <section id="payment-methods" class="payment-section">
        <div class="payment-section-head">
            <div>
                <span class="payment-kicker">Phương thức khả dụng</span>
                <h3>Bật/tắt kênh thanh toán</h3>
                <p>Chỉ các phương thức đang bật mới xuất hiện ở bước thanh toán của khách hàng.</p>
            </div>
        </div>

        <div class="payment-method-grid">
            @foreach ($methodCards as $method)
                @php($enabled = $isEnabled($method['key'], $method['default']))
                <label class="payment-method-card {{ $enabled ? 'is-enabled' : '' }}">
                    <input type="hidden" name="{{ $method['key'] }}" value="0">
                    <input
                        type="checkbox"
                        name="{{ $method['key'] }}"
                        value="1"
                        class="payment-switch-input"
                        @checked($enabled)
                    >
                    <span class="payment-method-icon">
                        <i class="fa-solid {{ $method['icon'] }}"></i>
                    </span>
                    <span class="payment-method-copy">
                        <span class="payment-method-row">
                            <strong>{{ $method['title'] }}</strong>
                            <em>{{ $method['tag'] }}</em>
                        </span>
                        <small>{{ $method['description'] }}</small>
                        <b class="payment-method-status">{{ $enabled ? 'Đang bật' : 'Đang tắt' }}</b>
                    </span>
                    <span class="payment-switch" aria-hidden="true">
                        <span></span>
                    </span>
                </label>
            @endforeach
        </div>
    </section>

    <div class="payment-config-layout">
        <section class="payment-gateway-panel">
            <div class="payment-gateway-head">
                <span><i class="fa-solid fa-credit-card"></i></span>
                <div>
                    <strong>Thông tin VNPAY</strong>
                    <small>{{ $vnpayReady ? 'Đã có đủ thông tin cơ bản' : 'Nhập TMN Code và Hash Secret để kích hoạt ổn định' }}</small>
                </div>
                <em class="{{ $vnpayReady ? 'is-ready' : 'is-missing' }}">{{ $vnpayReady ? 'Sẵn sàng' : 'Cần cấu hình' }}</em>
            </div>

            <div class="payment-field-grid">
                <label class="payment-field">
                    <span>TMN Code</span>
                    <input
                        name="payment_vnpay_tmn_code"
                        value="{{ $settingValue('payment_vnpay_tmn_code') }}"
                        class="admin-input"
                        placeholder="Ví dụ: ABC12345"
                        autocomplete="off"
                    >
                    @error('payment_vnpay_tmn_code')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="payment-field">
                    <span>Hash Secret</span>
                    <div class="payment-secret-field">
                        <input
                            id="payment_vnpay_hash_secret"
                            type="password"
                            name="payment_vnpay_hash_secret"
                            value="{{ $settingValue('payment_vnpay_hash_secret') }}"
                            class="admin-input"
                            placeholder="Nhập khóa bảo mật VNPAY"
                            autocomplete="new-password"
                        >
                        <button type="button" class="payment-secret-toggle" data-secret-target="payment_vnpay_hash_secret" aria-label="Hiện hoặc ẩn Hash Secret">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('payment_vnpay_hash_secret')
                        <small>{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </section>

        <section class="payment-gateway-panel">
            <div class="payment-gateway-head">
                <span><i class="fa-solid fa-wallet"></i></span>
                <div>
                    <strong>Thông tin MoMo</strong>
                    <small>{{ $momoReady ? 'Đã có đủ Partner Code, Access Key và Secret Key' : 'Hoàn tất 3 trường để ví MoMo hoạt động' }}</small>
                </div>
                <em class="{{ $momoReady ? 'is-ready' : 'is-missing' }}">{{ $momoReady ? 'Sẵn sàng' : 'Cần cấu hình' }}</em>
            </div>

            <div class="payment-field-grid">
                <label class="payment-field">
                    <span>Partner Code</span>
                    <input
                        name="payment_momo_partner_code"
                        value="{{ $settingValue('payment_momo_partner_code') }}"
                        class="admin-input"
                        placeholder="Mã đối tác MoMo"
                        autocomplete="off"
                    >
                    @error('payment_momo_partner_code')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="payment-field">
                    <span>Access Key</span>
                    <input
                        name="payment_momo_access_key"
                        value="{{ $settingValue('payment_momo_access_key') }}"
                        class="admin-input"
                        placeholder="Access Key"
                        autocomplete="off"
                    >
                    @error('payment_momo_access_key')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="payment-field">
                    <span>Secret Key</span>
                    <div class="payment-secret-field">
                        <input
                            id="payment_momo_secret_key"
                            type="password"
                            name="payment_momo_secret_key"
                            value="{{ $settingValue('payment_momo_secret_key') }}"
                            class="admin-input"
                            placeholder="Nhập khóa bí mật MoMo"
                            autocomplete="new-password"
                        >
                        <button type="button" class="payment-secret-toggle" data-secret-target="payment_momo_secret_key" aria-label="Hiện hoặc ẩn Secret Key">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('payment_momo_secret_key')
                        <small>{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </section>

        <aside class="payment-guide-panel">
            <span class="payment-kicker">Gợi ý vận hành</span>
            <h3>Trước khi bật cổng thật</h3>
            <ul>
                <li><i class="fa-solid fa-check"></i> Kiểm tra thông tin Sandbox/Production đúng môi trường.</li>
                <li><i class="fa-solid fa-check"></i> Chạy thử một giao dịch nhỏ trước khi mở cho khách.</li>
                <li><i class="fa-solid fa-check"></i> Không chia sẻ Hash Secret hoặc Secret Key trong tin nhắn nội bộ.</li>
            </ul>
            <div class="payment-guide-note">
                <i class="fa-solid fa-lock"></i>
                <span>Các khóa bí mật chỉ nên cấp cho tài khoản quản trị hệ thống.</span>
            </div>
        </aside>
    </div>

    <div class="payment-savebar">
        <div>
            <strong>Cấu hình sẽ áp dụng ngay sau khi lưu</strong>
            <span>Khách đặt vé sẽ chỉ thấy các phương thức đang bật.</span>
        </div>
        <div class="payment-savebar-actions">
            <button type="reset" class="payment-btn is-ghost">
                <i class="fa-solid fa-rotate-left"></i>
                Hoàn tác
            </button>
            <button type="submit" class="payment-btn is-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu cấu hình
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-secret-target]').forEach(function(button) {
            button.addEventListener('click', function() {
                const input = document.getElementById(button.dataset.secretTarget);

                if (!input) {
                    return;
                }

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                button.querySelector('i')?.classList.toggle('fa-eye', !isHidden);
                button.querySelector('i')?.classList.toggle('fa-eye-slash', isHidden);
            });
        });

        document.querySelectorAll('.payment-switch-input').forEach(function(input) {
            input.addEventListener('change', function() {
                const card = input.closest('.payment-method-card');
                const status = card?.querySelector('.payment-method-status');

                card?.classList.toggle('is-enabled', input.checked);

                if (status) {
                    status.textContent = input.checked ? 'Đang bật' : 'Đang tắt';
                }
            });
        });
    });
</script>
@endpush
