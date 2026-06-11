@extends('layouts.leader')

@section('styles')
<style>
    .filter-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--ig700);margin-bottom:.5rem;display:block}
</style>
@endsection

@section('content')
@php
    $isDateMode = request('date') && !request('month');
    $navQueryKey = $isDateMode ? 'date' : 'month';
@endphp
<div class="container-fluid px-lg-5 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3 border-bottom pb-3">
        <div>
            <h3 class="fw-800 mb-1" style="color:var(--ig800)">
                <i class="bi bi-file-earmark-text-fill me-2 opacity-75"></i>Laporan Part NG
            </h3>
            <p class="text-muted mb-0">Semua data — {{ $monthLabel }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery([$navQueryKey => $prevMonth]) }}" class="btn btn-outline-secondary shadow-sm" style="border-radius:10px;padding:.5rem .7rem">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="fw-bold px-3 py-2 rounded-3" style="background:var(--ig100);color:var(--ig800);font-size:.9rem;min-width:140px;text-align:center">
                {{ $monthLabel }}
            </span>
            <a href="{{ request()->fullUrlWithQuery([$navQueryKey => $nextMonth]) }}" class="btn btn-outline-secondary shadow-sm" style="border-radius:10px;padding:.5rem .7rem">
                <i class="bi bi-chevron-right"></i>
            </a>
            <span class="mx-2"></span>
            <button type="button" class="btn btn-success shadow-sm rounded-pill px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="glass-card p-3 border-0 mb-4">
        <form action="{{ route('leader.report.monthly') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="filter-label mb-1"><i class="bi bi-calendar me-1"></i>Periode</label>
                <div class="input-group">
                    <select id="filter-type" class="form-select bg-light border-0 shadow-sm" style="border-radius:10px 0 0 0;font-size:.85rem;max-width:100px">
                        <option value="date" {{ request('date') && !request('month') ? 'selected' : '' }}>Harian</option>
                        <option value="month" {{ request('month') ? 'selected' : '' }}>Bulanan</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:0;padding:.3rem .5rem;border-color:transparent;background:var(--ig100);color:var(--ig700);font-weight:600" onclick="navigateDate(-1)"><i class="bi bi-chevron-left"></i></button>
                    <input type="{{ request('month') ? 'month' : 'date' }}" id="filter-value" name="{{ request('month') ? 'month' : 'date' }}" class="form-control bg-light border-0 shadow-sm" style="border-radius:0;font-size:.85rem" value="{{ request('month') ? request('month') : (request('date') ?: date('Y-m-d')) }}">
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:0 10px 10px 0;padding:.3rem .5rem;border-color:transparent;background:var(--ig100);color:var(--ig700);font-weight:600" onclick="navigateDate(1)"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div class="col-auto">
                <label class="filter-label mb-1"><i class="bi bi-calendar-week me-1"></i>Minggu</label>
                <select name="week" class="form-select bg-light border-0 shadow-sm" style="border-radius:10px;font-size:.85rem">
                    <option value="">Semua</option>
                    <option value="1" {{ request('week') == '1' ? 'selected' : '' }}>Minggu ke-1</option>
                    <option value="2" {{ request('week') == '2' ? 'selected' : '' }}>Minggu ke-2</option>
                    <option value="3" {{ request('week') == '3' ? 'selected' : '' }}>Minggu ke-3</option>
                    <option value="4" {{ request('week') == '4' ? 'selected' : '' }}>Minggu ke-4</option>
                    <option value="5" {{ request('week') == '5' ? 'selected' : '' }}>Minggu ke-5</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="filter-label mb-1"><i class="bi bi-building me-1"></i>Divisi</label>
                <select name="divisi" class="form-select bg-light border-0 shadow-sm" style="border-radius:10px;font-size:.85rem">
                    <option value="">All</option>
                    <option value="Assembling" {{ request('divisi') == 'Assembling' ? 'selected' : '' }}>Assembling</option>
                    <option value="DST" {{ request('divisi') == 'DST' ? 'selected' : '' }}>DST</option>
                    <option value="Painting" {{ request('divisi') == 'Painting' ? 'selected' : '' }}>Painting</option>
                    <option value="Mower" {{ request('divisi') == 'Mower' ? 'selected' : '' }}>Mower</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="filter-label mb-1"><i class="bi bi-tags me-1"></i>Kategori</label>
                <select name="category" class="form-select bg-light border-0 shadow-sm" style="border-radius:10px;font-size:.85rem">
                    <option value="">All</option>
                    <option value="part scrap" {{ request('category') == 'part scrap' ? 'selected' : '' }}>Part scrap</option>
                    <option value="bukan tanggung jawab" {{ request('category') == 'bukan tanggung jawab' ? 'selected' : '' }}>Bukan tanggung jawab</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-pink shadow-sm" style="border-radius:10px;font-size:.85rem;padding:.45rem 1.2rem">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('leader.report.monthly') }}" class="btn btn-light shadow-sm" style="border-radius:10px;font-size:.85rem;padding:.45rem 1.2rem;color:var(--ig700);font-weight:600">Reset</a>
            </div>
            <div class="col-auto ms-auto text-end">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small fw-bold">Data:</span>
                        <span class="fw-bold fs-4" style="color:var(--ig600)">{{ $parts->count() }}</span>
                        <span class="text-muted small">item</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small fw-bold">Total Cost:</span>
                        <span class="fw-bold fs-4" style="color:#0d9488">$ {{ format_harga($totalCost) }}</span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card glass-card border-0" style="border-radius:16px;overflow:hidden;box-shadow:0 10px 40px rgba(99,102,241,.08)">
        <div class="card-header card-header-pink d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-table me-2"></i>Daftar Laporan Part NG — {{ $monthLabel }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height:600px">
                <table class="table table-hover table-premium mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="10%">Tanggal</th>
                            <th width="14%">Rak / Part</th>
                            <th width="10%">Keterangan</th>
                            <th width="8%">Divisi / Kategori</th>
                            <th width="8%">Penyebab</th>
                            <th width="8%">Penanganan</th>
                            <th class="text-center" width="5%">Jml</th>
                            <th class="text-end" width="10%">Cost (USD)</th>
                            <th width="8%">PIC</th>
                            <th class="text-center" width="5%">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $index => $p)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $parts->count() - $index }}</td>
                            <td>
                                <div class="fw-bold fs-6" style="color:var(--ig800)">{{ \Carbon\Carbon::parse($p->Date_Part_Ng)->format('d M Y') }}</div>
                                <div class="small text-muted fw-500">{{ \Carbon\Carbon::parse($p->Date_Part_Ng)->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="badge-pink shadow-sm mb-1">{{ $p->Code_Rack }}</div>
                                <div class="fw-bold small text-dark mb-1">{{ $p->Name_Item_Rack }}</div>
                                <div class="text-muted small font-monospace bg-light rounded px-2 py-1 d-inline-block">{{ $p->Code_Item_Rack }}</div>
                            </td>
                            <td>
                                <p class="mb-0 small" style="max-width:200px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden" title="{{ $p->Desc_Part_Ng }}">{{ $p->Desc_Part_Ng }}</p>
                            </td>
                            <td>
                                <div><span class="badge bg-info">{{ $p->Divisi ?? '-' }}</span></div>
                                <div class="mt-1"><span class="badge" style="background:var(--ig100);color:var(--ig800);border:1px solid var(--ig200);white-space:normal;text-align:left;line-height:1.2">{{ $p->Category_Part_Ng }}</span></div>
                            </td>
                            <td class="small">{{ $p->penyebab ?? '-' }}</td>
                            <td class="small">{{ $p->penanganan ?? '-' }}</td>
                            <td class="text-center fw-bold fs-6">{{ $p->Total_Part_Ng }}</td>
                            <td class="text-end fw-bold" style="color:#0d9488">
                                $ {{ format_harga($p->cost ?? 0) }}
                            </td>
                            <td>
                                @if($p->penanggungjawab)
                                <span class="small fw-bold text-success"><i class="bi bi-person-check-fill me-1"></i>{{ $p->penanggungjawab }}</span>
                                @else
                                <span class="small text-muted fst-italic">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1 flex-wrap" style="max-width:110px;margin:0 auto">
                                    @php $photos = array_filter([$p->Photo_Path_Part_Ng, $p->Photo_Path_Part_Ng_2, $p->Photo_Path_Part_Ng_3]); @endphp
                                    @forelse($photos as $photo)
                                    <img src="{{ asset($photo) }}" class="img-thumbnail rounded-3 shadow-sm border-0" style="height:45px;width:45px;object-fit:cover;cursor:pointer;transition:transform .2s" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" onclick="showPhoto('{{ asset($photo) }}')" alt="Foto">
                                    @empty
                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center mx-auto text-muted" style="height:45px;width:45px;border:1px dashed #ccc">
                                        <i class="bi bi-camera-video-off" style="font-size:0.8rem"></i>
                                    </div>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="opacity-50 mb-3" style="color:var(--ig400)"><i class="bi bi-inbox fs-1"></i></div>
                                <h6 class="fw-bold text-muted">Belum ada data Part NG</h6>
                                <p class="small text-muted">Silakan sesuaikan filter pencarian.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Foto -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 glass-card">
                <div class="modal-header border-bottom-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <img id="modalPhotoSrc" src="" class="img-fluid rounded-4 shadow-lg" alt="Foto Detail" style="max-height:80vh;border:4px solid white">
                </div>
            </div>
        </div>
    </div>



    <!-- Modal Export -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 glass-card">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color:var(--ig800)"><i class="bi bi-file-earmark-excel me-2"></i>Export Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm" action="{{ route('leader.export') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Bulan</label>
                            <input type="month" name="month" class="form-control" style="border-radius:12px" value="{{ request('month', now()->format('Y-m')) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Minggu</label>
                            <select name="week" class="form-select" style="border-radius:12px">
                                <option value="">Semua Minggu</option>
                                <option value="1">Minggu ke-1</option>
                                <option value="2">Minggu ke-2</option>
                                <option value="3">Minggu ke-3</option>
                                <option value="4">Minggu ke-4</option>
                                <option value="5">Minggu ke-5</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Divisi</label>
                            <select name="divisi" class="form-select" style="border-radius:12px">
                                <option value="">Semua Divisi</option>
                                <option value="Assembling">Assembling</option>
                                <option value="DST">DST</option>
                                <option value="Painting">Painting</option>
                                <option value="Mower">Mower</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Kategori</label>
                            <select name="category" class="form-select" style="border-radius:12px">
                                <option value="">Semua Kategori</option>
                                <option value="part scrap">Part Scrap</option>
                                <option value="bukan tanggung jawab">Bukan Tanggung Jawab Warehouse</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success fw-bold py-2" style="border-radius:12px">
                                <i class="bi bi-download me-2"></i>Download Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var filterType = document.getElementById('filter-type');
        var filterValue = document.getElementById('filter-value');
        if (filterType && filterValue) {
            filterType.addEventListener('change', function() {
                var isMonth = this.value === 'month';
                filterValue.type = isMonth ? 'month' : 'date';
                filterValue.name = isMonth ? 'month' : 'date';
                filterValue.value = '';
            });
        }
    });

    function showPhoto(url) {
        document.getElementById('modalPhotoSrc').src = url;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }

    function navigateDate(dir) {
        const type = document.getElementById('filter-type').value;
        const input = document.getElementById('filter-value');
        let d;
        if (type === 'month') {
            d = new Date(input.value + '-01');
            d.setMonth(d.getMonth() + dir);
            input.value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        } else {
            d = new Date(input.value);
            d.setDate(d.getDate() + dir);
            input.value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        input.form.submit();
    }
</script>
@endpush
@endsection
