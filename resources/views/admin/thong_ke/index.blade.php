@extends('layouts.admin')

@push('styles')
<style>
.stats-page { padding: 0; }

/* === HEADER === */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.page-header h2 {
    font-size: 1.4rem;
    font-weight: 900;
    color: var(--white);
    margin: 0;
}

.page-header p {
    font-size: 0.8rem;
    color: var(--gray-text);
    margin: 4px 0 0;
}

/* === FILTER BAR === */
.filter-bar {
    background: linear-gradient(135deg, rgba(30, 30, 35, 0.95), rgba(22, 22, 27, 0.98));
    border: 1px solid rgba(217, 154, 50, 0.2);
    border-radius: 14px;
    padding: 8px 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    flex-wrap: nowrap;
    width: 100%;
    box-sizing: border-box;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.04);
    border-radius: 10px;
    padding: 4px 10px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.25s ease;
    flex-shrink: 0;
}

.filter-group:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(217, 154, 50, 0.3);
    box-shadow: 0 0 15px rgba(217, 154, 50, 0.1);
}

.filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gold-main);
    white-space: nowrap;
    display: flex;
    align-items: center;
}

.filter-group label i {
    font-size: 0.8rem;
    opacity: 0.9;
}

/* === CUSTOM DROPDOWN === */
.custom-select {
    position: relative;
    min-width: 180px;
    user-select: none;
}

.custom-select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    padding: 6px 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.custom-select-trigger span {
    color: #e5e5e5;
    font-size: 0.8rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
}

.custom-select-trigger i {
    font-size: 0.7rem;
    color: var(--gold-main);
    transition: transform 0.2s;
}

.custom-select.open .custom-select-trigger i {
    transform: rotate(180deg);
}

.custom-select-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #1a1a1f;
    border: 1px solid rgba(217, 154, 50, 0.3);
    border-radius: 10px;
    padding: 6px;
    z-index: 1000;
    display: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    max-height: 250px;
    overflow-y: auto;
    min-width: 200px;
}

.custom-select.open .custom-select-dropdown {
    display: block;
    animation: dropDown 0.2s ease;
}

@keyframes dropDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-select-option {
    padding: 8px 12px;
    font-size: 0.8rem;
    color: #ccc;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.custom-select-option:hover {
    background: rgba(217, 154, 50, 0.15);
    color: var(--white);
}

.custom-select-option.selected {
    background: linear-gradient(135deg, var(--gold-main), var(--gold-light));
    color: #000;
    font-weight: 600;
}

.custom-select-option.selected:hover {
    background: linear-gradient(135deg, var(--gold-main), var(--gold-light));
    color: #000;
}

/* Scrollbar for dropdown */
.custom-select-dropdown::-webkit-scrollbar {
    width: 4px;
}

.custom-select-dropdown::-webkit-scrollbar-track {
    background: transparent;
}

.custom-select-dropdown::-webkit-scrollbar-thumb {
    background: rgba(217, 154, 50, 0.3);
    border-radius: 2px;
}

.filter-input {
    background: transparent;
    border: none;
    color: #e5e5e5;
    font-size: 0.8rem;
    padding: 6px 4px;
    cursor: pointer;
    width: 105px;
    transition: color 0.2s;
}

.filter-input:hover { color: var(--white); }
.filter-input:focus { outline: none; color: var(--white); }

.filter-input::-webkit-calendar-picker-indicator {
    filter: invert(0.7);
    cursor: pointer;
    padding: 2px;
    border-radius: 4px;
    transition: all 0.2s;
}

.filter-input::-webkit-calendar-picker-indicator:hover {
    filter: invert(1);
    background: rgba(217, 154, 50, 0.2);
}

.filter-sep {
    color: rgba(255, 255, 255, 0.25);
    font-size: 0.75rem;
    display: flex;
    align-items: center;
}

.period-tabs {
    display: flex;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    padding: 2px;
    gap: 2px;
}

.period-tab {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.6);
    background: transparent;
    cursor: pointer;
    transition: all 0.25s ease;
}

.period-tab:hover { color: var(--gold-main); background: rgba(217, 154, 50, 0.1); }
.period-tab.active {
    background: linear-gradient(135deg, var(--gold-main), var(--gold-light));
    color: #000;
    box-shadow: 0 2px 10px rgba(217, 154, 50, 0.4);
}

.filter-actions { display: flex; gap: 6px; margin-left: auto; flex-shrink: 0; }

