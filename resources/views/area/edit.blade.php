@extends('layouts.area')

@section('content')
<div class="container py-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--pink-700);"><i class="bi bi-pencil-square me-2"></i>Edit Part NG</h4>
            <p class="text-muted mb-0 small">Edit data Part NG yang sudah diinput</p>
        </div>
        <a href="{{ route('area.dashboard') }}" class="btn btn-pink-outline">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="glass-card p-4">
        <form id="editForm" method="POST" action="{{ route('area.part-ng.update', $part->Id_Part_Ng) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Informasi Rak (readonly) --}}
                <div class="col-12">
                    <div class="bg-light rounded-3 p-3 border">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-box me-1"></i>Informasi Rak (tidak bisa diubah)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">No Rak</label>
                                <input type="text" class="form-control bg-white" value="{{ $part->Code_Rack }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Kode Part</label>
                                <input type="text" class="form-control bg-white" value="{{ $part->Code_Item_Rack }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nama Part</label>
                                <input type="text" class="form-control bg-white" value="{{ $part->Name_Item_Rack }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div class="col-12">
                    <label class="form-label fw-bold small text-uppercase" style="color: var(--pink-700);">
                        <i class="bi bi-pencil-square me-1"></i>Keterangan
                    </label>
                    <textarea name="Desc_Part_Ng" class="form-control" rows="4" style="border-radius: 12px;" required>{{ old('Desc_Part_Ng', $part->Desc_Part_Ng) }}</textarea>
                </div>

                {{-- Kategori --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase" style="color: var(--pink-700);">
                        <i class="bi bi-tags me-1"></i>Kategori
                    </label>
                    <select name="Category_Part_Ng" class="form-select" style="border-radius: 12px;" required>
                        <option value="part scrap" {{ $part->Category_Part_Ng == 'part scrap' ? 'selected' : '' }}>Part Scrap</option>
                        <option value="bukan tanggung jawab" {{ str_starts_with($part->Category_Part_Ng, 'bukan tanggung jawab') ? 'selected' : '' }}>Bukan Tanggung Jawab</option>
                    </select>
                </div>

                {{-- Jumlah Pcs --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase" style="color: var(--pink-700);">
                        <i class="bi bi-hash me-1"></i>Jumlah Pcs
                    </label>
                    <input type="number" name="Total_Part_Ng" class="form-control" style="border-radius: 12px;" min="1" value="{{ old('Total_Part_Ng', $part->Total_Part_Ng) }}" required>
                </div>

                {{-- Foto --}}
                <div class="col-12">
                    <label class="form-label fw-bold small text-uppercase" style="color: var(--pink-700);">
                        <i class="bi bi-camera me-1"></i>Foto (biarkan kosong jika tidak ingin mengubah)
                    </label>
                    <div class="row g-3">
                        @for ($i = 1; $i <= 3; $i++)
                            @php
                                $dbField = $i === 1 ? 'Photo_Path_Part_Ng' : ($i === 2 ? 'Photo_Path_Part_Ng_2' : 'Photo_Path_Part_Ng_3');
                                $inputName = $i === 1 ? 'photo' : 'photo_' . $i;
                                $photo = $part->$dbField;
                            @endphp
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 text-center" style="border: 2px dashed var(--pink-300) !important; background: var(--pink-50);">
                                    @if($photo)
                                        <div class="mb-2 position-relative d-inline-block">
                                            <img src="{{ asset($photo) }}" class="img-fluid rounded-3 shadow-sm" style="max-height: 150px; object-fit: contain;">
                                            <div class="mt-2">
                                                <label class="btn btn-sm btn-outline-danger" style="border-radius: 8px; cursor: pointer;">
                                                    <i class="bi bi-trash me-1"></i>Hapus
                                                    <input type="checkbox" name="remove_photo_{{ $i }}" value="1" class="d-none" onchange="this.closest('label').classList.toggle('btn-outline-danger')">
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="mt-2">
                                        <label class="btn btn-sm btn-pink" style="border-radius: 8px; cursor: pointer;">
                                            <i class="bi bi-upload me-1"></i>{{ $photo ? 'Ganti' : 'Pilih' }} Foto {{ $i }}
                                            <input type="file" name="{{ $inputName }}" accept="image/*" class="d-none" onchange="previewFoto(this, {{ $i }})">
                                        </label>
                                    </div>
                                    <div id="preview{{ $i }}" class="mt-2 d-none">
                                        <img src="" class="img-fluid rounded-3 shadow-sm" style="max-height: 150px; object-fit: contain;">
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- Submit --}}
                <div class="col-12">
                    <button type="submit" class="btn btn-pink btn-lg w-100 shadow-sm" style="border-radius: 12px;">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function previewFoto(input, num) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview' + num);
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
