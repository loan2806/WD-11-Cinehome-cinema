@extends('layouts.admin')

@section('page-title', 'Tạo thông báo đẩy mới')

@section('content')
@php
    $typeGuide = [
        'info' => ['label' => 'Thông tin', 'icon' => 'fa-circle-info', 'class' => 'is-info', 'hint' => 'Cập nhật chung, nhắc lịch hoặc thông tin vận hành.'],
        'success' => ['label' => 'Thành công', 'icon' => 'fa-circle-check', 'class' => 'is-success', 'hint' => 'Xác nhận thao tác, đơn vé hoặc ưu đãi đã hoàn tất.'],
        'warning' => ['label' => 'Cảnh báo', 'icon' => 'fa-triangle-exclamation', 'class' => 'is-warning', 'hint' => 'Lưu ý quan trọng cần người dùng đọc ngay.'],
        'promo' => ['label' => 'Khuyến mãi', 'icon' => 'fa-gift', 'class' => 'is-promo', 'hint' => 'Thông báo voucher, combo hoặc chiến dịch giảm giá.'],
        'system' => ['label' => 'Hệ thống', 'icon' => 'fa-gear', 'class' => 'is-system', 'hint' => 'Thông báo bảo trì hoặc thay đổi hệ thống.'],
    ];

    $audienceGuide = [
        'all' => ['label' => 'Tất cả người dùng', 'icon' => 'fa-globe', 'class' => 'is-all'],
        'user' => ['label' => 'Khách hàng thường', 'icon' => 'fa-user', 'class' => 'is-user'],
        'vip' => ['label' => 'Khách hàng VIP', 'icon' => 'fa-crown', 'class' => 'is-vip'],
        'staff' => ['label' => 'Nhân viên', 'icon' => 'fa-user-tie', 'class' => 'is-staff'],
        'admin' => ['label' => 'Quản trị viên', 'icon' => 'fa-user-shield', 'class' => 'is-admin'],
        'nguoi_dung_cu_the' => ['label' => 'Người dùng cụ thể', 'icon' => 'fa-user-pen', 'class' => 'is-specific'],
    ];

    $selectedType = old('loai', 'info');
    $selectedAudience = old('doi_tuong_nhan', 'all');
@endphp

