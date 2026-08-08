@extends('layouts.admin')

@push('styles')
{{-- Flatpickr disabled due to CORS issues - using native date input --}}
<style>
    .stats-page {
        padding: 0;
    }

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

    .filter-row>* {
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
        from {
            opacity: 0;
            transform: translateY(-10px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
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
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
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
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--gold-main), var(--gold-light));
    }

    .kpi-card:hover {
        border-color: var(--gold-main);
        transform: translateY(-2px);
    }

    .kpi-icon {
        width: 40px;
        height: 40px;
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

    .main-grid.full {
        grid-template-columns: 1fr;
    }

    /* === PANEL CARD === */
    .panel {
        background: var(--black-card);
        border: 1px solid rgba(217, 154, 50, 0.12);
        border-radius: 12px;
        overflow: hidden;
    }

    .panel-header {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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

    .panel-header h3 i {
        color: var(--gold-main);
    }

    .panel-header .badge {
        font-size: 0.7rem;
        color: var(--gray-text);
    }

    .panel-body {
        padding: 16px;
    }

    /* === CHART CONTAINER === */
    .chart-wrap {
        height: 300px;
        position: relative;
    }

    .chart-wrap.sm {
        height: 220px;
    }

    /* === TOP LIST === */
    .top-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .top-list li {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    }

    .top-list li:last-child {
        border-bottom: none;
    }

    .rank-badge {
        width: 24px;
        height: 24px;
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

    .rank-badge.gold {
        background: linear-gradient(135deg, #f7b84b, #d99a32);
        color: #000;
    }

    .rank-badge.silver {
        background: rgba(156, 163, 175, 0.15);
        color: #9ca3af;
    }

    .top-info {
        flex: 1;
        min-width: 0;
    }

    .top-info strong {
        display: block;
        font-size: 0.85rem;
        color: var(--white);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .top-info small {
        font-size: 0.7rem;
        color: var(--gray-text);
    }

    .top-amount {
        text-align: right;
        flex-shrink: 0;
    }

    .top-amount strong {
        font-size: 0.85rem;
        color: var(--gold-main);
        font-weight: 800;
        display: block;
    }

    .top-amount small {
        font-size: 0.65rem;
        color: var(--gray-text);
    }

    /* === PAYMENT === */
    .pay-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .pay-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: var(--black-soft);
        border-radius: 8px;
    }

    .pay-item .icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: rgba(217, 154, 50, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pay-item .icon i {
        color: var(--gold-main);
        font-size: 0.8rem;
    }

    .pay-item .info {
        flex: 1;
    }

    .pay-item .info strong {
        font-size: 0.8rem;
        color: var(--white);
    }

    .pay-item .info small {
        font-size: 0.65rem;
        color: var(--gray-text);
    }

    .pay-item .amount {
        text-align: right;
    }

    .pay-item .amount strong {
        font-size: 0.85rem;
        color: var(--gold-main);
        font-weight: 800;
    }

    .pay-item .amount small {
        font-size: 0.65rem;
        color: var(--gray-text);
    }

    /* === BAR STATS === */
    .bar-stat {
        margin-bottom: 12px;
    }

    .bar-stat:last-child {
        margin-bottom: 0;
    }

    .bar-stat-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .bar-stat-label span:first-child {
        font-size: 0.8rem;
        color: var(--white);
    }

    .bar-stat-label span:last-child {
        font-size: 0.8rem;
        color: var(--gold-main);
        font-weight: 700;
    }

    .bar-track {
        height: 6px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 3px;
        overflow: hidden;
    }

    .bar-fill {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, var(--gold-main), var(--gold-light));
        transition: width 0.5s ease;
    }

    /* === MINI STATS === */
    .mini-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 16px;
    }

    .mini-stat {
        background: var(--black-soft);
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }

    .mini-stat .val {
        font-size: 1.1rem;
        font-weight: 900;
        color: var(--gold-main);
    }

    .mini-stat .lbl {
        font-size: 0.65rem;
        color: var(--gray-text);
        margin-top: 4px;
        text-transform: uppercase;
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

    <div class="filter-section-modern">

        {{-- =====================================================
        MAIN FILTER ROW
    ====================================================== --}}

        <div class="filter-main-row">

            {{-- =================================================
            PERIOD TABS
        ================================================== --}}

            <div class="period-tabs">

                <button type="button"
                    class="period-tab {{ $periodType == 'day' ? 'active' : '' }}"
                    data-period="day"
                    onclick="selectPeriodType('day')">
                    <i class="fas fa-calendar-day"></i>
                    <span>Ngày</span>
                </button>

                <button type="button"
                    class="period-tab {{ $periodType == 'month' ? 'active' : '' }}"
                    data-period="month"
                    onclick="selectPeriodType('month')">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Tháng</span>
                </button>

                <button type="button"
                    class="period-tab {{ $periodType == 'quarter' ? 'active' : '' }}"
                    data-period="quarter"
                    onclick="selectPeriodType('quarter')">
                    <i class="fas fa-calendar-week"></i>
                    <span>Quý</span>
                </button>

                <button type="button"
                    class="period-tab {{ $periodType == 'year' ? 'active' : '' }}"
                    data-period="year"
                    onclick="selectPeriodType('year')">
                    <i class="fas fa-calendar"></i>
                    <span>Năm</span>
                </button>

            </div>


            {{-- =================================================
            TIME SELECTOR
        ================================================== --}}

            <div class="time-selector-box" id="timeSelectorBox">

                {{-- =============================================
                NGÀY: TỪ NGÀY → ĐẾN NGÀY
            ============================================== --}}

                <div class="time-range-wrapper {{ $periodType == 'day' ? '' : 'hidden' }}"
                    id="dateRangeWrap">

                    {{-- Từ ngày --}}
                    <div class="time-range-item">

                        <label>Từ ngày</label>

                        <div class="input-wrapper-inner">

                            <i class="fas fa-calendar-day input-icon"></i>

                            <input
                                type="date"
                                class="date-input"
                                id="dateFrom"
                                value="{{ $periodType == 'day' && $from ? Carbon\Carbon::parse($from)->toDateString() : date('Y-m-d') }}"
                                onchange="updateFilterDescription()">

                        </div>

                    </div>


                    <span class="range-separator">
                        <i class="fas fa-arrow-right"></i>
                    </span>


                    {{-- Đến ngày --}}
                    <div class="time-range-item">

                        <label>Đến ngày</label>

                        <div class="input-wrapper-inner">

                            <i class="fas fa-calendar-day input-icon"></i>

                            <input
                                type="date"
                                class="date-input"
                                id="dateTo"
                                value="{{ $periodType == 'day' && $to ? Carbon\Carbon::parse($to)->toDateString() : date('Y-m-d') }}"
                                onchange="updateFilterDescription()">

                        </div>

                    </div>

                </div>


                {{-- =============================================
                THÁNG: TỪ THÁNG → ĐẾN THÁNG
            ============================================== --}}

                <div class="time-range-wrapper {{ $periodType == 'month' ? '' : 'hidden' }}"
                    id="monthRangeWrap">

                    {{-- Từ tháng --}}
                    <div class="time-range-item">

                        <label>Từ tháng</label>

                        <div class="input-wrapper-inner">

                            <i class="fas fa-calendar-alt input-icon"></i>

                            <input
                                type="month"
                                class="date-input"
                                id="monthFrom"
                                value="{{ $periodType == 'month' && $from ? substr($from, 0, 7) : date('Y-m') }}"
                                onchange="updateFilterDescription()">

                        </div>

                    </div>


                    <span class="range-separator">
                        <i class="fas fa-arrow-right"></i>
                    </span>


                    {{-- Đến tháng --}}
                    <div class="time-range-item">

                        <label>Đến tháng</label>

                        <div class="input-wrapper-inner">

                            <i class="fas fa-calendar-alt input-icon"></i>

                            <input
                                type="month"
                                class="date-input"
                                id="monthTo"
                                value="{{ $periodType == 'month' && $to ? substr($to, 0, 7) : date('Y-m') }}"
                                onchange="updateFilterDescription()">

                        </div>

                    </div>

                </div>


                {{-- =============================================
                QUÝ: TỪ QUÝ → ĐẾN QUÝ
            ============================================== --}}

                <div class="time-range-wrapper {{ $periodType == 'quarter' ? '' : 'hidden' }}"
                    id="quarterRangeWrap">


                    {{-- -----------------------------------------
                    TỪ QUÝ
                ------------------------------------------ --}}

                    <div class="time-range-item">

                        <label>Từ quý</label>

                        <div class="filter-dropdown"
                            id="dropdownQuyFrom">

                            <div class="filter-dropdown-trigger"
                                onclick="toggleDropdown('dropdownQuyFrom')">

                                <span class="filter-label">
                                    <i class="fas fa-calendar-week"></i>
                                </span>

                                <span class="filter-value"
                                    id="selectedQuyFromName">

                                    Q{{ $fromQuarter ?? ceil(date('n') / 3) }}/{{ $fromQuarterYear ?? date('Y') }}

                                </span>

                                <i class="fas fa-chevron-down filter-arrow"></i>

                            </div>


                            <div class="filter-dropdown-menu">

                                <div class="filter-options">

                                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)

                                    @for($q = 1; $q <= 4; $q++)

                                        <div
                                        class="filter-option"
                                        data-quarter="{{ $q }}"
                                        data-year="{{ $y }}"
                                        onclick="selectQuarterFrom({{ $q }}, {{ $y }})">

                                        <i class="fas fa-calendar-week me-2"></i>

                                        Q{{ $q }}/{{ $y }}

                                </div>

                                @endfor

                                @endfor

                            </div>

                        </div>

                    </div>

                </div>


                <span class="range-separator">
                    <i class="fas fa-arrow-right"></i>
                </span>


                {{-- -----------------------------------------
                    ĐẾN QUÝ
                ------------------------------------------ --}}

                <div class="time-range-item">

                    <label>Đến quý</label>

                    <div class="filter-dropdown"
                        id="dropdownQuyTo">

                        <div class="filter-dropdown-trigger"
                            onclick="toggleDropdown('dropdownQuyTo')">

                            <span class="filter-label">
                                <i class="fas fa-calendar-week"></i>
                            </span>

                            <span class="filter-value"
                                id="selectedQuyToName">

                                Q{{ $toQuarter ?? ($fromQuarter ?? ceil(date('n') / 3)) }}/{{ $toQuarterYear ?? ($fromQuarterYear ?? date('Y')) }}
                            </span>

                            <i class="fas fa-chevron-down filter-arrow"></i>

                        </div>


                        <div class="filter-dropdown-menu">

                            <div class="filter-options">

                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)

                                @for($q = 1; $q <= 4; $q++)

                                    <div
                                    class="filter-option"
                                    data-quarter="{{ $q }}"
                                    data-year="{{ $y }}"
                                    onclick="selectQuarterTo({{ $q }}, {{ $y }})">

                                    <i class="fas fa-calendar-week me-2"></i>

                                    Q{{ $q }}/{{ $y }}

                            </div>

                            @endfor

                            @endfor

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =============================================
                NĂM: TỪ NĂM → ĐẾN NĂM
            ============================================== --}}

        <div class="time-range-wrapper {{ $periodType == 'year' ? '' : 'hidden' }}"
            id="yearRangeWrap">


            {{-- -----------------------------------------
                    TỪ NĂM
                ------------------------------------------ --}}

            <div class="time-range-item">

                <label>Từ năm</label>

                <div class="filter-dropdown"
                    id="dropdownNamFrom">

                    <div class="filter-dropdown-trigger"
                        onclick="toggleDropdown('dropdownNamFrom')">

                        <span class="filter-label">
                            <i class="fas fa-calendar"></i>
                        </span>

                        <span class="filter-value"
                            id="selectedNamFromName">

                            {{ $fromYear ?? date('Y') }}

                        </span>

                        <i class="fas fa-chevron-down filter-arrow"></i>

                    </div>


                    <div class="filter-dropdown-menu">

                        <div class="filter-options">

                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)

                            <div
                                class="filter-option"
                                data-value="{{ $y }}"
                                onclick="selectYearFrom({{ $y }})">

                                <i class="fas fa-calendar me-2"></i>

                                {{ $y }}

                            </div>

                            @endfor

                        </div>

                    </div>

                </div>

            </div>


            <span class="range-separator">
                <i class="fas fa-arrow-right"></i>
            </span>


            {{-- -----------------------------------------
                    ĐẾN NĂM
                ------------------------------------------ --}}

            <div class="time-range-item">

                <label>Đến năm</label>

                <div class="filter-dropdown"
                    id="dropdownNamTo">

                    <div class="filter-dropdown-trigger"
                        onclick="toggleDropdown('dropdownNamTo')">

                        <span class="filter-label">
                            <i class="fas fa-calendar"></i>
                        </span>

                        <span class="filter-value"
                            id="selectedNamToName">

                            {{ $toYear ?? ($fromYear ?? date('Y')) }}

                        </span>

                        <i class="fas fa-chevron-down filter-arrow"></i>

                    </div>


                    <div class="filter-dropdown-menu">

                        <div class="filter-options">

                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)

                            <div
                                class="filter-option"
                                data-value="{{ $y }}"
                                onclick="selectYearTo({{ $y }})">

                                <i class="fas fa-calendar me-2"></i>

                                {{ $y }}

                            </div>

                            @endfor

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =================================================
            MOVIE FILTER
        ================================================== --}}

    <div class="filter-dropdown"
        id="dropdownPhim">

        <div class="filter-dropdown-trigger"
            onclick="toggleDropdown('dropdownPhim')">

            <span class="filter-label">
                <i class="fas fa-film"></i>
            </span>

            <span class="filter-value"
                id="selectedPhimName">

                {{ ($phimId ?? '') ? ($movies[$phimId] ?? 'Tất cả phim') : 'Tất cả phim' }}

            </span>

            <i class="fas fa-chevron-down filter-arrow"></i>

        </div>


        <div class="filter-dropdown-menu">

            <div class="filter-search">

                <input
                    type="text"
                    placeholder="Tìm kiếm phim..."
                    onkeyup="filterOptions('dropdownPhim', this.value)">

            </div>


            <div class="filter-options"
                id="optionsPhim">

                {{-- Tất cả phim --}}
                <div
                    class="filter-option {{ ($phimId ?? '') == '' ? 'selected' : '' }}"
                    data-value=""
                    onclick="selectFilterOption('phim', '', 'Tất cả phim')">

                    <i class="fas fa-list me-2"></i>

                    Tất cả phim

                </div>


                {{-- Danh sách phim --}}
                @foreach($movies ?? [] as $id => $name)

                <div
                    class="filter-option {{ ($phimId ?? '') == $id ? 'selected' : '' }}"
                    data-value="{{ $id }}"
                    data-search="{{ strtolower($name) }}"
                    onclick="selectFilterOption('phim', '{{ $id }}', '{{ $name }}')">

                    <i class="fas fa-film me-2"></i>

                    {{ $name }}

                </div>

                @endforeach

            </div>

        </div>


        <input
            type="hidden"
            id="filterPhimId"
            value="{{ $phimId ?? '' }}">

    </div>


    {{-- =================================================
            ROOM FILTER
        ================================================== --}}

    <div class="filter-dropdown"
        id="dropdownPhong">

        <div class="filter-dropdown-trigger"
            onclick="toggleDropdown('dropdownPhong')">

            <span class="filter-label">
                <i class="fas fa-door-open"></i>
            </span>

            <span class="filter-value"
                id="selectedPhongName">

                {{ ($phongChieuId ?? '') ? ($rooms[$phongChieuId] ?? 'Tất cả phòng') : 'Tất cả phòng' }}

            </span>

            <i class="fas fa-chevron-down filter-arrow"></i>

        </div>


        <div class="filter-dropdown-menu">

            <div class="filter-search">

                <input
                    type="text"
                    placeholder="Tìm kiếm phòng..."
                    onkeyup="filterOptions('dropdownPhong', this.value)">

            </div>


            <div class="filter-options"
                id="optionsPhong">

                {{-- Tất cả phòng --}}
                <div
                    class="filter-option {{ ($phongChieuId ?? '') == '' ? 'selected' : '' }}"
                    data-value=""
                    onclick="selectFilterOption('phong', '', 'Tất cả phòng')">

                    <i class="fas fa-list me-2"></i>

                    Tất cả phòng

                </div>


                {{-- Danh sách phòng --}}
                @foreach($rooms ?? [] as $id => $name)

                <div
                    class="filter-option {{ ($phongChieuId ?? '') == $id ? 'selected' : '' }}"
                    data-value="{{ $id }}"
                    data-search="{{ strtolower($name) }}"
                    onclick="selectFilterOption('phong', '{{ $id }}', '{{ $name }}')">

                    <i class="fas fa-door-open me-2"></i>

                    {{ $name }}

                </div>

                @endforeach

            </div>

        </div>


        <input
            type="hidden"
            id="filterPhongId"
            value="{{ $phongChieuId ?? '' }}">

    </div>


    {{-- =================================================
            ACTION BUTTONS
        ================================================== --}}

    <div class="filter-actions">

        {{-- Làm mới --}}
        <button
            type="button"
            class="btn-filter-secondary"
            onclick="resetFilters()">

            <i class="fas fa-redo-alt"></i>

            <span>Làm mới</span>

        </button>


        {{-- Lọc --}}
        <button
            type="button"
            class="btn-filter-primary"
            onclick="applyFilters()">

            <i class="fas fa-filter"></i>

            <span>Lọc</span>

        </button>


        {{-- Xuất Excel --}}
        <a
            href="{{ route('admin.thong-ke.export-excel', [
                    'period_type' => $periodType ?? 'day',
                    'from' => $from ? Carbon\Carbon::parse($from)->toDateString() : '',
                    'to' => $to ? Carbon\Carbon::parse($to)->toDateString() : '',
                    'phim_id' => $phimId ?? '',
                    'phong_chieu_id' => $phongChieuId ?? ''
                ]) }}"
            class="btn-export">

            <i class="fas fa-file-excel"></i>

            <span>Xuất Excel</span>

        </a>

    </div>

