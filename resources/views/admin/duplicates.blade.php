@extends('layouts.app')
@section('hide_navbar', true)

@section('content')

{{-- Admin Navbar --}}
<nav class="navbar navbar-expand-lg navbar-dark navbar-admin">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-wrench-adjustable-circle me-2"></i>Admin Iseki Repair
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-graph-up me-1"></i>Report
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.duplicates') }}">
                        <i class="bi bi-copy me-1"></i>Cek Duplikasi
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm text-white"
                            style="background: rgba(255,255,255,0.15); border-radius: 8px; padding: 0.4rem 1rem;">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4 pb-5 fade-in">

    <h4 class="fw-bold mb-3">
        <i class="bi bi-search me-2" style="color: var(--pink-600);"></i>Deteksi Duplikasi
    </h4>

    {{-- Filter Form --}}
    <div class="glass-card mb-4 p-3">
        <form method="GET" action="{{ route('admin.duplicates') }}" class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="form-label fw-bold small text-muted text-uppercase">
                    <i class="bi bi-calendar-month me-1"></i>Pilih Bulan
                </label>
                <input type="month" name="month" class="form-control" value="{{ $month }}" style="border-radius: 10px;">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-pink">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Hasil Deteksi --}}
    <div class="glass-card">
        <div class="card-header-pink d-flex align-items-center justify-content-between">
            <span><i class="bi bi-search me-2"></i>Hasil Deteksi Duplikasi</span>
            <span class="badge bg-white fw-bold" style="color: var(--pink-700);">{{ count($allDuplicates) }} grup</span>
        </div>

        @if(count($allDuplicates) > 0)

        <div style="max-height: 80vh; overflow-y: auto; overflow-x: auto;">
            <table class="table table-premium mb-0" style="min-width: 900px;">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr class="text-nowrap">
                        <th style="width: 4%">No</th>
                        <th style="min-width: 260px">Keterangan Perbaikan</th>
                        <th style="width: 8%">Jumlah</th>
                        <th style="width: 12%">Tipe</th>
                        <th style="min-width: 150px">Traktor</th>
                        <th style="min-width: 150px">Tanggal</th>
                        <th style="width: 8%" class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rank = 1; @endphp
                    @foreach($allDuplicates as $idx => $dup)

                    {{-- Baris Ringkasan --}}
                    <tr style="cursor: pointer;" onclick="toggleDetail(this.dataset.groupIdx)" data-group-idx="{{ $idx }}">
                        <td>
                            <span class="fw-bold text-muted">{{ $rank++ }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ reset($dup['texts']) }}</div>
                            @if($dup['similarity'] < 100)
                                <small class="text-muted fst-italic">+ {{ $dup['count'] - 1 }} teks mirip lainnya</small>
                                @endif
                        </td>
                        <td>
                            <span class="badge" id="count-badge-{{ $idx }}" style="font-size: 0.85rem; border-radius: 8px; padding: 0.4rem 0.75rem; background: #fbbf24; color: #78350f;">
                                {{ $dup['active_count'] }}x
                            </span>
                        </td>
                        <td>
                            @if($dup['similarity'] == 100)
                            <span class="badge bg-danger" style="border-radius: 8px; padding: 0.35rem 0.6rem;">
                                <i class="bi bi-bullseye me-1"></i>Exact 100%
                            </span>
                            @else
                            <span class="badge" style="border-radius: 8px; padding: 0.35rem 0.6rem; background: #fef3c7; color: #92400e;">
                                <i class="bi bi-diagram-3 me-1"></i>Similar {{ $dup['similarity'] }}%
                            </span>
                            @endif
                        </td>
                        <td>
                            @foreach(array_unique($dup['tractors']) as $tractor)
                            <span class="badge-pink me-1 mb-1 d-inline-block" style="font-size: 0.75rem;">{{ $tractor }}</span>
                            @endforeach
                        </td>
                        <td>
                            @foreach($dup['dates'] as $dateIdx => $date)
                            @php $datePid = $dup['perbaikan_ids'][$dateIdx]; @endphp
                            <span class="badge bg-light text-dark me-1 mb-1 d-inline-block"
                                id="date-badge-{{ $datePid }}"
                                style="font-size: 0.75rem; border-radius: 6px;{{ in_array($datePid, $excludedIds) ? ' display:none;' : '' }}">{{ $date }}</span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm" id="btn-toggle-{{ $idx }}"
                                style="border-radius: 8px; background: #fce7f3; color: var(--pink-700); border: 1px solid #fbcfe8; font-size: 0.78rem; padding: 0.3rem 0.7rem;">
                                <i class="bi bi-chevron-down me-1" id="icon-{{ $idx }}"></i>Lihat
                            </button>
                        </td>
                    </tr>

                    {{-- Baris Detail (tersembunyi) --}}
                    <tr id="detail-{{ $idx }}" style="display: none; background: #fdf4ff;">
                        <td colspan="7" class="p-0">
                            <div class="p-3">
                                {{-- Active Items --}}
                                <div class="fw-bold small text-muted text-uppercase mb-2" style="letter-spacing: 0.05em;">
                                    <i class="bi bi-list-ul me-1"></i>
                                    Rincian Data Duplikasi (Klik <i class="bi bi-x-circle text-danger"></i> untuk mengeluarkan dari hitungan)
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0"
                                        style="background: white; border-radius: 10px; overflow: hidden; font-size: 0.85rem;">
                                        <thead style="background: linear-gradient(135deg, #f9a8d4, #ec4899); color: white;">
                                            <tr>
                                                <th style="width: 5%; padding: 0.5rem 0.75rem;">#</th>
                                                <th style="padding: 0.5rem 0.75rem;">Keterangan Perbaikan</th>
                                                <th style="padding: 0.5rem 0.75rem;">Traktor</th>
                                                <th style="padding: 0.5rem 0.75rem;">Tanggal</th>
                                                @if($dup['similarity'] < 100)
                                                    <th style="padding: 0.5rem 0.75rem;">Kemiripan</th>
                                                    @endif
                                                    <th style="padding: 0.5rem 0.75rem; width: 6%;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="active-body-{{ $idx }}">
                                            @foreach($dup['texts'] as $ti => $text)
                                            @php
                                            $pid = $dup['perbaikan_ids'][$ti];
                                            $isExcluded = in_array($pid, $excludedIds);
                                            $rowBg = $ti == 0 ? 'background: #fdf2f8;' : '';
                                            @endphp
                                            <tr id="row-{{ $pid }}" class="{{ $isExcluded ? 'd-none' : '' }}" style="{{ $rowBg }}">
                                                <td style="padding: 0.5rem 0.75rem; color: #9ca3af; font-weight: 600;">
                                                    {{ $ti + 1 }}
                                                </td>
                                                <td style="padding: 0.5rem 0.75rem;">
                                                    @if($ti == 0)
                                                    <span class="badge me-1"
                                                        style="background: #fce7f3; color: var(--pink-700); font-size: 0.7rem; border-radius: 6px;">
                                                        Acuan
                                                    </span>
                                                    @endif
                                                    {{ $text }}
                                                </td>
                                                <td style="padding: 0.5rem 0.75rem;">
                                                    <span class="badge-pink" style="font-size: 0.75rem;">
                                                        {{ $dup['tractors'][$ti] ?? '-' }}
                                                    </span>
                                                </td>
                                                <td style="padding: 0.5rem 0.75rem;">
                                                    <span class="badge bg-light text-dark" style="font-size: 0.75rem; border-radius: 6px;">
                                                        {{ $dup['dates'][$ti] ?? '-' }}
                                                    </span>
                                                </td>
                                                @if($dup['similarity'] < 100)
                                                    <td style="padding: 0.5rem 0.75rem;">
                                                    @if($ti == 0)
                                                    <span class="text-muted small fst-italic">—</span>
                                                    @else
                                                    @php
                                                    similar_text(strtolower(trim($dup['texts'][0])), strtolower(trim($text)), $pct);
                                                    $bgPct = $pct >= 90 ? '#fee2e2' : ($pct >= 80 ? '#fef3c7' : '#f0fdf4');
                                                    $colPct = $pct >= 90 ? '#dc2626' : ($pct >= 80 ? '#92400e' : '#166534');
                                                    @endphp
                                                    <span class="badge" style="font-size: 0.75rem; border-radius: 6px; background: {{ $bgPct }}; color: {{ $colPct }};">
                                                        {{ round($pct, 1) }}% mirip
                                                    </span>
                                                    @endif
                        </td>
                        @endif
                        <td style="padding: 0.5rem 0.75rem;" class="text-center">
                            <button class="btn btn-sm btn-outline-danger" data-pid="{{ $pid }}" data-idx="{{ $idx }}" onclick="event.stopPropagation(); excludeItem(this.dataset.pid, this.dataset.idx)" title="Keluarkan dari hitungan"
                                style="border-radius: 6px; padding: 0.15rem 0.4rem; font-size: 0.78rem;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Excluded Items --}}
        <div id="excluded-section-{{ $idx }}" class="{{ collect($dup['perbaikan_ids'])->filter(fn($pid) => in_array($pid, $excludedIds))->isEmpty() ? 'd-none' : '' }}">
            <div class="fw-bold small text-muted text-uppercase mt-3 mb-2" style="letter-spacing: 0.05em;">
                <i class="bi bi-arrow-counterclockwise me-1"></i>
                Item yang Dikeluarkan (centang untuk mengembalikan)
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0"
                    style="background: #f9fafb; border-radius: 10px; overflow: hidden; font-size: 0.85rem; opacity: 0.7;">
                    <tbody id="excluded-body-{{ $idx }}">
                        @foreach($dup['texts'] as $ti => $text)
                        @php $pid = $dup['perbaikan_ids'][$ti]; $isExcluded = in_array($pid, $excludedIds); @endphp
                        <tr id="excluded-row-{{ $pid }}" class="{{ !$isExcluded ? 'd-none' : '' }}" style="text-decoration: line-through; color: #9ca3af;">
                            <td style="padding: 0.5rem 0.75rem; width: 5%;">
                                <input type="checkbox" class="form-check-input" data-pid="{{ $pid }}" data-idx="{{ $idx }}" onchange="includeItem(this.dataset.pid, this.dataset.idx)" title="Kembalikan ke hitungan">
                            </td>
                            <td style="padding: 0.5rem 0.75rem;">{{ $text }}</td>
                            <td style="padding: 0.5rem 0.75rem;">{{ $dup['tractors'][$ti] ?? '-' }}</td>
                            <td style="padding: 0.5rem 0.75rem;">{{ $dup['dates'][$ti] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </td>
    </tr>

    @endforeach
    </tbody>
    </table>
</div>

@else

<div class="text-center py-5">
    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
    <p class="text-muted mt-2 mb-0">
        Tidak ditemukan duplikasi untuk bulan <strong>{{ $month }}</strong>.
    </p>
</div>

@endif
</div>
</div>

<script>
    function toggleDetail(idx) {
        const row = document.getElementById('detail-' + idx);
        const btn = document.getElementById('btn-toggle-' + idx);
        const isHidden = row.style.display === 'none';

        if (isHidden) {
            row.style.display = '';
            btn.innerHTML = '<i class="bi bi-chevron-up me-1"></i>Tutup';
            btn.style.background = '#ec4899';
            btn.style.color = 'white';
        } else {
            row.style.display = 'none';
            btn.innerHTML = '<i class="bi bi-chevron-down me-1"></i>Lihat';
            btn.style.background = '#fce7f3';
            btn.style.color = 'var(--pink-700)';
        }
    }

    function excludeItem(perbaikanId, groupIdx) {
        fetch("{{ route('admin.duplicates.exclude') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    perbaikan_id: perbaikanId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Hide from active, show in excluded
                    document.getElementById('row-' + perbaikanId).classList.add('d-none');
                    document.getElementById('excluded-row-' + perbaikanId).classList.remove('d-none');
                    document.getElementById('excluded-section-' + groupIdx).classList.remove('d-none');
                    // Hide date badge in summary row
                    var dateBadge = document.getElementById('date-badge-' + perbaikanId);
                    if (dateBadge) dateBadge.style.display = 'none';
                    // Update count badge
                    updateCountBadge(groupIdx, -1);
                }
            });
    }

    function includeItem(perbaikanId, groupIdx) {
        fetch("{{ route('admin.duplicates.include') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    perbaikan_id: perbaikanId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Show in active, hide in excluded
                    document.getElementById('row-' + perbaikanId).classList.remove('d-none');
                    document.getElementById('excluded-row-' + perbaikanId).classList.add('d-none');
                    // Check if excluded section is now empty
                    const excludedBody = document.getElementById('excluded-body-' + groupIdx);
                    const visibleExcluded = excludedBody.querySelectorAll('tr:not(.d-none)');
                    if (visibleExcluded.length === 0) {
                        document.getElementById('excluded-section-' + groupIdx).classList.add('d-none');
                    }
                    // Show date badge in summary row
                    var dateBadge = document.getElementById('date-badge-' + perbaikanId);
                    if (dateBadge) dateBadge.style.display = '';
                    // Update count badge
                    updateCountBadge(groupIdx, 1);
                }
            });
    }

    function updateCountBadge(groupIdx, delta) {
        const badge = document.getElementById('count-badge-' + groupIdx);
        const currentCount = parseInt(badge.textContent);
        const newCount = currentCount + delta;
        badge.textContent = newCount + 'x';
        // Re-sort groups after count changes
        sortGroups();
    }

    function sortGroups() {
        const tbody = document.querySelector('.table-premium tbody');
        if (!tbody) return;

        // Collect all group pairs (summary row + detail row)
        const groups = [];
        const rows = Array.from(tbody.children);
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.hasAttribute('data-group-idx')) {
                const idx = row.getAttribute('data-group-idx');
                const detailRow = document.getElementById('detail-' + idx);
                const badge = document.getElementById('count-badge-' + idx);
                const count = badge ? parseInt(badge.textContent) : 0;
                groups.push({
                    summaryRow: row,
                    detailRow: detailRow,
                    count: count
                });
            }
        }

        // Sort by count descending
        groups.sort((a, b) => b.count - a.count);

        // Re-append in sorted order and update rank numbers
        groups.forEach((group, i) => {
            // Update rank number
            const rankSpan = group.summaryRow.querySelector('td:first-child .fw-bold');
            if (rankSpan) rankSpan.textContent = (i + 1);
            tbody.appendChild(group.summaryRow);
            if (group.detailRow) tbody.appendChild(group.detailRow);
        });
    }
</script>

@endsection
