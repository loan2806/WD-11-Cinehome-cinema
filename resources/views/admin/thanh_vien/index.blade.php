@extends('layouts.admin')

@section('title', 'Thẻ thành viên & Điểm')
@section('page-title', 'Thẻ thành viên & Điểm')
@section('page-subtitle', 'Quản lý khách hàng thân thiết, điểm tích lũy và hạng thành viên')

@section('content')
@php
    $rankStats = [
        ['label' => 'Tổng thành viên', 'value' => $tongThanhVien, 'icon' => 'fa-users', 'tone' => 'is-total'],
        ['label' => 'Member', 'value' => $tongMember, 'icon' => 'fa-user', 'tone' => 'is-member'],
        ['label' => 'Silver', 'value' => $tongSilver, 'icon' => 'fa-medal', 'tone' => 'is-silver'],
        ['label' => 'Gold', 'value' => $tongGold, 'icon' => 'fa-crown', 'tone' => 'is-gold'],
        ['label' => 'Platinum', 'value' => $tongPlatinum, 'icon' => 'fa-gem', 'tone' => 'is-platinum'],
    ];

    $rankMeta = [
        'member' => ['label' => 'Member', 'icon' => 'fa-user', 'tone' => 'is-member'],
        'silver' => ['label' => 'Silver', 'icon' => 'fa-medal', 'tone' => 'is-silver'],
        'gold' => ['label' => 'Gold', 'icon' => 'fa-crown', 'tone' => 'is-gold'],
        'platinum' => ['label' => 'Platinum', 'icon' => 'fa-gem', 'tone' => 'is-platinum'],
    ];
@endphp

<div class="member-admin-page">
    <section class="member-hero">
        <div>
            <span class="member-kicker">
                <i class="fa-solid fa-crown"></i>
                Loyalty Center
            </span>
            <h2>Thẻ thành viên & điểm thưởng</h2>
            <p>Theo dõi hạng thành viên, điểm khả dụng và lịch sử tích lũy để chăm sóc khách hàng tốt hơn.</p>
            <div class="member-hero-actions">
                <a href="{{ route('admin.vouchers.index') }}" class="member-primary-btn">
                    <i class="fa-solid fa-gift"></i>
                    Quản lý voucher
                </a>
                <a href="{{ route('admin.khach-hang.index') }}" class="member-ghost-btn">
                    <i class="fa-solid fa-users-gear"></i>
                    Tài khoản khách hàng
                </a>
            </div>
        </div>

        <div class="member-rank-guide">
            <span>Quy tắc hạng</span>
            <strong>0 - 499 Member</strong>
            <strong>500+ Silver</strong>
            <strong>1000+ Gold</strong>
            <strong>2000+ Platinum</strong>
        </div>
    </section>

    <section class="member-stat-grid">
        @foreach($rankStats as $stat)
            <article class="member-stat-card {{ $stat['tone'] }}">
                <span><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                <div>
                    <small>{{ $stat['label'] }}</small>
                    <strong>{{ number_format($stat['value']) }}</strong>
                </div>
            </article>
        @endforeach
    </section>

    <section class="member-panel">
        <div class="member-panel-head">
            <div>
                <span class="member-kicker">Bộ lọc</span>
                <h3>Tìm nhanh thành viên</h3>
                <p>Lọc theo mã thẻ, tên, email, số điện thoại hoặc hạng thành viên.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.thanh-vien.index') }}" class="member-filter">
            <label class="member-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}"
                    placeholder="Tìm mã thành viên, tên, email hoặc số điện thoại...">
            </label>

            <select name="hang_thanh_vien" class="member-select">
                <option value="">Tất cả hạng</option>
                <option value="member" @selected(request('hang_thanh_vien') === 'member')>Member</option>
                <option value="silver" @selected(request('hang_thanh_vien') === 'silver')>Silver</option>
                <option value="gold" @selected(request('hang_thanh_vien') === 'gold')>Gold</option>
                <option value="platinum" @selected(request('hang_thanh_vien') === 'platinum')>Platinum</option>
            </select>

            <button type="submit" class="member-filter-btn">
                <i class="fa-solid fa-filter"></i>
                Lọc dữ liệu
            </button>

            @if(request()->hasAny(['tim_kiem', 'hang_thanh_vien']))
                <a href="{{ route('admin.thanh-vien.index') }}" class="member-reset-btn">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>
    </section>

    <section class="member-panel">
        <div class="member-panel-head">
            <div>
                <span class="member-kicker">Danh sách</span>
                <h3>Thành viên thân thiết</h3>
                <p>Ưu tiên khách có tổng điểm cao để dễ nhận diện nhóm VIP và chăm sóc lại.</p>
            </div>
            <span class="member-result-count">
                <i class="fa-solid fa-id-card"></i>
                {{ number_format($thanhViens->total()) }} thẻ
            </span>
        </div>

        <div class="member-table-wrap">
            <table class="member-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Mã thành viên</th>
                        <th>Liên hệ</th>
                        <th>Hạng</th>
                        <th>Điểm hiện tại</th>
                        <th>Tổng tích lũy</th>
                        <th>Ngày tham gia</th>
                        <th class="is-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thanhViens as $item)
                        @php
                            $user = $item->nguoiDung;
                            $name = $user?->ho_ten ?: 'Khách chưa cập nhật';
                            $initial = mb_strtoupper(mb_substr($name, 0, 1));
                            $rank = $rankMeta[$item->hang_thanh_vien] ?? $rankMeta['member'];
                        @endphp
                        <tr>
                            <td data-label="Khách hàng">
                                <div class="member-profile">
                                    <span class="member-avatar {{ $rank['tone'] }}">{{ $initial }}</span>
                                    <div>
                                        <strong>{{ $name }}</strong>
                                        <small>{{ $user?->email ?? 'Chưa có email' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Mã thành viên">
                                <span class="member-code">{{ $item->ma_thanh_vien }}</span>
                            </td>
                            <td data-label="Liên hệ">
                                <span class="member-contact">
                                    <i class="fa-solid fa-phone"></i>
                                    {{ $user?->so_dien_thoai ?? 'Chưa cập nhật' }}
                                </span>
                            </td>
                            <td data-label="Hạng">
                                <span class="member-rank-chip {{ $rank['tone'] }}">
                                    <i class="fa-solid {{ $rank['icon'] }}"></i>
                                    {{ $rank['label'] }}
                                </span>
                            </td>
                            <td data-label="Điểm hiện tại">
                                <span class="member-point is-current">{{ number_format($item->diem_hien_tai) }}</span>
                            </td>
                            <td data-label="Tổng tích lũy">
                                <span class="member-point">{{ number_format($item->tong_diem_tich_luy) }}</span>
                            </td>
                            <td data-label="Ngày tham gia">
                                <span class="member-date">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $item->ngay_tham_gia?->format('d/m/Y') ?? '---' }}
                                </span>
                            </td>
                            <td data-label="Thao tác" class="is-right">
                                <a href="{{ route('admin.thanh-vien.show', $item) }}" class="member-action-btn">
                                    <i class="fa-solid fa-eye"></i>
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="member-empty">
                                    <i class="fa-regular fa-id-card"></i>
                                    <h3>Chưa có thẻ thành viên</h3>
                                    <p>Khi khách hàng đăng ký hoặc phát sinh điểm, dữ liệu sẽ hiển thị tại đây.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="member-pagination">
            {{ $thanhViens->links() }}
        </div>
    </section>
</div>
@endsection
