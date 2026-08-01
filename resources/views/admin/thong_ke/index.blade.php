@extends('layouts.admin')

@push('styles')
{{-- Flatpickr disabled due to CORS issues - using native date input --}}
<style>
.stats-page { padding: 0; }
.stats-page input[type="date"],
.stats-page input[type="month"] {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #f0f0f0;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 0.82rem;
}
.stats-page input[type="date"]::-webkit-calendar-picker-indicator,
.stats-page input[type="month"]::-webkit-calendar-picker-indicator {
    filter: invert(0.7);
}

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

/* === UTILITY CLASS === */
.d-none {
    display: none !important;
}

/* Ensure hidden elements take no space */
.time-selector-box .d-none {
    display: none !important;
    width: 0 !important;
    min-width: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    height: 0 !important;
    overflow: hidden;
}

/* === FILTER SECTION MODERN === */
.filter-section {
    background: linear-gradient(135deg, rgba(30, 30, 35, 0.95), rgba(22, 22, 27, 0.98));
    border: 1px solid rgba(217, 154, 50, 0.2);
    border-radius: 16px;
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
}

.filter-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-row > * {
    flex-shrink: 0;
}

.filter-row-main {
    margin-bottom: 12px;
}

/* === PERIOD TABS === */
.period-tabs-container {
    display: inline-flex;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 5px;
    gap: 4px;
}

.period-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 20px;
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.25s ease;
    white-space: nowrap;
}

.period-tab:hover {
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.08);
}

.period-tab.active {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: #fff;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(231, 76, 60, 0.35);
}

/* === TIME SELECTOR BOX === */
.time-selector-box {
    display: inline-flex;
    align-items: center;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    padding: 0 4px;
    gap: 6px;
    height: 42px;
    flex-shrink: 0;
    border: none;
    box-sizing: border-box;
}

/* Wrapper for each time input group */
.time-input-wrapper {
    display: flex;
    align-items: center;
    gap: 0;
    min-width: 180px;
    max-width: 180px;
}

.time-input-wrapper.hidden {
    display: none !important;
}

.time-input-wrapper .input-wrapper-inner {
    display: flex;
    align-items: center;
    width: 100%;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0 12px;
    height: 42px;
    transition: all 0.25s ease;
}

.time-input-wrapper .input-wrapper-inner:hover {
    border-color: rgba(217, 154, 50, 0.4);
}

.time-input-wrapper .input-wrapper-inner:focus-within {
    border-color: var(--gold-main);
    box-shadow: 0 0 0 2px rgba(217, 154, 50, 0.15);
}

.time-input-wrapper .input-icon {
    color: var(--gold-main);
    font-size: 0.9rem;
    margin-right: 8px;
    display: flex;
    align-items: center;
}

.time-input-wrapper input {
    flex: 1;
    background: transparent;
    border: none;
    padding: 6px 0;
    color: #f0f0f0;
    font-size: 0.85rem;
    outline: none;
    min-width: 0;
}

.time-input-wrapper input::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.7;
    cursor: pointer;
    margin-left: 4px;
}

.time-input-wrapper input::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

.time-input-wrapper input:focus {
    outline: none;
}

.time-separator {
    color: rgba(255, 255, 255, 0.3);
    font-size: 0.75rem;
}

/* === FILTER DROPDOWN MODERN === */
.filter-dropdown {
    position: relative;
    width: 180px;
    height: 42px;
    flex-shrink: 0;
}

.filter-dropdown.hidden {
    display: none !important;
}

.filter-dropdown-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 14px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    height: 100%;
    width: 100%;
    box-sizing: border-box;
}

.filter-dropdown-trigger:hover {
    border-color: rgba(217, 154, 50, 0.5);
    background: rgba(255, 255, 255, 0.05);
}

.filter-dropdown.open .filter-dropdown-trigger {
    border-color: var(--gold-main);
    box-shadow: 0 0 0 2px rgba(217, 154, 50, 0.15);
    background: rgba(255, 255, 255, 0.08);
}

.filter-dropdown-trigger .filter-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #f0f0f0;
    font-size: 0.85rem;
}

.filter-dropdown-trigger .filter-label i {
    color: var(--gold-main);
    font-size: 0.9rem;
    width: 18px;
    text-align: center;
}

.filter-dropdown-trigger .filter-value {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.8rem;
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 500;
}

.filter-dropdown-trigger .filter-arrow {
    color: var(--gold-main);
    font-size: 0.75rem;
    transition: transform 0.25s ease;
    margin-left: auto;
}

.filter-dropdown.open .filter-arrow {
    transform: rotate(180deg);
}

.filter-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: linear-gradient(145deg, #1e1e24, #16161a);
    border: 1px solid rgba(217, 154, 50, 0.35);
    border-radius: 12px;
    padding: 8px;
    z-index: 1000;
    display: none;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(217, 154, 50, 0.1);
    max-height: 300px;
    overflow: hidden;
}

.filter-dropdown.open .filter-dropdown-menu {
    display: block;
    animation: dropDownFade 0.25s ease;
}

@keyframes dropDownFade {
    from { opacity: 0; transform: translateY(-10px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.filter-search {
    padding: 6px 8px;
    margin-bottom: 8px;
}

.filter-search input {
    width: 100%;
    padding: 10px 12px;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: #f0f0f0;
    font-size: 0.82rem;
    transition: all 0.2s;
}

.filter-search input:focus {
    outline: none;
    border-color: var(--gold-main);
    box-shadow: 0 0 0 2px rgba(217, 154, 50, 0.15);
}

.filter-search input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.filter-options {
    max-height: 220px;
    overflow-y: auto;
    padding-right: 4px;
}

.filter-options::-webkit-scrollbar {
    width: 5px;
}

.filter-options::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
}

.filter-options::-webkit-scrollbar-thumb {
    background: rgba(217, 154, 50, 0.4);
    border-radius: 4px;
}

.filter-options::-webkit-scrollbar-thumb:hover {
    background: rgba(217, 154, 50, 0.6);
}

.filter-option {
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.82rem;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 2px;
}

.filter-option:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.filter-option.selected {
    background: linear-gradient(135deg, rgba(231, 76, 60, 0.25), rgba(192, 57, 43, 0.2));
    color: #fff;
    font-weight: 600;
    border: 1px solid rgba(231, 76, 60, 0.3);
}

.filter-option.selected::before {
    content: '';
    width: 6px;
    height: 6px;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(231, 76, 60, 0.5);
}

/* === ACTION BUTTONS === */
.filter-actions {
    display: flex;
    gap: 10px;
    margin-left: auto;
    align-items: center;
}

.btn-filter-primary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 18px;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 2px 10px rgba(231, 76, 60, 0.35);
    height: 42px;
}

.btn-filter-primary:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
}

