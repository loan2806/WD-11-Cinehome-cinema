@extends('layouts.admin')

@section('title', 'Bao cao doanh thu')
@section('page-title', 'Bao cao doanh thu')
@section('page-subtitle', 'Tong hop doanh thu ve va do an theo khoang ngay')

@section('content')
<form method="GET" class="admin-panel mb-4 grid gap-3 md:grid-cols-[1fr_1fr_160px]">
    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white">
    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white">
    <button class="btn-admin"><i class="fa-solid fa-chart-line"></i> Xem bao cao</button>
</form>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card"><div class="stat-label">Doanh thu ve</div><div class="stat-value">{{ number_format($ticketRevenue) }}d</div></div>
    </div>
    <div class="col-md-4">
        <div class="stat-card"><div class="stat-label">Doanh thu do an</div><div class="stat-value">{{ number_format($foodRevenue) }}d</div></div>
    </div>
    <div class="col-md-4">
        <div class="stat-card"><div class="stat-label">Tong ve</div><div class="stat-value">{{ number_format($ticketCount) }}</div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="admin-panel">
            <div class="panel-header"><h5>Bieu do doanh thu</h5></div>
            <canvas id="revenueReportChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="admin-panel">
            <div class="panel-header"><h5>Top phim doanh thu</h5></div>
            <table class="admin-table">
                <thead><tr><th>Phim</th><th>Ve</th><th class="text-end">Doanh thu</th></tr></thead>
                <tbody>
                    @forelse($topMovies as $movie)
                        <tr>
                            <td>{{ $movie->movie_title }}</td>
                            <td>{{ $movie->sold_count }}</td>
                            <td class="text-end">{{ number_format($movie->revenue) }}d</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-gray-400">Chua co du lieu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const reportCanvas = document.getElementById('revenueReportChart');
    if (reportCanvas) {
        new Chart(reportCanvas, {
            type: 'bar',
            data: {
                labels: @json($daily->pluck('date')),
                datasets: [
                    { label: 'Ve', data: @json($daily->pluck('ticket')), borderWidth: 1 },
                    { label: 'Do an', data: @json($daily->pluck('food')), borderWidth: 1 }
                ]
            },
            options: {
                plugins: { legend: { labels: { color: '#fff' } } },
                scales: {
                    x: { ticks: { color: '#bbb' }, grid: { color: 'rgba(255,255,255,.08)' } },
                    y: { ticks: { color: '#bbb' }, grid: { color: 'rgba(255,255,255,.08)' } }
                }
            }
        });
    }
</script>
@endsection