</div>


{{-- =====================================================
        FILTER DESCRIPTION
    ====================================================== --}}

<div class="filter-description"
    id="filterDescription">

    <i class="fas fa-info-circle filter-description-icon"></i>

    <span
        class="filter-description-text"
        id="filterDescriptionText">

        <strong>Đang thống kê:</strong>

        @if($periodType == 'day')

        {{ $from && $to
                    ? Carbon\Carbon::parse($from)->format('d/m/Y') . ' → ' . Carbon\Carbon::parse($to)->format('d/m/Y')
                    : 'Hôm nay'
                }}

        @elseif($periodType == 'month')

        {{ $from && $to
                    ? Carbon\Carbon::parse($from)->format('m/Y') . ' → ' . Carbon\Carbon::parse($to)->format('m/Y')
                    : 'Tháng hiện tại'
                }}

        @elseif($periodType == 'quarter')

        Q{{ $quarterFrom ?? ($quarter ?? ceil(date('n') / 3)) }}/{{ $yearFrom ?? ($year ?? date('Y')) }}
        →
        Q{{ $quarterTo ?? ($quarter ?? ceil(date('n') / 3)) }}/{{ $yearTo ?? ($year ?? date('Y')) }}

        @else

        {{ $yearFrom ?? ($year ?? date('Y')) }}
        →
        {{ $yearTo ?? ($year ?? date('Y')) }}

        @endif

        • {{ ($phimId ?? '') ? ($movies[$phimId] ?? 'Tất cả phim') : 'Tất cả phim' }}

        • {{ ($phongChieuId ?? '') ? ($rooms[$phongChieuId] ?? 'Tất cả phòng') : 'Tất cả phòng' }}

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