.btn-filter-primary:active {
    transform: scale(0.98);
}

.btn-filter-primary i {
    font-size: 0.82rem;
}

.btn-filter-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: #e5e5e5;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
    height: 42px;
}

.btn-filter-secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    color: #fff;
}

.btn-filter-secondary i {
    color: var(--gold-main);
    font-size: 0.82rem;
}

.btn-export {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 14px;
    background: rgba(217, 154, 50, 0.15);
    border: 1px solid rgba(217, 154, 50, 0.3);
    border-radius: 10px;
    color: var(--gold-main);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    height: 42px;
}

.btn-export:hover {
    background: rgba(217, 154, 50, 0.25);
    border-color: var(--gold-main);
    box-shadow: 0 0 15px rgba(217, 154, 50, 0.2);
}

.btn-export i {
    font-size: 0.82rem;
}

/* === FILTER DESCRIPTION === */
.filter-description {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    margin-top: 12px;
}

.filter-description-icon {
    color: var(--gold-main);
    font-size: 0.85rem;
}

.filter-description-text {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.6);
}

.filter-description-text strong {
    color: var(--gold-main);
    font-weight: 600;
}

.filter-description-divider {
    color: rgba(255, 255, 255, 0.3);
    margin: 0 4px;
}

/* === RESPONSIVE === */
@media (max-width: 1400px) {
    .period-tab {
        padding: 8px 14px;
        font-size: 0.78rem;
    }
}

@media (max-width: 1200px) {
    .filter-row {
        gap: 10px;
    }
    
    .kpi-grid { 
        grid-template-columns: repeat(3, 1fr); 
    }
}

@media (max-width: 992px) {
    .filter-section {
        padding: 16px;
    }

    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .period-tabs-container {
        width: 100%;
    }

    .period-tab {
        flex: 1;
        text-align: center;
        padding: 10px 8px;
        font-size: 0.75rem;
    }

    .filter-dropdown,
    .time-selector-box,
    .filter-actions {
        width: 100%;
    }
    
    .filter-actions {
        justify-content: flex-end;
        margin-left: 0;
        margin-top: 10px;
    }
    
    .btn-filter-primary,
    .btn-filter-secondary,
    .btn-export {
        flex: 1;
        max-width: 140px;
    }
    
    .kpi-grid { 
        grid-template-columns: repeat(2, 1fr); 
    }
}

