<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Area\PartNgController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PricelistController as AdminPricelistController;
use App\Http\Controllers\Admin\CurrencyController as AdminCurrencyController;
use App\Http\Controllers\Admin\PartNgController as AdminPartNgController;
use App\Http\Controllers\Leader\DashboardController as LeaderDashboardController;
use App\Http\Controllers\Area\DashboardController as AreaDashboardController;


/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) return redirect()->route('admin.dashboard');
        if (auth()->user()->isLeader()) return redirect()->route('leader.dashboard');
        if (auth()->user()->isArea()) return redirect()->route('area.dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Area Routes (Type User = 3)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:3'])->prefix('area')->name('area.')->group(function () {
    Route::get('/dashboard', [AreaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [PartNgController::class, 'index'])->name('index');
    Route::get('/create', [PartNgController::class, 'create'])->name('create');
    Route::get('/create-manual', [PartNgController::class, 'createManual'])->name('create.manual');
    Route::post('/verify-rack', [PartNgController::class, 'verifyRack'])->name('verify.rack');
    Route::post('/store', [PartNgController::class, 'store'])->name('store');

    // CRUD Part NG untuk Area (hanya bisa edit/delete jika belum diproses)
    Route::get('/part-ng/{id}/edit', [AreaDashboardController::class, 'edit'])->name('part-ng.edit');
    Route::put('/part-ng/{id}', [AreaDashboardController::class, 'update'])->name('part-ng.update');
    Route::delete('/part-ng/{id}', [AreaDashboardController::class, 'destroy'])->name('part-ng.destroy');
});

/*
|--------------------------------------------------------------------------
| Member Search (Akses untuk beberapa role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/search-members', [PartNgController::class, 'searchMembers'])->name('members.search');
    Route::get('/search-rack-part', [PartNgController::class, 'searchRackPart'])->name('rack.part.search');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Login / Logout)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes (Type User = 1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:1'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/laporan', [AdminDashboardController::class, 'report'])->name('admin.report');
    Route::get('/belum-diproses', [AdminDashboardController::class, 'reportUnprocessed'])->name('admin.report-unprocessed');
    Route::get('/sudah-diproses', [AdminDashboardController::class, 'reportProcessed'])->name('admin.report-processed');
    Route::get('/export', [AdminDashboardController::class, 'exportCsv'])->name('admin.export');
    Route::post('/part-ng/{id}/process', [AdminDashboardController::class, 'process'])->name('admin.part_ng.process');
    Route::get('/part-ng/{id}/edit', [AdminDashboardController::class, 'editPartNg'])->name('admin.part-ng.edit');
    Route::put('/part-ng/{id}', [AdminDashboardController::class, 'updatePartNg'])->name('admin.part-ng.update');
    Route::delete('/part-ng/{id}', [AdminDashboardController::class, 'destroyPartNg'])->name('admin.part-ng.destroy');

    // Input Part NG
    Route::get('/input', [AdminPartNgController::class, 'create'])->name('admin.create');
    Route::post('/verify-rack', [AdminPartNgController::class, 'verifyRack'])->name('admin.verify.rack');
    Route::post('/store', [AdminPartNgController::class, 'store'])->name('admin.store');

    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

    // Ranking
    Route::get('/ranking', [AdminDashboardController::class, 'ranking'])->name('admin.ranking');

    // Pricelist
    Route::get('/pricelist', [AdminPricelistController::class, 'index'])->name('admin.pricelist.index');
    Route::get('/pricelist/import', [AdminPricelistController::class, 'importForm'])->name('admin.pricelist.import');
    Route::post('/pricelist/import', [AdminPricelistController::class, 'importExcel'])->name('admin.pricelist.import.excel');
    Route::put('/pricelist/{id}', [AdminPricelistController::class, 'update'])->name('admin.pricelist.update');

    // Currency
    Route::get('/currency', [AdminCurrencyController::class, 'index'])->name('admin.currency.index');
    Route::post('/currency', [AdminCurrencyController::class, 'store'])->name('admin.currency.store');
    Route::put('/currency/{id}', [AdminCurrencyController::class, 'update'])->name('admin.currency.update');
    Route::delete('/currency/{id}', [AdminCurrencyController::class, 'destroy'])->name('admin.currency.destroy');
});

/*
|--------------------------------------------------------------------------
| Leader Routes (Type User = 2)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:2'])->prefix('leader')->group(function () {
    Route::get('/dashboard', [LeaderDashboardController::class, 'dashboard'])->name('leader.dashboard');
    Route::get('/laporan', [LeaderDashboardController::class, 'report'])->name('leader.report');
    Route::get('/laporan-bulanan', [LeaderDashboardController::class, 'reportMonthly'])->name('leader.report.monthly');
    Route::get('/belum-diproses', [LeaderDashboardController::class, 'reportUnprocessed'])->name('leader.report.unprocessed');
    Route::get('/sudah-diproses', [LeaderDashboardController::class, 'reportProcessed'])->name('leader.report.processed');
    Route::get('/export', [LeaderDashboardController::class, 'exportCsv'])->name('leader.export');
    Route::post('/part-ng/{id}/process', [LeaderDashboardController::class, 'process'])->name('leader.part_ng.process');
    Route::get('/part-ng/{id}/edit', [LeaderDashboardController::class, 'editPartNg'])->name('leader.part-ng.edit');
    Route::put('/part-ng/{id}', [LeaderDashboardController::class, 'updatePartNg'])->name('leader.part-ng.update');
    Route::delete('/part-ng/{id}', [LeaderDashboardController::class, 'destroyPartNg'])->name('leader.part-ng.destroy');

    // Ranking
    Route::get('/ranking', [LeaderDashboardController::class, 'ranking'])->name('leader.ranking');

    // Input Part NG
    Route::get('/input', [PartNgController::class, 'create'])->name('leader.create');
    Route::get('/input-manual', [PartNgController::class, 'createManual'])->name('leader.create.manual');
    Route::post('/verify-rack', [PartNgController::class, 'verifyRack'])->name('leader.verify.rack');
    Route::post('/store', [PartNgController::class, 'store'])->name('leader.store');
});

/*
|--------------------------------------------------------------------------
| TEMPORARY: Fix Part NG Divisi — HAPUS ROUTE INI SETELAH DIJALANKAN DI SERVER
|--------------------------------------------------------------------------
| Akses: /maintenance/fix-divisi?token=ISEKI_FIX_2026
| Setelah fix selesai, hapus blok ini dan upload ulang web.php
*/
Route::get('/maintenance/fix-divisi', function () {
    $secret = 'ISEKI_FIX_2026';
    if (request('token') !== $secret) {
        abort(403, 'Unauthorized');
    }

    $dryRun = request('dry_run') === '1';

    $toFix = \Illuminate\Support\Facades\DB::select("
        SELECT pn.Id_Part_Ng, pn.proses, pn.Divisi AS old_Divisi, a.Divisi AS correct_Divisi
        FROM part_ng pn
        JOIN areas a
            ON pn.proses COLLATE utf8mb4_unicode_ci = a.Proses COLLATE utf8mb4_unicode_ci
        WHERE pn.Divisi COLLATE utf8mb4_unicode_ci != a.Divisi COLLATE utf8mb4_unicode_ci
    ");

    $updated = 0;
    if (!$dryRun && count($toFix) > 0) {
        \Illuminate\Support\Facades\DB::statement("
            UPDATE part_ng pn
            JOIN areas a
                ON pn.proses COLLATE utf8mb4_unicode_ci = a.Proses COLLATE utf8mb4_unicode_ci
            SET pn.Divisi = a.Divisi
            WHERE pn.Divisi COLLATE utf8mb4_unicode_ci != a.Divisi COLLATE utf8mb4_unicode_ci
        ");
        $updated = count($toFix);
    }

    $rows = array_map(fn($r) => "<tr><td>{$r->Id_Part_Ng}</td><td>{$r->proses}</td><td style='color:red'>{$r->old_Divisi}</td><td style='color:green'>{$r->correct_Divisi}</td></tr>", $toFix);
    $tableBody = implode('', $rows);
    $mode = $dryRun ? '<span style="color:orange">DRY RUN — tidak ada yang diubah</span>' : '<span style="color:green">UPDATE DIJALANKAN</span>';
    $jumlah = count($toFix);
    $pesan = $jumlah === 0
        ? '<p style="color:green;font-weight:bold">✅ Semua data sudah benar, tidak ada yang perlu diperbaiki.</p>'
        : "<p>{$jumlah} record ditemukan. Update: {$updated} record diubah.</p>";

    return response("
        <html><head><meta charset='utf-8'><title>Fix Divisi</title></head>
        <body style='font-family:monospace;padding:2rem'>
        <h2>Fix Part NG Divisi</h2>
        <p>Mode: {$mode}</p>
        {$pesan}
        " . ($jumlah > 0 ? "
        <table border='1' cellpadding='6' style='border-collapse:collapse'>
            <thead><tr><th>Id_Part_Ng</th><th>proses</th><th>Divisi Lama</th><th>Divisi Benar</th></tr></thead>
            <tbody>{$tableBody}</tbody>
        </table>
        " . ($dryRun ? "<br><a href='?token={$secret}'>▶ Jalankan Fix Sekarang</a>" : '') : '')
        . "
        <br><br><small style='color:gray'>Setelah fix selesai, hapus route ini dari web.php dan upload ulang.</small>
        </body></html>
    ");
});