{{-- Food & Combo Chart --}}
<div class="main-grid">
    <div class="panel" id="panel-food-revenue">
        <div class="panel-header">
            <h3>
                <i class="fas fa-utensils"></i>
                Doanh thu đồ ăn & combo
            </h3>
            <span class="badge">Theo loại</span>
        </div>

        <div class="panel-body">
            <div class="chart-wrap">
                <canvas id="foodRevenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="panel" id="panel-food-summary">
        <div class="panel-header">
            <h3>
                <i class="fas fa-chart-column"></i>
                Tổng quan bán đồ ăn
            </h3>
        </div>

        <div class="panel-body">
            <div class="mini-stats food-mini-stats">

                <div class="mini-stat">
                    <div class="val">
                        {{ number_format((float)($kpi['combo_revenue'] ?? 0), 0, ',', '.') }}đ
                    </div>
                    <div class="lbl">
                        <i class="fas fa-burger"></i>
                        Combo
                    </div>
                </div>

                <div class="mini-stat">
                    <div class="val">
                        {{ number_format((float)($kpi['snack_revenue'] ?? 0), 0, ',', '.') }}đ
                    </div>
                    <div class="lbl">
                        <i class="fas fa-cookie-bite"></i>
                        Đồ ăn & nước
                    </div>
                </div>

                @php
                $foodTotal =
                (float)($kpi['combo_revenue'] ?? 0)
                + (float)($kpi['snack_revenue'] ?? 0);
                @endphp

                <div class="mini-stat">
                    <div class="val">
                        {{ number_format($foodTotal, 0, ',', '.') }}đ
                    </div>
                    <div class="lbl">
                        <i class="fas fa-utensils"></i>
                        Tổng đồ ăn
                    </div>
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
    document.addEventListener('DOMContentLoaded', function() {

        /* =====================================================
           BIẾN FILTER
        ===================================================== */

        let currentPeriod = @json($periodType ?? 'day');

        let selectedPhim = @json($phimId ?? '');
        let selectedPhong = @json($phongChieuId ?? '');

        let selectedFromQuarter = @json($fromQuarter ?? ceil(date('n') / 3));
        let selectedFromQuarterYear = @json($fromQuarterYear ?? date('Y'));

        let selectedToQuarter = @json($toQuarter ?? ceil(date('n') / 3));
        let selectedToQuarterYear = @json($toQuarterYear ?? date('Y'));

        let selectedFromYear = @json($fromYear ?? date('Y'));
        let selectedToYear = @json($toYear ?? date('Y'));


        /* =====================================================
           CHỌN NGÀY / THÁNG / QUÝ / NĂM
        ===================================================== */

        window.selectPeriodType = function(type) {

            currentPeriod = type;

            document.querySelectorAll('.period-tab')
                .forEach(function(tab) {
                    tab.classList.remove('active');
                });

            const selectedTab =
                document.querySelector(
                    '.period-tab[data-period="' + type + '"]'
                );

            if (selectedTab) {
                selectedTab.classList.add('active');
            }

            const wraps = [
                'dateRangeWrap',
                'monthRangeWrap',
                'quarterRangeWrap',
                'yearRangeWrap'
            ];

            wraps.forEach(function(id) {

                const element = document.getElementById(id);

                if (element) {
                    element.classList.add('hidden');
                }

            });

            const map = {
                day: 'dateRangeWrap',
                month: 'monthRangeWrap',
                quarter: 'quarterRangeWrap',
                year: 'yearRangeWrap'
            };

            const target =
                document.getElementById(map[type]);

            if (target) {
                target.classList.remove('hidden');
            }

            updateFilterDescription();
        };


        /* =====================================================
           DROPDOWN
        ===================================================== */

        window.toggleDropdown = function(id) {

            const dropdown =
                document.getElementById(id);

            if (!dropdown) return;

            document.querySelectorAll('.filter-dropdown')
                .forEach(function(item) {

                    if (item.id !== id) {
                        item.classList.remove('open');
                    }

                });

            dropdown.classList.toggle('open');
        };


        /* Click bên ngoài thì đóng dropdown */
        document.addEventListener('click', function(e) {

            if (!e.target.closest('.filter-dropdown')) {

                document.querySelectorAll('.filter-dropdown')
                    .forEach(function(dropdown) {
                        dropdown.classList.remove('open');
                    });

            }

        });


        /* =====================================================
           CHỌN QUÝ FROM
        ===================================================== */

        window.selectQuarterFrom = function(quarter, year) {

            selectedFromQuarter = quarter;
            selectedFromQuarterYear = year;

            const text =
                document.getElementById('selectedQuyFromName');

            if (text) {
                text.textContent =
                    'Q' + quarter + '/' + year;
            }

            const dropdown =
                document.getElementById('dropdownQuyFrom');

            if (dropdown) {
                dropdown.classList.remove('open');
            }

            updateFilterDescription();
        };


        /* =====================================================
           CHỌN QUÝ TO
        ===================================================== */

        window.selectQuarterTo = function(quarter, year) {

            selectedToQuarter = quarter;
            selectedToQuarterYear = year;

            const text =
                document.getElementById('selectedQuyToName');

            if (text) {
                text.textContent =
                    'Q' + quarter + '/' + year;
            }

            const dropdown =
                document.getElementById('dropdownQuyTo');

            if (dropdown) {
                dropdown.classList.remove('open');
            }

            updateFilterDescription();
        };


        /* =====================================================
           CHỌN NĂM FROM
        ===================================================== */

        window.selectYearFrom = function(year) {

            selectedFromYear = year;

            const text =
                document.getElementById('selectedNamFromName');

            if (text) {
                text.textContent = year;
            }

            const dropdown =
                document.getElementById('dropdownNamFrom');

            if (dropdown) {
                dropdown.classList.remove('open');
            }

            updateFilterDescription();
        };


        /* =====================================================
           CHỌN NĂM TO
        ===================================================== */

        window.selectYearTo = function(year) {

            selectedToYear = year;

            const text =
                document.getElementById('selectedNamToName');

            if (text) {
                text.textContent = year;
            }

            const dropdown =
                document.getElementById('dropdownNamTo');

            if (dropdown) {
                dropdown.classList.remove('open');
            }

            updateFilterDescription();
        };


        /* =====================================================
           CHỌN PHIM / PHÒNG
        ===================================================== */

        window.selectFilterOption = function(type, value, label) {

            if (type === 'phim') {

                selectedPhim = value;

                const input =
                    document.getElementById('filterPhimId');

                if (input) {
                    input.value = value;
                }

                const text =
                    document.getElementById('selectedPhimName');

                if (text) {
                    text.textContent = label;
                }

                document
                    .querySelectorAll('#optionsPhim .filter-option')
                    .forEach(function(option) {
                        option.classList.remove('selected');
                    });
            }


            if (type === 'phong') {

                selectedPhong = value;

                const input =
                    document.getElementById('filterPhongId');

                if (input) {
                    input.value = value;
                }

                const text =
                    document.getElementById('selectedPhongName');

                if (text) {
                    text.textContent = label;
                }

                document
                    .querySelectorAll('#optionsPhong .filter-option')
                    .forEach(function(option) {
                        option.classList.remove('selected');
                    });
            }


            const dropdown =
                type === 'phim' ?
                document.getElementById('dropdownPhim') :
                document.getElementById('dropdownPhong');

            if (dropdown) {
                dropdown.classList.remove('open');
            }

            updateFilterDescription();
        };


        /* =====================================================
           SEARCH DROPDOWN
        ===================================================== */

        window.filterOptions = function(dropdownId, keyword) {

            const dropdown =
                document.getElementById(dropdownId);

            if (!dropdown) return;

            const options =
                dropdown.querySelectorAll('.filter-option');

            keyword =
                keyword
                .toLowerCase()
                .trim();

            options.forEach(function(option) {

                const text =
                    (
                        option.dataset.search ||
                        option.textContent
                    ).toLowerCase();

                option.style.display =
                    text.includes(keyword) ?
                    '' :
                    'none';
            });
        };


        /* =====================================================
           MÔ TẢ FILTER
        ===================================================== */

        window.updateFilterDescription = function() {

            const text =
                document.getElementById(
                    'filterDescriptionText'
                );

            if (!text) return;

            let periodText = '';


            /* NGÀY */
            if (currentPeriod === 'day') {

                const from =
                    document.getElementById('dateFrom')?.value;

                const to =
                    document.getElementById('dateTo')?.value;

                periodText =
                    from && to ?
                    formatDate(from) +
                    ' → ' +
                    formatDate(to) :
                    'Hôm nay';
            }


            /* THÁNG */
            else if (currentPeriod === 'month') {

                const from =
                    document.getElementById('monthFrom')?.value;

                const to =
                    document.getElementById('monthTo')?.value;

                periodText =
                    from && to ?
                    formatMonth(from) +
                    ' → ' +
                    formatMonth(to) :
                    'Tháng hiện tại';
            }


            /* QUÝ */
            else if (currentPeriod === 'quarter') {

                periodText =
                    'Q' +
                    selectedFromQuarter +
                    '/' +
                    selectedFromQuarterYear +
                    ' → Q' +
                    selectedToQuarter +
                    '/' +
                    selectedToQuarterYear;
            }


            /* NĂM */
            else if (currentPeriod === 'year') {

                periodText =
                    selectedFromYear +
                    ' → ' +
                    selectedToYear;
            }


            text.innerHTML =
                '<strong>Đang thống kê:</strong> ' +
                periodText +
                ' • ' +
                (
                    selectedPhim ?
                    (
                        document.getElementById(
                            'selectedPhimName'
                        )?.textContent ||
                        'Tất cả phim'
                    ) :
                    'Tất cả phim'
                ) +
                ' • ' +
                (
                    selectedPhong ?
                    (
                        document.getElementById(
                            'selectedPhongName'
                        )?.textContent ||
                        'Tất cả phòng'
                    ) :
                    'Tất cả phòng'
                );
        };


        /* =====================================================
           FORMAT DATE
        ===================================================== */

        function formatDate(value) {

            if (!value) return '';

            const parts = value.split('-');

            if (parts.length !== 3) {
                return value;
            }

            return parts[2] +
                '/' +
                parts[1] +
                '/' +
                parts[0];
        }


        /* =====================================================
           FORMAT MONTH
        ===================================================== */

        function formatMonth(value) {

            if (!value) return '';

            const parts = value.split('-');

            if (parts.length !== 2) {
                return value;
            }

            return parts[1] +
                '/' +
                parts[0];
        }


        /* =====================================================
           APPLY FILTER
        ===================================================== */

        window.applyFilters = function() {

            const params =
                new URLSearchParams();

            params.set(
                'period_type',
                currentPeriod
            );


            /* NGÀY */
            if (currentPeriod === 'day') {

                const from =
                    document.getElementById('dateFrom')?.value;

                const to =
                    document.getElementById('dateTo')?.value;

                if (from) {
                    params.set('from_date', from);
                }

                if (to) {
                    params.set('to_date', to);
                }
            }


            /* THÁNG */
            if (currentPeriod === 'month') {

                const from =
                    document.getElementById('monthFrom')?.value;

                const to =
                    document.getElementById('monthTo')?.value;

                if (from) {
                    params.set('from_month', from);
                }

                if (to) {
                    params.set('to_month', to);
                }
            }


            /* QUÝ */
            if (currentPeriod === 'quarter') {

                params.set(
                    'from_quarter',
                    selectedFromQuarter
                );

                params.set(
                    'from_quarter_year',
                    selectedFromQuarterYear
                );

                params.set(
                    'to_quarter',
                    selectedToQuarter
                );

                params.set(
                    'to_quarter_year',
                    selectedToQuarterYear
                );
            }


            /* NĂM */
            if (currentPeriod === 'year') {

                params.set(
                    'from_year',
                    selectedFromYear
                );

                params.set(
                    'to_year',
                    selectedToYear
                );
            }


            /* PHIM */
            if (selectedPhim !== '') {

                params.set(
                    'phim_id',
                    selectedPhim
                );
            }


            /* PHÒNG */
            if (selectedPhong !== '') {

                params.set(
                    'phong_chieu_id',
                    selectedPhong
                );
            }


            /* CHUYỂN TRANG */

            window.location.href =
                window.location.pathname +
                '?' +
                params.toString();
        };


        /* =====================================================
           RESET
        ===================================================== */

        window.resetFilters = function() {

            window.location.href =
                window.location.pathname;
        };


        /* =====================================================
           BIỂU ĐỒ DOANH THU
           DÙNG BIỂU ĐỒ CỘT
        ===================================================== */

        const revenueByTime =
            @json($revenueByTime ?? []);

        const lineCanvas =
            document.getElementById('lineChart');


        if (
            lineCanvas &&
            typeof Chart !== 'undefined'
        ) {

            let labels = [];
            let values = [];


            if (Array.isArray(revenueByTime)) {

                revenueByTime.forEach(function(item) {

                    labels.push(
                        item.label ??
                        item.date ??
                        item.period ??
                        item.thoi_gian ??
                        ''
                    );

                    values.push(
                        Number(
                            item.total_revenue ??
                            item.revenue ??
                            item.doanh_thu ??
                            0
                        )
                    );

                });
            }


            /*
             * Nếu chỉ có 1 ngày:
             * vẫn chỉ hiện 1 cột và cột được giới hạn độ rộng.
             *
             * Nếu nhiều ngày:
             * tự động chia đều các cột.
             */

            const isSingleColumn =
                labels.length === 1;


            window.revenueLineChart =
                new Chart(
                    lineCanvas, {

                        type: 'bar',

                        data: {

                            labels: labels,

                            datasets: [{

                                label: 'Doanh thu',

                                data: values,

                                borderWidth: 2,

                                borderRadius: 6,

                                /* Quan trọng */
                                barPercentage: isSingleColumn ?
                                    0.25 :
                                    0.65,

                                categoryPercentage: isSingleColumn ?
                                    0.5 :
                                    0.8,

                                maxBarThickness: isSingleColumn ?
                                    180 :
                                    70

                            }]
                        },


                        options: {

                            responsive: true,

                            maintainAspectRatio: false,


                            plugins: {

                                legend: {
                                    display: false
                                },


                                tooltip: {

                                    callbacks: {

                                        label: function(context) {

                                            return 'Doanh thu: ' +
                                                Number(
                                                    context.raw || 0
                                                ).toLocaleString(
                                                    'vi-VN'
                                                ) +
                                                'đ';
                                        }
                                    }
                                }
                            },


                            scales: {

                                x: {

                                    grid: {
                                        display: false
                                    },

                                    ticks: {
                                        color: '#777'
                                    }
                                },


                                y: {

                                    beginAtZero: true,

                                    ticks: {

                                        color: '#777',

                                        callback: function(value) {

                                            return Number(value)
                                                .toLocaleString('vi-VN') +
                                                'đ';
                                        }
                                    }
                                }
                            }
                        }
                    }
                );
        }


        /* =====================================================
           PIE / DOUGHNUT
        ===================================================== */

        const pieCanvas =
            document.getElementById('pieChart');


        if (
            pieCanvas &&
            typeof Chart !== 'undefined'
        ) {

            const ticketRevenue =
                Number(
                    @json((float)($kpi['ticket_revenue'] ?? 0))
                );

            const comboRevenue =
                Number(
                    @json((float)($kpi['combo_revenue'] ?? 0))
                );

            const snackRevenue =
                Number(
                    @json((float)($kpi['snack_revenue'] ?? 0))
                );


            window.revenuePieChart =
                new Chart(
                    pieCanvas, {

                        type: 'doughnut',

                        data: {

                            labels: [
                                'Vé',
                                'Combo',
                                'Đồ ăn'
                            ],

                            datasets: [{

                                data: [
                                    ticketRevenue,
                                    comboRevenue,
                                    snackRevenue
                                ],

                                borderWidth: 0
                            }]
                        },


                        options: {

                            responsive: true,

                            maintainAspectRatio: false,

                            cutout: '65%',


                            plugins: {

                                legend: {
                                    display: false
                                },


                                tooltip: {

                                    callbacks: {

                                        label: function(context) {

                                            return context.label +
                                                ': ' +
                                                Number(
                                                    context.raw || 0
                                                ).toLocaleString(
                                                    'vi-VN'
                                                ) +
                                                'đ';
                                        }
                                    }
                                }
                            }
                        }
                    }
                );
        }


        /* =====================================================
           KHỞI TẠO FILTER BAN ĐẦU
        ===================================================== */

        updateFilterDescription();

    });
