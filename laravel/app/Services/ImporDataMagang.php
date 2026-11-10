<?php

namespace App\Services;

use App\Models\DataMagang;
use App\Models\Departemen;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as TanggalExcel;

class ImporDataMagang
{
    private const KOLOM = [
        'nomorInduk'=>['NIS/NIM','NIM/NIS','NIM/NISN','NISN/NIM','NIM','NISN','NIS','NOMOR INDUK'],
        'nama'=>['NAMA','NAMA LENGKAP'],
        'asalSekolah'=>['UNIV/SEKOLAH','SEKOLAH/UNIV','ASAL SEKOLAH','SEKOLAH','UNIVERSITAS'],
        'jurusan'=>['PRODI','JURUSAN','PROGRAM STUDI'],
        'departemen'=>['DEPARTEMEN','DIVISI'],
        'divisi'=>['DIVISI'],
        'uuid'=>['UUID'],
        'jenisKelamin'=>['L/P','JENIS KELAMIN','JK'],
        'tglMulai'=>['TGL_MULAI','TANGGAL MULAI','TGL MULAI'],
        'tglSelesai'=>['TGL_SELESAI','TANGGAL SELESAI','TGL SELESAI'],
        'keterangan'=>['KET','KETERANGAN'],
        'tempatPrakerin'=>['TEMPAT PRAKERIN','TEMPAT_PRAKERIN'],
        'statusMagang'=>['STATUS','STATUS MAGANG'],
        // Jenjang SMK / Kuliah (dulu salah ketik "TIPE_MAAGNG" dan tidak ada di template: QC-034).
        'tipeMagang'=>['JENJANG','TIPE MAGANG','TIPE_MAGANG','TIPE','JENJANG (SMK/KULIAH)'],
    ];

    private const LABEL = [
        'nama'=>'NAMA','asal_sekolah'=>'UNIV/SEKOLAH','jurusan'=>'PRODI','departemen_id'=>'DEPARTEMEN',
        'divisi'=>'DIVISI','jenis_kelamin'=>'L/P','tgl_mulai'=>'TGL_MULAI','tgl_selesai'=>'TGL_SELESAI',
        'keterangan'=>'KET','tempat_prakerin'=>'TEMPAT PRAKERIN','status_magang'=>'STATUS','tipe_magang'=>'JENJANG',
    ];

