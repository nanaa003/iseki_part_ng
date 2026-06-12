@extends('layouts.admin')

@section('styles')
<style>
    .currency-badge{font-size:.75rem;padding:.25rem .6rem;border-radius:20px}
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3 border-bottom pb-3 border-pink-200">
        <div>
            <h3 class="fw-800 mb-1" style="color:var(--pink-800)">
                <i class="bi bi-currency-exchange me-2 opacity-75"></i>Master Currency
            </h3>
            <p class="text-muted mb-0">Atur logika konversi mata uang untuk pricelist</p>
        </div>
        <div>
            <button class="btn btn-pink shadow-sm rounded-pill px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-lg me-2"></i>Tambah Currency
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card glass-card border-0" style="border-radius:16px;overflow:hidden;box-shadow:0 10px 40px rgba(225,29,72,.08)">
        <div class="card-header card-header-pink d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-list me-2"></i>Daftar Currency</h6>
            <span class="badge bg-white fw-bold" style="color:var(--pink-700)">{{ $currencies->count() }} currency</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th width="10%">Code</th>
                            <th width="20%">Nama</th>
                            <th width="15%">Tipe Konversi</th>
                            <th width="15%">Rate</th>
                            <th width="10%">Base</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $c)
                        <tr>
                            <td class="fw-bold font-monospace">{{ $c->code }}</td>
                            <td>{{ $c->name }}</td>
                            <td>
                                <span class="badge {{ $c->conversion_type == 'divide' ? 'bg-warning text-dark' : 'bg-info' }} currency-badge">
                                    {{ $c->conversion_type == 'divide' ? 'Bagi' : 'Kali' }}
                                </span>
                            </td>
                            <td class="font-monospace fw-bold">{{ number_format((float) $c->conversion_rate, (float) $c->conversion_rate == round((float) $c->conversion_rate) ? 0 : 2) }}</td>
                            <td>
                                @if($c->is_base)
                                <span class="badge bg-success currency-badge">BASE</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-pink-outline" data-bs-toggle="modal" data-bs-target="#editModal{{ $c->id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @if(!$c->is_base)
                                <form action="{{ route('admin.currency.destroy', $c->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus currency {{ $c->code }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="opacity-50 mb-3" style="color:var(--pink-400)"><i class="bi bi-currency-exchange fs-1"></i></div>
                                <h6 class="fw-bold text-muted">Belum ada currency</h6>
                                <p class="small text-muted">Tambahkan currency untuk mengatur konversi harga pricelist.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:1px solid var(--glass-border);box-shadow:0 8px 32px rgba(219,39,119,.08)">
            <div class="modal-header card-header-pink">
                <h5 class="modal-title fs-6"><i class="bi bi-plus-lg me-2"></i>Tambah Currency</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.currency.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 text-start">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold text-muted small">Code</label>
                            <input type="text" name="code" class="form-control" placeholder="IDR" required maxlength="10">
                        </div>
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold text-muted small">Nama Currency</label>
                            <input type="text" name="name" class="form-control" placeholder="Rupiah" required maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">Tipe Konversi</label>
                            <select name="conversion_type" class="form-select" required>
                                <option value="divide">Bagi (harga / rate)</option>
                                <option value="multiply">Kali (harga * rate)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">Conversion Rate</label>
                            <input type="number" step="any" name="conversion_rate" class="form-control" placeholder="16000" required min="0.000001">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_base" id="createIsBase" value="1">
                        <label class="form-check-label fw-bold text-muted small" for="createIsBase">Jadikan sebagai BASE currency (USD)</label>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-pink">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($currencies as $c)
<!-- Modal Edit -->
<div class="modal fade" id="editModal{{ $c->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:1px solid var(--glass-border);box-shadow:0 8px 32px rgba(219,39,119,.08)">
            <div class="modal-header card-header-pink">
                <h5 class="modal-title fs-6"><i class="bi bi-pencil-square me-2"></i>Edit Currency</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.currency.update', $c->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4 text-start">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold text-muted small">Code</label>
                            <input type="text" name="code" class="form-control" value="{{ $c->code }}" required maxlength="10">
                        </div>
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold text-muted small">Nama Currency</label>
                            <input type="text" name="name" class="form-control" value="{{ $c->name }}" required maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">Tipe Konversi</label>
                            <select name="conversion_type" class="form-select" required>
                                <option value="divide" {{ $c->conversion_type == 'divide' ? 'selected' : '' }}>Bagi (harga / rate)</option>
                                <option value="multiply" {{ $c->conversion_type == 'multiply' ? 'selected' : '' }}>Kali (harga * rate)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">Conversion Rate</label>
                            <input type="number" step="0.000001" name="conversion_rate" class="form-control" value="{{ (float) $c->conversion_rate }}" required min="0.000001">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_base" id="editIsBase{{ $c->id }}" value="1" {{ $c->is_base ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-muted small" for="editIsBase{{ $c->id }}">Jadikan sebagai BASE currency (USD)</label>
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
