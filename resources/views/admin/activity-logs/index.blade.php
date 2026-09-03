@extends('layouts.admin')

@section('page-title', 'Nhật ký hệ thống')
@section('page-subtitle', 'Theo dõi thao tác người dùng và quản trị trên hệ thống CineHome')

@section('content')

@php
    $hasFilters = request()->hasAny(['keyword', 'chuc_nang', 'from', 'to']);

    $statCards = [
        [
            'label' => 'Bản ghi phù hợp',
            'value' => $summary['filtered'],
            'icon'  => 'fa-clipboard-list',
            'tone'  => 'is-total'
        ],
        [
            'label' => 'Hôm nay',
            'value' => $summary['today'],
            'icon'  => 'fa-calendar-day',
            'tone'  => 'is-today'
        ],
        [
            'label' => 'Chức năng',
            'value' => $summary['modules'],
            'icon'  => 'fa-layer-group',
            'tone'  => 'is-module'
        ],
        [
            'label' => 'Người thao tác',
            'value' => $summary['actors'],
            'icon'  => 'fa-users-gear',
            'tone'  => 'is-actor'
        ],
    ];
@endphp


<style>

/* =========================================================
   ACTIVITY LOG PAGE
   GIỮ GIAO DIỆN DARK ĐỎ/CAM CỦA CINEHOME
========================================================= */

.activity-log-page {
    width: 100%;
    color: #f8fafc;
    position: relative;
    z-index: 1;
}


/* =========================================================
   HERO
========================================================= */

.activity-log-hero {
    position: relative;
    z-index: 100;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(520px, 1.15fr);
    gap: 32px;

    padding: 30px;

    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;

    background:
        radial-gradient(
            circle at 0% 0%,
            rgba(255, 52, 76, 0.18),
            transparent 38%
        ),
        radial-gradient(
            circle at 100% 100%,
            rgba(255, 166, 0, 0.12),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            #17131a 0%,
            #11131a 55%,
            #18150f 100%
        );

    box-shadow:
        0 18px 45px rgba(0, 0, 0, 0.35);

    overflow: visible !important;
}


/* =========================================================
   HERO LEFT
========================================================= */

.activity-log-hero > div:first-child {
    min-width: 0;
    position: relative;
    z-index: 2;
}

.activity-log-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    padding: 7px 12px;

    border: 1px solid rgba(255, 166, 0, 0.28);
    border-radius: 999px;

    background: rgba(255, 166, 0, 0.08);

    color: #ffb52e;

    font-size: 12px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.activity-log-kicker i {
    font-size: 12px;
}

.activity-log-hero h2 {
    margin: 20px 0 8px;

    color: #ffffff;

    font-size: clamp(32px, 3vw, 46px);
    line-height: 1.05;
    font-weight: 900;
    letter-spacing: -1.5px;
}

.activity-log-hero p {
    margin: 0;

    max-width: 680px;

    color: #a9afbd;

    font-size: 14px;
    line-height: 1.7;
}


/* =========================================================
   META
========================================================= */

.activity-log-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;

    margin-top: 24px;
}

.activity-log-meta span {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    min-height: 34px;
    padding: 8px 12px;

    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 10px;

    background: rgba(255, 255, 255, 0.035);

    color: #aeb5c3;

    font-size: 12px;
    font-weight: 700;
}

.activity-log-meta i {
    color: #ffb52e;
}


/* =========================================================
   FILTER
========================================================= */

.activity-log-filter {
    position: relative;
    z-index: 1000;

    display: grid;
    grid-template-columns: minmax(200px, 1.6fr) minmax(180px, 1fr) minmax(140px, .8fr) minmax(140px, .8fr);
    gap: 12px;

    align-items: end;

    min-width: 0;

    padding: 4px;

    overflow: visible !important;
}

.activity-log-filter > label {
    position: relative;
    z-index: 10;

    display: flex;
    flex-direction: column;
    gap: 8px;

    min-width: 0;
}

.activity-log-filter > label > span {
    color: #aeb5c3;

    font-size: 11px;
    font-weight: 800;
}


/* =========================================================
   INPUT
========================================================= */

