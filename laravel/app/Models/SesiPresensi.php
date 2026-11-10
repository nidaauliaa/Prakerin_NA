<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SesiPresensi extends Model
{
    protected $table = 'sesi_presensi';

    protected $fillable = [
        'user_id', 'tanggal', 'sesi', 'waktu_absen', 'lokasi', 'lat', 'lng', 'jarak_meter',
        'akurasi_gps', 'foto_url', 'device_id', 'status', 'status_persetujuan',
        'disetujui_oleh', 'catatan_approval',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_absen' => 'datetime',
    ];

    /**
     * Tandai presensi yang hanya 1 sesi pada $tanggal sebagai 'menunggu' ACC admin/HRD.
     * Dipakai perintah presensi:tutup-hari dan penutupan otomatis. Aman dijalankan berulang.
     */
    public static function tutupHari(Carbon $tanggal): int
    {
        $idSatuSesi = static::whereDate('tanggal', $tanggal->toDateString())
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) = 1')
            ->pluck('user_id');

        return static::whereDate('tanggal', $tanggal->toDateString())
            ->whereIn('user_id', $idSatuSesi)
            ->where('status_persetujuan', 'auto')
            ->update(['status_persetujuan' => 'menunggu']);
    }

    /**
     * Penutupan hari otomatis untuk hosting tanpa cron/CLI (QC-098): hari ini (setelah sesi sore berakhir)
     * dan beberapa hari kerja ke belakang. Dibatasi sekali per 10 menit; gagal pun tidak mengganggu halaman.
     */
    public static function tutupHariOtomatis(int $hariKebelakang = 7): void
    {
        $sekarang = now();

        try {
            if (! Cache::add('tutup-hari-otomatis:'.$sekarang->format('YmdH').intdiv($sekarang->minute, 10), 1, 600)) {
                return;
            }

            $jamTutup = (int) config('prakerin.jendela.sore.selesai', 18);

            for ($i = 0; $i <= $hariKebelakang; $i++) {
                $t = $sekarang->copy()->startOfDay()->subDays($i);
                if ($i === 0 && $sekarang->hour < $jamTutup) {
                    continue; // hari ini belum ditutup
                }
                if (! \App\Support\KalenderKerja::hariKerja($t)) {
                    continue;
                }
                static::tutupHari($t);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laporanHarian()
    {
        return $this->hasOne(LaporanHarian::class);
    }

    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