@media (max-width: 600px) {
    .filter-section {
        padding: 12px;
        border-radius: 12px;
    }
    
    .period-tabs-container {
        flex-wrap: wrap;
    }
    
    .period-tab {
        min-width: calc(50% - 4px);
        padding: 10px 6px;
    }
    
    .kpi-grid { 
        grid-template-columns: 1fr; 
    }
    
    .btn-filter-primary,
    .btn-filter-secondary,
    .btn-export {
        max-width: none;
        justify-content: center;
    }
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
    {{-- FILTER SECTION MODERN --}}
    <div class="filter-section">
        {{-- MAIN FILTER ROW --}}
        <div class="filter-row filter-row-main">
            {{-- Period Tabs --}}
            <div class="period-tabs-container">
                <button type="button" class="period-tab {{ ($periodType ?? 'day') == 'day' ? 'active' : '' }}" data-period="day" onclick="setPeriod('day')">
                    <i class="fas fa-calendar-day me-1"></i>Ngày
                </button>
                <button type="button" class="period-tab {{ ($periodType ?? 'day') == 'month' ? 'active' : '' }}" data-period="month" onclick="setPeriod('month')">
                    <i class="fas fa-calendar-alt me-1"></i>Tháng
                </button>
                <button type="button" class="period-tab {{ ($periodType ?? 'day') == 'quarter' ? 'active' : '' }}" data-period="quarter" onclick="setPeriod('quarter')">
                    <i class="fas fa-calendar-week me-1"></i>Quý
                </button>
                <button type="button" class="period-tab {{ ($periodType ?? 'day') == 'year' ? 'active' : '' }}" data-period="year" onclick="setPeriod('year')">
                    <i class="fas fa-calendar me-1"></i>Năm
                </button>
            </div>

            {{-- Time Selector (Dynamic based on period type) --}}
            <div class="time-selector-box" id="timeSelectorBox">
            {{-- Single Date --}}
                <div class="time-input-wrapper {{ $periodType == 'day' ? '' : 'hidden' }}" id="dateSingleWrap">
                    <div class="input-wrapper-inner">
                        <i class="fas fa-calendar-day input-icon"></i>
                        <input type="date" class="date-input" id="dateSingle" value="{{ $periodType == 'day' ? (($from ?? '') ? Carbon\Carbon::parse($from)->toDateString() : date('Y-m-d')) : '' }}" onchange="updateFilterDescription()">
                    </div>
                </div>

                {{-- Month Year --}}
                <div class="time-input-wrapper {{ $periodType == 'month' ? '' : 'hidden' }}" id="dateMonthWrap">
                    <div class="input-wrapper-inner">
                        <i class="fas fa-calendar-alt input-icon"></i>
                        <input type="month" class="date-input" id="dateMonth" value="{{ $periodType == 'month' ? (($from ?? '') ? substr($from, 0, 7) : date('Y-m')) : '' }}" onchange="updateFilterDescription()">
                    </div>
                </div>
                
                {{-- Quarter Dropdown --}}
                <div class="filter-dropdown hidden" id="dropdownQuy">
                    <div class="filter-dropdown-trigger" onclick="toggleDropdown('dropdownQuy')">
                        <span class="filter-label">
                            <i class="fas fa-calendar-week"></i>
                        </span>
                        <span class="filter-value" id="selectedQuyName">
                            @if($periodType == 'quarter' && isset($quarter) && isset($year))
                                Q{{ $quarter }}/{{ $year }}
                            @else
                                Q{{ ceil((date('n')) / 3) }}/{{ date('Y') }}
                            @endif
                        </span>
                        <i class="fas fa-chevron-down filter-arrow"></i>
                    </div>
                    <div class="filter-dropdown-menu">
                        <div class="filter-options" id="optionsQuy">
                            @php
                                $currentQuarter = isset($quarter) ? $quarter : ceil(date('n') / 3);
                                $currentYearQ = isset($year) ? $year : date('Y');
                            @endphp
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <div class="filter-option {{ $currentYearQ == $y && $currentQuarter == 1 ? 'selected' : '' }}" data-quarter="1" data-year="{{ $y }}" onclick="selectQuy(1, {{ $y }})">
                                    <i class="fas fa-calendar-week me-2"></i>Q1/{{ $y }}
                                </div>
                                <div class="filter-option {{ $currentYearQ == $y && $currentQuarter == 2 ? 'selected' : '' }}" data-quarter="2" data-year="{{ $y }}" onclick="selectQuy(2, {{ $y }})">
                                    <i class="fas fa-calendar-week me-2"></i>Q2/{{ $y }}
                                </div>
                                <div class="filter-option {{ $currentYearQ == $y && $currentQuarter == 3 ? 'selected' : '' }}" data-quarter="3" data-year="{{ $y }}" onclick="selectQuy(3, {{ $y }})">
                                    <i class="fas fa-calendar-week me-2"></i>Q3/{{ $y }}
                                </div>
                                <div class="filter-option {{ $currentYearQ == $y && $currentQuarter == 4 ? 'selected' : '' }}" data-quarter="4" data-year="{{ $y }}" onclick="selectQuy(4, {{ $y }})">
                                    <i class="fas fa-calendar-week me-2"></i>Q4/{{ $y }}
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                
                {{-- Year Dropdown --}}
                <div class="filter-dropdown hidden" id="dropdownNam">
                    <div class="filter-dropdown-trigger" onclick="toggleDropdown('dropdownNam')">
                        <span class="filter-label">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <span class="filter-value" id="selectedNamName">{{ isset($year) && $periodType == 'year' ? $year : date('Y') }}</span>
                        <i class="fas fa-chevron-down filter-arrow"></i>
                    </div>
                    <div class="filter-dropdown-menu">
                        <div class="filter-options" id="optionsNam">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <div class="filter-option {{ (isset($year) && $year == $y && $periodType == 'year') ? 'selected' : '' }}" data-value="{{ $y }}" onclick="selectNam({{ $y }})">
                                    <i class="fas fa-calendar me-2"></i>{{ $y }}
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- Movie Filter --}}
            <div class="filter-dropdown" id="dropdownPhim">
                <div class="filter-dropdown-trigger" onclick="toggleDropdown('dropdownPhim')">
                    <span class="filter-label">
                        <i class="fas fa-film"></i>
                    </span>
                    <span class="filter-value" id="selectedPhimName">{{ ($phimId ?? '') ? ($movies[$phimId] ?? 'Tất cả phim') : 'Tất cả phim' }}</span>
                    <i class="fas fa-chevron-down filter-arrow"></i>
                </div>
                <div class="filter-dropdown-menu">
                    <div class="filter-search">
                        <input type="text" placeholder="Tìm kiếm phim..." onkeyup="filterOptions('dropdownPhim', this.value)">
                    </div>
                    <div class="filter-options" id="optionsPhim">
                        <div class="filter-option {{ ($phimId ?? '') == '' ? 'selected' : '' }}" data-value="" onclick="selectFilterOption('phim', '', 'Tất cả phim')">
                            <i class="fas fa-list me-2"></i>Tất cả phim
                        </div>
                        @foreach($movies ?? [] as $id => $name)
                            <div class="filter-option {{ ($phimId ?? '') == $id ? 'selected' : '' }}" data-value="{{ $id }}" data-search="{{ strtolower($name) }}" onclick="selectFilterOption('phim', '{{ $id }}', '{{ $name }}')">
                                <i class="fas fa-film me-2"></i>{{ $name }}
                            </div>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" id="filterPhimId" value="{{ $phimId ?? '' }}">
            </div>

            {{-- Room Filter --}}
            <div class="filter-dropdown" id="dropdownPhong">
                <div class="filter-dropdown-trigger" onclick="toggleDropdown('dropdownPhong')">
                    <span class="filter-label">
                        <i class="fas fa-door-open"></i>
                    </span>
                    <span class="filter-value" id="selectedPhongName">{{ ($phongChieuId ?? '') ? ($rooms[$phongChieuId] ?? 'Tất cả phòng') : 'Tất cả phòng' }}</span>
                    <i class="fas fa-chevron-down filter-arrow"></i>
                </div>
                <div class="filter-dropdown-menu">
                    <div class="filter-search">
                        <input type="text" placeholder="Tìm kiếm phòng..." onkeyup="filterOptions('dropdownPhong', this.value)">
                    </div>
                    <div class="filter-options" id="optionsPhong">
                        <div class="filter-option {{ ($phongChieuId ?? '') == '' ? 'selected' : '' }}" data-value="" onclick="selectFilterOption('phong', '', 'Tất cả phòng')">
                            <i class="fas fa-list me-2"></i>Tất cả phòng
                        </div>
                        @foreach($rooms ?? [] as $id => $name)
                            <div class="filter-option {{ ($phongChieuId ?? '') == $id ? 'selected' : '' }}" data-value="{{ $id }}" data-search="{{ strtolower($name) }}" onclick="selectFilterOption('phong', '{{ $id }}', '{{ $name }}')">
                                <i class="fas fa-door-open me-2"></i>{{ $name }}
                            </div>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" id="filterPhongId" value="{{ $phongChieuId ?? '' }}">
            </div>

            {{-- Action Buttons --}}
            <div class="filter-actions">
                <button type="button" class="btn-filter-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo-alt"></i>
                    <span>Làm mới</span>
                </button>
                <button type="button" class="btn-filter-primary" onclick="applyFilters()">
                    <i class="fas fa-filter"></i>
                    <span>Lọc</span>
                </button>
                <a href="{{ route('admin.thong-ke.export-excel', [
                    'period_type' => $periodType ?? 'day',
                    'from' => $from ? Carbon\Carbon::parse($from)->toDateString() : '',
                    'to' => $to ? Carbon\Carbon::parse($to)->toDateString() : '',
                    'phim_id' => $phimId ?? '',
                    'phong_chieu_id' => $phongChieuId ?? ''
                ]) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i>
                    <span>Xuất Excel</span>
                </a>
            </div>
        </div>

        {{-- FILTER DESCRIPTION --}}
        <div class="filter-description" id="filterDescription">
            <i class="fas fa-info-circle filter-description-icon"></i>
            <span class="filter-description-text" id="filterDescriptionText">
                <strong>Đang thống kê:</strong>
                {{ $periodType == 'day' ? 'Hôm nay' : ($periodType == 'month' ? 'Tháng hiện tại' : ($periodType == 'quarter' ? 'Quý hiện tại' : 'Năm hiện tại')) }}
                • Tất cả phim • Tất cả phòng
            </span>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi-card" id="kpi-total-revenue">
            <div class="kpi-icon" style="background: rgba(247, 184, 75, 0.12);">
                <i class="fas fa-coins" style="color: #f7b84b;"></i>
            </div>
            <div class="kpi-value">{{ number_format((float)($kpi['total_revenue'] ?? 0), 0, ',', '.') }}đ</div>
            <div class="kpi-label">Tổng doanh thu</div>
        </div>

        <div class="kpi-card" id="kpi-ticket-revenue">
            <div class="kpi-icon" style="background: rgba(34, 197, 94, 0.12);">
                <i class="fas fa-ticket" style="color: #22c55e;"></i>
            </div>
            <div class="kpi-value">{{ number_format((float)($kpi['ticket_revenue'] ?? 0), 0, ',', '.') }}đ</div>
            <div class="kpi-label">Doanh thu vé</div>
        </div>

        <div class="kpi-card" id="kpi-combo-revenue">
            <div class="kpi-icon" style="background: rgba(168, 85, 247, 0.12);">
                <i class="fas fa-burger" style="color: #a855f7;"></i>
            </div>
            <div class="kpi-value">{{ number_format((float)($kpi['combo_revenue'] ?? 0), 0, ',', '.') }}đ</div>
            <div class="kpi-label">Doanh thu combo</div>
        </div>

        <div class="kpi-card" id="kpi-snack-revenue">
            <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.12);">
                <i class="fas fa-cookie-bite" style="color: #3b82f6;"></i>
            </div>
            <div class="kpi-value">{{ number_format((float)($kpi['snack_revenue'] ?? 0), 0, ',', '.') }}đ</div>
            <div class="kpi-label">Đồ ăn & Nước</div>
        </div>

        <div class="kpi-card" id="kpi-tickets-sold">
            <div class="kpi-icon" style="background: rgba(168, 85, 247, 0.12);">
                <i class="fas fa-users" style="color: #a855f7;"></i>
            </div>
            <div class="kpi-value">{{ number_format((int)($kpi['tickets_sold'] ?? 0)) }}</div>
            <div class="kpi-label">Vé đã bán</div>
        </div>

        <div class="kpi-card" id="kpi-total-invoices">
            <div class="kpi-icon" style="background: rgba(236, 72, 153, 0.12);">
                <i class="fas fa-receipt" style="color: #ec4899;"></i>
            </div>
            <div class="kpi-value">{{ number_format((int)($kpi['total_invoices'] ?? 0)) }}</div>
            <div class="kpi-label">Tổng hóa đơn</div>
        </div>

        <div class="kpi-card" id="kpi-total-showtimes">
            <div class="kpi-icon" style="background: rgba(14, 165, 233, 0.12);">
                <i class="fas fa-film" style="color: #0ea5e9;"></i>
            </div>
            <div class="kpi-value">{{ number_format((int)($kpi['total_showtimes'] ?? 0)) }}</div>
            <div class="kpi-label">Suất chiếu</div>
        </div>

        <div class="kpi-card" id="kpi-vouchers-used">
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
                    <div class="mini-stat" id="pie-percent-ticket">
                        <div class="val">{{ $kpi['total_revenue'] > 0 ? round($kpi['ticket_revenue'] / $kpi['total_revenue'] * 100) : 0 }}%</div>
                        <div class="lbl">Vé</div>
                    </div>
                    <div class="mini-stat" id="pie-percent-combo">
                        <div class="val">{{ $kpi['total_revenue'] > 0 ? round($kpi['combo_revenue'] / $kpi['total_revenue'] * 100) : 0 }}%</div>
                        <div class="lbl">Combo</div>
                    </div>
                    <div class="mini-stat" id="pie-percent-food">
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
        <div class="panel" id="panel-top-films">
            <div class="panel-header">
                <h3><i class="fas fa-trophy"></i>Top phim doanh thu cao</h3>
                <span class="badge">Top 5</span>
            </div>
            <div class="panel-body" style="padding: 12px 16px;" id="top-films-container">
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
        <div class="panel" id="panel-room-revenue">
            <div class="panel-header">
                <h3><i class="fas fa-door-open"></i>Doanh thu theo phòng</h3>
            </div>
            <div class="panel-body" id="room-revenue-container">
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
        <div class="panel" id="panel-seat-revenue">
            <div class="panel-header">
                <h3><i class="fas fa-chair"></i>Doanh thu theo loại ghế</h3>
            </div>
            <div class="panel-body" id="seat-revenue-container">
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
        <div class="panel" id="panel-payment-methods">
            <div class="panel-header">
                <h3><i class="fas fa-credit-card"></i>Phương thức thanh toán</h3>
            </div>
            <div class="panel-body">
                <div class="pay-list" id="payment-methods-container">
                @if(!empty($paymentMethods) && count($paymentMethods) > 0)
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
                @else
                <p style="text-align: center; color: var(--gray-text); padding: 30px;">Chưa có dữ liệu</p>
                @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Voucher Stats --}}
    <div class="panel" id="panel-voucher-stats">
        <div class="panel-header">
            <h3><i class="fas fa-ticket"></i>Thống kê Voucher</h3>
        </div>
        <div class="panel-body" id="voucher-stats-container">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                <div style="background: var(--black-soft); border-radius: 10px; padding: 16px; text-align: center;" id="voucher-issued">
                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--gold-main);">{{ number_format((int)($voucherStats['total_issued'] ?? 0)) }}</div>
                    <div style="font-size: 0.7rem; color: var(--gray-text); text-transform: uppercase; margin-top: 4px;">Đã phát hành</div>
                </div>
                <div style="background: var(--black-soft); border-radius: 10px; padding: 16px; text-align: center;" id="voucher-used">
                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--gold-main);">{{ number_format((int)($voucherStats['total_used'] ?? 0)) }}</div>
                    <div style="font-size: 0.7rem; color: var(--gray-text); text-transform: uppercase; margin-top: 4px;">Đã sử dụng</div>
                </div>
                <div style="background: var(--black-soft); border-radius: 10px; padding: 16px; text-align: center;" id="voucher-usage-rate">
                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--gold-main);">{{ $voucherStats['usage_rate'] ?? 0 }}%</div>
                    <div style="font-size: 0.7rem; color: var(--gray-text); text-transform: uppercase; margin-top: 4px;">Tỷ lệ sử dụng</div>
                </div>
                <div style="background: var(--black-soft); border-radius: 10px; padding: 16px; text-align: center;" id="voucher-discount">
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
// ==========================================
// GLOBAL STATE
// ==========================================
let lineChart = null;
let pieChart = null;
let currentFilters = {
    period_type: '{{ $periodType ?? 'day' }}',
    from: '{{ $from ? Carbon\Carbon::parse($from)->toDateString() : '' }}',
    to: '{{ $to ? Carbon\Carbon::parse($to)->toDateString() : '' }}',
    phim_id: '{{ $phimId ?? '' }}',
    phong_chieu_id: '{{ $phongChieuId ?? '' }}'
};

