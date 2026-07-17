@extends('layouts.admin')

@section('page-title', 'Chi tiết thông báo đẩy')

@section('content')
@php
    $typeMeta = [
        'info' => ['label' => 'Thông tin', 'icon' => 'fa-circle-info', 'class' => 'is-info'],
        'success' => ['label' => 'Thành công', 'icon' => 'fa-circle-check', 'class' => 'is-success'],
        'warning' => ['label' => 'Cảnh báo', 'icon' => 'fa-triangle-exclamation', 'class' => 'is-warning'],
        'promo' => ['label' => 'Khuyến mãi', 'icon' => 'fa-gift', 'class' => 'is-promo'],
        'system' => ['label' => 'Hệ thống', 'icon' => 'fa-gear', 'class' => 'is-system'],
    ];

    $audienceMeta = [
        'all' => ['label' => 'Tất cả người dùng', 'icon' => 'fa-globe', 'class' => 'is-all'],
        'user' => ['label' => 'Khách hàng thường', 'icon' => 'fa-user', 'class' => 'is-user'],
        'vip' => ['label' => 'Khách hàng VIP', 'icon' => 'fa-crown', 'class' => 'is-vip'],
        'staff' => ['label' => 'Nhân viên', 'icon' => 'fa-user-tie', 'class' => 'is-staff'],
        'admin' => ['label' => 'Quản trị viên', 'icon' => 'fa-user-shield', 'class' => 'is-admin'],
        'nguoi_dung_cu_the' => ['label' => 'Người dùng cụ thể', 'icon' => 'fa-user-pen', 'class' => 'is-specific'],
        'khach_hang' => ['label' => 'Khách hàng', 'icon' => 'fa-user', 'class' => 'is-user'],
        'nhan_vien' => ['label' => 'Nhân viên', 'icon' => 'fa-user-tie', 'class' => 'is-staff'],
        'quan_tri_vien' => ['label' => 'Quản trị viên', 'icon' => 'fa-user-shield', 'class' => 'is-admin'],
    ];

    $statusMeta = [
        'da_gui' => ['label' => 'Đã gửi', 'icon' => 'fa-paper-plane', 'class' => 'is-sent'],
        'chua_gui' => ['label' => 'Chưa gửi', 'icon' => 'fa-clock', 'class' => 'is-pending'],
    ];

    $type = $typeMeta[$thongBaoPush->loai] ?? ['label' => ucfirst($thongBaoPush->loai), 'icon' => 'fa-bell', 'class' => 'is-system'];
    $audience = $audienceMeta[$thongBaoPush->doi_tuong_nhan] ?? ['label' => $thongBaoPush->doi_tuong_nhan, 'icon' => 'fa-users', 'class' => 'is-all'];
    $status = $statusMeta[$thongBaoPush->trang_thai] ?? ['label' => $thongBaoPush->trang_thai, 'icon' => 'fa-clock', 'class' => 'is-pending'];
    $recipientCollection = collect($nguoiNhanList);
    $recipientTotal = $recipientCount ?? $recipientCollection->count();
@endphp

