@extends('layouts.admin')

@section('styles')
<style>
    .filter-label {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--pink-700);
        margin-bottom: .5rem;
        display: block
    }

    .custom-table-container {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(219, 39, 119, .06);
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border)
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3 border-bottom pb-3 border-pink-200">
        <div>
            <h3 class="fw-800 mb-1" style="color:var(--pink-800)">
                <i class="bi bi-hourglass-split me-2 opacity-75"></i>Belum Diproses
            </h3>
            <p class="text-muted mb-0">Data Part NG yang belum diproses — {{ $monthLabel }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-success shadow-sm rounded-pill px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="glass-card p-3 border-0 mb-4">
        <form action="{{ route('admin.report-unprocessed') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="filter-label mb-1"><i class="bi bi-calendar me-1"></i>Bulan</label>
                <div class="input-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:10px 0 0 10px;padding:.3rem .5rem;border-color:transparent;background:var(--pink-100);color:var(--pink-700);font-weight:600" onclick="navigateMonth(-1)"><i class="bi bi-chevron-left"></i></button>
                    <input type="month" id="filter-month" name="month" class="form-control bg-light border-0 shadow-sm" style="border-radius:0;font-size:.85rem" value="{{ request('month', now()->format('Y-m')) }}">
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:0 10px 10px 0;padding:.3rem .5rem;border-color:transparent;background:var(--pink-100);color:var(--pink-700);font-weight:600" onclick="navigateMonth(1)"><i class="bi bi-chevron-right"></i></button>
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
<option value="">Semua Divisi</option>
<optgroup label="Assembling">
    <option value="Assembling" {{ request('divisi') == 'Assembling' ? 'selected' : '' }}>Semua Assembling</option>
    <option value="mainline" {{ request('divisi') == 'mainline' ? 'selected' : '' }}>Mainline</option>
    <option value="subassy" {{ request('divisi') == 'subassy' ? 'selected' : '' }}>Sub Assy</option>
    <option value="sub engine" {{ request('divisi') == 'sub engine' ? 'selected' : '' }}>Sub Engine</option>
    <option value="inspeksi" {{ request('divisi') == 'inspeksi' ? 'selected' : '' }}>Inspeksi</option>
    <option value="mower" {{ request('divisi') == 'mower' ? 'selected' : '' }}>Repair Mower</option>
</optgroup>
<optgroup label="Painting">
    <option value="Painting" {{ request('divisi') == 'Painting' ? 'selected' : '' }}>Semua Painting</option>
    <option value="painting a" {{ request('divisi') == 'painting a' ? 'selected' : '' }}>Painting A (Line A)</option>
    <option value="painting b" {{ request('divisi') == 'painting b' ? 'selected' : '' }}>Painting B (Line B)</option>
</optgroup>
<option value="DST" {{ request('divisi') == 'DST' ? 'selected' : '' }}>DST</option>
</select>
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
                <a href="{{ route('admin.report-unprocessed') }}" class="btn btn-light shadow-sm" style="border-radius:10px;font-size:.85rem;padding:.45rem 1.2rem;color:var(--pink-700);font-weight:600">Reset</a>
            </div>
            <div class="col-auto ms-auto text-end">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small fw-bold">Data:</span>
                        <span class="fw-bold fs-4" style="color:var(--pink-600)">{{ $parts->count() }}</span>
                        <span class="text-muted small">item</span>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-1">
                        <div class="d-flex gap-4 align-items-end">
                            <div class="d-flex flex-column align-items-end">
                                <span class="text-muted fw-bold" style="font-size: 0.75rem;" title="Bukan Tanggung Jawab"><i class="bi bi-x-circle-fill text-danger me-1"></i>Bukan TJ</span>
                                <span class="fw-bold fs-4 text-danger lh-1">$ {{ format_harga($totalCostBukanTanggungJawab) }}</span>
                            </div>
                            <div class="d-flex flex-column align-items-end border-start ps-4 border-2">
                                <span class="text-muted fw-bold" style="font-size: 0.75rem;"><i class="bi bi-trash3-fill me-1" style="color:#fd7e14"></i>Part Scrap</span>
                                <span class="fw-bold fs-4 lh-1" style="color:#fd7e14">$ {{ format_harga($totalCostPartScrap) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 pt-1 border-top" style="font-size: 0.8rem; width: 100%; justify-content: flex-end;">
                            <span class="text-muted fw-bold">Total Keseluruhan:</span>
                            <span class="fw-bold" style="color:#0d9488">$ {{ format_harga($totalCost) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card glass-card border-0 custom-table-container">
        <div class="card-header card-header-pink d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-table me-2"></i>Daftar Part NG Belum Diproses — {{ $monthLabel }}</h6>
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
                            <th class="text-center" width="5%">Aksi</th>
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
                                <div><span class="badge bg-info">{{ $p->Divisi ?? '-' }}</span></div>
                                <div class="mt-1"><span class="badge" style="background:var(--pink-100);color:var(--pink-800);border:1px solid var(--pink-200);white-space:normal;text-align:left;line-height:1.2">{{ $p->Category_Part_Ng }}</span></div>
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
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-pink fw-bold" style="border-radius:8px" onclick="openProcessModal({{ $index }})">
                                        <i class="bi bi-gear-fill me-1"></i>Proses
                                    </button>
                                    <a href="{{ route('admin.part-ng.edit', $p->Id_Part_Ng) }}" class="btn btn-sm btn-light border-primary text-primary fw-bold" style="border-radius:8px">
                                        <i class="bi bi-pencil-fill me-1"></i>Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="opacity-50 mb-3" style="color:var(--pink-400)"><i class="bi bi-check2-circle fs-1"></i></div>
                                <h6 class="fw-bold text-muted">Semua data {{ $monthLabel }} sudah diproses</h6>
                                <p class="small text-muted">Tidak ada data Part NG yang belum diproses.</p>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="background-color:var(--pink-100);border-radius:50%;opacity:1;padding:.8rem;margin:.5rem"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <img id="modalPhotoSrc" src="" class="img-fluid rounded-4 shadow-lg" alt="Foto Detail" style="max-height:80vh;border:4px solid white">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Process -->
    <div class="modal fade" id="processModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 glass-card">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color:var(--pink-800)"><i class="bi bi-gear-fill me-2"></i>Proses Part NG</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="processForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="card bg-light border-0 rounded-4 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-secondary me-1" id="detailRack">-</span>
                                    <span class="badge bg-dark font-monospace" id="detailCode">-</span>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-5" style="color:var(--pink-700)" id="detailQty">0</div>
                                    <div class="small text-muted lh-1">pcs</div>
                                </div>
                            </div>
                            <div class="fw-bold small mb-1" id="detailName">-</div>
                            <div class="small text-muted mb-2" id="detailDesc" style="font-size:.75rem">-</div>
                            <div class="d-flex justify-content-between small border-top pt-2">
                                <span class="text-muted" id="detailDate">-</span>
                                <span class="fw-bold" style="color:#0d9488" id="detailCost">$ 0.00</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Penanggungjawab</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="pj_type" id="pjMember" value="member" checked>
                                    <label class="form-check-label small" for="pjMember">Pilih Member</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="pj_type" id="pjManual" value="manual">
                                    <label class="form-check-label small" for="pjManual">Lain-lain</label>
                                </div>
                            </div>
                            <div id="pjMemberGroup">
                                <input type="text" id="memberSearch" class="form-control" style="border-radius:12px" placeholder="Cari nama member...">
                                <div id="memberResults" class="list-group mt-1" style="max-height:200px;overflow-y:auto;display:none;position:absolute;z-index:1000;width:calc(100% - 2rem);box-shadow:0 4px 12px rgba(0,0,0,.1);border-radius:8px"></div>
                                <input type="hidden" name="penanggungjawab" id="penanggungjawabInput" value="">
                                <small class="text-muted mt-1 d-block">Klik nama member untuk memilih</small>
                            </div>
                            <div id="pjManualGroup" class="d-none">
                                <input type="text" id="penanggungjawabManual" class="form-control" style="border-radius:12px" placeholder="Ketik keterangan lain...">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Penyebab</label>
                            <textarea name="penyebab" class="form-control" rows="3" style="border-radius:12px" required placeholder="Masukkan penyebab"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Pencegahan / Penanganan</label>
                            <textarea name="penanganan" class="form-control" rows="3" style="border-radius:12px" required placeholder="Masukkan pencegahan/penanganan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="prevPart()" style="display:none">
                                <i class="bi bi-chevron-left me-1"></i>Sebelumnya
                            </button>
                            <span class="small text-muted fw-bold" id="partCounter">1 / 1</span>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-pink" id="saveBtn" onclick="saveProcess()">
                                    <i class="bi bi-check-lg me-1"></i>Simpan
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="nextBtn" onclick="nextPart()">
                                    Selanjutnya<i class="bi bi-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
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
                        <input type="hidden" name="status" value="unprocessed">
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
<optgroup label="Assembling">
    <option value="Assembling" {{ request('divisi') == 'Assembling' ? 'selected' : '' }}>Semua Assembling</option>
    <option value="mainline" {{ request('divisi') == 'mainline' ? 'selected' : '' }}>Mainline</option>
    <option value="subassy" {{ request('divisi') == 'subassy' ? 'selected' : '' }}>Sub Assy</option>
    <option value="sub engine" {{ request('divisi') == 'sub engine' ? 'selected' : '' }}>Sub Engine</option>
    <option value="inspeksi" {{ request('divisi') == 'inspeksi' ? 'selected' : '' }}>Inspeksi</option>
    <option value="mower" {{ request('divisi') == 'mower' ? 'selected' : '' }}>Repair Mower</option>
</optgroup>
<optgroup label="Painting">
    <option value="Painting" {{ request('divisi') == 'Painting' ? 'selected' : '' }}>Semua Painting</option>
    <option value="painting a" {{ request('divisi') == 'painting a' ? 'selected' : '' }}>Painting A (Line A)</option>
    <option value="painting b" {{ request('divisi') == 'painting b' ? 'selected' : '' }}>Painting B (Line B)</option>
</optgroup>
<option value="DST" {{ request('divisi') == 'DST' ? 'selected' : '' }}>DST</option>
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
    function navigateMonth(dir) {
        const input = document.getElementById('filter-month');
        const d = new Date(input.value + '-01');
        d.setMonth(d.getMonth() + dir);
        input.value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        input.form.submit();
    }
</script>
@php
$partsMapped = $parts->values()->map(fn($p) => [
'id' => $p->Id_Part_Ng,
'tanggal' => \Carbon\Carbon::parse($p->Date_Part_Ng)->format('d M Y'),
'code_rack' => $p->Code_Rack,
'code_item' => $p->Code_Item_Rack,
'name_item' => $p->Name_Item_Rack,
'desc' => $p->Desc_Part_Ng,
'category' => $p->Category_Part_Ng,
'divisi' => $p->Divisi,
'qty' => $p->Total_Part_Ng,
'cost' => $p->cost ?? 0,
'pic' => $p->penanggungjawab ?? '',
'penyebab' => $p->penyebab ?? '',
'penanganan' => $p->penanganan ?? '',
]);
@endphp
<script>
    const partsData = @json($partsMapped);

    const BASE_URL = "{{ url('admin/part-ng') }}";
    const MEMBER_SEARCH_URL = "{{ route('members.search') }}";
    let currentPartIndex = 0;
    let searchTimeout = null;

    function showPhoto(url) {
        document.getElementById('modalPhotoSrc').src = url;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }

    function openProcessModal(index) {
        currentPartIndex = index;
        loadPartData(index);
        new bootstrap.Modal(document.getElementById('processModal')).show();
    }

    function loadPartData(index) {
        const data = partsData[index];
        if (!data) return;

        document.getElementById('detailRack').textContent = data.code_rack || '-';
        document.getElementById('detailCode').textContent = data.code_item || '-';
        document.getElementById('detailName').textContent = data.name_item || '-';
        document.getElementById('detailDesc').textContent = data.desc || '-';
        document.getElementById('detailDate').textContent = data.tanggal || '-';
        document.getElementById('detailQty').textContent = data.qty || 0;
        document.getElementById('detailCost').textContent = '$ ' + (data.cost || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        let form = document.getElementById('processForm');
        form.querySelector('textarea[name="penyebab"]').value = data.penyebab || '';
        form.querySelector('textarea[name="penanganan"]').value = data.penanganan || '';

        let pjInput = document.getElementById('penanggungjawabInput');
        let pjManual = document.getElementById('penanggungjawabManual');
        let memberSearch = document.getElementById('memberSearch');
        if (data.pic) {
            pjInput.value = data.pic;
            pjManual.value = data.pic;
            memberSearch.value = data.pic;
        } else {
            pjInput.value = '';
            pjManual.value = '';
            memberSearch.value = '';
        }

        document.getElementById('partCounter').textContent = (index + 1) + ' / ' + partsData.length;
        document.getElementById('prevBtn').style.display = index > 0 ? '' : 'none';
        document.getElementById('nextBtn').style.display = index < partsData.length - 1 ? '' : 'none';
    }

    function saveProcess() {
        const data = partsData[currentPartIndex];
        if (!data) return;

        let form = document.getElementById('processForm');
        let formData = new FormData(form);
        let pjType = document.querySelector('input[name="pj_type"]:checked').value;
        let pjValue = '';
        if (pjType === 'member') {
            pjValue = document.getElementById('penanggungjawabInput').value;
        } else {
            const manualText = document.getElementById('penanggungjawabManual').value.trim();
            pjValue = manualText ? 'Lain Lain - ' + manualText : 'Lain Lain';
        }
        if (pjValue) {
            formData.set('penanggungjawab', pjValue);
        }
        let url = BASE_URL + '/' + data.id + '/process';

        let btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

        fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    partsData[currentPartIndex].penyebab = form.querySelector('textarea[name="penyebab"]').value;
                    partsData[currentPartIndex].penanganan = form.querySelector('textarea[name="penanganan"]').value;
                    partsData[currentPartIndex].pic = pjValue;
                    showToast(res.message, 'success');
                    if (currentPartIndex < partsData.length - 1) {
                        setTimeout(() => nextPart(), 800);
                    } else {
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('processModal'))?.hide();
                        }, 800);
                    }
                }
            })
            .catch(() => showToast('Gagal menyimpan data.', 'danger'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Simpan';
            });
    }

    function prevPart() {
        if (currentPartIndex > 0) loadPartData(--currentPartIndex);
    }

    function nextPart() {
        if (currentPartIndex < partsData.length - 1) loadPartData(++currentPartIndex);
    }

    function showToast(msg, type) {
        let c = document.getElementById('toastContainer');
        if (!c) {
            c = document.createElement('div');
            c.id = 'toastContainer';
            c.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999';
            document.body.appendChild(c);
        }
        let t = document.createElement('div');
        t.className = 'alert alert-' + type + ' alert-dismissible fade show py-2 px-3 mb-2 shadow-sm';
        t.style.cssText = 'border-radius:12px;font-size:.85rem;min-width:280px';
        t.innerHTML = msg + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert" style="font-size:.75rem"></button>';
        c.appendChild(t);
        setTimeout(() => {
            t.remove();
        }, 3000);
    }

    // Member search
    document.addEventListener('DOMContentLoaded', function() {
        const memberSearch = document.getElementById('memberSearch');
        const memberResults = document.getElementById('memberResults');
        const pjInput = document.getElementById('penanggungjawabInput');

        document.querySelectorAll('input[name="pj_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('pjMemberGroup').classList.toggle('d-none', this.value !== 'member');
                document.getElementById('pjManualGroup').classList.toggle('d-none', this.value !== 'manual');
            });
        });

        function loadMembers(q) {
            clearTimeout(searchTimeout);
            memberResults.innerHTML = '<div class="list-group-item text-muted small">Memuat...</div>';
            memberResults.style.display = 'block';
            const delay = q === '' ? 50 : 300;
            searchTimeout = setTimeout(() => {
                fetch(MEMBER_SEARCH_URL + '?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        memberResults.innerHTML = '';
                        if (data.length === 0) {
                            memberResults.innerHTML = '<div class="list-group-item text-muted small">Tidak ditemukan</div>';
                        } else {
                            data.forEach(m => {
                                const a = document.createElement('a');
                                a.href = '#';
                                a.className = 'list-group-item list-group-item-action py-2 small';
                                a.textContent = m.nama;
                                a.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    memberSearch.value = m.nama;
                                    pjInput.value = m.nama;
                                    memberResults.style.display = 'none';
                                });
                                memberResults.appendChild(a);
                            });
                        }
                        memberResults.style.display = 'block';
                    });
            }, delay);
        }

        memberSearch.addEventListener('focus', function() {
            loadMembers('');
        });

        memberSearch.addEventListener('input', function() {
            loadMembers(this.value.trim());
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#pjMemberGroup')) {
                memberResults.style.display = 'none';
            }
        });
    });
</script>
@endpush
@endsection