// ==========================================
// FILTER DROPDOWN - MODERN
// ==========================================
function setPeriod(period) {
    // Update tab active state
    document.querySelectorAll('.period-tab').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.period-tab[data-period="${period}"]`).classList.add('active');

    currentFilters.period_type = period;

    // Hide all time input wrappers
    document.getElementById('dateSingleWrap').classList.add('hidden');
    document.getElementById('dateMonthWrap').classList.add('hidden');
    document.getElementById('dropdownQuy')?.classList.add('hidden');
    document.getElementById('dropdownNam')?.classList.add('hidden');
    document.getElementById('dropdownQuy')?.classList.remove('active');
    document.getElementById('dropdownNam')?.classList.remove('active');

    const now = new Date();
    let fromDate = '', toDate = '';

    switch (period) {
        case 'month':
            document.getElementById('dateMonthWrap').classList.remove('hidden');
            const monthValue = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
            document.getElementById('dateMonth').value = monthValue;
            fromDate = monthValue + '-01';
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
            toDate = monthValue + '-' + String(lastDay).padStart(2, '0');
            break;
        case 'quarter':
            document.getElementById('dropdownQuy')?.classList.add('active');
            document.getElementById('dropdownQuy')?.classList.remove('hidden');
            const currentQuarter = Math.ceil((now.getMonth() + 1) / 3);
            document.getElementById('selectedQuyName').textContent = `Q${currentQuarter}/${now.getFullYear()}`;
            const startMonthQ = (currentQuarter - 1) * 3 + 1;
            const endMonthQ = startMonthQ + 2;
            const quarterEndDay = new Date(now.getFullYear(), endMonthQ, 0).getDate();
            fromDate = now.getFullYear() + '-' + String(startMonthQ).padStart(2, '0') + '-01';
            toDate = now.getFullYear() + '-' + String(endMonthQ).padStart(2, '0') + '-' + String(quarterEndDay).padStart(2, '0');
            break;
        case 'year':
            document.getElementById('dropdownNam')?.classList.add('active');
            document.getElementById('dropdownNam')?.classList.remove('hidden');
            document.getElementById('selectedNamName').textContent = now.getFullYear();
            fromDate = now.getFullYear() + '-01-01';
            toDate = now.getFullYear() + '-12-31';
            break;
        default: // day
            document.getElementById('dateSingleWrap').classList.remove('hidden');
            const today = now.toISOString().split('T')[0];
            document.getElementById('dateSingle').value = today;
            fromDate = today;
            toDate = today;
            break;
    }

    currentFilters.from = fromDate;
    currentFilters.to = toDate;
    updateFilterDescription();
}

// ==========================================
// FILTER DROPDOWN
// ==========================================
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const isOpen = dropdown.classList.contains('open');
    
    // Close all dropdowns
    document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('open'));
    
    // Toggle this dropdown
    if (!isOpen) {
        dropdown.classList.add('open');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.filter-dropdown')) {
        document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('open'));
    }
});

function selectFilterOption(type, value, displayText) {
    if (type === 'phim') {
        document.getElementById('filterPhimId').value = value;
        document.getElementById('selectedPhimName').textContent = displayText;
        currentFilters.phim_id = value;
        
        // Update selected state
        document.querySelectorAll('#optionsPhim .filter-option').forEach(opt => opt.classList.remove('selected'));
        const selectedOpt = value ? document.querySelector(`#optionsPhim .filter-option[data-value="${value}"]`) : document.querySelector('#optionsPhim .filter-option[data-value=""]');
        if (selectedOpt) selectedOpt.classList.add('selected');
    } else if (type === 'phong') {
        document.getElementById('filterPhongId').value = value;
        document.getElementById('selectedPhongName').textContent = displayText;
        currentFilters.phong_chieu_id = value;
        
        // Update selected state
        document.querySelectorAll('#optionsPhong .filter-option').forEach(opt => opt.classList.remove('selected'));
        const selectedOpt = value ? document.querySelector(`#optionsPhong .filter-option[data-value="${value}"]`) : document.querySelector('#optionsPhong .filter-option[data-value=""]');
        if (selectedOpt) selectedOpt.classList.add('selected');
    }
    
    // Close dropdown
    document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('open'));
    updateFilterDescription();
}