.activity-log-search > div {
    position: relative;
}

.activity-log-search i {
    position: absolute;
    left: 14px;
    top: 50%;

    transform: translateY(-50%);

    color: #ff3f55;

    pointer-events: none;
}

.activity-log-filter input:not([type="hidden"]) {
    width: 100%;
    height: 48px;

    box-sizing: border-box;

    padding: 0 14px;

    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 11px;

    outline: none;

    background:
        linear-gradient(
            180deg,
            rgba(255, 255, 255, .065),
            rgba(255, 255, 255, .035)
        );

    color: #f8fafc;

    font-size: 13px;
    font-weight: 600;

    transition: .2s ease;
}

.activity-log-search input {
    padding-left: 40px !important;
}

.activity-log-filter input:not([type="hidden"])::placeholder {
    color: #737b8a;
}

.activity-log-filter input:not([type="hidden"]):focus {
    border-color: rgba(255, 59, 82, .75);

    box-shadow:
        0 0 0 3px rgba(255, 59, 82, .10);
}


/* =========================================================
   DATE INPUT - DARK
========================================================= */

.activity-log-filter input[type="date"] {
    color-scheme: dark;
}


/* =========================================================
   CUSTOM SELECT
   QUAN TRỌNG: DROPDOWN LUÔN NỔI
========================================================= */

.activity-function-filter {
    position: relative !important;
    z-index: 2000 !important;
}

.activity-select {
    position: relative;

    width: 100%;

    z-index: 3000;
}

.activity-select-trigger {
    position: relative;
    z-index: 3001;

    display: flex;
    align-items: center;
    justify-content: space-between;

    width: 100%;
    height: 48px;

    padding: 0 14px;

    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 11px;

    outline: none;

    background:
        linear-gradient(
            180deg,
            rgba(255, 255, 255, .065),
            rgba(255, 255, 255, .035)
        );

    color: #e8ebf1;

    cursor: pointer;

    font-size: 13px;
    font-weight: 700;

    text-align: left;

    transition: .2s ease;
}

.activity-select-trigger:hover {
    border-color: rgba(255, 59, 82, .45);
}

.activity-select.open .activity-select-trigger {
    border-color: #ff3b52;

    box-shadow:
        0 0 0 3px rgba(255, 59, 82, .10);
}

.activity-select-trigger i {
    color: #858c9b;

    transition: transform .2s ease;
}

.activity-select.open .activity-select-trigger i {
    transform: rotate(180deg);
    color: #ff3b52;
}


/* =========================================================
   DROPDOWN
   SỬA LỖI BỊ ĐÈ
========================================================= */

.activity-select-dropdown {
    position: absolute !important;

    top: calc(100% + 7px) !important;
    left: 0 !important;
    right: 0 !important;

    z-index: 99999 !important;

    display: none;

    width: 100%;

    max-height: 240px;

    overflow-y: auto !important;
    overflow-x: hidden;

    padding: 6px;

    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 12px;

    background:
        linear-gradient(
            180deg,
            #20232b 0%,
            #171920 100%
        );

    box-shadow:
        0 20px 50px rgba(0, 0, 0, .65),
        0 0 0 1px rgba(0, 0, 0, .35);

    isolation: isolate;
}

.activity-select.open .activity-select-dropdown {
    display: block !important;
}


/* =========================================================
   DROPDOWN OPTION
========================================================= */

.activity-select-option {
    position: relative;
    z-index: 100000;

    display: flex;
    align-items: center;

    min-height: 42px;

    padding: 9px 12px;

    border-radius: 8px;

    color: #d8dce5;

    cursor: pointer;

    font-size: 13px;
    font-weight: 600;

    transition: .15s ease;
}

.activity-select-option:hover {
    background: rgba(255, 59, 82, .10);
    color: #ffffff;
}

.activity-select-option.active {
    background:
        linear-gradient(
            90deg,
            rgba(255, 59, 82, .17),
            rgba(255, 166, 0, .07)
        );

    color: #ff5266;

    font-weight: 800;
}

.activity-select-option.active::after {
    content: "✓";

    margin-left: auto;

    color: #ff5266;

    font-weight: 900;
}


