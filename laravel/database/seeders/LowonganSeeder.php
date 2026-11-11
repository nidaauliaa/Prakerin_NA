<?php

namespace Database\Seeders;

use App\Models\Lowongan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LowonganSeeder extends Seeder
{
    /** 12 posisi awal, disalin dari prototype dashboard.html */
    public function run(): void
    {
        $data = [
            // [judul, tipe_magang, kuota, jurusan_diterima, deskripsi]
            ['SHE (K3)', 'kuliah', 2, 'K3 Umum, Teknik Lingkungan', 'Membantu inspeksi K3 di area pabrik karoseri, audit dokumen keselamatan kerja, dan monitoring edukasi SHE harian.'],
            ['HCRS', 'kuliah', 2, 'Psikologi, Manajemen SDM', 'Mendukung operasional Human Capital Recruitment & Selection, pemrosesan berkas pelamar, serta administrasi seleksi.'],
            ['HCD', 'kuliah', 2, 'Psikologi, Manajemen SDM', 'Membantu perencanaan program pelatihan karyawan, evaluasi pengembangan kompetensi, serta Human Capital Development.'],
            ['IT (Web Developer)', 'kuliah', 2, 'Sistem Informasi, Teknik Informatika, Rekayasa Keamanan Siber', 'Pengembangan sistem internal perusahaan, pemeliharaan modul web portal, integrasi database, dan keamanan siber.'],
            ['IFP - Stamping', 'smk', 2, 'SMK Teknik Mesin / Otomotif', 'Praktek operasional mesin cetak komponen plat body kendaraan, pengecekan kualifikasi hasil stamping & maintenance ringan.'],
            ['PPID - Logistik', 'smk', 2, 'SMK Semua Jurusan', 'Pencatatan keluar masuk barang suku cadang, inventarisasi gudang logistik, dan penyusunan laporan operasional PPID.'],
            ['Body Bus', 'smk', 2, 'SMK Pengelasan', 'Pengerjaan perakitan rangka & bodi bus, pengelasan struktur utama, serta penyelarasan kontur bodi kendaraan.'],
            ['Trailer', 'smk', 2, 'SMK Pengelasan', 'Perakitan dan pengelasan sasis trailer heavy-duty. Inspeksi ketahanan sambungan las, serta persiapan finishing.'],
            ['Administrasi', 'smk', 14, 'SMK Administrasi Perkantoran, SMK Akuntansi', 'Pengelolaan dokumen kantor, input data keuangan / akuntansi harian, filing berkas, dan korespondensi departemen.'],
            ['Dapur', 'smk', 2, 'SMK Tataboga', 'Membantu persiapan bahan makanan katering perusahaan, standar kebersihan dapur industri, dan penyajian konsumsi.'],
            ['Staff Legal', 'kuliah', 1, 'S1 Hukum', 'Penyusunan draf perizinan, peninjauan kontrak kerja sama (legal review), serta pengarsipan dokumen hukum perusahaan.'],
            ['Finishing Mini Bus', 'smk', 3, 'SMK Teknik Mesin, Otomotif, Elektro', 'Pemeriksaan akhir kelistrikan & kelengkapan interior mini bus, pemasangan komponen aksesoris, serta final QC.'],
        ];

        foreach ($data as [$judul, $tipe, $kuota, $jurusan, $deskripsi]) {
            Lowongan::updateOrCreate(
                ['slug' => Str::slug($judul)],
                [
                    'judul'            => $judul,
                    'tipe_magang'      => $tipe,
                    'kuota'            => $kuota,
                    'jurusan_diterima' => $jurusan,
                    'deskripsi'        => $deskripsi,
                    'lokasi'           => 'Magelang',
                    'status'           => 'dibuka',
                ]
            );
        }
    }
}