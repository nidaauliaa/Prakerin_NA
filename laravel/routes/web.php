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
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\PenilaianPublikController;
use App\Http\Controllers\Admin\FormPenilaianController;
use App\Http\Controllers\Admin\SuratKeteranganController;
use App\Http\Controllers\LamaranController;
use App\Http\Controllers\Pelamar\AuthController as PelamarAuthController;
use App\Http\Middleware\WajibLoginPelamar;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LamaranAdminController;
use App\Http\Controllers\Admin\LowonganAdminController;
use App\Http\Controllers\Admin\SuratBalasanController;
use App\Http\Controllers\Admin\ProfilAdminController;
use App\Http\Controllers\Pelamar\DashboardController as PelamarDashboardController;

/*
|--------------------------------------------------------------------------
| PORTAL PENERIMAAN MAGANG (publik)
|--------------------------------------------------------------------------
*/
Route::get('/', [BerandaController::class, 'index'])->name('beranda');
 
// Langkah 1: tombol "Kirim Lamaran" di modal (hanya periode) -> login / form data
Route::post('/lowongan/{lowongan}/lamar', [LamaranController::class, 'pilih'])
    ->middleware('throttle:10,1')
    ->name('lamaran.pilih');
 
// Form penilaian (publik, tanpa login): dibuka penilai lewat link yang dibagikan admin
Route::get('/penilaian/{kode}', [PenilaianPublikController::class, 'tampil'])->name('penilaian.isi');
Route::post('/penilaian/{kode}', [PenilaianPublikController::class, 'kirim'])->middleware('throttle:10,1')->name('penilaian.kirim');

/*
|--------------------------------------------------------------------------
| LOGIN PRAKERIN (pelamar) - terpisah dari login absensi (/login)
|--------------------------------------------------------------------------
*/
Route::prefix('prakerin')->name('pelamar.')->group(function () {
    Route::get('/login', [PelamarAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PelamarAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/daftar', [PelamarAuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [PelamarAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::post('/logout', [PelamarAuthController::class, 'logout'])->name('logout');
});
 
// Dashboard peserta (wajib login pelamar): profil, riwayat, notifikasi, surat balasan, foto profil
Route::prefix('prakerin')->name('pelamar.')->middleware(WajibLoginPelamar::class)->group(function () {
    Route::get('/dashboard', [PelamarDashboardController::class, 'index'])->name('dashboard');
    Route::get('/berkas/{jenis}', [PelamarDashboardController::class, 'berkas'])->where('jenis', 'foto|cv|surat')->name('berkas');
    Route::get('/foto-profil', [PelamarDashboardController::class, 'foto'])->name('foto');
    Route::post('/foto-profil', [PelamarDashboardController::class, 'ubahFoto'])->middleware('throttle:10,1')->name('foto.update');
    Route::get('/lamaran/{lamaran}/surat-balasan', [PelamarDashboardController::class, 'suratBalasan'])->name('surat');
    Route::get('/surat-keterangan/{surat}', [PelamarDashboardController::class, 'suratKeterangan'])->name('keterangan');
});

// Langkah 2 & 3: lengkapi data + kirim lamaran (wajib login pelamar)
Route::middleware(WajibLoginPelamar::class)->group(function () {
    Route::get('/lamaran/lengkapi', [LamaranController::class, 'form'])->name('lamaran.form');
    Route::post('/lamaran', [LamaranController::class, 'store'])->middleware('throttle:5,1')->name('lamaran.store');
});
 
/*
|--------------------------------------------------------------------------
| LANDING ABSENSI (lama, dipindah dari '/')
|--------------------------------------------------------------------------
*/
Route::get('/absensi', [AuthController::class, 'tampilLanding'])->name('landing');

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

        // Foto profil admin (menu foto di header)
        Route::get('/profil/foto', [ProfilAdminController::class, 'foto'])->name('profil.foto');
        Route::post('/profil/foto', [ProfilAdminController::class, 'ubahFoto'])->name('profil.foto.update');

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

        // Dashboard admin
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Lamaran Masuk
        Route::get('/lamaran', [LamaranAdminController::class, 'index'])->name('lamaran.index');
        Route::get('/lamaran/{lamaran}/berkas/{jenis}', [LamaranAdminController::class, 'berkas'])
            ->where('jenis', 'foto|cv|surat')->name('lamaran.berkas');
        Route::post('/lamaran/{lamaran}/terima', [LamaranAdminController::class, 'terima'])->name('lamaran.terima');
        Route::post('/lamaran/{lamaran}/tolak', [LamaranAdminController::class, 'tolak'])->name('lamaran.tolak');

        // Surat Balasan Ijin Prakerin (dibuat HRD setelah menerima lamaran)
        Route::post('/lamaran/{lamaran}/surat-balasan', [SuratBalasanController::class, 'simpan'])->name('lamaran.surat.simpan');
        Route::get('/lamaran/{lamaran}/surat-balasan/pdf', [SuratBalasanController::class, 'pdf'])->name('lamaran.surat.pdf');
        
        // Penilaian: buat form (link dibagikan ke penilai) + lihat hasil isian
        Route::get('/penilaian', [FormPenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('/penilaian/buat', [FormPenilaianController::class, 'create'])->name('penilaian.create');
        Route::post('/penilaian', [FormPenilaianController::class, 'store'])->name('penilaian.store');
        Route::delete('/penilaian/hasil/{penilaian}', [FormPenilaianController::class, 'hapusHasil'])->name('penilaian.hasil.destroy');
        Route::get('/penilaian/{form}', [FormPenilaianController::class, 'show'])->name('penilaian.show');
        Route::get('/penilaian/{form}/edit', [FormPenilaianController::class, 'edit'])->name('penilaian.edit');
        Route::put('/penilaian/{form}', [FormPenilaianController::class, 'update'])->name('penilaian.update');
        Route::post('/penilaian/{form}/status', [FormPenilaianController::class, 'status'])->name('penilaian.status');
        Route::delete('/penilaian/{form}', [FormPenilaianController::class, 'destroy'])->name('penilaian.destroy');

        // Surat Keterangan selesai prakerin (nilai dari penilaian, diterbitkan HRD)
        Route::get('/surat-keterangan', [SuratKeteranganController::class, 'index'])->name('surat-keterangan.index');
        Route::post('/surat-keterangan/{surat}', [SuratKeteranganController::class, 'simpan'])->name('surat-keterangan.simpan');
        Route::post('/surat-keterangan/{surat}/sinkron', [SuratKeteranganController::class, 'sinkron'])->name('surat-keterangan.sinkron');
        Route::post('/surat-keterangan/{surat}/terbitkan', [SuratKeteranganController::class, 'terbitkan'])->name('surat-keterangan.terbitkan');
        Route::post('/surat-keterangan/{surat}/tarik', [SuratKeteranganController::class, 'tarik'])->name('surat-keterangan.tarik');
        Route::get('/surat-keterangan/{surat}/pdf', [SuratKeteranganController::class, 'pdf'])->name('surat-keterangan.pdf');

        // Kelola Lowongan
        Route::get('/lowongan', [LowonganAdminController::class, 'index'])->name('lowongan.index');
        Route::post('/lowongan', [LowonganAdminController::class, 'store'])->name('lowongan.store');
        Route::put('/lowongan/{lowongan}', [LowonganAdminController::class, 'update'])->name('lowongan.update');
        Route::post('/lowongan/{lowongan}/status', [LowonganAdminController::class, 'status'])->name('lowongan.status');
        Route::delete('/lowongan/{lowongan}', [LowonganAdminController::class, 'destroy'])->name('lowongan.destroy');
    });