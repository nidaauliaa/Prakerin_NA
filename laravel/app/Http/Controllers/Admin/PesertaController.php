<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use App\Http\Controllers\Controller;
use App\Models\DataMagang;
use App\Models\Departemen;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PesertaController extends Controller
{
    public function create()
    {
        return view('admin.peserta.create', [
            'departemen' => Departemen::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $r->merge(['no_ktp' => preg_replace('/\D/', '', (string)$r->no_ktp) ?: null]);

        // Wajib hanya: nama, NIS/NIM, departemen, tanggal mulai & selesai.
        // Sisanya boleh kosong dan dilengkapi nanti lewat Edit Peserta (sama seperti peserta hasil import Excel).
        $data = $r->validate([
            'tipe_magang' => 'required|in:smk,kuliah',
            'nama' => 'required|string|max:150',
            'nis_nim' => 'required|string|max:50|unique:users,nis_nim',
            'email' => 'nullable|email|max:255|unique:users,email',
            'no_wa' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'asal_sekolah' => 'nullable|string|max:150',
            'jurusan' => 'nullable|string|max:150',
            'departemen_id' => 'required|exists:departemen,id',
            'divisi' => 'nullable|string|max:100',
            'tempat_prakerin' => 'nullable|string|max:100',
            'pembimbing' => 'nullable|string|max:150',
            'no_wa_pembimbing' => 'nullable|string|max:20',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'keterangan' => 'nullable|string|max:255',
            'no_ktp' => 'nullable|digits:16',
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'foto_pas' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'cv_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'surat_permohonan_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar.',
            'email' => 'Format :attribute tidak valid.',
            'digits' => ':attribute harus :digits digit.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => 'Format :attribute harus :values.',
            'max' => ':attribute terlalu besar / panjang.',
            'after_or_equal' => ':attribute tidak boleh sebelum tanggal mulai.',
            'before' => ':attribute harus sebelum hari ini.',
            'exists' => ':attribute tidak valid.',
        ], [
            'nama' => 'Nama lengkap', 'nis_nim' => 'NIS/NIM', 'email' => 'Email', 'no_wa' => 'No. WhatsApp',
            'departemen_id' => 'Departemen', 'tgl_mulai' => 'Tanggal mulai', 'tgl_selesai' => 'Tanggal selesai',
            'tipe_magang' => 'Tipe magang', 'no_ktp' => 'No. KTP', 'tanggal_lahir' => 'Tanggal lahir',
            'foto_pas' => 'Foto', 'cv_path' => 'CV', 'surat_permohonan_path' => 'Surat permohonan',
        ]);

        // Samakan format dengan data hasil import: "04/05/2026 - 04/11/2026"
        $data['periode_magang'] = \Carbon\Carbon::parse($data['tgl_mulai'])->format('d/m/Y')
            . ' - ' . \Carbon\Carbon::parse($data['tgl_selesai'])->format('d/m/Y');

        $foto = $r->hasFile('foto_pas') ? $r->file('foto_pas')->store('peserta/foto', 'public') : null;
        $cv = $r->hasFile('cv_path') ? $r->file('cv_path')->store('peserta/cv', 'public') : null;
        $surat = $r->hasFile('surat_permohonan_path') ? $r->file('surat_permohonan_path')->store('peserta/surat', 'public') : null;

        try {
            $user = DB::transaction(function () use ($data, $foto, $cv, $surat) {
                $uuid = (string)Str::uuid();

                $user = User::create([
                    'name' => $data['nama'],
                    // Email tidak wajib: kalau kosong dibiarkan NULL (tidak digenerate).
                    'email' => $data['email'] ?? null,
                    'nis_nim' => $data['nis_nim'],
                    'password' => User::PASSWORD_DEFAULT,   // di-hash oleh cast 'hashed'
                    'wajib_ganti_password' => true,
                    'peran' => 'peserta',
                    'status_akun' => 'aktif',
                    'departemen_id' => $data['departemen_id'],
                    'uuid_kartu' => $uuid,
                    'foto_profil_url' => $foto ? $this->urlPublik($foto) : null,
                    'asal_sekolah' => $data['asal_sekolah'] ?? null,
                    'jurusan' => $data['jurusan'] ?? null,
                ]);

                DataMagang::create([
                    'nama' => $data['nama'],
                    'tipe_magang' => $data['tipe_magang'],
                    'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                    'no_ktp' => $data['no_ktp'] ?? null,
                    'no_wa' => $data['no_wa'] ?? null,
                    'alamat' => $data['alamat'] ?? null,
                    'tempat_lahir' => $data['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                    'nis_nim' => $data['nis_nim'],
                    'asal_sekolah' => $data['asal_sekolah'] ?? null,
                    'jurusan' => $data['jurusan'] ?? null,
                    'departemen_id' => $data['departemen_id'],
                    'divisi' => $data['divisi'] ?? null,
                    'uuid' => $uuid,
                    'user_id' => $user->id,
                    'ditautkan_pada' => now(),
                    'foto_pas' => $foto,
                    'pembimbing' => $data['pembimbing'] ?? null,
                    'no_wa_pembimbing' => $data['no_wa_pembimbing'] ?? null,
                    'cv_path' => $cv,
                    'surat_permohonan_path' => $surat,
                    'periode_magang' => $data['periode_magang'] ?? null,
                    'status_kehadiran_awal' => 'hadir',
                    'tempat_prakerin' => $data['tempat_prakerin'] ?? null,
                    'tgl_mulai' => $data['tgl_mulai'] ?? null,
                    'tgl_selesai' => $data['tgl_selesai'] ?? null,
                    'keterangan' => $data['keterangan'] ?? null,
                    'status_magang' => 'ACTIVE',
                ]);

                return $user;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete(array_filter([$foto, $cv, $surat]));
            throw $e;
        }

        return redirect()->route('admin.aktif.index')
            ->with('success', 'Peserta berhasil ditambahkan.')
            ->with('qr_peserta_id', $user->id);
    }

    /**
     * Foto pas / CV / surat permohonan, disajikan lewat PHP (bukan alamat /storage langsung).
     * Alamat /storage dibangun dari APP_URL; kalau APP_URL beda dengan alamat situs asli, atau situs ada
     * di subfolder, gambarnya rusak dan "Lihat CV/surat" jadi 404 (QC-010, QC-016, QC-019, QC-043).
     * Rutenya ada di grup admin, jadi berkas pribadi hanya bisa dibuka admin.
     */
    public function berkas(User $peserta, string $jenis)
    {
        abort_unless($peserta->peran === 'peserta', 404);

        $kolom = ['foto' => 'foto_pas', 'cv' => 'cv_path', 'surat' => 'surat_permohonan_path'][$jenis] ?? null;
        abort_if($kolom === null, 404);

        $path = DataMagang::where('user_id', $peserta->id)->value($kolom);
        $disk = Storage::disk('public');

        abort_if(blank($path) || ! $disk->exists($path), 404, 'Berkas tidak ditemukan.');

        return response()->file($disk->path($path));
    }

    public function edit(User $peserta)
    {
        abort_unless($peserta->peran === 'peserta', 404);

        // Akun hasil pendaftaran mandiri belum punya baris data_magang: tampilkan form kosong,
        // barisnya dibuat saat disimpan (lihat update()).
        $dm = DataMagang::where('user_id', $peserta->id)->first() ?? new DataMagang();

        return view('admin.peserta.edit', [
            'peserta' => $peserta,
            'dm' => $dm,
            'departemen' => Departemen::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $r, User $peserta)
    {
        abort_unless($peserta->peran === 'peserta', 404);

        $dm = DataMagang::where('user_id', $peserta->id)->first() ?? new DataMagang();

        $r->merge(['no_ktp' => preg_replace('/\D/', '', (string)$r->no_ktp) ?: null]);

        // Hampir semua field nullable: peserta hasil import Excel belum punya NIK, alamat, dll.
        $data = $r->validate([
            'tipe_magang' => 'required|in:smk,kuliah',
            'nama' => 'required|string|max:150',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($peserta->id)],
            'no_ktp' => 'nullable|digits:16',
            'no_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'jenis_kelamin' => 'nullable|in:L,P',
            'nis_nim' => ['required', 'string', 'max:50', Rule::unique('users', 'nis_nim')->ignore($peserta->id)],
            'asal_sekolah' => 'nullable|string|max:150',
            'jurusan' => 'nullable|string|max:150',
            'pembimbing' => 'nullable|string|max:150',
            'no_wa_pembimbing' => 'nullable|string|max:20',
            'departemen_id' => 'required|exists:departemen,id',
            'divisi' => 'nullable|string|max:100',
            'periode_magang' => 'nullable|string|max:100',
            'tempat_prakerin' => 'nullable|string|max:100',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date|after_or_equal:tgl_mulai',
            'keterangan' => 'nullable|string|max:255',
            'foto_pas' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'cv_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'surat_permohonan_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar.',
        ], [
            'nama' => 'Nama lengkap', 'nis_nim' => 'NIS/NIM', 'departemen_id' => 'Departemen', 'tipe_magang' => 'Tipe magang',
        ]);

        // Simpan berkas baru (kalau ada). Berkas lama baru dihapus setelah database berhasil diperbarui.
        $baru = [];
        foreach (['foto_pas' => 'peserta/foto', 'cv_path' => 'peserta/cv', 'surat_permohonan_path' => 'peserta/surat'] as $field => $dir) {
            if ($r->hasFile($field)) {
                $baru[$field] = $r->file($field)->store($dir, 'public');
            }
        }
        $lama = $dm->only(array_keys($baru));

        try {
            DB::transaction(function () use ($peserta, $dm, $data, $baru) {
                $user = [
                    'name' => $data['nama'],
                    'departemen_id' => $data['departemen_id'],
                    'asal_sekolah' => $data['asal_sekolah'] ?? null,
                    'jurusan' => $data['jurusan'] ?? null,
                ];
                $user['nis_nim'] = $data['nis_nim']; // NIS/NIM login ikut berubah (wajib, tidak boleh dikosongkan)
                $user['email'] = $data['email'] ?? null; // email boleh dikosongkan: kolom kosong = email dihapus
                if (isset($baru['foto_pas'])) {
                    $user['foto_profil_url'] = $this->urlPublik($baru['foto_pas']);
                }
                // Akun hasil pendaftaran mandiri: buat baris data_magang-nya sekarang.
                if (!$dm->exists) {
                    $uuid = $peserta->uuid_kartu ?: (string) Str::uuid();
                    $user['uuid_kartu'] = $uuid;
                    $dm->fill([
                        'user_id' => $peserta->id,
                        'uuid' => $uuid,
                        'ditautkan_pada' => now(),
                        'status_kehadiran_awal' => 'hadir',
                        'status_magang' => 'ACTIVE',
                    ]);
                }

                // Masa magang diperpanjang: peserta yang sempat ditandai SELESAI otomatis aktif lagi.
                if ($dm->exists && strtoupper((string) $dm->status_magang) === 'SELESAI'
                    && !empty($data['tgl_selesai']) && $data['tgl_selesai'] >= today()->toDateString()) {
                    $dm->status_magang = 'ACTIVE';
                }

                $peserta->update($user);

                $dm->fill(array_merge(
                    Arr::except($data, ['email', 'foto_pas', 'cv_path', 'surat_permohonan_path']),
                    $baru
                ))->save();
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete(array_values($baru));
            throw $e;
        }

        Storage::disk('public')->delete(array_values(array_filter($lama)));

        return redirect()->route('admin.presensi.index')
            ->with('success', 'Data peserta berhasil diperbarui.');
    }

    /**
     * URL publik untuk berkas di disk 'public'.
     * Storage::disk() bertipe Filesystem (kontrak) yang tidak punya url() di mata editor;
     * implementasi aslinya FilesystemAdapter punya, jadi dideklarasikan lewat @var.
     */
    private function urlPublik(string $path): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return $disk->url($path);
    }

    // Tampilan QR di layar: SVG (dibuat tanpa ekstensi GD/Imagick, jadi aman di hosting gratis seperti InfinityFree).
    public function qrImage(User $peserta)
    {
        abort_unless($peserta->peran === 'peserta', 404);
        return response($this->svgBersih($this->buildQr($peserta, true)), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store',
        ]);
    }

    // Download QR selalu dalam format SVG (vektor, tajam di ukuran berapa pun, tanpa butuh ekstensi GD).
    public function qrDownload(User $peserta)
    {
        abort_unless($peserta->peran === 'peserta', 404);
        $nama = 'QR-'.Str::slug($peserta->name).'-'.$peserta->nis_nim.'.svg';

        return response($this->svgBersih($this->buildQr($peserta, true)), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$nama.'"',
        ]);
    }

    /**
     * Pastikan isi SVG bersih. Hosting bersama kadang menyisipkan karakter liar (spasi, BOM,
     * teks peringatan PHP) sebelum "<?xml", dan itu membuat browser menolak SVG-nya.
     * Buang semua yang ada sebelum "<svg" dan kosongkan output buffer yang tersisa.
     */
    private function svgBersih(string $svg): string
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $awal = strpos($svg, '<svg');

        return $awal === false ? $svg : substr($svg, $awal);
    }

    /**
     * Membuat gambar QR dari uuid_kartu (endroid/qr-code 5.x, method berantai).
     * $svg = true -> SVG (tanpa GD). $svg = false -> PNG (butuh ekstensi GD).
     */
    private function buildQr(User $peserta, bool $svg = false): string
    {
        // Pengaman: kalau uuid_kartu masih kosong, isi dulu.
        if (empty($peserta->uuid_kartu)) {
            $peserta->uuid_kartu = (string) Str::uuid();
            $peserta->save();
        }

        $writer = $svg ? new SvgWriter() : new PngWriter();

        if (method_exists(Builder::class, 'create')) {
            // endroid/qr-code 5.x
            $result = Builder::create()
                ->writer($writer)
                ->writerOptions([])
                ->data($peserta->uuid_kartu)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(300)
                ->margin(10)
                ->build();
        } else {
            // endroid/qr-code 6.x (Builder::create() sudah dihapus, parameter lewat constructor)
            $result = (new Builder(
                writer: $writer,
                writerOptions: [],
                data: $peserta->uuid_kartu,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 300,
                margin: 10,
            ))->build();
        }

        return $result->getString();
    }
}