<div class="push-admin-page">
    <section class="push-hero push-hero--compose">
        <div class="push-hero-content">
            <a href="{{ route('admin.thong-bao-push.index') }}" class="push-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                Danh sách thông báo
            </a>
            <span class="push-kicker">
                <i class="fa-solid fa-pen-nib"></i>
                Soạn thông báo
            </span>
            <h2>Tạo thông báo đẩy mới</h2>
            <p>Viết nội dung ngắn gọn, chọn đúng nhóm nhận và kiểm tra phần xem trước trước khi gửi.</p>
        </div>
        <div class="push-hero-badge">
            <i class="fa-solid fa-paper-plane"></i>
            Gửi ngay sau khi xác nhận
        </div>
    </section>

    @if ($errors->any())
        <div class="push-alert is-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Vui lòng kiểm tra lại thông tin</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.thong-bao-push.store') }}" class="push-compose-grid">
        @csrf

        <section class="push-panel push-compose-main">
            <div class="push-panel-head">
                <div>
                    <span>Nội dung</span>
                    <h3>Thông tin thông báo</h3>
                    <p>Tiêu đề nên ngắn, rõ ý; nội dung nên tập trung vào hành động người dùng cần làm.</p>
                </div>
            </div>

            <div class="push-form-grid">
                <label class="push-field push-field--full">
                    <span>Tiêu đề thông báo <em>*</em></span>
                    <input
                        type="text"
                        name="tieu_de"
                        id="tieu_de"
                        value="{{ old('tieu_de') }}"
                        maxlength="255"
                        class="@error('tieu_de') is-invalid @enderror"
                        placeholder="VD: Voucher cuối tuần dành riêng cho bạn">
                    @error('tieu_de')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="push-field push-field--full">
                    <span>Nội dung thông báo <em>*</em></span>
                    <textarea
                        name="noi_dung"
                        id="noi_dung"
                        rows="6"
                        class="@error('noi_dung') is-invalid @enderror"
                        placeholder="Nhập nội dung hiển thị cho người nhận...">{{ old('noi_dung') }}</textarea>
                    @error('noi_dung')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="push-field">
                    <span>Loại thông báo <em>*</em></span>
                    <select name="loai" id="loai" class="@error('loai') is-invalid @enderror">
                        @foreach ($loaiOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('loai')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="push-field">
                    <span>Đối tượng nhận <em>*</em></span>
                    <select name="doi_tuong_nhan" id="doi_tuong_nhan" class="@error('doi_tuong_nhan') is-invalid @enderror">
                        @foreach ($doiTuongOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedAudience === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('doi_tuong_nhan')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <div id="nguoi_dung_cu_the_wrapper" class="push-specific-box push-field--full {{ $selectedAudience === 'nguoi_dung_cu_the' ? '' : 'is-hidden' }}">
                    <label class="push-field">
                        <span>Chọn người dùng cụ thể <em>*</em></span>
                        <select name="nguoi_dung_cu_the" id="nguoi_dung_cu_the" class="@error('nguoi_dung_cu_the') is-invalid @enderror">
                            <option value="">Chọn người dùng...</option>
                        </select>
                        @error('nguoi_dung_cu_the')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>
                    <p>
                        <i class="fa-solid fa-circle-info"></i>
                        Danh sách sẽ tự tải khi bạn chọn nhóm “Người dùng cụ thể”.
                    </p>
                </div>
            </div>

            <div class="push-type-guide">
                @foreach ($typeGuide as $value => $meta)
                    <article class="push-guide-card {{ $selectedType === $value ? 'is-active' : '' }}" data-type-card="{{ $value }}">
                        <span class="push-chip {{ $meta['class'] }}">
                            <i class="fa-solid {{ $meta['icon'] }}"></i>
                            {{ $meta['label'] }}
                        </span>
                        <p>{{ $meta['hint'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="push-form-actions">
                <a href="{{ route('admin.thong-bao-push.index') }}" class="push-soft-btn">
                    <i class="fa-solid fa-xmark"></i>
                    Hủy
                </a>
                <button type="submit" class="push-primary-btn">
                    <i class="fa-solid fa-paper-plane"></i>
                    Tạo và gửi thông báo
                </button>
            </div>
        </section>

        <aside class="push-compose-side">
            <section class="push-phone-preview">
                <div class="push-phone-top">
                    <span>CineHome</span>
                    <small>vừa xong</small>
                </div>
                <div class="push-preview-card">
                    <span class="push-preview-icon">
                        <i class="fa-solid fa-bell"></i>
                    </span>
                    <div>
                        <strong id="previewTitle">Tiêu đề thông báo của bạn</strong>
                        <p id="previewContent">Nội dung thông báo sẽ hiển thị tại đây.</p>
                        <span id="previewType" class="push-chip {{ $typeGuide[$selectedType]['class'] ?? 'is-info' }}">
                            <i class="fa-solid {{ $typeGuide[$selectedType]['icon'] ?? 'fa-circle-info' }}"></i>
                            {{ $typeGuide[$selectedType]['label'] ?? 'Thông tin' }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="push-panel push-audience-panel">
                <div class="push-panel-head">
                    <div>
                        <span>Người nhận</span>
                        <h3>Quy mô nhóm gửi</h3>
                    </div>
                </div>

                <div class="push-audience-list">
                    @foreach ($audienceGuide as $value => $meta)
                        @php
                            $count = $value === 'nguoi_dung_cu_the'
                                ? ($audienceCounts['all'] ?? 0)
                                : ($audienceCounts[$value] ?? 0);
                        @endphp
                        <article class="{{ $selectedAudience === $value ? 'is-active' : '' }}" data-audience-card="{{ $value }}">
                            <span class="push-chip {{ $meta['class'] }}">
                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                                {{ $meta['label'] }}
                            </span>
                            <strong>{{ number_format($count) }}</strong>
                        </article>
                    @endforeach
                </div>
            </section>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeLabels = @json($typeGuide);
    const audienceLabels = @json($audienceGuide);
    const usersEndpoint = @json(route('admin.thong-bao-push.users-by-role'));
    const selectedSpecificUser = @json((string) old('nguoi_dung_cu_the', ''));

    const titleInput = document.getElementById('tieu_de');
    const contentInput = document.getElementById('noi_dung');
    const typeSelect = document.getElementById('loai');
    const audienceSelect = document.getElementById('doi_tuong_nhan');
    const userWrapper = document.getElementById('nguoi_dung_cu_the_wrapper');
    const userSelect = document.getElementById('nguoi_dung_cu_the');

    const previewTitle = document.getElementById('previewTitle');
    const previewContent = document.getElementById('previewContent');
    const previewType = document.getElementById('previewType');

    const updatePreviewText = () => {
        previewTitle.textContent = titleInput.value.trim() || 'Tiêu đề thông báo của bạn';
        previewContent.textContent = contentInput.value.trim() || 'Nội dung thông báo sẽ hiển thị tại đây.';
    };

    const updateTypePreview = () => {
        const meta = typeLabels[typeSelect.value] || typeLabels.info;
        previewType.className = `push-chip ${meta.class}`;
        previewType.innerHTML = `<i class="fa-solid ${meta.icon}"></i>${meta.label}`;

        document.querySelectorAll('[data-type-card]').forEach((card) => {
            card.classList.toggle('is-active', card.dataset.typeCard === typeSelect.value);
        });
    };

    const updateAudienceCards = () => {
        document.querySelectorAll('[data-audience-card]').forEach((card) => {
            card.classList.toggle('is-active', card.dataset.audienceCard === audienceSelect.value);
        });
    };

    const loadSpecificUsers = async () => {
        const isSpecific = audienceSelect.value === 'nguoi_dung_cu_the';
        userWrapper.classList.toggle('is-hidden', !isSpecific);
        userSelect.required = isSpecific;

        if (!isSpecific) {
            userSelect.value = '';
            return;
        }

        userSelect.disabled = true;
        userSelect.innerHTML = '<option value="">Đang tải danh sách...</option>';

        try {
            const response = await fetch(`${usersEndpoint}?role=nguoi_dung_cu_the`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            const users = await response.json();

            userSelect.innerHTML = '<option value="">Chọn người dùng...</option>';
            users.forEach((user) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.ho_ten} - ${user.email}`;
                userSelect.appendChild(option);
            });

            if (selectedSpecificUser) {
                userSelect.value = selectedSpecificUser;
            }

            if (!users.length) {
                userSelect.innerHTML = '<option value="">Chưa có người dùng phù hợp</option>';
            }
        } catch (error) {
            userSelect.innerHTML = '<option value="">Không tải được danh sách người dùng</option>';
        } finally {
            userSelect.disabled = false;
        }
    };

    titleInput.addEventListener('input', updatePreviewText);
    contentInput.addEventListener('input', updatePreviewText);
    typeSelect.addEventListener('change', updateTypePreview);
    audienceSelect.addEventListener('change', () => {
        updateAudienceCards();
        loadSpecificUsers();
    });

    updatePreviewText();
    updateTypePreview();
    updateAudienceCards();
    loadSpecificUsers();
});
</script>
@endpush