/* =========================================================
   SCROLLBAR DROPDOWN
========================================================= */

.activity-select-dropdown::-webkit-scrollbar {
    width: 6px;
}

.activity-select-dropdown::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, .03);
}

.activity-select-dropdown::-webkit-scrollbar-thumb {
    background: rgba(255, 59, 82, .45);
    border-radius: 10px;
}


/* =========================================================
   FILTER BUTTON
========================================================= */

.activity-log-filter-actions {
    grid-column: 1 / -1;

    display: flex;
    gap: 10px;

    position: relative;
    z-index: 20;
}

.activity-log-filter-actions button {
    flex: 1;

    height: 48px;

    border: 0;
    border-radius: 11px;

    background:
        linear-gradient(
            135deg,
            #ff334d 0%,
            #ff4e5f 50%,
            #ff7b34 100%
        );

    color: #ffffff;

    cursor: pointer;

    font-size: 13px;
    font-weight: 900;

    box-shadow:
        0 10px 25px rgba(255, 51, 77, .18);

    transition: transform .2s ease, box-shadow .2s ease;
}

.activity-log-filter-actions button:hover {
    transform: translateY(-1px);

    box-shadow:
        0 14px 30px rgba(255, 51, 77, .28);
}

.activity-log-filter-actions button i {
    margin-right: 7px;
}

.activity-log-filter-actions a {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 48px;
    height: 48px;

    border: 1px solid rgba(255,255,255,.09);
    border-radius: 11px;

    background: rgba(255,255,255,.045);

    color: #aeb5c3;

    text-decoration: none;

    transition: .2s ease;
}

.activity-log-filter-actions a:hover {
    color: #ff5266;

    border-color: rgba(255,59,82,.4);
}


/* =========================================================
   STATS
========================================================= */

.activity-log-stats {
    position: relative;
    z-index: 2;

    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));

    gap: 14px;

    margin-top: 16px;
}

.activity-log-stat {
    position: relative;

    display: flex;
    align-items: center;
    gap: 14px;

    min-height: 112px;

    padding: 18px;

    border: 1px solid rgba(255,255,255,.07);
    border-radius: 17px;

    background:
        linear-gradient(
            145deg,
            #171b24,
            #11141b
        );

    box-shadow:
        0 12px 30px rgba(0,0,0,.20);

    overflow: hidden;
}

.activity-log-stat::before {
    content: "";

    position: absolute;

    top: 0;
    left: 0;
    right: 0;

    height: 2px;

    background:
        linear-gradient(
            90deg,
            #ff334d,
            #ffb52e
        );
}

.activity-log-stat > span {
    display: flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 42px;

    width: 42px;
    height: 42px;

    border-radius: 11px;

    background: rgba(255,255,255,.07);

    color: #ffb52e;
}

.activity-log-stat small {
    display: block;

    margin-bottom: 4px;

    color: #8f97a8;

    font-size: 10px;
    font-weight: 800;

    text-transform: uppercase;
}

.activity-log-stat strong {
    display: block;

    color: #ffffff;

    font-size: 25px;
    line-height: 1;
    font-weight: 900;
}

.activity-log-stat.is-today > span {
    color: #21d69a;
}

.activity-log-stat.is-module > span {
    color: #a76cff;
}

.activity-log-stat.is-actor > span {
    color: #58a6ff;
}


/* =========================================================
   TABLE PANEL
========================================================= */

.activity-log-panel {
    position: relative;
    z-index: 1;

    margin-top: 16px;

    border: 1px solid rgba(255,255,255,.07);
    border-radius: 19px;

    background:
        linear-gradient(
            145deg,
            #151922,
            #101319
        );

    box-shadow:
        0 15px 40px rgba(0,0,0,.25);

    overflow: visible;
}


/* =========================================================
   PANEL HEADER
========================================================= */

.activity-log-panel-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 20px;

    padding: 24px 22px;

    border-bottom: 1px solid rgba(255,255,255,.06);
}

.activity-log-panel-head .activity-log-kicker {
    margin-bottom: 12px;

    padding: 6px 10px;

    font-size: 10px;
}

