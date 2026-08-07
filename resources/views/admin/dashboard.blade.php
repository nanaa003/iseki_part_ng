@extends('layouts.admin')

@section('styles')
<style>
    .admin-sidebar{position:sticky;top:20px}
    .filter-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--pink-700);margin-bottom:.5rem;display:block}
    .custom-table-container{border-radius:20px;overflow:hidden;box-shadow:0 8px 32px rgba(219,39,119,.06);background:var(--glass-bg);backdrop-filter:blur(20px);border:1px solid var(--glass-border)}
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3 border-bottom pb-3 border-pink-200">
        <div>
            <h3 class="fw-800 mb-1" style="color:var(--pink-800)">
                <i class="bi bi-grid-1x2-fill me-2 opacity-75"></i>Dashboard Part NG
            </h3>
            <p class="text-muted mb-0">Statistik Part NG — {{ $monthLabel }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['month' => $prevMonth]) }}" class="btn btn-sm" style="border-radius:10px;padding:.5rem .7rem;background:var(--pink-100);color:var(--pink-700);border:1px solid var(--pink-300);font-weight:600">
                <i class="bi bi-chevron-left"></i>
            </a>
            <form action="{{ route('admin.dashboard') }}" method="GET" class="m-0 p-0" id="monthFilterForm">
                <input type="month" name="month" class="form-control fw-bold px-3 py-2" style="border-radius:10px;background:var(--pink-100);color:var(--pink-800);border:1px solid var(--pink-300);font-size:.9rem;min-width:140px;text-align:center" value="{{ request('month', now()->format('Y-m')) }}" onchange="document.getElementById('monthFilterForm').submit()">
            </form>
            <a href="{{ request()->fullUrlWithQuery(['month' => $nextMonth]) }}" class="btn btn-sm" style="border-radius:10px;padding:.5rem .7rem;background:var(--pink-100);color:var(--pink-700);border:1px solid var(--pink-300);font-weight:600">
                <i class="bi bi-chevron-right"></i>
            </a>
            <a href="{{ route('admin.create') }}" class="btn btn-pink shadow-sm ms-2">
                <i class="bi bi-plus-circle me-1"></i>Input Part NG
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card glass-card border-0 mb-4 custom-table-container">
                <div class="card-header card-header-pink d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-graph-up me-2"></i>Total Inputan Part NG Bulan Ini</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="ngChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card glass-card border-0 mb-4 custom-table-container">
                <div class="card-header card-header-pink d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-pie-chart me-2"></i>Perbandingan Proses</h6>
                </div>
                <div class="card-body p-4 text-center">
                    <canvas id="ngChartComparison" height="200"></canvas>
                    <div class="mt-3 d-flex justify-content-center gap-4 small">
                        <div><span class="badge" style="background:#22c55e">&nbsp;</span> Sudah Diproses: <strong>{{ number_format($processedTotal) }}</strong></div>
                        <div><span class="badge" style="background:#ef4444">&nbsp;</span> Belum Diproses: <strong>{{ number_format($unprocessedTotal) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-0">
        <div class="col-lg-8">
            <div class="card glass-card border-0 mb-4 custom-table-container">
                <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;font-weight:700;border-radius:16px 16px 0 0!important">
                    <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-currency-dollar me-2"></i>Total Cost (USD)</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="costChart" height="80"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card glass-card border-0 mb-4 custom-table-container">
                <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;font-weight:700;border-radius:16px 16px 0 0!important">
                    <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-calculator me-2"></i>Total Cost Bulan Ini (USD)</h6>
                </div>
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center" style="min-height:200px">
                    <div class="fs-1 fw-bold" style="color:#0d9488">$ {{ format_harga($totalCost) }}</div>
                    <div class="small text-muted mt-2">Total cost Part NG (USD) {{ $monthLabel }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/Chart.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function createChart(canvasId, labels, data, lineColor, gradientColor) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            var gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, gradientColor + '0.5');
            gradient.addColorStop(1, gradientColor + '0.05');

            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Inputan',
                        data: data,
                        backgroundColor: gradient,
                        borderColor: lineColor,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: lineColor,
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                                fontColor: '#6c757d'
                            },
                            gridLines: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                zeroLineColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontColor: '#6c757d',
                                maxTicksLimit: 8
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleFontColor: '#1e293b',
                        bodyFontColor: lineColor,
                        borderColor: lineColor + '33',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return 'Inputan: ' + tooltipItem.yLabel;
                            }
                        }
                    }
                }
            });
        }

        createChart('ngChart', {!! $chartDates->toJson() !!}, {!! $chartTotals->toJson() !!}, '#e11d48', 'rgba(225, 29, 72, ');

        // Cost chart with USD formatting
        (function() {
            var ctx = document.getElementById('costChart').getContext('2d');
            var gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(13, 148, 136, 0.5');
            gradient.addColorStop(1, 'rgba(13, 148, 136, 0.05');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($costDates) !!},
                    datasets: [{
                        label: 'Biaya (USD)',
                        data: {!! json_encode($costTotals) !!},
                        backgroundColor: gradient,
                        borderColor: '#0d9488',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0d9488',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                                fontColor: '#6c757d',
                                callback: function(v) { return '$ ' + Number(v).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); }
                            },
                            gridLines: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                zeroLineColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontColor: '#6c757d',
                                maxTicksLimit: 8
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: { display: false },
                    tooltips: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleFontColor: '#1e293b',
                        bodyFontColor: '#0d9488',
                        borderColor: 'rgba(13, 148, 136, 0.2',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return '$ ' + Number(tooltipItem.yLabel).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
                            }
                        }
                    }
                }
            });
        })();

        // Doughnut chart: perbandingan sudah vs belum diproses
        var ctx2 = document.getElementById('ngChartComparison').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Sudah Diproses', 'Belum Diproses'],
                datasets: [{
                    data: [{{ $processedTotal }}, {{ $unprocessedTotal }}],
                    backgroundColor: ['#22c55e', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutoutPercentage: 60,
                legend: { display: false },
                tooltips: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleFontColor: '#1e293b',
                    bodyFontColor: '#1e293b',
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(item, data) {
                            var total = data.datasets[0].data.reduce(function(a,b){return a+b}, 0);
                            var pct = total > 0 ? ((item.value / total) * 100).toFixed(1) : 0;
                            return data.labels[item.index] + ': ' + item.value + ' Inputan (' + pct + '%)';
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
