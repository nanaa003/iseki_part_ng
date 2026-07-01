@extends('layouts.area')

@section('content')
<div class="container py-4 fade-in">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--pink-800);">Data Part NG</h4>
            <p class="text-muted mb-0 small">Daftar part NG yang telah diinput</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('area.create') }}" class="btn btn-pink shadow-sm">
                <i class="bi bi-upc-scan me-1"></i>Input Scan
            </a>
            <a href="{{ route('area.create.manual') }}" class="btn btn-pink-outline shadow-sm bg-white">
                <i class="bi bi-keyboard me-1"></i>Input Manual
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="glass-card p-3 mb-4">
        <form action="{{ route('area.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold" style="color: var(--pink-700);">Filter Tanggal</label>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" style="border-color:transparent;background:var(--pink-100);color:var(--pink-700);font-weight:600" onclick="navigateDate(-1)"><i class="bi bi-chevron-left"></i></button>
                    <input type="date" name="date" class="form-control" value="{{ request('date', $filterDate) }}">
                    <button type="button" class="btn btn-outline-secondary" style="border-color:transparent;background:var(--pink-100);color:var(--pink-700);font-weight:600" onclick="navigateDate(1)"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold" style="color: var(--pink-700);">Divisi</label>
                <select name="divisi" class="form-select">
                    <option value="">Semua Divisi</option>
                    <option value="Assembling" {{ request('divisi') == 'Assembling' ? 'selected' : '' }}>Assembling</option>
                    <option value="DST" {{ request('divisi') == 'DST' ? 'selected' : '' }}>DST</option>
                    <option value="Painting" {{ request('divisi') == 'Painting' ? 'selected' : '' }}>Painting</option>
                    <option value="Mower" {{ request('divisi') == 'Mower' ? 'selected' : '' }}>Mower</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold" style="color: var(--pink-700);">Kategori</label>
                <select name="category" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="bukan tanggung jawab" {{ request('category') == 'bukan tanggung jawab' ? 'selected' : '' }}>Bukan Tanggung Jawab</option>
                    <option value="part scrap" {{ request('category') == 'part scrap' ? 'selected' : '' }}>Part Scrap</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-pink w-100">
                    <i class="bi bi-search me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Data List -->
    <div class="card glass-card border-0">
        <div class="card-header card-header-pink d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-task me-2"></i>Daftar Part NG</h6>
            <span class="badge bg-white text-pink-700 rounded-pill px-3">{{ $parts->count() }} Data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-premium mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="12%">Waktu Input</th>
                            <th width="13%">Rack / Part</th>
                            <th width="15%">Keterangan</th>
                            <th width="10%">Divisi</th>
                            <th width="8%">Proses</th>
                            <th width="12%">Kategori</th>
                            <th class="text-center" width="25%">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $index => $p)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $parts->count() - $index }}</td>
                                <td>
                                    <div class="fw-bold" style="color: var(--pink-800);">{{ \Carbon\Carbon::parse($p->Date_Part_Ng)->format('H:i') }}</div>
                                    <div class="small text-muted">{{ \Carbon\Carbon::parse($p->Date_Part_Ng)->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <div class="badge-pink mb-1 d-inline-block">{{ $p->Code_Rack }}</div>
                                    <div class="fw-bold small">{{ $p->Code_Item_Rack }}</div>
                                    <div class="small text-muted text-truncate" style="max-width: 120px;" title="{{ $p->Name_Item_Rack }}">{{ $p->Name_Item_Rack }}</div>
                                </td>
                                <td>
                                    <p class="mb-0 small" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">{{ $p->Desc_Part_Ng }}</p>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $p->Divisi ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $p->proses ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $p->Category_Part_Ng }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        @php $photos = array_filter([$p->Photo_Path_Part_Ng, $p->Photo_Path_Part_Ng_2, $p->Photo_Path_Part_Ng_3]); @endphp
                                        @forelse($photos as $photo)
                                            <img src="{{ asset($photo) }}" 
                                                 class="img-thumbnail rounded shadow-sm" 
                                                 style="height: 45px; width: 45px; object-fit: cover; cursor: pointer;"
                                                 onclick="showPhoto('{{ asset($photo) }}')"
                                                 alt="Foto Part NG">
                                        @empty
                                            <span class="text-muted small"><i class="bi bi-camera-video-off d-block mb-1 fs-5"></i>No Photo</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-pink-300"></i>
                                        Belum ada data part NG hari ini.
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

<!-- Modal Foto -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 glass-card">
            <div class="modal-header border-bottom-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-4">
                <img id="modalPhotoSrc" src="" class="img-fluid rounded shadow" alt="Foto Detail" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function navigateDate(dir) {
        const input = document.querySelector('input[name="date"]');
        let d = new Date(input.value + 'T00:00:00');
        d.setDate(d.getDate() + dir);
        input.value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        input.form.submit();
    }

    function showPhoto(url) {
        document.getElementById('modalPhotoSrc').src = url;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
</script>
@endpush
@endsection