    public function jalankan(string $pathFile,bool $hanyaUji=false): array
    {
        // formatData=false: nilai mentah (tanggal Excel tetap angka seri, NIS/NIM angka tidak jadi 1.2E+9)
        $baris=IOFactory::load($pathFile)->getSheet(0)->toArray(null,true,false,false);
        if(count($baris)<2) throw new \InvalidArgumentException('File kosong atau hanya berisi judul kolom. Tidak ada data yang diimpor.');

        $peta=$this->petakanKolom($baris[0]);
        $hasil=['hanyaUji'=>$hanyaUji,'baru'=>0,'diperbarui'=>0,'tetap'=>0,'uuidBaru'=>0,'akunBaru'=>0,
                'departemenBaru'=>[],'peringatan'=>[],'galat'=>[],'rincian'=>[]];
        $duplikat=[]; $cache=[];

        DB::beginTransaction();
        try {
            for($i=1;$i<count($baris);$i++){
                $no=$i+1;
                $mentah=fn($k)=>isset($peta[$k]) ? ($baris[$i][$peta[$k]] ?? null) : null;
                $ambil=fn($k)=>$this->teks($mentah($k));
                $nim=$ambil('nomorInduk'); $nama=$ambil('nama');
                $asal=$ambil('asalSekolah'); $jurusan=$ambil('jurusan');
                $dep=$ambil('departemen'); $divisi=$ambil('divisi'); $uuid=strtolower($ambil('uuid'));
                if(($nim.$nama.$asal.$jurusan.$dep.$divisi.$uuid)==='') continue;
                if($nim===''||$nama===''){ $hasil['galat'][]="Baris {$no}: NIS/NIM dan NAMA wajib diisi. Baris dilewati."; continue; }
                $key=strtolower($nim);
                if(isset($duplikat[$key])){ $hasil['galat'][]="Baris {$no}: NIS/NIM {$nim} dobel di file (sudah dipakai baris {$duplikat[$key]}). Baris dilewati."; continue; }
                $duplikat[$key]=$no;
                if($uuid!==''&&!Str::isUuid($uuid)){ $hasil['galat'][]="Baris {$no}: UUID tidak valid. Baris dilewati."; continue; }

                $jk=strtoupper($ambil('jenisKelamin'));
                $jk=in_array($jk,['L','P'],true)?$jk:null;

                $start=$this->tanggal($ambil('tglMulai'),$mentah('tglMulai'));
                $end=$this->tanggal($ambil('tglSelesai'),$mentah('tglSelesai'));
                if($ambil('tglMulai')!=='' && $start===null) $hasil['peringatan'][]="Baris {$no} ({$nim}): format TGL_MULAI tidak dikenali, kolom ini diabaikan.";
                if($ambil('tglSelesai')!=='' && $end===null) $hasil['peringatan'][]="Baris {$no} ({$nim}): format TGL_SELESAI tidak dikenali, kolom ini diabaikan.";
                if($start && $end && $end<$start){ $hasil['galat'][]="Baris {$no} ({$nim}): TGL_SELESAI lebih awal dari TGL_MULAI. Baris dilewati."; continue; }

                $tipeMentah=strtolower($ambil('tipeMagang'));
                $tipe=$this->tipe($tipeMentah);
                if($tipeMentah!=='' && $tipe===null) $hasil['peringatan'][]="Baris {$no} ({$nim}): JENJANG \"{$tipeMentah}\" tidak dikenali (isi SMK atau KULIAH), kolom ini diabaikan.";

                $depId=null;
                if($dep!==''){
                    $dk=strtolower($dep);
                    if(!isset($cache[$dk])){
                        $d=Departemen::firstOrCreate(['nama'=>$dep]);
                        if($d->wasRecentlyCreated)$hasil['departemenBaru'][]=$d->nama;
                        $cache[$dk]=$d->id;
                    }
                    $depId=$cache[$dk];
                }

                // Hanya kolom yang ADA di file dan terisi yang dipakai. Sel kosong / kolom yang tidak ada
                // TIDAK menimpa data yang sudah tersimpan dengan kosong (QC-035, QC-116).
                $kandidat=[];
                foreach([
                    'nama'=>$nama,'asal_sekolah'=>$asal,'jurusan'=>$jurusan,'departemen_id'=>$depId,'divisi'=>$divisi,
                    'jenis_kelamin'=>$jk,'tgl_mulai'=>$start,'tgl_selesai'=>$end,'keterangan'=>$ambil('keterangan'),
                    'tempat_prakerin'=>$ambil('tempatPrakerin'),'status_magang'=>strtoupper($ambil('statusMagang')),
                    'tipe_magang'=>$tipe,
                ] as $kolom=>$nilai){
                    if($nilai!==null && $nilai!=='') $kandidat[$kolom]=$nilai;
                }

                try {
                    DB::transaction(function() use(&$hasil,$nim,$nama,$asal,$uuid,$kandidat,$no){
                        $rekam=DataMagang::where('nis_nim',$nim)->first();

                        if(!$rekam){
                            $uuidPakai=$uuid!==''?$uuid:(string)Str::uuid();
                            if(DataMagang::where('uuid',$uuidPakai)->exists()){
                                $hasil['galat'][]="Baris {$no}: UUID sudah dipakai peserta lain. Baris dilewati."; return;
                            }
                            $rekam=DataMagang::create($kandidat+[
                                'nis_nim'=>$nim,'uuid'=>$uuidPakai,'nama'=>$nama,
                                'tipe_magang'=>$this->tebakTipe($asal),
                                'status_magang'=>'ACTIVE',
                                'status_kehadiran_awal'=>'hadir',
                            ]);
                            $hasil['baru']++; if($uuid==='')$hasil['uuidBaru']++;
                        } else {
                            if($uuid!=='' && strtolower((string)$rekam->uuid)!==$uuid)
                                $hasil['peringatan'][]="Baris {$no} ({$nim}): UUID Excel berbeda, UUID lama dipertahankan.";

                            // Bandingkan nilai (bukan isDirty): tanggal dan angka dibandingkan sebagai teks yang sama,
                            // sehingga import ulang file yang sama dihitung "tetap", bukan "diperbarui" (QC-115).
                            $berubah=[];
                            foreach($kandidat as $kolom=>$baru){
                                $lama=$rekam->getAttribute($kolom);
                                $lamaTeks=$lama instanceof \DateTimeInterface ? $lama->format('Y-m-d') : (string)($lama ?? '');
                                if($lamaTeks!==(string)$baru) $berubah[$kolom]=$baru;
                            }
                            if($berubah){
                                $rekam->fill($berubah)->save();
                                $this->samakanAkun($rekam);
                                $hasil['diperbarui']++;
                                $hasil['rincian'][]="Baris {$no} ({$nim}): data yang sudah ada DIPERBARUI, kolom: "
                                    .implode(', ',array_map(fn($k)=>self::LABEL[$k] ?? $k,array_keys($berubah))).'.';
                            } else {
                                $hasil['tetap']++;
                            }
                        }

                        // Peserta baru (atau data lama yang belum punya akun) langsung dibuatkan akun login (QC-034, QC-114).
                        try {
                            if($this->pastikanAkun($rekam)==='dibuat') $hasil['akunBaru']++;
                        } catch(\DomainException $e){
                            $hasil['peringatan'][]="Baris {$no} ({$nim}): data masuk tetapi akun login belum dibuat. {$e->getMessage()}";
                        }
                    });
                } catch(\Throwable $e){ $hasil['galat'][]="Baris {$no} ({$nim}): {$e->getMessage()}"; }
            }
            $hanyaUji?DB::rollBack():DB::commit();
        } catch(\Throwable $e){DB::rollBack();throw $e;}

        return $hasil;
    }

