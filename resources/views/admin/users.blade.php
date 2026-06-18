@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--pink-800)">Manajemen User</h4>
            <p class="text-muted mb-0 small">Kelola Akun Akses Admin & Leader</p>
        </div>
        <button class="btn btn-pink shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-2"></i>Tambah User
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius:12px">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" style="border-radius:12px">
        <i class="bi bi-exclamation-circle me-2"></i>Terjadi kesalahan saat menambahkan user.
        <ul class="mb-0 mt-2 small">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card glass-card border-0">
        <div class="card-header card-header-pink d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Daftar User Aktif</h6>
            <span class="badge bg-white rounded-pill px-3" style="color:var(--pink-700)">{{ $users->count() }} User</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-premium mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="20%">Nama Lengkap</th>
                            <th width="15%">Username</th>
                            <th width="12%">Tipe User</th>
                            <th width="12%">Area</th>
                            <th class="text-center" width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $u)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold" style="color:var(--pink-800)">{{ $u->Name_User }}</div>
                            </td>
                            <td>
                                <div class="badge-pink"><i class="bi bi-person me-1"></i>{{ $u->Username_User }}</div>
                            </td>
                            <td>
                                @if($u->Id_Type_User == 1)
                                <span class="badge" style="background:linear-gradient(135deg,#f43f5e,#e11d48);border-radius:8px;padding:.35rem .75rem">
                                    <i class="bi bi-shield-lock me-1"></i>Admin
                                </span>
                                @elseif($u->Id_Type_User == 2)
                                <span class="badge" style="background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:8px;padding:.35rem .75rem">
                                    <i class="bi bi-clipboard2-check me-1"></i>Leader
                                </span>
                                @else
                                <span class="badge" style="background:linear-gradient(135deg,#10b981,#059669);border-radius:8px;padding:.35rem .75rem">
                                    <i class="bi bi-geo-alt me-1"></i>Area
                                </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $userAreas = $u->relationLoaded('areas') ? $u->areas : collect();
                                    $hasRestriction = $userAreas->isNotEmpty() || $u->area;
                                @endphp
                                @if($hasRestriction)
                                    @foreach($userAreas as $ua)
                                    <span class="badge bg-secondary rounded-pill px-3 py-2 mb-1" style="font-size:.75rem">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $ua->Name_Area }}
                                    </span>
                                    @endforeach
                                    @if($u->area && $userAreas->where('Id_Area', $u->area->Id_Area)->isEmpty())
                                    <span class="badge bg-secondary rounded-pill px-3 py-2 mb-1" style="font-size:.75rem">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $u->area->Name_Area }}
                                    </span>
                                    @endif
                                @else
                                <span class="badge" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);border-radius:8px;padding:.35rem .75rem;font-size:.75rem">
                                    <i class="bi bi-globe me-1"></i>All Area
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary" style="border-radius:8px" onclick="openEditModal({{ $u->Id_User }})" title="Edit User">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger ms-1" style="border-radius:8px" onclick="confirmDelete({{ $u->Id_User }}, '{{ $u->Name_User }}')" title="Hapus User">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada data user.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 glass-card">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:var(--pink-800)"><i class="bi bi-person-plus me-2"></i>Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Nama Lengkap</label>
                        <input type="text" name="Name_User" class="form-control" style="border-radius:12px" required placeholder="Masukkan nama user">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Username</label>
                        <input type="text" name="Username_User" class="form-control" style="border-radius:12px" required placeholder="Masukkan username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Password</label>
                        <input type="text" name="Password_User" class="form-control" style="border-radius:12px" required placeholder="Masukkan password teks">
                        <div class="form-text small">Catatan: Password disimpan dalam bentuk teks biasa.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Tipe User</label>
                        <select name="Id_Type_User" class="form-select" style="border-radius:12px" required>
                            @foreach($typeUsers as $type)
                            <option value="{{ $type->Id_Type_User }}">{{ $type->Name_Type_User }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Area</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="addAllArea" checked onchange="toggleAddAreas(this.checked)">
                            <label class="form-check-label fw-bold" for="addAllArea">All Area</label>
                            <div class="form-text small mt-0">Centang jika user bisa mengakses semua area.</div>
                        </div>
                        <div id="addAreaList" class="row g-2 d-none">
                            @foreach($areas as $area)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input add-area-cb" type="checkbox" name="Id_Areas[]" value="{{ $area->Id_Area }}" id="areaAdd{{ $area->Id_Area }}">
                                    <label class="form-check-label small" for="areaAdd{{ $area->Id_Area }}">{{ $area->Name_Area }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="Id_Area" value="">
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-pink">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 glass-card">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:var(--pink-800)"><i class="bi bi-pencil-square me-2"></i>Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Nama Lengkap</label>
                        <input type="text" name="Name_User" id="editName" class="form-control" style="border-radius:12px" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Username</label>
                        <input type="text" name="Username_User" id="editUsername" class="form-control" style="border-radius:12px" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Password <small class="text-muted fw-normal">(kosongkan jika tidak diubah)</small></label>
                        <input type="text" name="Password_User" id="editPassword" class="form-control" style="border-radius:12px" placeholder="Biarkan kosong jika tidak diubah">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Tipe User</label>
                        <select name="Id_Type_User" id="editType" class="form-select" style="border-radius:12px" required>
                            @foreach($typeUsers as $type)
                            <option value="{{ $type->Id_Type_User }}">{{ $type->Name_Type_User }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Area</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="editAllArea" onchange="toggleEditAreas(this.checked)">
                            <label class="form-check-label fw-bold" for="editAllArea">All Area</label>
                            <div class="form-text small mt-0">Centang jika user bisa mengakses semua area.</div>
                        </div>
                        <div id="editAreaList" class="row g-2">
                            @foreach($areas as $area)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input edit-area-cb" type="checkbox" name="Id_Areas[]" value="{{ $area->Id_Area }}" id="areaEdit{{ $area->Id_Area }}">
                                    <label class="form-check-label small" for="areaEdit{{ $area->Id_Area }}">{{ $area->Name_Area }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="Id_Area" value="">
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-pink">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $usersJson = $users->map(fn($u) => [
        'id' => $u->Id_User,
        'name' => $u->Name_User,
        'username' => $u->Username_User,
        'id_type' => $u->Id_Type_User,
        'id_area' => $u->Id_Area,
        'areas' => $u->relationLoaded('areas') ? $u->areas->pluck('Id_Area')->toArray() : [],
    ])->values();
@endphp
@push('scripts')
<script>
    const usersData = @json($usersJson);

    function toggleAddAreas(allArea) {
        document.getElementById('addAreaList').classList.toggle('d-none', allArea);
    }

    function toggleEditAreas(allArea) {
        document.getElementById('editAreaList').classList.toggle('d-none', allArea);
    }

    function openEditModal(id) {
        const user = usersData.find(u => u.id === id);
        if (!user) return;

        document.getElementById('editName').value = user.name;
        document.getElementById('editUsername').value = user.username;
        document.getElementById('editPassword').value = '';
        document.getElementById('editType').value = user.id_type;

        const hasAreas = user.areas && user.areas.length;
        document.getElementById('editAllArea').checked = !hasAreas;
        toggleEditAreas(!hasAreas);

        document.querySelectorAll('.edit-area-cb').forEach(cb => cb.checked = false);
        if (hasAreas) {
            user.areas.forEach(aid => {
                const cb = document.getElementById('areaEdit' + aid);
                if (cb) cb.checked = true;
            });
        }

        document.getElementById('editUserForm').action = '{{ url("admin/users") }}/' + id;

        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus User?',
            text: 'Yakin ingin menghapus user "' + name + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("admin/users") }}/' + id;
                form.style.display = 'none';
                const t = document.createElement('input');
                t.type = 'hidden'; t.name = '_token'; t.value = '{{ csrf_token() }}';
                form.appendChild(t);
                const m = document.createElement('input');
                m.type = 'hidden'; m.name = '_method'; m.value = 'DELETE';
                form.appendChild(m);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush
@endsection