function filterOptions(dropdownId, searchText) {
    const options = document.querySelector(`#${dropdownId} .filter-options`);
    const searchLower = searchText.toLowerCase();
    
    options.querySelectorAll('.filter-option').forEach(opt => {
        const text = opt.textContent.toLowerCase();
        opt.style.display = text.includes(searchLower) ? 'flex' : 'none';
    });
}

// ==========================================
// RESET FILTERS
// ==========================================
function resetFilters() {
    // Reset to default period (day)
    setPeriod('day');
    
    // Reset movie filter
    selectFilterOption('phim', '', 'Tất cả phim');
    
    // Reset room filter
    selectFilterOption('phong', '', 'Tất cả phòng');
    
    // Trigger filter
    applyFilters();
}

// ==========================================
// UPDATE FILTER DESCRIPTION
// ==========================================
function selectQuy(quarter, year) {
    document.getElementById('selectedQuyName').textContent = `Q${quarter}/${year}`;
    toggleDropdown('dropdownQuy');
    updateFilterDescription();
    applyFilters();
}

function selectNam(year) {
    document.getElementById('selectedNamName').textContent = year;
    toggleDropdown('dropdownNam');
    updateFilterDescription();
    applyFilters();
}

function updateFilterDescription() {
    const periodText = getPeriodDescription();
    const phimText = document.getElementById('selectedPhimName').textContent;
    const phongText = document.getElementById('selectedPhongName').textContent;
    
    document.getElementById('filterDescriptionText').innerHTML = 
        `<strong>Đang thống kê:</strong> ${periodText} • ${phimText} • ${phongText}`;
}

