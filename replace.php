<?php
$files = glob('resources/views/admin/report*.blade.php');
$dropdown_html = <<<HTML
                    <div class="dropdown" id="divisiFilter">
                        <button class="btn btn-light bg-light border-0 shadow-sm dropdown-toggle w-100 text-start d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border-radius:10px;font-size:.85rem;color:var(--pink-700)">
                            <span><i class="bi bi-check2-square me-1"></i>Pilih Proses</span>
                            <span class="badge rounded-pill ms-2" id="divisiCountBadge" style="display:none;background:var(--pink-600)">0</span>
                        </button>
                        <div class="dropdown-menu p-2 shadow-sm" style="min-width:250px;border-radius:12px;z-index:1050;">
                            <label class="form-check mb-2 ps-4 fw-bold" style="cursor:pointer; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                                <input class="form-check-input" type="checkbox" id="selectAllDivisi" onchange="toggleAllDivisi(this)">
                                <span class="form-check-label small">Pilih Semua</span>
                            </label>

                            @php
                                \$defaultDivisi = ['mainline', 'subassy', 'sub engine', 'inspeksi', 'mower', 'repair', 'painting a', 'painting b', 'DST'];
                                \$reqDivisi = request()->has('divisi') ? (array) request('divisi') : \$defaultDivisi;
                            @endphp

                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="mainline" @checked(in_array('mainline', \$reqDivisi))><span class="form-check-label small">Mainline</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="subassy" @checked(in_array('subassy', \$reqDivisi))><span class="form-check-label small">Sub Assy</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="sub engine" @checked(in_array('sub engine', \$reqDivisi))><span class="form-check-label small">Sub Engine</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="inspeksi" @checked(in_array('inspeksi', \$reqDivisi))><span class="form-check-label small">Inspeksi</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="mower" @checked(in_array('mower', \$reqDivisi))><span class="form-check-label small">Mower</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="repair" @checked(in_array('repair', \$reqDivisi))><span class="form-check-label small">Repair</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="painting a" @checked(in_array('painting a', \$reqDivisi))><span class="form-check-label small">Painting A (Line A)</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="painting b" @checked(in_array('painting b', \$reqDivisi))><span class="form-check-label small">Painting B (Line B)</span></label>
                            <label class="form-check mb-1 ps-4" style="cursor:pointer"><input class="form-check-input divisi-cb" type="checkbox" name="divisi[]" value="DST" @checked(in_array('DST', \$reqDivisi))><span class="form-check-label small">DST</span></label>

                            <hr class="my-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetDivisiFilter()"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Pilihan</button>
                        </div>
                    </div>
HTML;

$script_html = <<<HTML
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
HTML;

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Replace dropdown HTML
    $content = preg_replace('/<div class="dropdown" id="divisiFilter">.*?<\/div>\s*<\/div>/s', $dropdown_html, $content);
    // Replace script block
    $content = preg_replace('/<script>\s*function refreshDivisiCount\(\).*?<\/script>/s', $script_html, $content);
    file_put_contents($file, $content);
}
echo "Done";
