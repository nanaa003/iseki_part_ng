@extends('layouts.area')

@section('content')
<div class="hero-banner">
    <h1><i class="bi bi-plus-circle me-2"></i>Input Part NG</h1>
    <p>Silakan ikuti langkah-langkah berikut untuk menginput Part NG.</p>
</div>
<div class="container pb-5">
    <div class="mb-3">
        <a href="{{ route('area.dashboard') }}" class="btn btn-pink-outline">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div id="notificationArea"></div>

    <form id="partNgForm" enctype="multipart/form-data">
        @csrf

        {{-- ===================== STEP 1: SCAN NO RAK ===================== --}}
        <div class="glass-card mb-4 fade-in" id="step1">
            <div class="card-header-pink">
                <i class="bi bi-1-circle-fill me-2"></i>Step 1: Scan No Rak
            </div>
            <div class="card-body text-center p-4">
                <div id="reader-rack" style="width: 100%; max-width: 400px; margin: 0 auto;" class="mb-3 rounded-3 overflow-hidden shadow-sm"></div>

                <div class="row text-start justify-content-center mt-3">
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">
                            <i class="bi bi-upc-scan me-1"></i>Input / Hasil Scan No Rak
                        </label>
                        <div class="input-group">
                            <input type="text" id="code_rack" name="Code_Rack" class="form-control form-control-lg text-center fw-bold" style="color: var(--pink-600);" placeholder="Scan / Ketik No Rak">
                            <button class="btn btn-pink" type="button" id="btnVerifyRackManual">Cek Rak</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">
                            <i class="bi bi-box me-1"></i>Kode Part
                        </label>
                        <input type="text" id="code_item_rack" name="Code_Item_Rack" class="form-control form-control-lg text-center bg-light" readonly placeholder="-">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">
                            <i class="bi bi-card-text me-1"></i>Nama Part
                        </label>
                        <input type="text" id="name_item_rack" name="Name_Item_Rack" class="form-control form-control-lg text-center bg-light" readonly placeholder="-">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">
                            <i class="bi bi-cash-coin me-1"></i>Harga (USD)
                        </label>
                        <input type="text" id="harga_display" class="form-control form-control-lg text-center bg-light" readonly placeholder="-">
                    </div>
                </div>

                <input type="hidden" id="Id_Rack" name="Id_Rack">

                <button type="button" class="btn btn-pink btn-lg w-100 mt-2" id="btnNext1" disabled>
                    Lanjut ke Detail <i class="bi bi-arrow-right-circle ms-2"></i>
                </button>
            </div>
        </div>

        {{-- ===================== STEP 2: FOTO & DETAIL ===================== --}}
        <div class="glass-card mb-4 d-none slide-up" id="step2">
            <div class="card-header-pink">
                <i class="bi bi-2-circle-fill me-2"></i>Step 2: Foto & Keterangan Part NG
            </div>
            <div class="card-body p-4">

                <!-- 3 Foto Slots -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small text-uppercase mb-3 d-block">
                        <i class="bi bi-camera me-1"></i>Ambil Foto Part NG (maks 3)
                    </label>
                    <div class="row g-3">
                        @for ($i = 1; $i <= 3; $i++)
                        @php $inputId = 'photoInput' . $i; @endphp
                        <div class="col-md-4">
                            <div id="photoUploadArea{{ $i }}"
                                class="border rounded-3 p-4 text-center"
                                style="border: 2px dashed var(--pink-300) !important; background: var(--pink-50); cursor: pointer; border-radius: 16px !important; transition: all 0.2s;"
                                onclick="document.getElementById('{{ $inputId }}').click()">
                                <i class="bi bi-camera-fill" style="font-size: 2rem; color: var(--pink-400);"></i>
                                <p class="mt-2 mb-0 fw-semibold small" style="color: var(--pink-600);">Foto {{ $i }}</p>
                                <small class="text-muted">Tap untuk ambil/pilih</small>
                            </div>

                            <input type="file" id="{{ $inputId }}" name="{{ $i === 1 ? 'photo' : 'photo_' . $i }}" accept="image/*" capture="environment" class="d-none">

                            <div id="photoPreviewContainer{{ $i }}" class="d-none mt-2 text-center">
                                <img id="photoPreview{{ $i }}" src="" alt="Preview {{ $i }}"
                                     class="img-fluid rounded-3 shadow-sm"
                                     style="max-height: 200px; object-fit: contain; border: 2px solid var(--pink-200);">
                                <div class="mt-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-photo="{{ $i }}" style="border-radius: 8px;">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                    <div class="mt-2">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Foto bersifat opsional. Bisa diisi 0–3 foto.</small>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small text-uppercase">
                        <i class="bi bi-pencil-square me-1"></i>Keterangan
                    </label>
                    <textarea id="desc_part_ng" name="Desc_Part_Ng" class="form-control" rows="4" placeholder="Ketik manual keterangan kerusakan..." style="border-radius: 12px;"></textarea>
                </div>

                {{-- Jika area TIDAK diketahui (admin/leader tanpa area), tampilkan select manual --}}
                @if(!$area)
                <div class="mb-4" id="divisiWrapper">
                    <label class="form-label fw-bold text-muted small text-uppercase">
                        <i class="bi bi-building me-1"></i>Divisi
                    </label>
                    <select id="divisi" name="Divisi" class="form-select form-select-lg" style="border-radius: 12px; font-size: 1rem;">
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

                <div class="mb-4" id="prosesWrapper">
                    <label class="form-label fw-bold text-muted small text-uppercase">
                        <i class="bi bi-gear me-1"></i>Proses
                    </label>
                    <select id="proses" name="proses" class="form-select form-select-lg" style="border-radius: 12px; font-size: 1rem;">
                        <option value="">Pilih Proses...</option>
                        @foreach($areas->pluck('Proses')->unique() as $proses)
                        <option value="{{ $proses }}">{{ $proses }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                {{-- Area sudah diketahui dari user login, kirim sebagai hidden --}}
                <input type="hidden" name="Divisi" value="{{ $area->Divisi }}">
                <input type="hidden" name="proses" value="{{ $area->Proses }}">
                @endif

                {{-- Info area jika sudah auto-detect --}}
                @if($area)
                <div class="alert bg-light border-0 rounded-4 py-2 px-3 mb-4" style="border-left: 4px solid var(--pink-500) !important;">
                    <small class="fw-bold text-muted">
                        <i class="bi bi-geo-alt-fill me-1" style="color:var(--pink-500)"></i>
                        Area: <span style="color:var(--pink-700)">{{ $area->Name_Area }}</span> —
                        Divisi: <span style="color:var(--pink-700)">{{ $area->Divisi }}</span> —
                        Proses: <span style="color:var(--pink-700)">{{ $area->Proses }}</span>
                    </small>
                </div>
                @endif

                <!-- Kategori -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small text-uppercase">
                        <i class="bi bi-tags me-1"></i>Kategori
                    </label>
                    <select id="category_part_ng" name="Category_Part_Ng" class="form-select form-select-lg" style="border-radius: 12px; font-size: 1rem;">
                        <option value="">Pilih Kategori...</option>
                        <option value="bukan tanggung jawab">Bukan Tanggung Jawab</option>
                        <option value="part scrap">Part Scrap</option>
                    </select>
                    <small id="categoryHint" class="text-muted small mt-1 d-none"></small>
                </div>

                <!-- Jumlah Pcs -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small text-uppercase">
                        <i class="bi bi-hash me-1"></i>Jumlah Pcs
                    </label>
                    <input type="number" id="total_part_ng" name="Total_Part_Ng" class="form-control form-control-lg" style="border-radius: 12px; font-size: 1rem;" min="1" value="1" required>
                </div>

                <div class="d-flex gap-2 mt-4">
                    {{-- BUG FIX: tombol kembali yang sebelumnya ada di JS tapi tidak ada di HTML --}}
                    <button type="button" class="btn btn-outline-secondary w-25 p-3 fw-bold" id="btnBack1" style="border-radius: 12px;">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </button>
                    <button type="button" class="btn btn-success w-75 p-3 fw-bold shadow-sm" id="btnSubmit" style="border-radius: 12px;">
                        <i class="bi bi-save me-2"></i>Simpan Laporan
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>window.csrfToken = '{{ csrf_token() }}';</script>
<script>
    var csrfToken = window.csrfToken;

    {{-- Inject PHP variable ke JS secara aman --}}
    var areaData = {
        hasArea: {{ $area ? 'true' : 'false' }},
        divisi:  "{{ $area->Divisi ?? '' }}",
        proses:  "{{ $area->Proses ?? '' }}",
        name:    "{{ $area->Name_Area ?? '' }}"
    };

    document.addEventListener('DOMContentLoaded', function() {

        // ===================== NOTIFIKASI =====================
        function showNotification(msg, type) {
            let iconType = type === 'danger' ? 'error' : type;
            let titleText = 'Informasi';
            if (iconType === 'error')   titleText = 'Oops...';
            if (iconType === 'warning') titleText = 'Perhatian';
            if (iconType === 'success') titleText = 'Berhasil!';

            Swal.fire({
                icon: iconType,
                title: titleText,
                text: msg,
                confirmButtonColor: 'var(--pink-600)',
                confirmButtonText: 'Tutup'
            });
        }

        // ===================== NAVIGASI STEP =====================
        const goTo = (from, to) => {
            document.getElementById(from).classList.add('d-none');
            document.getElementById(to).classList.remove('d-none');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // ===================== QR SCANNER =====================
        let scannerRack = null;

        function initScannerRack() {
            if (!scannerRack) {
                scannerRack = new Html5QrcodeScanner(
                    "reader-rack",
                    { fps: 5, qrbox: { width: 250, height: 250 }, rememberLastUsedCamera: true },
                    false
                );
                scannerRack.render((decodedText) => {
                    document.getElementById('code_rack').value = decodedText;
                    verifyRack(decodedText);
                }, () => {});
            }
        }

        function stopScanner(scannerInstance) {
            if (scannerInstance) {
                scannerInstance.clear().catch(e => console.log(e));
            }
        }

        initScannerRack();

        // ===================== VERIFY RACK =====================
        function verifyRack(rack) {
            fetch("{{ route('area.verify.rack') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ qr_data: rack, _token: csrfToken })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('Id_Rack').value         = data.id_rack;
                    document.getElementById('code_rack').value       = data.code_rack;
                    document.getElementById('code_item_rack').value  = data.code_item_rack;
                    document.getElementById('name_item_rack').value  = data.name_item_rack;
                    document.getElementById('harga_display').value   = (data.harga && data.harga > 0)
                        ? '$ ' + Number(data.harga).toFixed(2)
                        : 'Harga tidak ditemukan';
                    if (!(data.harga && data.harga > 0)) {
                        showNotification('Kode part tidak ditemukan di pricelist — harga akan tersimpan $0. Pastikan kode part sudah benar.', 'warning');
                    }
                    document.getElementById('btnNext1').disabled     = false;
                    stopScanner(scannerRack);
                } else {
                    showNotification(data.message || 'Rak tidak ditemukan, silakan coba lagi.', 'danger');
                    document.getElementById('Id_Rack').value         = '';
                    document.getElementById('code_item_rack').value  = '';
                    document.getElementById('name_item_rack').value  = '';
                    document.getElementById('harga_display').value   = '';
                    document.getElementById('btnNext1').disabled     = true;
                }
            })
            .catch(() => {
                showNotification('Terjadi kesalahan koneksi. Periksa jaringan Anda.', 'danger');
            });
        }

        document.getElementById('btnVerifyRackManual').addEventListener('click', () => {
            const rack = document.getElementById('code_rack').value.trim();
            if (!rack) return showNotification('No Rak masih kosong. Silakan scan atau ketik No Rak terlebih dahulu.', 'danger');
            verifyRack(rack);
        });

        document.getElementById('code_rack').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const rack = document.getElementById('code_rack').value.trim();
                if (!rack) return showNotification('No Rak masih kosong. Silakan scan atau ketik No Rak terlebih dahulu.', 'danger');
                verifyRack(rack);
            }
        });

        // Step navigasi
        document.getElementById('btnNext1').addEventListener('click', () => {
            stopScanner(scannerRack);
            goTo('step1', 'step2');
        });

        // BUG FIX: Sekarang tombol btnBack1 ada di HTML, listener ini bisa berjalan
        document.getElementById('btnBack1').addEventListener('click', () => {
            goTo('step2', 'step1');
            scannerRack = null; // reset supaya bisa init ulang
            initScannerRack();
        });

        // ===================== KATEGORI HINT =====================
        function updateCategoryHint() {
            const cat     = document.getElementById('category_part_ng').value;
            const hint    = document.getElementById('categoryHint');
            const divEl   = document.getElementById('divisi');
            // Pakai areaData.divisi jika area sudah di-inject, atau ambil dari select manual
            const divVal  = areaData.hasArea ? areaData.divisi : (divEl ? divEl.value : '');

            if (cat === 'bukan tanggung jawab' && divVal) {
                hint.textContent = '→ Akan tersimpan sebagai: "bukan tanggung jawab ' + divVal + '"';
                hint.classList.remove('d-none');
            } else {
                hint.classList.add('d-none');
            }
        }

        const divEl = document.getElementById('divisi');
        if (divEl) {
            divEl.addEventListener('change', updateCategoryHint);
        }
        document.getElementById('category_part_ng').addEventListener('change', updateCategoryHint);

        // ===================== FOTO LOGIC (3 slots) =====================
        for (let i = 1; i <= 3; i++) {
            const input       = document.getElementById('photoInput' + i);
            const preview     = document.getElementById('photoPreview' + i);
            const previewBox  = document.getElementById('photoPreviewContainer' + i);
            const uploadArea  = document.getElementById('photoUploadArea' + i);

            // BUG FIX: Tambahkan pengecekan null sebelum attach listener
            if (!input || !preview || !previewBox || !uploadArea) continue;

            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];

                    // Cek ukuran file maks 5MB
                    if (file.size > 5 * 1024 * 1024) {
                        showNotification('Ukuran foto terlalu besar. Maksimal 5MB per foto.', 'warning');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        previewBox.classList.remove('d-none');
                        uploadArea.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });

            const removeBtn = document.querySelector('[data-remove-photo="' + i + '"]');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    input.value = '';
                    preview.src = '';
                    previewBox.classList.add('d-none');
                    uploadArea.classList.remove('d-none');
                });
            }
        }

        // ===================== SUBMIT =====================
        document.getElementById('btnSubmit').addEventListener('click', function() {
            const desc  = document.getElementById('desc_part_ng').value.trim();
            const cat   = document.getElementById('category_part_ng').value;
            const total = document.getElementById('total_part_ng').value;

            // Validasi field wajib
            if (!desc) return showNotification('Keterangan masih kosong. Silakan isi deskripsi kerusakan Part NG.', 'warning');

            // Jika tidak ada area otomatis, validasi select manual
            if (!areaData.hasArea) {
                const prosesEl = document.getElementById('proses');
                const divisiEl = document.getElementById('divisi');
                if (!prosesEl || !prosesEl.value) return showNotification('Proses belum dipilih. Silakan pilih proses terlebih dahulu.', 'warning');
                if (!divisiEl || !divisiEl.value) return showNotification('Divisi belum dipilih. Silakan pilih divisi terlebih dahulu.', 'warning');
            }

            if (!cat)                   return showNotification('Kategori belum dipilih. Silakan pilih kategori Part NG.', 'warning');
            if (!total || total < 1)    return showNotification('Jumlah Pcs minimal 1. Silakan isi jumlah yang valid.', 'warning');

            // Tentukan final category
            let finalCategory = cat;
            if (cat === 'bukan tanggung jawab') {
                const divEl  = document.getElementById('divisi');
                const divVal = areaData.hasArea ? areaData.divisi : (divEl ? divEl.value : '');
                finalCategory = 'bukan tanggung jawab ' + divVal;
            }

            const formData = new FormData(document.getElementById('partNgForm'));
            formData.set('Category_Part_Ng', finalCategory);

            // Pastikan Divisi & proses selalu terkirim (override jika area sudah auto)
            if (areaData.hasArea) {
                formData.set('Divisi', areaData.divisi);
                formData.set('proses', areaData.proses);
            }

            // Disable tombol supaya tidak double submit
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

            fetch("{{ route('area.store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(async res => {
                const text = await res.text();
                try {
                    return { status: res.status, data: JSON.parse(text) };
                } catch (e) {
                    throw new Error('Server returned non-JSON: ' + text.substring(0, 300));
                }
            })
            .then(({ status, data }) => {
                if (status === 422) {
                    const msg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Validasi gagal.');
                    showNotification(msg, 'warning');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-save me-2"></i>Simpan Laporan';
                    return;
                }
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data Part NG berhasil disimpan!',
                        confirmButtonColor: 'var(--pink-600)',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('area.dashboard') }}";
                    });
                } else {
                    showNotification(data.message || 'Gagal menyimpan data. Silakan coba lagi.', 'danger');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-save me-2"></i>Simpan Laporan';
                }
            })
            .catch(err => {
                showNotification('Terjadi kesalahan saat menyimpan: ' + err.message, 'danger');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-save me-2"></i>Simpan Laporan';
            });
        });
    });
</script>
@endpush