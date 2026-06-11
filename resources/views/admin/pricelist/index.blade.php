@extends('layouts.admin')

@section('styles')
<style>
    .table-pricelist{width:100%;border-collapse:collapse}
    .table-pricelist thead th{font-weight:700;font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;padding:.9rem 1rem;color:#fff;background:linear-gradient(135deg,var(--pink-500),var(--pink-600));border:none}
    .table-pricelist thead th:first-child{padding-left:1.5rem}
    .table-pricelist thead th:last-child{padding-right:1.5rem}
    .table-pricelist tbody td{padding:.85rem 1rem;vertical-align:middle;border-bottom:1px solid var(--pink-50);font-size:.875rem;background:#fff}
    .table-pricelist tbody tr:hover td{background:var(--pink-50)}
    .table-pricelist tbody tr td:first-child{padding-left:1.5rem;font-weight:600;color:var(--pink-700);border-left:3px solid transparent}
    .table-pricelist tbody tr:hover td:first-child{border-left-color:var(--pink-400)}
    .table-pricelist tbody tr td:last-child{padding-right:1.5rem}
    .table-pricelist tbody tr:last-child td{border-bottom:none}

</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3 border-bottom pb-3 border-pink-200">
        <div>
            <h3 class="fw-800 mb-1" style="color:var(--pink-800)">
                <i class="bi bi-tags-fill me-2 opacity-75"></i>Pricelist
            </h3>
            <p class="text-muted mb-0">Daftar harga part NG (USD)</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pricelist.import') }}" class="btn btn-pink shadow-sm rounded-pill px-4 py-2 fw-bold">
                <i class="bi bi-upload me-2"></i>Import Excel
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card glass-card border-0" style="border-radius:16px;overflow:hidden;box-shadow:0 10px 40px rgba(225,29,72,.08)">
        <div class="card-header card-header-pink d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-table me-2"></i>Data Pricelist</h6>
            <span class="badge bg-white fw-bold" style="color:var(--pink-700)">{{ $pricelists->count() }} item</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height:400px">
                <table class="table table-hover table-pricelist mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="15%">No Rak</th>
                            <th width="15%">Kode Part</th>
                            <th width="25%">Nama Part</th>
                            <th width="15%" class="text-end">Harga Asli</th>
                            <th width="15%" class="text-end">Final (USD)</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pricelists as $item)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $item->no }}</td>
                            <td><span class="badge-pink">{{ $item->no_rak }}</span></td>
                            <td class="font-monospace fw-bold">{{ $item->kode_part }}</td>
                            <td>{{ $item->nama_part }}</td>
                            <td class="text-end fw-bold">
                                {{ format_harga($item->harga_asli) }} <span class="text-muted small">{{ $item->currency }}</span>
                            </td>
                            <td class="text-end fw-bold text-success">$ {{ format_harga($item->harga_usd) }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-pink-outline" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="opacity-50 mb-3" style="color:var(--pink-400)"><i class="bi bi-inbox fs-1"></i></div>
                                <h6 class="fw-bold text-muted">Belum ada data pricelist</h6>
                                <p class="small text-muted">Silakan import data pricelist dari Excel.</p>
                                <a href="{{ route('admin.pricelist.import') }}" class="btn btn-pink rounded-pill px-4 mt-2">
                                    <i class="bi bi-upload me-2"></i>Import Excel
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pricelists->hasPages())
            <div class="d-flex justify-content-center py-3 border-top">
                {{ $pricelists->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@foreach($pricelists as $item)
<!-- Modal Edit -->
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#fff;border-radius:16px;border:1px solid var(--glass-border);box-shadow:0 8px 32px rgba(219,39,119,.08)">
            <div class="modal-header card-header-pink">
                <h5 class="modal-title fs-6"><i class="bi bi-pencil-square me-2"></i>Edit Pricelist</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pricelist.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nomor Rak</label>
                        <input type="text" name="no_rak" class="form-control" value="{{ $item->no_rak }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Kode Part</label>
                        <input type="text" name="kode_part" class="form-control" value="{{ $item->kode_part }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nama Part</label>
                        <input type="text" name="nama_part" class="form-control" value="{{ $item->nama_part }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold text-muted small">Harga Asli</label>
                            <input type="number" step="0.01" name="harga_asli" class="form-control" value="{{ $item->harga_asli }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold text-muted small">Currency</label>
                            <input type="text" name="currency" class="form-control" value="{{ $item->currency }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-pink">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
