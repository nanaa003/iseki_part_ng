@extends('layouts.admin')

@section('title', 'Ranking Cost - Iseki Part NG')

@section('styles')
<style>
    .custom-table-container {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(219,39,119,.06);
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        height: 100%;
    }
    .rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #fff;
        font-size: 0.9rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .rank-1 { background: linear-gradient(135deg, #FFD700, #FDB931); } /* Gold */
    .rank-2 { background: linear-gradient(135deg, #E0E0E0, #9E9E9E); } /* Silver */
    .rank-3 { background: linear-gradient(135deg, #CD7F32, #A0522D); } /* Bronze */
    .rank-other { background: var(--pink-200); color: var(--pink-800); }
    
    .table-compact th, .table-compact td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 py-4 fade-in">
    <!-- Header & Month Filter -->
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3 border-bottom pb-3 border-pink-200">
        <div>
            <h3 class="fw-800 mb-1" style="color:var(--pink-800)">
                <i class="bi bi-trophy-fill me-2 opacity-75 text-warning"></i>Ranking Cost Part NG
            </h3>
            <p class="text-muted mb-0">Peringkat cost berdasarkan Penanggungjawab (Member) dan Area (Divisi) — {{ $monthLabel }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.ranking', ['month' => $prevMonth]) }}" class="btn btn-sm" style="border-radius:10px;padding:.5rem .7rem;background:var(--pink-100);color:var(--pink-700);border:1px solid var(--pink-300);font-weight:600">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="fw-bold px-3 py-2 rounded-3" style="background:var(--pink-100);color:var(--pink-800);font-size:.9rem;min-width:140px;text-align:center">
                {{ $monthLabel }}
            </span>
            <a href="{{ route('admin.ranking', ['month' => $nextMonth]) }}" class="btn btn-sm" style="border-radius:10px;padding:.5rem .7rem;background:var(--pink-100);color:var(--pink-700);border:1px solid var(--pink-300);font-weight:600">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Panel Kiri: Member (Penanggungjawab) -->
        <div class="col-lg-6">
            <div class="card glass-card border-0 mb-4 custom-table-container">
                <div class="card-header card-header-pink d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-person-badge me-2"></i>Ranking Member (PIC)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="border: none;">
                        <table class="table table-premium table-compact mb-0">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center">Rank</th>
                                    <th>Penanggungjawab</th>
                                    <th class="text-center">Kasus</th>
                                    <th class="text-end">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($memberRankings as $index => $rank)
                                <tr>
                                    <td class="text-center">
                                        @if($index == 0)
                                            <span class="rank-badge rank-1" title="Peringkat 1"><i class="bi bi-trophy-fill"></i></span>
                                        @elseif($index == 1)
                                            <span class="rank-badge rank-2" title="Peringkat 2">{{ $index + 1 }}</span>
                                        @elseif($index == 2)
                                            <span class="rank-badge rank-3" title="Peringkat 3">{{ $index + 1 }}</span>
                                        @else
                                            <span class="rank-badge rank-other">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success"><i class="bi bi-person-check-fill me-1"></i>{{ $rank['name'] }}</span>
                                        <div class="small text-muted mt-1">{{ number_format($rank['total_qty']) }} Pcs</div>
                                    </td>
                                    <td class="text-center fw-bold">{{ number_format($rank['frekuensi']) }}</td>
                                    <td class="text-end fw-bold text-danger">
                                        @if($rank['total_cost'] > 0)
                                            ${{ number_format($rank['total_cost'], 2) }}
                                        @else
                                            <span class="text-muted fw-normal fst-italic">No Cost</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                            <p class="mb-0">Tidak ada data untuk bulan ini.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Area (Divisi) -->
        <div class="col-lg-6">
            <div class="card glass-card border-0 mb-4 custom-table-container">
                <div class="card-header card-header-pink d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-building me-2"></i>Ranking Area (Divisi)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="border: none;">
                        <table class="table table-premium table-compact mb-0">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center">Rank</th>
                                    <th>Area / Divisi</th>
                                    <th class="text-center">Kasus</th>
                                    <th class="text-end">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($areaRankings as $index => $rank)
                                <tr>
                                    <td class="text-center">
                                        @if($index == 0)
                                            <span class="rank-badge rank-1" title="Peringkat 1"><i class="bi bi-trophy-fill"></i></span>
                                        @elseif($index == 1)
                                            <span class="rank-badge rank-2" title="Peringkat 2">{{ $index + 1 }}</span>
                                        @elseif($index == 2)
                                            <span class="rank-badge rank-3" title="Peringkat 3">{{ $index + 1 }}</span>
                                        @else
                                            <span class="rank-badge rank-other">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background:var(--pink-100);color:var(--pink-800);border:1px solid var(--pink-200);font-size:0.8rem">
                                            <i class="bi bi-geo-alt-fill me-1"></i>{{ $rank['name'] }}
                                        </span>
                                        <div class="small text-muted mt-1">{{ number_format($rank['total_qty']) }} Pcs</div>
                                    </td>
                                    <td class="text-center fw-bold">{{ number_format($rank['frekuensi']) }}</td>
                                    <td class="text-end fw-bold text-danger">
                                        @if($rank['total_cost'] > 0)
                                            ${{ number_format($rank['total_cost'], 2) }}
                                        @else
                                            <span class="text-muted fw-normal fst-italic">No Cost</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                            <p class="mb-0">Tidak ada data untuk bulan ini.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