.filter-actions .btn-admin,
.filter-actions .btn-admin-outline {
    padding: 6px 12px;
    font-size: 0.78rem;
    border-radius: 8px;
}

/* === KPI GRID === */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}

.kpi-card {
    background: var(--black-card);
    border: 1px solid rgba(217, 154, 50, 0.12);
    border-radius: 12px;
    padding: 16px;
    position: relative;
    overflow: hidden;
    transition: all 0.25s;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--gold-main), var(--gold-light));
}

.kpi-card:hover {
    border-color: var(--gold-main);
    transform: translateY(-2px);
}

.kpi-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.kpi-value {
    font-size: 1.4rem;
    font-weight: 900;
    color: var(--white);
    margin-bottom: 4px;
}

.kpi-label {
    font-size: 0.7rem;
    color: var(--gray-text);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* === MAIN GRID === */
.main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
}

.main-grid.full { grid-template-columns: 1fr; }

/* === PANEL CARD === */
.panel {
    background: var(--black-card);
    border: 1px solid rgba(217, 154, 50, 0.12);
    border-radius: 12px;
    overflow: hidden;
}

.panel-header {
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.panel-header h3 {
    font-size: 0.9rem;
    font-weight: 800;
    color: var(--white);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.panel-header h3 i { color: var(--gold-main); }
.panel-header .badge { font-size: 0.7rem; color: var(--gray-text); }

.panel-body { padding: 16px; }

/* === CHART CONTAINER === */
.chart-wrap {
    height: 300px;
    position: relative;
}

.chart-wrap.sm { height: 220px; }

/* === TOP LIST === */
.top-list { list-style: none; padding: 0; margin: 0; }
.top-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.top-list li:last-child { border-bottom: none; }

.rank-badge {
    width: 24px; height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 900;
    background: rgba(217, 154, 50, 0.12);
    color: var(--gold-main);
    flex-shrink: 0;
}
.rank-badge.gold { background: linear-gradient(135deg, #f7b84b, #d99a32); color: #000; }
.rank-badge.silver { background: rgba(156, 163, 175, 0.15); color: #9ca3af; }

.top-info { flex: 1; min-width: 0; }
.top-info strong { display: block; font-size: 0.85rem; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.top-info small { font-size: 0.7rem; color: var(--gray-text); }

.top-amount { text-align: right; flex-shrink: 0; }
.top-amount strong { font-size: 0.85rem; color: var(--gold-main); font-weight: 800; display: block; }
.top-amount small { font-size: 0.65rem; color: var(--gray-text); }

/* === PAYMENT === */
.pay-list { display: flex; flex-direction: column; gap: 8px; }
.pay-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: var(--black-soft);
    border-radius: 8px;
}
.pay-item .icon {
    width: 32px; height: 32px;
    border-radius: 6px;
    background: rgba(217, 154, 50, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}
.pay-item .icon i { color: var(--gold-main); font-size: 0.8rem; }
.pay-item .info { flex: 1; }
.pay-item .info strong { font-size: 0.8rem; color: var(--white); }
.pay-item .info small { font-size: 0.65rem; color: var(--gray-text); }
.pay-item .amount { text-align: right; }
.pay-item .amount strong { font-size: 0.85rem; color: var(--gold-main); font-weight: 800; }
.pay-item .amount small { font-size: 0.65rem; color: var(--gray-text); }

/* === BAR STATS === */
.bar-stat { margin-bottom: 12px; }
.bar-stat:last-child { margin-bottom: 0; }
.bar-stat-label { display: flex; justify-content: space-between; margin-bottom: 6px; }
.bar-stat-label span:first-child { font-size: 0.8rem; color: var(--white); }
.bar-stat-label span:last-child { font-size: 0.8rem; color: var(--gold-main); font-weight: 700; }
.bar-track { height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--gold-main), var(--gold-light)); transition: width 0.5s ease; }

/* === MINI STATS === */
.mini-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 16px; }
.mini-stat { background: var(--black-soft); border-radius: 8px; padding: 12px; text-align: center; }
.mini-stat .val { font-size: 1.1rem; font-weight: 900; color: var(--gold-main); }
.mini-stat .lbl { font-size: 0.65rem; color: var(--gray-text); margin-top: 4px; text-transform: uppercase; }

/* === RESPONSIVE === */
@media (max-width: 1400px) {
    .filter-bar { gap: 6px; padding: 8px 12px; }
    .period-tab { padding: 5px 10px; font-size: 0.7rem; }
}
@media (max-width: 1200px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .filter-bar { gap: 6px; }
}
@media (max-width: 992px) {
    .main-grid { grid-template-columns: 1fr; }
    .filter-bar { flex-wrap: wrap; }
}
@media (max-width: 600px) {
    .kpi-grid { grid-template-columns: 1fr; }
    .filter-bar { gap: 8px; }
}
</style>
@endpush

@section('content')
<div class="stats-page">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h2><i class="fas fa-chart-line me-2" style="color: var(--gold-main);"></i>Thống kê doanh thu</h2>
            <p>Tổng quan hoạt động kinh doanh CineHome</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET">
        {{-- Hidden inputs để giữ lại filter khi dropdown submit --}}
        <input type="hidden" name="from" value="{{ $from ? Carbon\Carbon::parse($from)->toDateString() : '' }}">
        <input type="hidden" name="to" value="{{ $to ? Carbon\Carbon::parse($to)->toDateString() : '' }}">
        <input type="hidden" name="period_type" id="periodTypeInput" value="{{ $periodType ?? 'day' }}">

        <div class="filter-bar">
            {{-- Period Tabs --}}
            <div class="filter-group">
                <div class="period-tabs">
                    <button type="button" class="period-tab {{ ($periodType ?? 'day') == 'day' ? 'active' : '' }}" onclick="setPeriod('day')">Ngày</button>
                    <button type="button" class="period-tab {{ ($periodType ?? 'day') == 'month' ? 'active' : '' }}" onclick="setPeriod('month')">Tháng</button>
                    <button type="button" class="period-tab {{ ($periodType ?? 'day') == 'quarter' ? 'active' : '' }}" onclick="setPeriod('quarter')">Quý</button>
                    <button type="button" class="period-tab {{ ($periodType ?? 'day') == 'year' ? 'active' : '' }}" onclick="setPeriod('year')">Năm</button>
                </div>
            </div>

            {{-- Date Range --}}
            <div class="filter-group">
                <label><i class="fas fa-calendar-alt"></i></label>
                <input class="filter-input" type="date" name="from" value="{{ $from ? Carbon\Carbon::parse($from)->toDateString() : '' }}" title="Từ ngày">
                <span class="filter-sep"><i class="fas fa-arrow-right"></i></span>
                <input class="filter-input" type="date" name="to" value="{{ $to ? Carbon\Carbon::parse($to)->toDateString() : '' }}" title="Đến ngày">
            </div>

            {{-- Movie Filter --}}
            <div class="filter-group">
                <label><i class="fas fa-film"></i></label>
                <div class="custom-select" data-name="phim_id">
                    <div class="custom-select-trigger" onclick="toggleSelect(this)">
                        <span>{{ ($phimId ?? '') ? ($movies[$phimId] ?? 'Tất cả phim') : 'Tất cả phim' }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="custom-select-dropdown">
                        <div class="custom-select-option {{ ($phimId ?? '') == '' ? 'selected' : '' }}" data-value="" onclick="selectOption(this)">Tất cả phim</div>
                        @foreach($movies ?? [] as $id => $name)
                            <div class="custom-select-option {{ ($phimId ?? '') == $id ? 'selected' : '' }}" data-value="{{ $id }}" onclick="selectOption(this)">{{ $name }}</div>
                        @endforeach
                    </div>
                    <input type="hidden" name="phim_id" value="{{ $phimId ?? '' }}">
                </div>
            </div>

            {{-- Room Filter --}}
            <div class="filter-group">
                <label><i class="fas fa-door-open"></i></label>
                <div class="custom-select" data-name="phong_chieu_id">
                    <div class="custom-select-trigger" onclick="toggleSelect(this)">
                        <span>{{ ($phongChieuId ?? '') ? ($rooms[$phongChieuId] ?? 'Tất cả phòng') : 'Tất cả phòng' }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="custom-select-dropdown">
                        <div class="custom-select-option {{ ($phongChieuId ?? '') == '' ? 'selected' : '' }}" data-value="" onclick="selectOption(this)">Tất cả phòng</div>
                        @foreach($rooms ?? [] as $id => $name)
                            <div class="custom-select-option {{ ($phongChieuId ?? '') == $id ? 'selected' : '' }}" data-value="{{ $id }}" onclick="selectOption(this)">{{ $name }}</div>
                        @endforeach
                    </div>
                    <input type="hidden" name="phong_chieu_id" value="{{ $phongChieuId ?? '' }}">
                </div>
            </div>

            {{-- Actions --}}
            <div class="filter-actions">
                <button type="submit" class="btn-admin"><i class="fas fa-search me-1"></i>Lọc</button>
                <a href="{{ route('admin.thong-ke.export-excel', request()->query()) }}" class="btn-admin-outline"><i class="fas fa-download me-1"></i>Xuất Excel</a>
            </div>
        </div>
    </form>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(247, 184, 75, 0.12);">
                <i class="fas fa-coins" style="color: #f7b84b;"></i>
            </div>
            <div class="kpi-value">{{ number_format((float)($kpi['total_revenue'] ?? 0), 0, ',', '.') }}đ</div>
            <div class="kpi-label">Tổng doanh thu</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(34, 197, 94, 0.12);">
                <i class="fas fa-ticket" style="color: #22c55e;"></i>
            </div>
            <div class="kpi-value">{{ number_format((float)($kpi['ticket_revenue'] ?? 0), 0, ',', '.') }}đ</div>
            <div class="kpi-label">Doanh thu vé</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(168, 85, 247, 0.12);">
                <i class="fas fa-burger" style="color: #a855f7;"></i>
            </div>
            <div class="kpi-value">{{ number_format((float)($kpi['combo_revenue'] ?? 0), 0, ',', '.') }}đ</div>
            <div class="kpi-label">Doanh thu combo</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.12);">
                <i class="fas fa-cookie-bite" style="color: #3b82f6;"></i>
            </div>
            <div class="kpi-value">{{ number_format((float)($kpi['snack_revenue'] ?? 0), 0, ',', '.') }}đ</div>
            <div class="kpi-label">Đồ ăn & Nước</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(168, 85, 247, 0.12);">
                <i class="fas fa-users" style="color: #a855f7;"></i>
            </div>
            <div class="kpi-value">{{ number_format((int)($kpi['tickets_sold'] ?? 0)) }}</div>
            <div class="kpi-label">Vé đã bán</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(236, 72, 153, 0.12);">
                <i class="fas fa-receipt" style="color: #ec4899;"></i>
            </div>
            <div class="kpi-value">{{ number_format((int)($kpi['total_invoices'] ?? 0)) }}</div>
            <div class="kpi-label">Tổng hóa đơn</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(14, 165, 233, 0.12);">
                <i class="fas fa-film" style="color: #0ea5e9;"></i>
            </div>
            <div class="kpi-value">{{ number_format((int)($kpi['total_showtimes'] ?? 0)) }}</div>
            <div class="kpi-label">Suất chiếu</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(249, 115, 22, 0.12);">
                <i class="fas fa-ticket-simple" style="color: #f97316;"></i>
            </div>
            <div class="kpi-value">{{ number_format((int)($kpi['vouchers_used'] ?? 0)) }}</div>
            <div class="kpi-label">Voucher sử dụng</div>
        </div>
    </div>

    {{-- Main Charts --}}
    <div class="main-grid">
        {{-- Line Chart --}}
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-chart-area"></i>Doanh thu theo thời gian</h3>
                <span class="badge">Biểu đồ đường</span>
            </div>
            <div class="panel-body">
                <div class="chart-wrap">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Pie Chart + Stats --}}
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-chart-pie"></i>Cơ cấu doanh thu</h3>
            </div>
            <div class="panel-body">
                <div class="chart-wrap sm">
                    <canvas id="pieChart"></canvas>
                </div>
                <div class="mini-stats">
                    <div class="mini-stat">
                        <div class="val">{{ $kpi['total_revenue'] > 0 ? round($kpi['ticket_revenue'] / $kpi['total_revenue'] * 100) : 0 }}%</div>
                        <div class="lbl">Vé</div>
                    </div>
                    <div class="mini-stat">
                        <div class="val">{{ $kpi['total_revenue'] > 0 ? round($kpi['combo_revenue'] / $kpi['total_revenue'] * 100) : 0 }}%</div>
                        <div class="lbl">Combo</div>
                    </div>
                    <div class="mini-stat">
                        <div class="val">{{ $kpi['total_revenue'] > 0 ? round($kpi['snack_revenue'] / $kpi['total_revenue'] * 100) : 0 }}%</div>
                        <div class="lbl">Đồ ăn</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Lists Row --}}
    <div class="main-grid">
        {{-- Top Films --}}
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-trophy"></i>Top phim doanh thu cao</h3>
                <span class="badge">Top 5</span>
            </div>
            <div class="panel-body" style="padding: 12px 16px;">
                @if(!empty($topFilms) && count($topFilms) > 0)
                <ul class="top-list">
                    @foreach($topFilms as $i => $film)
                    <li>
                        <span class="rank-badge {{ $i == 0 ? 'gold' : ($i == 1 ? 'silver' : '') }}">{{ $i + 1 }}</span>
                        <div class="top-info">
                            <strong>{{ $film['ten_phim'] ?? $film['movie'] ?? 'N/A' }}</strong>
                            <small>{{ number_format((int)($film['tickets_sold'] ?? 0)) }} vé</small>
                        </div>
                        <div class="top-amount">
                            <strong>{{ number_format((float)($film['total_revenue'] ?? 0), 0, ',', '.') }}đ</strong>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <p style="text-align: center; color: var(--gray-text); padding: 30px;">Chưa có dữ liệu</p>
                @endif
            </div>
        </div>

        {{-- Revenue by Room --}}
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-door-open"></i>Doanh thu theo phòng</h3>
            </div>
            <div class="panel-body">
                @if(!empty($revenueByRoom) && count($revenueByRoom) > 0)
                    @php $maxRoom = max(array_column($revenueByRoom, 'total_revenue')); @endphp
                    @foreach($revenueByRoom as $room)
                    <div class="bar-stat">
                        <div class="bar-stat-label">
                            <span>{{ $room['ten_phong'] ?? 'N/A' }}</span>
                            <span>{{ number_format((float)($room['total_revenue'] ?? 0), 0, ',', '.') }}đ</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $maxRoom > 0 ? (($room['total_revenue'] ?? 0) / $maxRoom * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                @else
                <p style="text-align: center; color: var(--gray-text); padding: 30px;">Chưa có dữ liệu</p>
                @endif
            </div>
        </div>
    </div>

    {{-- More Stats --}}
    <div class="main-grid">
        {{-- Revenue by Seat Type --}}
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-chair"></i>Doanh thu theo loại ghế</h3>
            </div>
            <div class="panel-body">
                @if(!empty($revenueBySeatType) && count($revenueBySeatType) > 0)
                    @php $maxSeat = max(array_column($revenueBySeatType, 'total_revenue')); @endphp
                    @foreach($revenueBySeatType as $seat)
                    <div class="bar-stat">
                        <div class="bar-stat-label">
                            <span>{{ $seat['ten_loai'] ?? 'N/A' }}</span>
                            <span>{{ number_format((float)($seat['total_revenue'] ?? 0), 0, ',', '.') }}đ</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $maxSeat > 0 ? (($seat['total_revenue'] ?? 0) / $maxSeat * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                @else
                <p style="text-align: center; color: var(--gray-text); padding: 30px;">Chưa có dữ liệu</p>
                @endif
            </div>
        </div>

        {{-- Payment Methods --}}
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-credit-card"></i>Phương thức thanh toán</h3>
            </div>
            <div class="panel-body">
                @if(!empty($paymentMethods) && count($paymentMethods) > 0)
                <div class="pay-list">
                    @foreach($paymentMethods as $method)
                    <div class="pay-item">
                        <div class="icon"><i class="fas fa-wallet"></i></div>
                        <div class="info">
                            <strong>{{ $method['label'] ?? 'N/A' }}</strong>
                            <small>{{ number_format((int)($method['count'] ?? 0)) }} giao dịch</small>
                        </div>
                        <div class="amount">
                            <strong>{{ number_format((float)($method['total_revenue'] ?? 0), 0, ',', '.') }}đ</strong>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p style="text-align: center; color: var(--gray-text); padding: 30px;">Chưa có dữ liệu</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Voucher Stats --}}
    <div class="panel">
        <div class="panel-header">
            <h3><i class="fas fa-ticket"></i>Thống kê Voucher</h3>
        </div>
        <div class="panel-body">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                <div style="background: var(--black-soft); border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--gold-main);">{{ number_format((int)($voucherStats['total_issued'] ?? 0)) }}</div>
                    <div style="font-size: 0.7rem; color: var(--gray-text); text-transform: uppercase; margin-top: 4px;">Đã phát hành</div>
                </div>
                <div style="background: var(--black-soft); border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--gold-main);">{{ number_format((int)($voucherStats['total_used'] ?? 0)) }}</div>
                    <div style="font-size: 0.7rem; color: var(--gray-text); text-transform: uppercase; margin-top: 4px;">Đã sử dụng</div>
                </div>
                <div style="background: var(--black-soft); border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--gold-main);">{{ $voucherStats['usage_rate'] ?? 0 }}%</div>
                    <div style="font-size: 0.7rem; color: var(--gray-text); text-transform: uppercase; margin-top: 4px;">Tỷ lệ sử dụng</div>
                </div>
                <div style="background: var(--black-soft); border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--gold-main);">{{ number_format((float)($voucherStats['total_discount'] ?? 0), 0, ',', '.') }}đ</div>
                    <div style="font-size: 0.7rem; color: var(--gray-text); text-transform: uppercase; margin-top: 4px;">Tiền giảm giá</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Custom dropdown functions
