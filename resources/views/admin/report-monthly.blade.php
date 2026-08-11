@extends('layouts.admin')

@section('styles')
<style>
    .filter-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--pink-700);margin-bottom:.5rem;display:block}
    .custom-table-container{border-radius:20px;overflow:hidden;box-shadow:0 8px 32px rgba(219,39,119,.06);background:var(--glass-bg);backdrop-filter:blur(20px);border:1px solid var(--glass-border)}
</style>
@endsection

@section('content')
@php
    $isDateMode = request('date') && !request('month');
    $navQueryKey = $isDateMode ? 'date' : 'month';
@endphp
<div class="container-fluid px-lg-5 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3 border-bottom pb-3 border-pink-200">
        <div>
            <h3 class="fw-800 mb-1" style="color:var(--pink-800)">
                <i class="bi bi-file-earmark-text-fill me-2 opacity-75"></i>Laporan Part NG
            </h3>
            <p class="text-muted mb-0">Semua data — {{ $monthLabel }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery([$navQueryKey => $prevMonth]) }}" class="btn btn-outline-secondary shadow-sm" style="border-radius:10px;padding:.5rem .7rem">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="fw-bold px-3 py-2 rounded-3" style="background:var(--pink-100);color:var(--pink-800);font-size:.9rem;min-width:140px;text-align:center">
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
        <form action="{{ route('admin.report.monthly') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="filter-label mb-1"><i class="bi bi-calendar me-1"></i>Periode</label>
                <div class="input-group">
                    <select id="filter-type" class="form-select bg-light border-0 shadow-sm" style="border-radius:10px 0 0 0;font-size:.85rem;max-width:100px">
                        <option value="date" {{ request('date') && !request('month') ? 'selected' : '' }}>Harian</option>
                        <option value="month" {{ request('month') ? 'selected' : '' }}>Bulanan</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:0;padding:.3rem .5rem;border-color:transparent;background:var(--pink-100);color:var(--pink-700);font-weight:600" onclick="navigateDate(-1)"><i class="bi bi-chevron-left"></i></button>
                    <input type="{{ request('month') ? 'month' : 'date' }}" id="filter-value" name="{{ request('month') ? 'month' : 'date' }}" class="form-control bg-light border-0 shadow-sm" style="border-radius:0;font-size:.85rem" value="{{ request('month') ? request('month') : (request('date') ?: date('Y-m-d')) }}">
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:0 10px 10px 0;padding:.3rem .5rem;border-color:transparent;background:var(--pink-100);color:var(--pink-700);font-weight:600" onclick="navigateDate(1)"><i class="bi bi-chevron-right"></i></button>
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
                <label class="filter-label mb-1"><i class="bi bi-building me-1"></i>Divisi / Proses</label>
                                    <div class="dropdown" id="divisiFilter">
                        <button class="btn btn-light bg-light border-0 shadow-sm dropdown-toggle w-100 text-start d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border-radius:10px;font-size:.85rem;color:var(--pink-700)">
                            <span><i class="bi bi-check2-square me-1"></i>Pilih Proses</span>
                            <span class="badge rounded-pill ms-2" id="divisiCountBadge" style="display:none;background:var(--pink-600)">0</span>
                        </button>
                        <div class="dropdown-menu p-2 shadow-sm" style="min-width:250px;border-radius:12px;z-index:20;">
                            <label class="form-check mb-2 ps-4 fw-bold" style="cursor:pointer; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                                <input class="form-check-input" type="checkbox" id="selectAllDivisi" onchange="toggleAllDivisi(this)">
                                <span class="form-check-label small">Pilih Semua</span>
                            </label>

                            @php
                                $defaultDivisi = ['mainline', 'subassy', 'sub engine', 'inspeksi', 'mower', 'repair', 'painting a', 'painting b', 'DST'];
                                $reqDivisi = request()->has('divisi') ? (array) request('divisi') : $defaultDivisi;
                            @endphp

                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="mainline" @checked(in_array('mainline', $reqDivisi))><span class="form-check-label small">Mainline</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="subassy" @checked(in_array('subassy', $reqDivisi))><span class="form-check-label small">Sub Assy</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="sub engine" @checked(in_array('sub engine', $reqDivisi))><span class="form-check-label small">Sub Engine</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="inspeksi" @checked(in_array('inspeksi', $reqDivisi))><span class="form-check-label small">Inspeksi</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="mower" @checked(in_array('mower', $reqDivisi))><span class="form-check-label small">Mower</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="repair" @checked(in_array('repair', $reqDivisi))><span class="form-check-label small">Repair</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="painting a" @checked(in_array('painting a', $reqDivisi))><span class="form-check-label small">Painting A (Line A)</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="painting b" @checked(in_array('painting b', $reqDivisi))><span class="form-check-label small">Painting B (Line B)</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="DST" @checked(in_array('DST', $reqDivisi))><span class="form-check-label small">DST</span></label>

                            <hr class="my-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetDivisiFilter()"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Pilihan</button>
                        </div>
                    </div>
            </div>
            <div class="col-auto">
                <label class="filter-label mb-1"><i class="bi bi-tags me-1"></i>Kategori</label>
                <select name="category" class="form-select bg-light border-0 shadow-sm" style="border-radius:10px;font-size:.85rem">
                    <option value="">All</option>
                    <option value="part scrap" {{ request('category') == 'part scrap' ? 'selected' : '' }}>Part scrap</option>
                    <option value="bukan tanggung jawab" {{ Str::startsWith(request('category'), 'bukan tanggung jawab') ? 'selected' : '' }}>Bukan tanggung jawab</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-pink shadow-sm" style="border-radius:10px;font-size:.85rem;padding:.45rem 1.2rem">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.report.monthly') }}" class="btn btn-light shadow-sm" style="border-radius:10px;font-size:.85rem;padding:.45rem 1.2rem;color:var(--pink-700);font-weight:600">Reset</a>
            </div>
            <div class="col-auto ms-auto text-end">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small fw-bold">Data:</span>
                        <span class="fw-bold fs-4" style="color:var(--pink-600)">{{ $parts->count() }}</span>
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
    <div class="card glass-card border-0 custom-table-container">
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
                                <div class="fw-bold fs-6" style="color:var(--pink-800)">{{ \Carbon\Carbon::parse($p->Date_Part_Ng)->format('d M Y') }}</div>
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
                                <div><span class="badge bg-info">{{ $p->Divisi ?? '-' }}</span> <span class="badge" style="background:var(--pink-100);color:var(--pink-800);border:1px solid var(--pink-200)">{{ $p->proses ?? '-' }}</span></div>
                                <div class="mt-1"><span class="badge" style="background:var(--pink-100);color:var(--pink-800);border:1px solid var(--pink-200);white-space:normal;text-align:left;line-height:1.2">{{ $p->Category_Part_Ng }}</span></div>
                            </td>
                            <td class="small">{{ $p->penyebab ?? '-' }}</td>
                            <td class="small">{{ $p->penanganan ?? '-' }}</td>
                            <td class="text-center fw-bold fs-6">{{ $p->Total_Part_Ng }}</td>
                            <td class="text-end fw-bold">
                                @if(!($p->harga_found ?? true))
                                <span class="small fw-bold text-danger fst-italic" title="Kode part tidak ditemukan di tabel pricelist">Harga tidak ditemukan</span>
                                @else
                                <span style="color:#0d9488">$ {{ format_harga($p->cost ?? 0) }}</span>
                                @endif
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
                                <div class="opacity-50 mb-3" style="color:var(--pink-400)"><i class="bi bi-inbox fs-1"></i></div>
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
</div>