<div class="push-admin-page">
    <section class="push-hero push-hero--detail">
        <div class="push-hero-content">
            <a href="{{ route('admin.thong-bao-push.index') }}" class="push-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại danh sách
            </a>
            <span class="push-kicker">
                <i class="fa-solid fa-receipt"></i>
                Mã thông báo #{{ $thongBaoPush->id }}
            </span>
            <h2>{{ $thongBaoPush->tieu_de }}</h2>
            <p>Kiểm tra nội dung, nhóm nhận và thời điểm gửi của thông báo đẩy.</p>
            <div class="push-hero-meta">
                <span><i class="fa-solid {{ $type['icon'] }}"></i>{{ $type['label'] }}</span>
                <span><i class="fa-solid {{ $audience['icon'] }}"></i>{{ $audience['label'] }}</span>
                <span><i class="fa-solid fa-users"></i>{{ number_format($recipientTotal) }} người nhận</span>
            </div>
        </div>

        <form action="{{ route('admin.thong-bao-push.destroy', $thongBaoPush) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="push-danger-btn">
                <i class="fa-solid fa-trash"></i>
                Xóa thông báo
            </button>
        </form>
    </section>

    <div class="push-detail-grid">
        <section class="push-panel push-detail-message">
            <div class="push-panel-head">
                <div>
                    <span>Nội dung</span>
                    <h3>Bản xem thông báo</h3>
                    <p>Nội dung bên dưới là thông tin người dùng sẽ nhận được trên hệ thống.</p>
                </div>
                <span class="push-status {{ $status['class'] }}">
                    <i class="fa-solid {{ $status['icon'] }}"></i>
                    {{ $status['label'] }}
                </span>
            </div>

            <article class="push-message-shell">
                <span class="push-preview-icon">
                    <i class="fa-solid fa-bell"></i>
                </span>
                <div>
                    <div class="push-message-top">
                        <strong>CineHome</strong>
                        <small>{{ $thongBaoPush->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <h3>{{ $thongBaoPush->tieu_de }}</h3>
                    <p>{{ $thongBaoPush->noi_dung }}</p>
                    <span class="push-chip {{ $type['class'] }}">
                        <i class="fa-solid {{ $type['icon'] }}"></i>
                        {{ $type['label'] }}
                    </span>
                </div>
            </article>
        </section>

        <aside class="push-panel push-detail-side">
            <div class="push-panel-head">
                <div>
                    <span>Thông tin gửi</span>
                    <h3>Chi tiết vận hành</h3>
                </div>
            </div>

            <div class="push-detail-facts">
                <article>
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Người tạo</span>
                    <strong>{{ $thongBaoPush->nguoiTao->ho_ten ?? 'Hệ thống' }}</strong>
                </article>
                <article>
                    <i class="fa-solid {{ $audience['icon'] }}"></i>
                    <span>Đối tượng nhận</span>
                    <strong>{{ $audience['label'] }}</strong>
                </article>
                <article>
                    <i class="fa-solid fa-clock"></i>
                    <span>Thời gian gửi</span>
                    <strong>{{ optional($thongBaoPush->thoi_gian_gui)->format('d/m/Y H:i') ?? 'Chưa gửi' }}</strong>
                </article>
                <article>
                    <i class="fa-solid fa-users"></i>
                    <span>Số người nhận</span>
                    <strong>{{ number_format($recipientTotal) }}</strong>
                </article>
            </div>
        </aside>
    </div>

    @if ($thongBaoPush->doi_tuong_nhan === 'nguoi_dung_cu_the' && $recipientCollection->count() > 0)
        <section class="push-panel">
            <div class="push-panel-head">
                <div>
                    <span>Người nhận</span>
                    <h3>Danh sách người dùng cụ thể</h3>
                    <p>Những tài khoản đã được gắn với thông báo này.</p>
                </div>
                <strong>{{ number_format($recipientCollection->count()) }} người</strong>
            </div>

            <div class="push-table-wrap">
                <table class="push-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recipientCollection as $nguoiDung)
                            <tr>
                                <td data-label="ID"><span class="push-code">#{{ $nguoiDung->id }}</span></td>
                                <td data-label="Họ tên"><strong>{{ $nguoiDung->ho_ten }}</strong></td>
                                <td data-label="Email">{{ $nguoiDung->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <div class="push-form-actions">
        <a href="{{ route('admin.thong-bao-push.index') }}" class="push-soft-btn">
            <i class="fa-solid fa-list"></i>
            Danh sách thông báo
        </a>
        <form action="{{ route('admin.thong-bao-push.destroy', $thongBaoPush) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="push-danger-btn">
                <i class="fa-solid fa-trash"></i>
                Xóa thông báo
            </button>
        </form>
    </div>
</div>
@endsection
