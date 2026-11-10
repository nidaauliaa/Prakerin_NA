<?php

use App\Http\Controllers\Api\V1\MonitoringApiController;
use App\Http\Controllers\Api\V1\PesertaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 (untuk divisi IT) - prefix otomatis /api
|--------------------------------------------------------------------------
| Semua endpoint WAJIB token: header "Authorization: Bearer <token>".
| Token dibuat di halaman admin: /admin/api-token
*/
Route::pattern('nomor_induk', '[A-Za-z0-9._-]+');

Route::prefix('v1')->middleware(['throttle:api-v1', 'api.token'])->group(function () {
    Route::get('/ping', [MonitoringApiController::class, 'ping']);
    Route::get('/sekolah', [MonitoringApiController::class, 'sekolah']);
    Route::get('/departemen', [MonitoringApiController::class, 'departemen']);
    Route::get('/presensi', [MonitoringApiController::class, 'presensiHarian']);
    Route::get('/rekap', [MonitoringApiController::class, 'rekapBulanan']);
    Route::get('/hari-libur', [MonitoringApiController::class, 'hariLibur']);

    Route::get('/peserta', [PesertaApiController::class, 'index']);
    Route::get('/peserta/{nomor_induk}', [PesertaApiController::class, 'show']);
    Route::get('/peserta/{nomor_induk}/presensi', [PesertaApiController::class, 'presensi']);
    Route::get('/peserta/{nomor_induk}/laporan', [PesertaApiController::class, 'laporan']);
    Route::get('/peserta/{nomor_induk}/tugas', [PesertaApiController::class, 'tugas']);
});