<!-- Modal Foto -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 glass-card">
            <div class="modal-header border-bottom-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="background-color:var(--pink-100);border-radius:50%;opacity:1;padding:.8rem;margin:.5rem"></button>
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
                <h5 class="modal-title fw-bold" style="color:var(--pink-800)"><i class="bi bi-file-earmark-excel me-2"></i>Export Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm" action="{{ route('admin.export') }}" method="GET">
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
                        <label class="form-label fw-bold small text-muted text-uppercase">Divisi / Proses</label>
                        <select name="divisi" class="form-select" style="border-radius:12px">
                            <option value="">Semua Proses</option>
                            <option value="assembling" @selected(request('divisi') == 'assembling')>ALL ASSEMBLING</option>
                            <option value="painting" @selected(request('divisi') == 'painting')>ALL PAINTING</option>
                            <option value="mainline" @selected(request('divisi') == 'mainline')>Mainline</option>
                            <option value="subassy" @selected(request('divisi') == 'subassy')>Sub Assy</option>
                            <option value="sub engine" @selected(request('divisi') == 'sub engine')>Sub Engine</option>
                            <option value="inspeksi" @selected(request('divisi') == 'inspeksi')>Inspeksi</option>
                            <option value="mower" @selected(request('divisi') == 'mower')>Mower</option>
                            <option value="repair" @selected(request('divisi') == 'repair')>Repair</option>
                            <option value="painting a" @selected(request('divisi') == 'painting a')>Painting A (Line A)</option>
                            <option value="painting b" @selected(request('divisi') == 'painting b')>Painting B (Line B)</option>
                            <option value="DST" @selected(request('divisi') == 'DST')>DST</option>
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

@push('scripts')
<script>
function refreshDivisiCount() {
    var cbs = document.querySelectorAll('#divisiFilter input.divisi-cb');
    var n = 0;
    var allChecked = true;
    cbs.forEach(function (cb) { 
        if (cb.checked) n++; 
        else allChecked = false;
    });
    var badge = document.getElementById('divisiCountBadge');
    if (badge) {
        badge.style.display = n > 0 ? 'inline-block' : 'none';
        badge.textContent = n;
    }
    var selectAllCb = document.getElementById('selectAllDivisi');
    if (selectAllCb) {
        selectAllCb.checked = (cbs.length > 0 && allChecked);
    }
}
function toggleAllDivisi(source) {
    document.querySelectorAll('#divisiFilter input.divisi-cb').forEach(function (cb) { cb.checked = source.checked; });
    refreshDivisiCount();
}
function resetDivisiFilter() {
    document.querySelectorAll('#divisiFilter input.divisi-cb').forEach(function (cb) { cb.checked = false; });
    refreshDivisiCount();
}
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('divisiFilter');
    if (!container) return;
    container.querySelectorAll('input.divisi-cb').forEach(function (cb) {
        cb.addEventListener('change', refreshDivisiCount);
    });
    refreshDivisiCount();
});
</script>
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