.activity-log-panel-head h3 {
    margin: 0 0 6px;

    color: #ffffff;

    font-size: 20px;
    font-weight: 900;
}

.activity-log-panel-head p {
    margin: 0;

    color: #7f8797;

    font-size: 12px;
}

.activity-log-count {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 9px 12px;

    border-radius: 10px;

    background: rgba(255,166,0,.08);

    color: #ffb52e;

    font-size: 11px;
    font-weight: 800;

    white-space: nowrap;
}


/* =========================================================
   TABLE
========================================================= */

.activity-log-table-wrap {
    width: 100%;

    overflow-x: auto;
}

.activity-log-table {
    width: 100%;

    border-collapse: collapse;

    min-width: 900px;
}

.activity-log-table thead th {
    padding: 15px 16px;

    background: rgba(255,255,255,.025);

    color: #737c8d;

    font-size: 10px;
    font-weight: 900;

    text-align: left;
    text-transform: uppercase;

    letter-spacing: .04em;

    border-bottom: 1px solid rgba(255,255,255,.06);
}

.activity-log-table tbody tr {
    transition: background .2s ease;
}

.activity-log-table tbody tr:hover {
    background: rgba(255,255,255,.025);
}

.activity-log-table tbody td {
    padding: 17px 16px;

    color: #cdd2dc;

    border-bottom: 1px solid rgba(255,255,255,.045);

    vertical-align: middle;
}


/* =========================================================
   TIME
========================================================= */

.activity-log-time {
    display: block;

    color: #e7eaf0;

    font-size: 12px;
    font-weight: 800;
}

.activity-log-table td > small {
    display: block;

    margin-top: 5px;

    color: #697182;

    font-size: 10px;
}


/* =========================================================
   ACTOR
========================================================= */

.activity-log-actor {
    display: flex;
    align-items: center;
    gap: 10px;
}

.activity-log-actor > span {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    flex: 0 0 38px;

    border-radius: 11px;

    background:
        linear-gradient(
            135deg,
            rgba(255,59,82,.22),
            rgba(255,166,0,.12)
        );

    color: #ff6170;

    font-size: 13px;
    font-weight: 900;
}

.activity-log-actor strong {
    display: block;

    color: #f1f3f7;

    font-size: 12px;
}

.activity-log-actor small {
    display: block;

    margin-top: 3px;

    color: #6e7788;

    font-size: 10px;
}


/* =========================================================
   MODULE
========================================================= */

.activity-log-module {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 7px 9px;

    border-radius: 8px;

    background: rgba(75, 105, 255, .08);

    color: #aab8ff;

    font-size: 10px;
    font-weight: 800;
}

.activity-log-module i {
    color: #7185ff;
}


/* =========================================================
   ACTION
========================================================= */

.activity-log-action > span {
    display: inline-flex;

    padding: 7px 10px;

    border-radius: 7px;

    background: rgba(255,255,255,.055);

    color: #d8dde7;

    font-size: 10px;
    font-weight: 900;
}

.activity-log-action > span.is-danger {
    background: rgba(255,59,82,.10);
    color: #ff6676;
}

.activity-log-action > span.is-create {
    background: rgba(32,214,154,.09);
    color: #38dda8;
}

.activity-log-action > span.is-update {
    background: rgba(255,181,46,.09);
    color: #ffbd46;
}

.activity-log-action p {
    margin: 7px 0 0;

    color: #777f90;

    font-size: 11px;
    line-height: 1.5;
}


/* =========================================================
   IP
========================================================= */

.activity-log-ip {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    color: #9ea7b7;

    font-family: monospace;

    font-size: 11px;
}

.activity-log-ip i {
    color: #7185ff;
}


/* =========================================================
   EMPTY
========================================================= */

.activity-log-empty {
    padding: 60px 20px;

    text-align: center;
}

.activity-log-empty > i {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    width: 55px;
    height: 55px;

    margin-bottom: 14px;

    border-radius: 15px;

    background: rgba(255,166,0,.08);

    color: #ffb52e;

    font-size: 22px;
}

.activity-log-empty h3 {
    margin: 0 0 6px;

    color: #f1f3f7;

    font-size: 17px;
}

