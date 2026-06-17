@extends('layouts.admin')

@section('content')
<div class="container-fluid px-lg-5 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1" style="color:var(--pink-800)">
                <i class="bi bi-pencil-square me-2"></i>Edit Part NG
            </h3>
            <p class="text-muted mb-0">Edit data Part NG — semua field bisa diubah</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-pink-outline">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="glass-card p-4 border-0">
        <form id="editForm" method="POST" action="{{ route('admin.part-ng.update', $part->Id_Part_Ng) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Informasi Rak --}}
                <div class="col-12">
                    <h6 class="fw-bold text-muted small text-uppercase mb-3" style="color:var(--pink-700) !important">
                        <i class="bi bi-box me-1"></i>Informasi Rak & Part
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">No Rak</label>
                            <input type="text" name="Code_Rack" class="form-control" style="border-radius: 12px;" value="{{ old('Code_Rack', $part->Code_Rack) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Kode Part</label>
                            <input type="text" name="Code_Item_Rack" class="form-control" style="border-radius: 12px;" value="{{ old('Code_Item_Rack', $part->Code_Item_Rack) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Nama Part</label>
                            <input type="text" name="Name_Item_Rack" class="form-control" style="border-radius: 12px;" value="{{ old('Name_Item_Rack', $part->Name_Item_Rack) }}">
                        </div>
                    </div>
                </div>

                {{-- Divisi & Proses (khusus admin bisa edit) --}}
                <div class="col-12">
                    <h6 class="fw-bold text-muted small text-uppercase mb-3" style="color:var(--pink-700) !important">
                        <i class="bi bi-gear me-1"></i>Divisi & Proses
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Divisi</label>
                            <select name="Divisi" class="form-select" style="border-radius: 12px;">
                                <option value="">Pilih Divisi...</option>
                                <option value="Assembling" {{ $part->Divisi == 'Assembling' ? 'selected' : '' }}>Assembling</option>
                                <option value="DST" {{ $part->Divisi == 'DST' ? 'selected' : '' }}>DST</option>
                                <option value="Painting" {{ $part->Divisi == 'Painting' ? 'selected' : '' }}>Painting</option>
                                <option value="Mower" {{ $part->Divisi == 'Mower' ? 'selected' : '' }}>Mower</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Proses</label>
                            <select name="proses" class="form-select" style="border-radius: 12px;">
                                <option value="">Pilih Proses...</option>
                                <option value="DST" {{ $part->proses == 'DST' ? 'selected' : '' }}>DST</option>
                                <option value="SUB" {{ $part->proses == 'SUB' ? 'selected' : '' }}>SUB</option>
                                <option value="LINE A" {{ $part->proses == 'LINE A' ? 'selected' : '' }}>LINE A</option>
                                <option value="LINE B" {{ $part->proses == 'LINE B' ? 'selected' : '' }}>LINE B</option>
                                <option value="MOWER" {{ $part->proses == 'MOWER' ? 'selected' : '' }}>MOWER</option>
                                <option value="PAINTING" {{ $part->proses == 'PAINTING' ? 'selected' : '' }}>PAINTING</option>
                                <option value="COLLECTOR" {{ $part->proses == 'COLLECTOR' ? 'selected' : '' }}>COLLECTOR</option>
                            </select>
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
                        <i class="bi bi-camera me-1"></i>Foto
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
                                        <div class="mb-2">
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

                {{-- Proses Fields (hanya bisa diedit kalo sudah diproses) --}}
                @php $isProcessed = $part->penanggungjawab ? true : false; @endphp
                <div class="col-12">
                    <h6 class="fw-bold text-muted small text-uppercase mb-3" style="color:var(--pink-700) !important">
                        <i class="bi bi-check-circle me-1"></i>Data Proses
                        @if(!$isProcessed)
                        <span class="badge bg-warning text-dark ms-2">Belum diproses</span>
                        @else
                        <span class="badge bg-success ms-2">Sudah diproses</span>
                        @endif
                    </h6>
                    @if(!$isProcessed)
                    <div class="alert alert-warning rounded-3 py-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i>Data ini belum diproses. Gunakan tombol <strong>Proses</strong> di laporan untuk mengisi data proses.
                    </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Penanggungjawab</label>
                            <input type="text" name="penanggungjawab" class="form-control" style="border-radius: 12px;"
                                value="{{ old('penanggungjawab', $part->penanggungjawab) }}"
                                {{ !$isProcessed ? 'readonly' : '' }}
                                placeholder="{{ !$isProcessed ? '(isi melalui form Proses)' : 'Nama penanggungjawab' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Penyebab</label>
                            <textarea name="penyebab" class="form-control" rows="2" style="border-radius: 12px;"
                                {{ !$isProcessed ? 'readonly' : '' }}>{{ old('penyebab', $part->penyebab) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Penanganan</label>
                            <textarea name="penanganan" class="form-control" rows="2" style="border-radius: 12px;"
                                {{ !$isProcessed ? 'readonly' : '' }}>{{ old('penanganan', $part->penanganan) }}</textarea>
                        </div>
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