function getPeriodDescription() {
    const period = currentFilters.period_type || 'day';

    switch (period) {
        case 'day':
            const dateValue = document.getElementById('dateSingle').value;
            if (dateValue) {
                const d = new Date(dateValue + 'T00:00:00');
                return `Ngày ${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
            }
            const today = new Date();
            return `Ngày ${String(today.getDate()).padStart(2, '0')}/${String(today.getMonth() + 1).padStart(2, '0')}/${today.getFullYear()}`;
        case 'month':
            const monthValue = document.getElementById('dateMonth').value;
            if (monthValue) {
                const [y, m] = monthValue.split('-');
                return `Tháng ${parseInt(m)}/${y}`;
            }
            return 'Tháng hiện tại';
        case 'quarter':
            const selectedQuy = document.getElementById('selectedQuyName').textContent;
            if (selectedQuy) {
                return `Quý ${selectedQuy}`;
            }
            return 'Quý hiện tại';
        case 'year':
            const selectedNam = document.getElementById('selectedNamName').textContent;
            if (selectedNam) {
                return `Năm ${selectedNam}`;
            }
            return 'Năm hiện tại';
        default:
            return 'Hôm nay';
    }
}

// ==========================================
// FILTER SUBMIT - MODERN
// ==========================================
function applyFilters() {
    const period = currentFilters.period_type || 'day';
    
    switch (period) {
        case 'day':
            const dateVal = document.getElementById('dateSingle').value;
            // Format: yyyy-mm-dd from native date input
            if (dateVal) {
                currentFilters.from = dateVal;
                currentFilters.to = dateVal;
            }
            break;
        case 'month':
            const monthVal = document.getElementById('dateMonth').value;
            if (monthVal) {
                // Format: yyyy-mm from native month input
                const lastDay = new Date(monthVal + '-01');
                lastDay.setMonth(lastDay.getMonth() + 1, 0);
                const endDay = String(lastDay.getDate()).padStart(2, '0');
                currentFilters.from = monthVal + '-01';
                currentFilters.to = monthVal + '-' + endDay;
            }
            break;
        case 'quarter':
            const quyText = document.getElementById('selectedQuyName').textContent;
            if (quyText) {
                const match = quyText.match(/Q(\d)\/(\d{4})/);
                if (match) {
                    const q = parseInt(match[1]);
                    const y = match[2];
                    const startMonth = (q - 1) * 3 + 1;
                    const endMonth = startMonth + 2;
                    const endDay = new Date(y, endMonth, 0).getDate();
                    currentFilters.from = y + '-' + String(startMonth).padStart(2, '0') + '-01';
                    currentFilters.to = y + '-' + String(endMonth).padStart(2, '0') + '-' + String(endDay).padStart(2, '0');
                }
            }
            break;
        case 'year':
            const namText = document.getElementById('selectedNamName').textContent;
            if (namText) {
                currentFilters.from = namText + '-01-01';
                currentFilters.to = namText + '-12-31';
            }
            break;
    }
    
    // Get phim and phong filters
    currentFilters.phim_id = document.getElementById('filterPhimId').value;
    currentFilters.phong_chieu_id = document.getElementById('filterPhongId').value;
    
    updateFilterDescription();
    fetchStatistics();
}

// ==========================================
// FETCH STATISTICS DATA
// ==========================================
let fetchTimeout = null;

async function fetchStatistics() {
    // Debounce - cancel previous call if exists
    if (fetchTimeout) clearTimeout(fetchTimeout);
    
    fetchTimeout = setTimeout(async () => {
        try {
            const params = new URLSearchParams();
            params.append('period_type', currentFilters.period_type);
            if (currentFilters.from) params.append('from', currentFilters.from);
            if (currentFilters.to) params.append('to', currentFilters.to);
            if (currentFilters.phim_id) params.append('phim_id', currentFilters.phim_id);
            if (currentFilters.phong_chieu_id) params.append('phong_chieu_id', currentFilters.phong_chieu_id);

            const response = await fetch(`/admin/api/statistics?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                console.warn('Statistics API returned non-OK status:', response.status);
                return;
            }

            const result = await response.json();

            if (result.success && result.data) {
                updateDashboard(result.data);
            }
        } catch (error) {
            // Silent fail - don't show error on page load
            console.warn('Statistics fetch skipped:', error.message);
        }
    }, 100);
}

