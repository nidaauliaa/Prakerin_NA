<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Middleware\WajibGantiPassword;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\KartuIdController;
use App\Http\Controllers\Admin\PresensiController;
use App\Http\Controllers\Admin\PesertaController;
use App\Http\Controllers\Admin\PesertaKelolaController;
use App\Http\Controllers\Admin\PresensiExportController;
use App\Http\Controllers\Admin\ZonaController;
use App\Http\Controllers\Admin\PesertaAktifController;
use App\Http\Controllers\Admin\ArsipController;
use App\Http\Controllers\Admin\AkunPesertaController;
use App\Http\Controllers\Admin\TugasAdminController;
use App\Http\Controllers\Admin\KalenderKerjaController;
use App\Http\Controllers\Admin\LokasiCurigaController;
use App\Http\Controllers\Auth\LupaSandiController;
use App\Http\Controllers\Admin\ApiTokenController;

/*
|--------------------------------------------------------------------------
| LANDING
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'tampilLanding'])->name('landing');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'tampilMasuk'])->name('login');
Route::post('/login', [AuthController::class, 'masuk'])->name('login.store')->middleware('throttle:login');

// Lupa kata sandi: peserta mengisi form, permintaannya masuk ke admin (halaman Akun Peserta)
Route::get('/lupa-sandi', [LupaSandiController::class, 'tampil'])->name('lupa-sandi');
Route::post('/lupa-sandi', [LupaSandiController::class, 'kirim'])->name('lupa-sandi.kirim')->middleware('throttle:lupa-sandi');

/*
|--------------------------------------------------------------------------
| PESERTA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'keluar'])->name('logout');

    // Boleh diakses walau belum ganti sandi
    Route::get('/ganti-password', [PasswordController::class, 'tampil'])->name('password.ganti');
    Route::post('/ganti-password', [PasswordController::class, 'simpan'])->name('password.simpan');

    // Semua halaman peserta di bawah ini diblokir sampai sandi awal diganti
    Route::middleware(WajibGantiPassword::class)->group(function () {

        Route::get('/presensi', [AttendanceController::class, 'index'])->name('presensi.index');
        Route::get('/presensi/scan', [AttendanceController::class, 'scan'])->name('presensi.scan');

        Route::post('/presensi/verifikasi', [AttendanceController::class, 'verifikasiUuid'])
            ->name('presensi.verifikasi')
            ->middleware('throttle:presensi-verifikasi');

        Route::post('/presensi/simpan', [AttendanceController::class, 'simpan'])
            ->name('presensi.simpan')
            ->middleware('throttle:presensi-simpan');

        Route::get('/presensi/{sesiPresensi}/foto', [AttendanceController::class, 'foto'])->name('presensi.foto');

        // Foto pas milik peserta yang sedang login (tidak bergantung APP_URL / symlink storage)
        Route::get('/profil/foto', [AttendanceController::class, 'fotoSaya'])->name('profil.foto');

        Route::get('/laporan', [ReportController::class, 'riwayat'])->name('laporan.riwayat');
        Route::get('/laporan/{sesiPresensi}/isi', [ReportController::class, 'form'])->name('laporan.form');
        Route::post('/laporan/{sesiPresensi}/simpan', [ReportController::class, 'store'])->name('laporan.store');

        Route::post('/tugas', [TugasController::class, 'simpan'])
            ->name('tugas.simpan')
            ->middleware('throttle:tugas-simpan');

        Route::get('/tugas/{tugas}/unduh', [TugasController::class, 'unduh'])->name('tugas.unduh');
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware('admin')
    ->group(function () {

        // Logout admin (nama lengkap: admin.logout)
        Route::post('/logout', function (Request $request) {
            foreach (['web', 'admin'] as $guard) {
                if (config("auth.guards.$guard")) {
                    Auth::guard($guard)->logout();
                }
            }
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        })->name('logout');

        Route::get('/kartu-id', [KartuIdController::class, 'cetak'])->name('kartu.cetak');

        // Presensi
        Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
        Route::post('/presensi/status', [PresensiController::class, 'updateStatus'])->name('presensi.status');
        Route::post('/presensi/acc-semua', [PresensiController::class, 'approveSemua'])->name('presensi.approve-all');
        Route::post('/presensi/{presensi}/approve', [PresensiController::class, 'approve'])->name('presensi.approve');
        Route::post('/presensi/{presensi}/reject', [PresensiController::class, 'reject'])->name('presensi.reject');
        Route::get('/presensi-export', [PresensiExportController::class, 'form'])->name('presensi.export.form');
        Route::get('/presensi-export/unduh', [PresensiExportController::class, 'download'])->name('presensi.export');

        //API-Token
        Route::get('/api-token', [ApiTokenController::class, 'index'])->name('api-token.index');
        Route::post('/api-token', [ApiTokenController::class, 'store'])->name('api-token.store');
        Route::post('/api-token/{apiToken}/cabut', [ApiTokenController::class, 'cabut'])->name('api-token.cabut');

        // Peserta aktif & arsip peserta yang sudah tidak aktif
        Route::get('/peserta-aktif', [PesertaAktifController::class, 'index'])->name('aktif.index');
        Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
        Route::get('/arsip/foto/{sesiPresensi}', [ArsipController::class, 'foto'])->name('arsip.foto');
        Route::get('/arsip/tugas/{tugas}/unduh', [ArsipController::class, 'unduhTugas'])->name('arsip.tugas');
        Route::get('/arsip/{dataMagang}', [ArsipController::class, 'show'])->name('arsip.show');

        // Zona absen per peserta
        Route::get('/zona', [ZonaController::class, 'index'])->name('zona.index');
        Route::post('/zona', [ZonaController::class, 'update'])->name('zona.update');
        // Edit zona (peta satelit) -- dibuka lewat tombol "Edit Zona" di halaman Zona Absen
        Route::get('/zona/edit', [ZonaController::class, 'edit'])->name('zona.edit');
        Route::post('/zona/titik', [ZonaController::class, 'simpanTitik'])->name('zona.titik.store');
        Route::put('/zona/titik/{zona}', [ZonaController::class, 'perbaruiTitik'])->name('zona.titik.update');
        Route::delete('/zona/titik/{zona}', [ZonaController::class, 'hapusTitik'])->name('zona.titik.destroy');

        // Akun peserta (reset kata sandi)
        Route::get('/akun', [AkunPesertaController::class, 'index'])->name('akun.index');
        Route::post('/akun/permintaan/{permintaan}/selesai', [AkunPesertaController::class, 'selesaikan'])->name('akun.selesaikan');
        Route::post('/akun/permintaan/{permintaan}/tolak', [AkunPesertaController::class, 'tolak'])->name('akun.tolak');
        Route::post('/akun/{peserta}/reset', [AkunPesertaController::class, 'reset'])->name('akun.reset');

        // Kalender Kerja: hari libur + hari kerja pengganti
        Route::get('/kalender-kerja', [KalenderKerjaController::class, 'index'])->name('kalender.index');
        Route::post('/kalender-kerja', [KalenderKerjaController::class, 'store'])->name('kalender.store');
        Route::put('/kalender-kerja/{libur}', [KalenderKerjaController::class, 'update'])->name('kalender.update');
        Route::delete('/kalender-kerja/{libur}', [KalenderKerjaController::class, 'destroy'])->name('kalender.destroy');

        // Monitoring tugas peserta
        Route::get('/tugas', [TugasAdminController::class, 'index'])->name('tugas.index');
        Route::get('/tugas/{tugas}/unduh', [TugasAdminController::class, 'unduh'])->name('tugas.unduh');

        // Log lokasi mencurigakan (deteksi fake GPS)
        Route::get('/lokasi-curiga', [LokasiCurigaController::class, 'index'])->name('lokasi-curiga.index');

        // Peserta
        Route::get('/peserta/import', fn () => view('admin.peserta.import'))->name('peserta.import.form');
        Route::get('/peserta/create', [PesertaController::class, 'create'])->name('peserta.create');
        Route::post('/peserta', [PesertaController::class, 'store'])->name('peserta.store');
        Route::get('/peserta/{peserta}/edit', [PesertaController::class, 'edit'])->name('peserta.edit');   
        Route::put('/peserta/{peserta}', [PesertaController::class, 'update'])->name('peserta.update');    
        // Foto pas, CV, dan surat permohonan: hanya admin
        Route::get('/peserta/{peserta}/berkas/{jenis}', [PesertaController::class, 'berkas'])
            ->where('jenis', 'foto|cv|surat')->name('peserta.berkas');
        Route::get('/peserta/{peserta}/qr.svg', [PesertaController::class, 'qrImage'])->name('peserta.qr.image');
        Route::get('/peserta/{peserta}/qr/download', [PesertaController::class, 'qrDownload'])->name('peserta.qr.download');

        Route::post('/peserta/import', [PesertaKelolaController::class, 'import'])->name('peserta.import');
        Route::post('/peserta/buat-akun', [PesertaKelolaController::class, 'buatAkunMassal'])->name('peserta.buat-akun');
        Route::get('/peserta/template', [PesertaKelolaController::class, 'template'])->name('peserta.template');
        Route::delete('/peserta/{peserta}/riwayat', [PesertaKelolaController::class, 'hapusRiwayat'])->name('peserta.riwayat.destroy');
        Route::delete('/peserta/{peserta}', [PesertaKelolaController::class, 'destroy'])->name('peserta.destroy');
        Route::post('/peserta/{peserta}/reset-password', [PesertaKelolaController::class, 'resetPassword'])->name('peserta.reset-password');
    });