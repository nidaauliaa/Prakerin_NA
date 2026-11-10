<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataMagang;
use App\Models\Departemen;
use App\Models\LaporanHarian;
use App\Models\LogPerubahanStatus;
use App\Models\SesiPresensi;
use App\Models\User;
use App\Services\ImporDataMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PesertaKelolaController extends Controller
{
    public function import(Request $r)
    {
        $r->validate(['file'=>'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            $hasil=app(ImporDataMagang::class)->jalankan($r->file('file')->getRealPath());
        } catch(\InvalidArgumentException $e){
            // Kolom wajib NIS/NIM atau NAMA tidak ada, atau file kosong: tolak seluruh file dengan pesan jelas (dulu 500).
            return back()->with('error',$e->getMessage());
        } catch(\Throwable $e){
            report($e);
            return back()->with('error','File tidak bisa dibaca (rusak, atau bukan Excel/CSV yang valid). Tidak ada data yang diimpor.');
        }

        $pesan="Import selesai: {$hasil['baru']} peserta baru, {$hasil['diperbarui']} diperbarui, {$hasil['tetap']} tetap";
        if($hasil['akunBaru']) $pesan.=", {$hasil['akunBaru']} akun login dibuat (sandi awal ".User::PASSWORD_DEFAULT.')';
        if($lewat=count($hasil['galat'])) $pesan.=", {$lewat} baris dilewati";
        $pesan.='.';

        return back()
            ->with('success',$pesan)
            ->with('import_errors',array_merge($hasil['galat'],$hasil['peringatan'],$hasil['rincian']));
    }

    /** Buatkan akun login untuk semua data magang yang belum punya akun ("Belum punya akun" di Peserta Aktif). */
    public function buatAkunMassal()
    {
        $impor=app(ImporDataMagang::class);
        $dibuat=0; $gagal=[];

        DataMagang::whereNull('user_id')->whereNotNull('nis_nim')->where('nis_nim','<>','')->orderBy('nama')
            ->each(function($dm) use($impor,&$dibuat,&$gagal){
                try {
                    DB::transaction(fn()=>$impor->pastikanAkun($dm));
                    $dibuat++;
                } catch(\Throwable $e){
                    $gagal[]="{$dm->nama} ({$dm->nis_nim}): {$e->getMessage()}";
                }
            });

        return back()
            ->with('success',"{$dibuat} akun login dibuat (sandi awal ".User::PASSWORD_DEFAULT.', wajib diganti saat login pertama).'.($gagal?' '.count($gagal).' gagal, lihat catatan.':''))
            ->with('import_errors',$gagal);
    }

    public function template()
    {
        $ss=new Spreadsheet();
        $sheet=$ss->getActiveSheet();
        $headers=['NIS/NIM','NAMA','UNIV/SEKOLAH','PRODI','DEPARTEMEN','DIVISI','UUID','L/P','TGL_MULAI','TGL_SELESAI','KET','TEMPAT PRAKERIN','JENJANG'];
        $sheet->fromArray($headers,null,'A1');
        $sheet->fromArray(['11111','Contoh Peserta','SMK Contoh','Teknik Informatika','IT','Development','', 'L','2026-11-02','2027-02-27','','PT Mekar Armada Jaya','SMK'],null,'A2');

        return response()->streamDownload(function() use($ss){
            (new Xlsx($ss))->save('php://output');
        },'template-import-peserta.xlsx',[
            'Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }

    /** Kembalikan sandi peserta ke sandi awal; peserta wajib menggantinya lagi saat login. */
    public function resetPassword(User $peserta)
    {
        abort_unless($peserta->peran==='peserta',404);

        $peserta->update([
            'password'=>User::PASSWORD_DEFAULT,
            'wajib_ganti_password'=>true,
        ]);

        return back()->with('success',"Sandi {$peserta->name} direset ke sandi awal (".User::PASSWORD_DEFAULT."). Peserta wajib menggantinya saat masuk.");
    }

    /**
     * Hapus RIWAYAT PRESENSI satu peserta saja: sesi presensi (pagi/sore), laporan hariannya,
     * perubahan status manual admin, dan foto selfie. Akun, data magang, QR, zona, dan tugas tetap ada.
     */
    public function hapusRiwayat(User $peserta)
    {
        abort_unless($peserta->peran==='peserta',404);

        $sesi=SesiPresensi::where('user_id',$peserta->id)->get(['id','foto_url']);

        DB::transaction(function() use($peserta,$sesi){
            LaporanHarian::whereIn('sesi_presensi_id',$sesi->pluck('id'))->delete();
            SesiPresensi::where('user_id',$peserta->id)->delete();
            LogPerubahanStatus::where('user_id',$peserta->id)->delete();
        });

        foreach($sesi as $s){
            if($s->foto_url) Storage::disk('local')->delete($s->foto_url);
        }

        return back()->with('success',"Riwayat presensi {$peserta->name} dihapus ({$sesi->count()} presensi). Data peserta tetap ada.");
    }

    public function destroy(User $peserta)
    {
        abort_unless($peserta->peran==='peserta',404);

        $data=$peserta->dataMagang;
        foreach([$data?->foto_pas,$data?->cv_path,$data?->surat_permohonan_path] as $file){
            if($file) Storage::disk('public')->delete($file);
        }

        $nama=$peserta->name;
        $peserta->delete();
        if($data) $data->delete();

        return back()->with('success',"Peserta {$nama} dihapus.");
    }
}