// ==========================================
// UPDATE DASHBOARD WITH DATA
// ==========================================
function updateDashboard(data) {
    if (!data) return;

    const summary = data.summary || {};
    const lineChartData = data.lineChart || [];
    const revenueStructure = data.revenueStructure || {};

    // Update KPI Cards
    updateKPICards(summary);

    // Update Line Chart
    updateLineChart(lineChartData);

    // Update Pie Chart
    updatePieChart(revenueStructure);

    // Update Top Films
    updateTopFilms(data.topMovies || []);

    // Update Room Revenue
    updateRoomRevenue(data.roomRevenue || []);

    // Update Seat Revenue
    updateSeatRevenue(data.seatRevenue || []);

    // Update Payment Methods
    updatePaymentMethods(data.paymentMethods || []);

    // Update Voucher Stats
    updateVoucherStats(data.voucherStatistics || {});
}

// ==========================================
// KPI CARDS
// ==========================================
function updateKPICards(summary) {
    if (!summary) return;

    const kpiValues = {
        'kpi-total-revenue': summary.total_revenue,
        'kpi-ticket-revenue': summary.ticket_revenue,
        'kpi-combo-revenue': summary.combo_revenue,
        'kpi-snack-revenue': summary.snack_revenue,
        'kpi-tickets-sold': summary.tickets_sold,
        'kpi-total-invoices': summary.total_invoices,
        'kpi-total-showtimes': summary.total_showtimes,
        'kpi-vouchers-used': summary.vouchers_used,
    };

    for (const [cardId, value] of Object.entries(kpiValues)) {
        const card = document.getElementById(cardId);
        if (card) {
            const valueElement = card.querySelector('.kpi-value');
            if (valueElement) {
                const numValue = value || 0;
                if (['kpi-tickets-sold', 'kpi-total-invoices', 'kpi-total-showtimes', 'kpi-vouchers-used'].includes(cardId)) {
                    valueElement.textContent = new Intl.NumberFormat('vi-VN').format(parseInt(numValue));
                } else {
                    valueElement.textContent = new Intl.NumberFormat('vi-VN').format(numValue) + 'đ';
                }
            }
        }
    }
}

// ==========================================
// LINE CHART
// ==========================================
function updateLineChart(data) {
    const ctx = document.getElementById('lineChart');
    if (!ctx) return;

    const labels = data.map(item => item.period || '');
    const ticketData = data.map(item => item.ticket_revenue || 0);
    const comboData = data.map(item => item.combo_revenue || 0);
    const snackData = data.map(item => item.snack_revenue || 0);

    if (lineChart) {
        lineChart.data.labels = labels;
        lineChart.data.datasets[0].data = ticketData;
        lineChart.data.datasets[1].data = comboData;
        lineChart.data.datasets[2].data = snackData;
        lineChart.update();
    } else {
        lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Doanh thu ve',
                        data: ticketData,
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
                        data: comboData,
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
                        label: 'Do an & Nuoc',
                        data: snackData,
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
    }
}

// ==========================================
// PIE CHART
// ==========================================
function updatePieChart(structure) {
    const ctx = document.getElementById('pieChart');
    if (!ctx) return;

    const ticketRevenue = structure.ticket?.revenue || 0;
    const comboRevenue = structure.combo?.revenue || 0;
    const foodRevenue = structure.food?.revenue || 0;
    const total = ticketRevenue + comboRevenue + foodRevenue;

    const ticketPercent = total > 0 ? Math.round((ticketRevenue / total) * 100) : 0;
    const comboPercent = total > 0 ? Math.round((comboRevenue / total) * 100) : 0;
    const foodPercent = total > 0 ? Math.round((foodRevenue / total) * 100) : 0;

    const ticketPercentEl = document.getElementById('pie-percent-ticket');
    const comboPercentEl = document.getElementById('pie-percent-combo');
    const foodPercentEl = document.getElementById('pie-percent-food');

    if (ticketPercentEl) {
        const valEl = ticketPercentEl.querySelector('.val');
        if (valEl) valEl.textContent = ticketPercent + '%';
    }
    if (comboPercentEl) {
        const valEl = comboPercentEl.querySelector('.val');
        if (valEl) valEl.textContent = comboPercent + '%';
    }
    if (foodPercentEl) {
        const valEl = foodPercentEl.querySelector('.val');
        if (valEl) valEl.textContent = foodPercent + '%';
    }

    if (pieChart) {
        pieChart.data.datasets[0].data = [ticketRevenue, comboRevenue, foodRevenue];
        pieChart.update();
    } else {
        pieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Ve', 'Combo', 'Do an & Nuoc'],
                datasets: [{
                    data: [ticketRevenue, comboRevenue, foodRevenue],
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
    }
}

// ==========================================
// TOP FILMS
// ==========================================
function updateTopFilms(films) {
    const container = document.getElementById('top-films-container');
    if (!container) return;

    if (!films || films.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--gray-text); padding: 30px;">Chua co du lieu</p>';
        return;
    }

    container.innerHTML = '<ul class="top-list">' + films.map((film, i) => `
        <li>
            <span class="rank-badge ${i == 0 ? 'gold' : (i == 1 ? 'silver' : '')}">${i + 1}</span>
            <div class="top-info">
                <strong>${film.ten_phim || 'N/A'}</strong>
                <small>${new Intl.NumberFormat('vi-VN').format(film.tickets_sold || 0)} ve</small>
            </div>
            <div class="top-amount">
                <strong>${new Intl.NumberFormat('vi-VN').format(film.total_revenue || 0)}đ</strong>
            </div>
        </li>
    `).join('') + '</ul>';
}

// ==========================================
// ROOM REVENUE
// ==========================================
function updateRoomRevenue(rooms) {
    const container = document.getElementById('room-revenue-container');
    if (!container) return;

    if (!rooms || rooms.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--gray-text); padding: 30px;">Chua co du lieu</p>';
        return;
    }

    const maxRevenue = Math.max(...rooms.map(r => r.total_revenue || 0), 1);

    container.innerHTML = rooms.map(room => `
        <div class="bar-stat">
            <div class="bar-stat-label">
                <span>${room.ten_phong || 'N/A'}</span>
                <span>${new Intl.NumberFormat('vi-VN').format(room.total_revenue || 0)}đ</span>
            </div>
            <div class="bar-track">
                <div class="bar-fill" style="width: ${(room.total_revenue || 0) / maxRevenue * 100}%"></div>
            </div>
        </div>
    `).join('');
}

