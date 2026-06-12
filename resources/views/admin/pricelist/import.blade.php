@extends('layouts.admin')

@section('styles')
<style>
    .upload-zone { border: 2px dashed var(--pink-300); border-radius: 16px; padding: 3rem 2rem; text-align: center; cursor: pointer; transition: all .3s; background: var(--pink-50); }
    .upload-zone:hover { border-color: var(--pink-500); background: var(--pink-100); }
    .upload-zone.has-file { border-color: #22c55e; background: #f0fdf4; }
    .template-badge { font-size: .75rem; border-radius: 8px; padding: .25rem .6rem; background: #fef3c7; color: #92400e; display: inline-block; }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3 border-bottom pb-3 border-pink-200">
        <div>
            <h3 class="fw-800 mb-1" style="color:var(--pink-800)">
                <i class="bi bi-upload me-2 opacity-75"></i>Import Pricelist
            </h3>
            <p class="text-muted mb-0">Upload file Excel untuk mengimpor data pricelist</p>
        </div>
        <div>
            <a href="{{ route('admin.pricelist.index') }}" class="btn btn-outline-secondary shadow-sm rounded-pill px-4 py-2 fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card glass-card border-0" style="border-radius:16px;box-shadow:0 10px 40px rgba(225,29,72,.08)">
                <div class="card-header card-header-pink py-3">
                    <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-file-earmark-excel me-2"></i>Upload File Excel</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.pricelist.import.excel') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                            <div id="uploadIcon"><i class="bi bi-cloud-upload" style="font-size:3rem;color:var(--pink-400)"></i></div>
                            <div id="uploadText" class="mt-3">
                                <h6 class="fw-bold mb-1" style="color:var(--pink-700)">Klik untuk upload file</h6>
                                <p class="text-muted small mb-0">Format: .xlsx, .xls, .csv</p>
                            </div>
                            <div id="uploadFilename" class="mt-2 d-none">
                                <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i><span id="fileNameText"></span></span>
                            </div>
                            <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" class="d-none" required onchange="handleFile(this)">
                        </div>
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-pink rounded-pill px-5 py-2 fw-bold" id="submitBtn" disabled>
                                <i class="bi bi-upload me-2"></i>Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card glass-card border-0" style="border-radius:16px;box-shadow:0 10px 40px rgba(225,29,72,.08)">
                <div class="card-header card-header-pink py-3">
                    <h6 class="mb-0 fw-bold fs-6"><i class="bi bi-info-circle me-2"></i>Template Format</h6>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-3">File Excel harus memiliki kolom dengan urutan:</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="template-badge"><strong>A:</strong> ITEM CODE / KODE PART</span>
                        <span class="template-badge"><strong>B:</strong> CURRENCY</span>
                        <span class="template-badge"><strong>C:</strong> HARGA ASLI</span>
                    </div>
                    <p class="small text-muted mb-2">Baris pertama header (dilewati). Data dimulai baris ke-2.</p>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Rack code &amp; Nama part otomatis dicocokkan dari database Rack via kode part.
                        Jika kode part tidak ditemukan di database Rack, baris akan dilewati.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function handleFile(input) {
        const zone = document.getElementById('uploadZone');
        const icon = document.getElementById('uploadIcon');
        const text = document.getElementById('uploadText');
        const filename = document.getElementById('uploadFilename');
        const fileNameText = document.getElementById('fileNameText');
        const submitBtn = document.getElementById('submitBtn');

        if (input.files && input.files[0]) {
            zone.classList.add('has-file');
            icon.innerHTML = '<i class="bi bi-file-earmark-excel" style="font-size:3rem;color:#22c55e"></i>';
            text.innerHTML = '<p class="small text-success mb-0 fw-bold">File siap diupload</p>';
            fileNameText.textContent = input.files[0].name;
            filename.classList.remove('d-none');
            submitBtn.disabled = false;
        }
    }
</script>
@endpush
@endsection