</script>

@endpush

@push('styles')

<style>
    .chart-wrap {
        position: relative;
        width: 100%;
        height: 330px;
    }

    .chart-wrap.sm {
        position: relative;
        width: 100%;
        height: 240px;
    }
</style>
@endpush

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const comboRevenue = {{ (float)($kpi['combo_revenue'] ?? 0) }};
    const snackRevenue = {{ (float)($kpi['snack_revenue'] ?? 0) }};

    const foodCanvas = document.getElementById('foodRevenueChart');

    if (foodCanvas) {

        new Chart(foodCanvas, {
            type: 'doughnut',

            data: {
                labels: [
                    'Combo',
                    'Đồ ăn & nước'
                ],

                datasets: [{
                    data: [
                        comboRevenue,
                        snackRevenue
                    ],

                    borderWidth: 0
                }]
            },

            options: {
                responsive: true,

                maintainAspectRatio: false,

                cutout: '65%',

                plugins: {

                    legend: {
                        position: 'bottom',

                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },

                    tooltip: {
                        callbacks: {

                            label: function(context) {

                                const value = context.raw || 0;

                                return context.label + ': ' +
                                    new Intl.NumberFormat('vi-VN').format(value) +
                                    'đ';
                            }
                        }
                    }
                }
            }
        });
    }

});
</script>

@endpush