// ==========================================
// SEAT REVENUE
// ==========================================
function updateSeatRevenue(seats) {
    const container = document.getElementById('seat-revenue-container');
    if (!container) return;

    if (!seats || seats.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--gray-text); padding: 30px;">Chua co du lieu</p>';
        return;
    }

    const maxRevenue = Math.max(...seats.map(s => s.total_revenue || 0), 1);

    container.innerHTML = seats.map(seat => `
        <div class="bar-stat">
            <div class="bar-stat-label">
                <span>${seat.ten_loai || 'N/A'}</span>
                <span>${new Intl.NumberFormat('vi-VN').format(seat.total_revenue || 0)}đ</span>
            </div>
            <div class="bar-track">
                <div class="bar-fill" style="width: ${(seat.total_revenue || 0) / maxRevenue * 100}%"></div>
            </div>
        </div>
    `).join('');
}

// ==========================================
// PAYMENT METHODS
// ==========================================
function updatePaymentMethods(methods) {
    const container = document.getElementById('payment-methods-container');
    if (!container) return;

    if (!methods || methods.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--gray-text); padding: 30px;">Chua co du lieu</p>';
        return;
    }

    container.innerHTML = methods.map(method => `
        <div class="pay-item">
            <div class="icon"><i class="fas fa-wallet"></i></div>
            <div class="info">
                <strong>${method.label || 'N/A'}</strong>
                <small>${new Intl.NumberFormat('vi-VN').format(method.count || 0)} giao dich</small>
            </div>
            <div class="amount">
                <strong>${new Intl.NumberFormat('vi-VN').format(method.total_revenue || 0)}đ</strong>
            </div>
        </div>
    `).join('');
}

// ==========================================
// VOUCHER STATS
// ==========================================
function updateVoucherStats(stats) {
    const issued = document.getElementById('voucher-issued');
    const used = document.getElementById('voucher-used');
    const usageRate = document.getElementById('voucher-usage-rate');
    const discount = document.getElementById('voucher-discount');

    if (issued) {
        const valueEl = issued.querySelector('div:first-child');
        if (valueEl) valueEl.textContent = new Intl.NumberFormat('vi-VN').format(stats.total_issued || 0);
    }
    if (used) {
        const valueEl = used.querySelector('div:first-child');
        if (valueEl) valueEl.textContent = new Intl.NumberFormat('vi-VN').format(stats.total_used || 0);
    }
    if (usageRate) {
        const valueEl = usageRate.querySelector('div:first-child');
        if (valueEl) valueEl.textContent = (stats.usage_rate || 0) + '%';
    }
    if (discount) {
        const valueEl = discount.querySelector('div:first-child');
        if (valueEl) valueEl.textContent = new Intl.NumberFormat('vi-VN').format(stats.total_discount || 0) + 'đ';
    }
}

// ==========================================
// INITIALIZE
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter UI based on current period type
    const period = currentFilters.period_type || 'day';
    
    // Hide all time input wrappers first
    document.getElementById('dateSingleWrap')?.classList.add('hidden');
    document.getElementById('dateMonthWrap')?.classList.add('hidden');
    document.getElementById('dateQuarterWrap')?.classList.add('hidden');
    document.getElementById('dateYearWrap')?.classList.add('hidden');
    
    // Show correct time input wrapper based on period
    switch (period) {
        case 'month':
            document.getElementById('dateMonthWrap')?.classList.remove('hidden');
            break;
        case 'quarter':
            document.getElementById('dropdownQuy')?.classList.add('active');
            break;
        case 'year':
            document.getElementById('dropdownNam')?.classList.add('active');
            break;
        default:
            document.getElementById('dateSingleWrap')?.classList.remove('hidden');
    }
    
    // Update filter description
    updateFilterDescription();

    // Initialize charts with server-side data
    const revenueData = {!! json_encode($revenueByTime ?? []) !!};

    if (revenueData && revenueData.length > 0) {
        const chartLabels = revenueData.map(item => item.period || '');
        const ticketChartData = revenueData.map(item => item.ticket_revenue || 0);
        const comboChartData = revenueData.map(item => item.combo_revenue || 0);
        const snackChartData = revenueData.map(item => item.snack_revenue || 0);

        const lineCtx = document.getElementById('lineChart');
        if (lineCtx) {
            lineChart = new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Doanh thu ve',
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
                            label: 'Do an & Nuoc',
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
        }

        const pieCtx = document.getElementById('pieChart');
        if (pieCtx) {
            const kpi = {!! json_encode($kpi ?? []) !!};
            const ticketRevenue = kpi?.ticket_revenue || 0;
            const comboRevenue = kpi?.combo_revenue || 0;
            const snackRevenue = kpi?.snack_revenue || 0;
            const total = ticketRevenue + comboRevenue + snackRevenue;

            pieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Ve', 'Combo', 'Do an & Nuoc'],
                    datasets: [{
                        data: [ticketRevenue, comboRevenue, snackRevenue],
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
        }
    }
});
</script>

{{-- Flatpickr disabled - using native date input --}}
{{--
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vi.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viLocale = flatpickr.l10ns.vi;
    viLocale.months = {
        longhand: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
        shorthand: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12']
    };

    const dateSingleEl = document.getElementById("dateSingle");
    if (dateSingleEl) {
        flatpickr(dateSingleEl, {
            locale: viLocale,
            dateFormat: "d/m/Y",
            allowInput: true
        });
    }

    const dateMonthEl = document.getElementById("dateMonth");
    if (dateMonthEl) {
        flatpickr(dateMonthEl, {
            locale: viLocale,
            plugins: [
                new flatpickr.plugins.monthSelect({
                    shorthand: true,
                    dateFormat: "m/Y"
                })
            ],
            disableMobile: true,
            allowInput: true
        });
    }
});
</script>
--}}
{{-- Using native date input for better compatibility --}}
@endpush
