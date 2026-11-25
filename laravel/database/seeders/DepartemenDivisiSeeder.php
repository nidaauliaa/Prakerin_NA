<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\Divisi;
use Illuminate\Database\Seeder;

/**
 * Departemen + Divisi/Section (sumber: Dept_and_Section_List.xlsx).
 * Lowongan dibuka per DIVISI/SECTION, bukan per departemen.
 *
 * Aman dijalankan berulang kali (tidak menggandakan data):
 *   php artisan db:seed --class=DepartemenDivisiSeeder
 *
 *  - Departemen yang sudah ada (nama sama, huruf besar/kecil diabaikan) dipakai ulang, jadi peserta/akun
 *    lama yang menunjuk ke departemen itu tidak terganggu. Kuota departemen tidak disentuh.
 *  - Kolom "jurusan" = kualifikasi dari Excel (dipakai mengisi "Jurusan yang diterima" saat membuat lowongan).
 */
class DepartemenDivisiSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->data() as $namaDepartemen => $sections) {
            $departemen = Departemen::whereRaw('LOWER(nama) = ?', [mb_strtolower($namaDepartemen)])->first()
                ?? Departemen::create(['nama' => $namaDepartemen]);

            foreach ($sections as [$namaSection, $jurusan]) {
                Divisi::updateOrCreate(
                    ['departemen_id' => $departemen->id, 'nama' => $namaSection],
                    ['jurusan' => $jurusan]
                );
            }
        }
    }

    /** @return array<string, array<int, array{0:string,1:string}>>  departemen => [[section, jurusan], ...] */
    private function data(): array
    {
        return [
            'HC & GA' => [
                ['HC Services', 'Mahasiswa Psikologi/Manajemen/Administrasi Bisnis/Ilmu Perkantoran Min Sem 5'],
                ['HC R & D', 'Mahasiswa Psikologi/Manajemen SDM/Min Sem 5'],
                ['SHE', 'Mahasiswa SHE/KESMAS Min Sem 5'],
                ['Administrasi GA', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
                ['Bangunan', 'Teknik sipil/Arsitektur/Bangunan'],
            ],
            'SALES' => [
                ['Sales Administration', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
            ],
            'ACCOUNTING' => [
                ['Accounting Karoseri', 'Akuntansi'],
            ],
            'MARKETING' => [
                ['Marketing Promosi', 'Mahasiswa Jur Ilmu Komunikasi/manajemen/DKV/Administrasi perkantoran/Ekonomi/Marketing Min Sem 5'],
            ],
            'PURCHASING KAROSERI' => [
                ['Purchasing', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
            ],
            'IT' => [
                ['IT Hardware', 'TKTJ'],
                ['IT Software', 'Teknologi Informasi/sistem Informasi/Teknik Informasi/Pemrograman Min Sem 5'],
            ],
            'FINANCE' => [
                ['Finance', 'Akuntansi'],
            ],
            'QUALITY KAR' => [
                ['Quality Control', 'Teknik Mesin/TKRO/Elektronika'],
                ['ADMIN QA', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
            ],
            'PRODUKSI BUS' => [
                ['FINISHING BUS', 'Elektronika,Fabrikasi Logam Manufaktur,TBO, Mekatronika,TKRO Min Kelas XI'],
                ['BODY BUS', 'Welding,Fabrikasi Logam Manufaktur,pemesinan,TBO, TKRO Min Kelas XI'],
            ],
            'PRODUKSI BOX' => [
                ['Body Box & HD', 'Welding,Fabrikasi Logam Manufaktur,pemesinan,TBO, TKRO Min Kelas XI'],
                ['CAT & PUTTY BOX & HD', 'TBO/TKRO'],
                ['Finishing Box & HD', 'Elektronika,Fabrikasi Logam Manufaktur,TBO, Mekatronika, Min Kelas XI'],
            ],
            'ENGINEERING KAROSERI' => [
                ['ADMIN Engginering', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
                ['Product Eng', 'TEKNIK MESIN/TEKNOLOGI REKAYASA MANUFAKTUR/OTOMOTIF'],
                ['Product development/RND', 'TEKNIK MESIN/TEKNOLOGI REKAYASA MANUFAKTUR/OTOMOTIF/DESAIN'],
                ['Engineering process', 'TEKNIK MESIN/TEKNOLOGI REKAYASA MANUFAKTUR/OTOMOTIF/Elektronika'],
                ['STICKER', 'DKV/TBO/Otomotif'],
                ['Admin Maintenance', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
                ['Maintenance/PS', 'Teknik Mesin/Otomotif/Teknologi Rekayasa Manufaktur/Mekatronika/Elektronika'],
            ],
            'PPID' => [
                ['Gudang Logistik', 'All Jurusan'],
                ['Gudang Kaca', 'All Jurusan'],
            ],
            'PRODUKSI MINIBUS' => [
                ['FINISHING MINIBUS', 'Elektronika,Fabrikasi Logam Manufaktur,TBO, Mekatronika, Min Kelas XI'],
                ['BODY MINIBUS', 'Welding,Fabrikasi Logam Manufaktur,pemesinan,TBO, TKRO Min Kelas XI'],
            ],
            'COMPONENT KAROSERI' => [
                ['Komponen', 'Pemesinan,Teknik fabrikasi logam manufaktur,mekatronika,TBO,TKRO Min Kelas XI'],
                ['Fiber', 'Welding,TKRO Min Kelas XI'],
                ['Admin', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
                ['Admin Fiber', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
                ['Vaccum/Furniture', 'Furniture/TKRO'],
                ['Interior', 'Welding, TKRO, Fabrikasi Logam & Manufaktur'],
            ],
            'PRODUKSI STAMPING' => [
                ['Produksi', 'Siswa SMK Jur Welding,Pemesinan,Fabrikasi Logam dan Manufaktur, TBO, TKR Min kelas XI'],
            ],
            'TOOLING' => [
                ['Tool Design', 'TEKNIK MESIN/TEKNOLOGI REKAYASA MANUFAKTUR/OTOMOTIF'],
                ['Maintenance Die', 'TEKNIK MESIN/TEKNOLOGI REKAYASA MANUFAKTUR/OTOMOTIF'],
            ],
            'PLANT SERVICE' => [
                ['Plant Service Stamping', 'Siswa SMK Jur Pemesinan,Teknik fabrikasi logam manufaktur,mekatronika,TKRO Min Kelas XI'],
                ['Administrasi PS', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
            ],
            'PPICD STAMPING' => [
                ['PPC STAMPING', 'Siswa SMK Jur Pemesinan,Teknik fabrikasi logam manufaktur,mekatronika,TKRO Min Kelas XI'],
            ],
            'COMMERCIAL' => [
                ['TRAILER', 'TKRO, Welding'],
                ['SALES MRKT TRAILER', 'Administrasi perkantoran/akuntansi Min Kelas XI'],
            ],
            'QUALITY STT' => [
                ['QUALITY', 'Teknik Mesin/Teknik Industri'],
            ],
        ];
    }
}