.activity-log-empty p {
    margin: 0;

    color: #70798a;

    font-size: 12px;
}


/* =========================================================
   PAGINATION
========================================================= */

.activity-log-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;

    padding: 18px 20px;

    border-top: 1px solid rgba(255,255,255,.06);
}

.activity-log-pagination-info {
    color: #7d8696;

    font-size: 11px;
    font-weight: 700;
}

.activity-log-pagination-nav {
    display: flex;
    align-items: center;
    gap: 6px;

    flex-wrap: wrap;
}

.activity-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 34px;
    height: 34px;

    padding: 0 9px;

    border: 1px solid rgba(255,255,255,.08);
    border-radius: 8px;

    background: rgba(255,255,255,.035);

    color: #aeb5c3;

    text-decoration: none;

    font-size: 11px;
    font-weight: 800;

    transition: .2s ease;
}

.activity-page-btn:hover {
    border-color: rgba(255,59,82,.4);

    background: rgba(255,59,82,.08);

    color: #ff6373;
}

.activity-page-btn.active {
    border-color: transparent;

    background:
        linear-gradient(
            135deg,
            #ff334d,
            #ff7040
        );

    color: #ffffff;

    box-shadow:
        0 7px 18px rgba(255,51,77,.20);
}

.activity-page-btn.disabled {
    opacity: .3;

    cursor: not-allowed;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1250px) {

    .activity-log-hero {
        grid-template-columns: 1fr;
    }

    .activity-log-filter {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .activity-log-filter-actions {
        grid-column: 1 / -1;
    }

    .activity-log-stats {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }
}


@media (max-width: 700px) {

    .activity-log-hero {
        padding: 20px;

        border-radius: 17px;
    }

    .activity-log-hero h2 {
        font-size: 30px;
    }

    .activity-log-filter {
        grid-template-columns: 1fr;
    }

    .activity-log-filter-actions {
        grid-column: auto;
    }

    .activity-log-stats {
        grid-template-columns: 1fr;
    }

    .activity-log-panel-head {
        flex-direction: column;
    }

    .activity-log-pagination {
        flex-direction: column;
        align-items: flex-start;
    }
}


/* =========================================================
   FIX QUAN TRỌNG CHO DROPDOWN KHI LAYOUT CÓ NOTIFICATION
========================================================= */

/*
 * Không cho các wrapper của riêng trang này tạo stacking context
 * làm dropdown bị chìm xuống.
 */

.activity-log-page,
.activity-log-page > *,
.activity-log-hero,
.activity-log-filter,
.activity-function-filter,
.activity-select {
    overflow: visible !important;
}

.activity-log-hero {
    isolation: auto !important;
}

.activity-select-dropdown {
    isolation: isolate !important;
}


/*
 * Đảm bảo dropdown cao hơn các card bên dưới.
 */

.activity-log-hero {
    z-index: 100 !important;
}

.activity-log-filter {
    z-index: 1000 !important;
}

.activity-function-filter {
    z-index: 2000 !important;
}

.activity-select {
    z-index: 3000 !important;
}

.activity-select-dropdown {
    z-index: 99999 !important;
}

</style>


<div class="activity-log-page">

    {{-- =====================================================
         HERO + FILTER
    ====================================================== --}}
    <section class="activity-log-hero">

        {{-- LEFT --}}
        <div>

            <span class="activity-log-kicker">
                <i class="fa-solid fa-shield-halved"></i>
                Audit Trail
            </span>

            <h2>
                Nhật ký hoạt động hệ thống
            </h2>

            <p>
                Kiểm tra nhanh ai đã thao tác, thao tác ở module nào,
                thời điểm nào và từ địa chỉ IP nào.
            </p>

            <div class="activity-log-meta">

                <span>
                    <i class="fa-solid fa-database"></i>
                    {{ number_format($logs->total()) }}
                    bản ghi đang lọc
                </span>

                <span>
                    <i class="fa-regular fa-clock"></i>
                    Sắp xếp mới nhất trước
                </span>

                <span>
                    <i class="fa-solid fa-filter"></i>
                    {{ $hasFilters ? 'Đang áp dụng bộ lọc' : 'Chưa lọc dữ liệu' }}
                </span>

            </div>

        </div>


        {{-- RIGHT FILTER --}}
        <form
            method="GET"
            action="{{ route('admin.activity-logs.index') }}"
            class="activity-log-filter"
        >

            {{-- SEARCH --}}
            <label class="activity-log-search">

                <span>Tìm kiếm</span>

                <div>

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        placeholder="Hành động, mô tả, email, IP..."
                    >

                </div>

            </label>


            {{-- CHỨC NĂNG --}}
            <label class="activity-function-filter">

                <span>Chức năng</span>

                <div class="activity-select">

                    <button
                        type="button"
                        class="activity-select-trigger"
                    >

                        <span class="activity-select-text">
                            {{ request('chuc_nang') ?: 'Tất cả chức năng' }}
                        </span>

                        <i class="fa-solid fa-chevron-down"></i>

                    </button>


                    <div class="activity-select-dropdown">

                        {{-- Tất cả --}}
                        <div
                            class="activity-select-option
                            {{ !request('chuc_nang') ? 'active' : '' }}"
                            data-value=""
                        >
                            Tất cả chức năng
                        </div>


                        {{-- Danh sách module --}}
                        @foreach($modules as $module)

                            <div
                                class="activity-select-option
                                {{ request('chuc_nang') === $module ? 'active' : '' }}"
                                data-value="{{ $module }}"
                            >
                                {{ $module }}
                            </div>

                        @endforeach

                    </div>


                    <input
                        type="hidden"
                        name="chuc_nang"
                        value="{{ request('chuc_nang') }}"
                    >

                </div>

            </label>


            {{-- TỪ NGÀY --}}
            <label>

                <span>Từ ngày</span>

                <input
                    type="date"
                    name="from"
                    id="from"
                    value="{{ request('from') }}"
                >

            </label>


            {{-- ĐẾN NGÀY --}}
            <label>

                <span>Đến ngày</span>

                <input
                    type="date"
                    name="to"
                    id="to"
                    value="{{ request('to') }}"
                >

            </label>


            {{-- BUTTON --}}
            <div class="activity-log-filter-actions">

                <button type="submit">

                    <i class="fa-solid fa-filter"></i>

                    Lọc nhật ký

                </button>


                @if($hasFilters)

                    <a
                        href="{{ route('admin.activity-logs.index') }}"
                        title="Xóa bộ lọc"
                    >

                        <i class="fa-solid fa-rotate-left"></i>

                    </a>

                @endif

            </div>

        </form>

    </section>


    {{-- =====================================================
         STATISTICS
    ====================================================== --}}
    <section class="activity-log-stats">

        @foreach($statCards as $card)

            <article class="activity-log-stat {{ $card['tone'] }}">

                <span>
                    <i class="fa-solid {{ $card['icon'] }}"></i>
                </span>

                <div>

                    <small>
                        {{ $card['label'] }}
                    </small>

                    <strong>
                        {{ number_format($card['value']) }}
                    </strong>

                </div>

            </article>

        @endforeach

    </section>


    {{-- =====================================================
         TABLE
    ====================================================== --}}
    <section class="activity-log-panel">

        <div class="activity-log-panel-head">

            <div>

                <span class="activity-log-kicker">
                    Danh sách
                </span>

                <h3>
                    Dòng thời gian thao tác
                </h3>

                <p>
                    Mỗi bản ghi gồm người thao tác, module,
                    hành động, mô tả và IP để hỗ trợ rà soát nhanh.
                </p>

            </div>


            <span class="activity-log-count">

                <i class="fa-solid fa-list-check"></i>

                {{ number_format($logs->total()) }}
                bản ghi

            </span>

        </div>


        <div class="activity-log-table-wrap">

            <table class="activity-log-table">

                <thead>

                    <tr>

                        <th>
                            Thời gian
                        </th>

                        <th>
                            Người thao tác
                        </th>

                        <th>
                            Chức năng
                        </th>

                        <th>
                            Hành động & mô tả
                        </th>

                        <th>
                            Địa chỉ IP
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($logs as $log)

                        @php

                            $actorName =
                                $log->nguoiDung?->ho_ten
                                ?: 'Hệ thống';

                            $actorEmail =
                                $log->nguoiDung?->email
                                ?: 'Tác vụ tự động';

                            $initial =
                                mb_strtoupper(
                                    mb_substr(
                                        $actorName,
                                        0,
                                        1
                                    )
                                );

                            $actionText =
                                mb_strtolower(
                                    $log->hanh_dong ?? ''
                                );

                            $tone = 'is-neutral';

                            if (
                                str_contains($actionText, 'xóa') ||
                                str_contains($actionText, 'hủy') ||
                                str_contains($actionText, 'khóa')
                            ) {

                                $tone = 'is-danger';

                            } elseif (
                                str_contains($actionText, 'thêm') ||
                                str_contains($actionText, 'tạo')
                            ) {

                                $tone = 'is-create';

                            } elseif (
                                str_contains($actionText, 'cập nhật') ||
                                str_contains($actionText, 'sửa')
                            ) {

                                $tone = 'is-update';

                            }

                        @endphp


                        <tr>

                            {{-- TIME --}}
                            <td data-label="Thời gian">

                                <span class="activity-log-time">

                                    {{ $log->created_at->format('d/m/Y H:i:s') }}

                                </span>

                                <small>

                                    {{ $log->created_at->diffForHumans() }}

                                </small>

                            </td>


                            {{-- ACTOR --}}
                            <td data-label="Người thao tác">

                                <div class="activity-log-actor">

                                    <span>
                                        {{ $initial }}
                                    </span>

                                    <div>

                                        <strong>
                                            {{ $actorName }}
                                        </strong>

                                        <small>
                                            {{ $actorEmail }}
                                        </small>

                                    </div>

                                </div>

                            </td>


                            {{-- MODULE --}}
                            <td data-label="Chức năng">

                                <span class="activity-log-module">

                                    <i class="fa-solid fa-cube"></i>

                                    {{ $log->chuc_nang ?: 'Không xác định' }}

                                </span>

                            </td>


                            {{-- ACTION --}}
                            <td data-label="Hành động">

                                <div class="activity-log-action">

                                    <span class="{{ $tone }}">

                                        {{ $log->hanh_dong ?: 'Không rõ hành động' }}

                                    </span>

                                    <p>
                                        {{ $log->mo_ta ?: 'Không có mô tả chi tiết.' }}
                                    </p>

                                </div>

                            </td>


                            {{-- IP --}}
                            <td data-label="Địa chỉ IP">

                                <span class="activity-log-ip">

                                    <i class="fa-solid fa-network-wired"></i>

                                    {{ $log->dia_chi_ip ?: '-' }}

                                </span>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="5">

                                <div class="activity-log-empty">

                                    <i class="fa-solid fa-clock-rotate-left"></i>

                                    <h3>
                                        Chưa có nhật ký phù hợp
                                    </h3>

                                    <p>
                                        Thử thay đổi từ khóa,
                                        chức năng hoặc khoảng ngày
                                        để xem thêm bản ghi.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =================================================
             PAGINATION
        ================================================== --}}
        <div class="activity-log-pagination">

            <span class="activity-log-pagination-info">

                Hiển thị
                {{ $logs->firstItem() ?? 0 }}
                -
                {{ $logs->lastItem() ?? 0 }}

                trên

                {{ number_format($logs->total()) }}

                bản ghi

            </span>


            @if($logs->lastPage() > 1)

                <nav
                    class="activity-log-pagination-nav"
                    aria-label="Phân trang"
                >

                    {{-- PREVIOUS --}}
                    @if($logs->onFirstPage())

                        <span class="activity-page-btn disabled">

                            <i class="fa-solid fa-chevron-left"></i>

                        </span>

                    @else

                        <a
                            href="{{ $logs->previousPageUrl() }}"
                            class="activity-page-btn"
                            aria-label="Trang trước"
                        >

                            <i class="fa-solid fa-chevron-left"></i>

                        </a>

                    @endif


                    {{-- PAGE NUMBERS --}}
                    @foreach(
                        $logs->getUrlRange(
                            1,
                            $logs->lastPage()
                        )
                        as $page => $url
                    )

                        @if($page == $logs->currentPage())

                            <span class="activity-page-btn active">
                                {{ $page }}
                            </span>

                        @else

                            <a
                                href="{{ $url }}"
                                class="activity-page-btn"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endforeach


                    {{-- NEXT --}}
                    @if($logs->hasMorePages())

                        <a
                            href="{{ $logs->nextPageUrl() }}"
                            class="activity-page-btn"
                            aria-label="Trang sau"
                        >

                            <i class="fa-solid fa-chevron-right"></i>

                        </a>

                    @else

                        <span class="activity-page-btn disabled">

                            <i class="fa-solid fa-chevron-right"></i>

                        </span>

                    @endif

                </nav>

            @endif

        </div>

    </section>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       DROPDOWN CHỨC NĂNG
    ====================================================== */

    const select = document.querySelector('.activity-select');

    if (select) {

        const trigger =
            select.querySelector('.activity-select-trigger');

        const text =
            select.querySelector('.activity-select-text');

        const input =
            select.querySelector(
                'input[name="chuc_nang"]'
            );

        const options =
            select.querySelectorAll(
                '.activity-select-option'
            );


        /* MỞ / ĐÓNG DROPDOWN */
        trigger.addEventListener('click', function (e) {

            e.preventDefault();
            e.stopPropagation();

            select.classList.toggle('open');

        });


        /* CHỌN OPTION */
        options.forEach(function (option) {

            option.addEventListener('click', function (e) {

                e.preventDefault();
                e.stopPropagation();

                const value =
                    this.dataset.value || '';

                const label =
                    this.textContent.trim();


                input.value = value;

                text.textContent = label;


                options.forEach(function (item) {

                    item.classList.remove('active');

                });


                this.classList.add('active');

                select.classList.remove('open');

            });

        });


        /* CLICK RA NGOÀI */
        document.addEventListener('click', function (e) {

            if (!select.contains(e.target)) {

                select.classList.remove('open');

            }

        });


        /* ESC ĐỂ ĐÓNG */
        document.addEventListener('keydown', function (e) {

            if (e.key === 'Escape') {

                select.classList.remove('open');

            }

        });

    }


    /* =====================================================
       LỌC TỪ NGÀY -> ĐẾN NGÀY
    ====================================================== */

    const fromInput =
        document.getElementById('from');

    const toInput =
        document.getElementById('to');


    if (fromInput && toInput) {


        function updateDateLimits() {

            const fromDate =
                fromInput.value;

            const toDate =
                toInput.value;


            /* TỪ NGÀY -> ĐẾN NGÀY */

            if (fromDate) {

                toInput.min = fromDate;

            } else {

                toInput.removeAttribute('min');

            }


            /* ĐẾN NGÀY -> TỪ NGÀY */

            if (toDate) {

                fromInput.max = toDate;

            } else {

                fromInput.removeAttribute('max');

            }


            /* DỮ LIỆU KHÔNG HỢP LỆ */

            if (
                fromDate &&
                toDate &&
                toDate < fromDate
            ) {

                toInput.value = '';

                fromInput.removeAttribute('max');

            }

        }


        /* CHỌN TỪ NGÀY */

        fromInput.addEventListener(
            'change',
            function () {

                const fromDate =
                    this.value;


                if (!fromDate) {

                    toInput.removeAttribute('min');

                    return;

                }


                toInput.min = fromDate;


                if (
                    toInput.value &&
                    toInput.value < fromDate
                ) {

                    toInput.value = '';

                }

            }
        );


        /* CHỌN ĐẾN NGÀY */

        toInput.addEventListener(
            'change',
            function () {

                const toDate =
                    this.value;


                if (!toDate) {

                    fromInput.removeAttribute('max');

                    return;

                }


                fromInput.max = toDate;


                if (
                    fromInput.value &&
                    fromInput.value > toDate
                ) {

                    fromInput.value = '';

                }

            }
        );


        /* LOAD TRANG */

        updateDateLimits();

    }

});

</script>

@endsection