    /**
     * Pastikan data magang punya akun login peserta. Mengembalikan 'dibuat', 'ditautkan', atau null (sudah punya).
     * Akun baru: NIS/NIM sebagai username, sandi awal User::PASSWORD_DEFAULT (wajib diganti saat login pertama).
     * @throws \DomainException bila ada konflik (mis. UUID kartu sudah dipakai akun lain).
     */
    public function pastikanAkun(DataMagang $dm): ?string
    {
        if($dm->user_id) return null;

        $nim=trim((string)$dm->nis_nim);
        if($nim==='') throw new \DomainException('NIS/NIM kosong.');

        // Sudah ada akun dengan NIS/NIM ini (mis. dibuat manual atau daftar sendiri): cukup ditautkan.
        $akun=User::where('nis_nim',$nim)->first();
        if($akun){
            app(PenautanPeserta::class)->jalankan($akun,$nim);
            return 'ditautkan';
        }

        if(User::where('uuid_kartu',$dm->uuid)->exists()){
            throw new \DomainException('UUID kartu sudah dipakai akun lain.');
        }

        $akun=User::create([
            'name'=>$dm->nama,
            'email'=>null,
            'nis_nim'=>$nim,
            'password'=>User::PASSWORD_DEFAULT,   // di-hash oleh cast 'hashed'
            'wajib_ganti_password'=>true,
            'peran'=>'peserta',
            'status_akun'=>config('prakerin.status_setelah_tautan','aktif'),
            'departemen_id'=>$dm->departemen_id,
            'uuid_kartu'=>$dm->uuid,
            'asal_sekolah'=>$dm->asal_sekolah,
            'jurusan'=>$dm->jurusan,
        ]);
        $dm->forceFill(['user_id'=>$akun->id,'ditautkan_pada'=>now()])->save();

        return 'dibuat';
    }

    /** Data magang berubah lewat import -> nama/departemen/sekolah/jurusan di akun login ikut sama. */
    private function samakanAkun(DataMagang $dm): void
    {
        if(!$dm->user_id) return;
        User::whereKey($dm->user_id)->update([
            'name'=>$dm->nama,'departemen_id'=>$dm->departemen_id,
            'asal_sekolah'=>$dm->asal_sekolah,'jurusan'=>$dm->jurusan,
        ]);
    }

    /** Sel Excel -> teks bersih. Angka bulat (mis. NISN) ditulis tanpa notasi ilmiah. */
    private function teks($v): string
    {
        if($v===null || is_bool($v)) return '';
        if($v instanceof \DateTimeInterface) return $v->format('Y-m-d');
        if(is_float($v)){
            return (floor($v)==$v && abs($v)<1e15) ? sprintf('%.0f',$v) : rtrim(rtrim(sprintf('%.10F',$v),'0'),'.');
        }
        return Str::squish((string)$v);
    }

    /** Tanggal Excel (angka seri) atau teks d/m/Y, d-m-Y, Y-m-d -> 'Y-m-d'. Tidak dikenali -> null. */
    private function tanggal(string $teks,$mentah): ?string
    {
        if($teks==='') return null;
        try {
            if(is_int($mentah) || is_float($mentah)){
                return TanggalExcel::excelToDateTimeObject($mentah)->format('Y-m-d');
            }
            // Format Indonesia: tanggal/bulan/tahun (strtotime akan membaca bulan/tanggal dan salah)
            if(preg_match('~^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$~',$teks,$m)){
                return checkdate((int)$m[2],(int)$m[1],(int)$m[3]) ? sprintf('%04d-%02d-%02d',$m[3],$m[2],$m[1]) : null;
            }
            $t=strtotime($teks);
            return $t ? date('Y-m-d',$t) : null;
        } catch(\Throwable){ return null; }
    }

    private function tipe(string $v): ?string
    {
        if(in_array($v,['smk','sma','stm','pkl'],true)) return 'smk';
        if(in_array($v,['kuliah','mahasiswa','univ','universitas','s1','d3','d4'],true)) return 'kuliah';
        return null;
    }

    /** Kolom JENJANG tidak ada / kosong: tebak dari nama asal sekolah/kampus. */
    private function tebakTipe(string $asal): string
    {
        return preg_match('/univ|politeknik|poltek|institut|stmik|stikom|akademi|sekolah tinggi/i',$asal) ? 'kuliah' : 'smk';
    }

    private function petakanKolom(array $judul): array
    {
        $peta=[];
        foreach($judul as $i=>$teks){
            $bersih=strtoupper(Str::squish(str_replace("\xEF\xBB\xBF",'',(string)$teks)));
            foreach(self::KOLOM as $k=>$alias){
                if(!isset($peta[$k])&&in_array($bersih,$alias,true))$peta[$k]=$i;
            }
        }
        $hilang=[];
        if(!isset($peta['nomorInduk'])) $hilang[]='NIS/NIM';
        if(!isset($peta['nama'])) $hilang[]='NAMA';
        if($hilang){
            throw new \InvalidArgumentException('Kolom wajib tidak ditemukan di baris judul: '.implode(' dan ',$hilang).'. Pakai template resmi dan jangan ubah judul kolom. Tidak ada data yang diimpor.');
        }
        return $peta;
    }
}