function toggleSelect(trigger) {
    const select = trigger.closest('.custom-select');
    const wasOpen = select.classList.contains('open');
    
    // Close all other dropdowns
    document.querySelectorAll('.custom-select.open').forEach(el => el.classList.remove('open'));
    
    // Toggle this dropdown
    if (!wasOpen) {
        select.classList.add('open');
    }
}

function selectOption(option) {
    const select = option.closest('.custom-select');
    const value = option.dataset.value;
    const name = select.dataset.name;
    
    // Update trigger text
    select.querySelector('.custom-select-trigger span').textContent = option.textContent;
    
    // Update hidden input
    select.querySelector('input[type="hidden"]').value = value;
    
    // Update selected state
    select.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('selected'));
    option.classList.add('selected');
    
    // Close dropdown
    select.classList.remove('open');
    
    // Auto submit form
    select.closest('form').submit();
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-select')) {
        document.querySelectorAll('.custom-select.open').forEach(el => el.classList.remove('open'));
    }
});

// Period selector
function setPeriod(period) {
    document.querySelectorAll('.period-tab').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.period-tab[onclick="setPeriod('${period}')"]`).classList.add('active');
    document.getElementById('periodTypeInput').value = period;
    // Auto submit form when period changes
    document.querySelector('.filter-bar form').submit();
}

// Chart data
const revenueData = {!! json_encode($revenueByTime ?? []) !!};
const chartLabels = revenueData.map(item => item.period || item.label || '');
const ticketChartData = revenueData.map(item => item.ticket_revenue || 0);
const comboChartData = revenueData.map(item => item.combo_revenue || 0);
const snackChartData = revenueData.map(item => item.snack_revenue || 0);

// Line Chart
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [
            {
                label: 'Doanh thu vé',
                data: ticketChartData,
                borderColor: '#f7b84b',
                backgroundColor: 'rgba(247, 184, 75, 0.15)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#f7b84b',
                borderWidth: 2
            },
            {
                label: 'Doanh thu combo',
                data: comboChartData,
                borderColor: '#a855f7',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#a855f7',
                borderWidth: 2
            },
            {
                label: 'Đồ ăn & Nước',
                data: snackChartData,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#3b82f6',
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { color: '#aeb7c8', usePointStyle: true, padding: 16, font: { size: 12, weight: '600' } }
            },
            tooltip: {
                backgroundColor: '#0d1119',
                titleColor: '#fff8ef',
                bodyColor: '#aeb7c8',
                borderColor: 'rgba(217, 154, 50, 0.4)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: ctx => ctx.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                }
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                ticks: { color: '#aeb7c8', maxTicksLimit: 10 }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                ticks: {
                    color: '#aeb7c8',
                    callback: v => v >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (v/1000).toFixed(0) + 'K'
                }
            }
        },
        interaction: { intersect: false, mode: 'index' }
    }
});

// Pie Chart
new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Vé', 'Combo', 'Đồ ăn & Nước'],
        datasets: [{
            data: [
                {{ (float)($kpi['ticket_revenue'] ?? 0) }},
                {{ (float)($kpi['combo_revenue'] ?? 0) }},
                {{ (float)($kpi['snack_revenue'] ?? 0) }}
            ],
            backgroundColor: ['#f7b84b', '#a855f7', '#3b82f6'],
            borderWidth: 0,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: '#aeb7c8', usePointStyle: true, padding: 12, font: { size: 11 } }
            },
            tooltip: {
                backgroundColor: '#0d1119',
                titleColor: '#fff8ef',
                bodyColor: '#aeb7c8',
                borderColor: 'rgba(217, 154, 50, 0.4)',
                borderWidth: 1,
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: ctx => ctx.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                }
            }
        }
    }
});
</script>
@endpush
