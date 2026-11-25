<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataMagang;
use App\Models\Departemen;
use App\Models\Lamaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LamaranAdminController extends Controller
{
    private const BERKAS = [
        'foto'  => 'foto_pas',
        'cv'    => 'cv_path',
        'surat' => 'surat_permohonan_path',
    ];

    public function index(Request $request)
    {
        $tipe   = in_array($request->tipe, ['smk', 'kuliah'], true) ? $request->tipe : 'semua';
        $status = in_array($request->status, ['semua', 'menunggu', 'diterima', 'ditolak'], true)
            ? $request->status : 'menunggu';

        $query = Lamaran::with(['lowongan' => fn ($q) => $q->withTrashed(), 'divisi', 'dataMagang', 'suratBalasan'])->latest();
        $tipe !== 'semua'   && $query->where('tipe_magang', $tipe);
        $status !== 'semua' && $query->where('status', $status);

        $lamaran = $query->paginate(15)->withQueryString();

        // Data untuk modal. Kalau dibuka dari dashboard (?buka=ID) dan barisnya
        // tidak ada di halaman ini, ikut disertakan.
        $baris = $lamaran->getCollection();
        if ($request->filled('buka') && ! $baris->contains('id', (int) $request->buka)) {
            $tambahan = Lamaran::with(['lowongan' => fn ($q) => $q->withTrashed(), 'divisi', 'dataMagang', 'suratBalasan'])->find((int) $request->buka);
            $tambahan && $baris = $baris->push($tambahan);
        }

        return view('admin.lamaran.index', [
            'lamaran'    => $lamaran,
            'rows'       => $baris->map(fn (Lamaran $l) => $this->untukModal($l))->values(),
            'tipe'       => $tipe,
            'status'     => $status,
            'departemen' => DB::table('departemen')->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    /** Berkas pribadi dilayani lewat route admin (disimpan di disk privat). */
    public function berkas(Lamaran $lamaran, string $jenis)
    {
        $kolom = self::BERKAS[$jenis] ?? abort(404);
        $path  = $lamaran->{$kolom};

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    /**
     * Terima pelamar = sama seperti "Tambah Anak Magang" (PesertaController@store):
     * membuat akun peserta (users) + data_magang sekaligus, sandi awal User::PASSWORD_DEFAULT.
     * Berkas disalin dari disk privat (lamaran/*) ke disk `public` (peserta/*) karena halaman
     * peserta lama (PesertaController@berkas) membacanya dari sana.
     */
    public function terima(Request $request, Lamaran $lamaran)
    {
        if ($lamaran->status !== 'menunggu') {
            return back()->withErrors(['status' => 'Lamaran ini sudah diputuskan sebelumnya.']);
        }

        // NIS/NIM & jenis kelamin diisi pelamar saat mendaftar; departemen mengikuti posisi yang dilamar.
        // (Input admin hanya dipakai sebagai cadangan untuk lamaran lama yang datanya belum lengkap.)
        $posisi = $lamaran->lowongan()->withTrashed()->first();

        $v = \Illuminate\Support\Facades\Validator::make([
            'nis_nim'       => trim((string) ($lamaran->nis_nim ?: $request->input('nis_nim'))),
            'jenis_kelamin' => $lamaran->jenis_kelamin ?: $request->input('jenis_kelamin'),
            'departemen_id' => $posisi?->departemen_id,
        ], [
            'nis_nim'       => ['required', 'string', 'max:50', 'unique:users,nis_nim', 'unique:data_magang,nis_nim'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'departemen_id' => ['required', 'integer', 'exists:departemen,id'],
        ], [
            'nis_nim.required'       => 'NIS/NIM pelamar belum terisi (lamaran lama). Isi pada kolom yang muncul di panel Terima.',
            'nis_nim.unique'         => 'NIS/NIM ini sudah dipakai peserta lain.',
            'jenis_kelamin.required' => 'Jenis kelamin pelamar belum terisi (lamaran lama). Isi pada kolom yang muncul di panel Terima.',
            'departemen_id.required' => 'Posisi ini belum dihubungkan ke departemen. Atur di menu Lowongan (Edit > Departemen), lalu terima lagi.',
            'departemen_id.exists'   => 'Departemen pada posisi ini tidak valid. Perbarui di menu Lowongan.',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v);
        }

        $data = $v->validated();

        // Satu pelamar hanya bisa diterima di satu posisi.
        if ($lamaran->pelamar_id && Lamaran::where('pelamar_id', $lamaran->pelamar_id)
                ->where('status', 'diterima')->where('id', '!=', $lamaran->id)->exists()) {
            return back()->withErrors(['status' => 'Pelamar ini sudah diterima di posisi lain. Satu pelamar hanya bisa diterima di satu posisi.']);
        }

        $lowongan = $lamaran->lowongan()->withTrashed()->first();

        // Pastikan menerima orang ini tidak melebihi kuota harian posisi tersebut.
        if ($lowongan && $penuh = $lowongan->cekKetersediaan($lamaran->tgl_mulai->copy(), $lamaran->tgl_selesai->copy())) {
            return back()->withErrors([
                'kuota' => sprintf(
                    'Tidak bisa diterima: kuota %s penuh pada %s s/d %s (%d orang).',
                    $lowongan->judul,
                    $penuh['dari']->translatedFormat('j M Y'),
                    $penuh['sampai']->translatedFormat('j M Y'),
                    $lowongan->kuota
                ),
            ]);
        }

        $departemen = Departemen::findOrFail($data['departemen_id']);
        $publik     = Storage::disk('public');

        $foto  = $this->salinKePublik($lamaran->foto_pas, 'peserta/foto');
        $cv    = $this->salinKePublik($lamaran->cv_path, 'peserta/cv');
        $surat = $this->salinKePublik($lamaran->surat_permohonan_path, 'peserta/surat');

        $otomatis = 0;

        try {
            $user = DB::transaction(function () use ($lamaran, $data, $departemen, $foto, $cv, $surat, $publik, &$otomatis) {
                $uuid = (string) Str::uuid();

                // Email pelamar dipakai kalau belum dipakai akun lain (kolom users.email unik).
                $email = User::where('email', $lamaran->email)->exists() ? null : $lamaran->email;

                $user = User::create([
                    'name'                 => $lamaran->nama,
                    'email'                => $email,
                    'nis_nim'              => $data['nis_nim'],
                    // Satu sandi untuk portal & presensi: pakai sandi akun portal pelamar (sudah berupa hash).
                    'password'             => $lamaran->pelamar?->getRawOriginal('password') ?? User::PASSWORD_DEFAULT,
                    'wajib_ganti_password' => ! $lamaran->pelamar,
                    'peran'                => 'peserta',
                    'status_akun'          => 'aktif',
                    'departemen_id'        => $departemen->id,
                    'uuid_kartu'           => $uuid,
                    'foto_profil_url'      => $foto ? $publik->url($foto) : null,
                    'asal_sekolah'         => $lamaran->asal_sekolah,
                    'jurusan'              => $lamaran->jurusan,
                ]);

                $dm = DataMagang::create([
                    'nama'                  => $lamaran->nama,
                    'tipe_magang'           => $lamaran->tipe_magang,
                    'jenis_kelamin'         => $data['jenis_kelamin'],
                    'no_wa'                 => $lamaran->no_wa,
                    'alamat'                => $lamaran->alamat,
                    'nis_nim'               => $data['nis_nim'],
                    'asal_sekolah'          => $lamaran->asal_sekolah,
                    'jurusan'               => $lamaran->jurusan,
                    'departemen_id'         => $departemen->id,
                    'divisi'                => $departemen->nama,
                    'uuid'                  => $uuid,
                    'user_id'               => $user->id,
                    'ditautkan_pada'        => now(),
                    'foto_pas'              => $foto,
                    'pembimbing'            => $lamaran->pembimbing,
                    'no_wa_pembimbing'      => $lamaran->no_wa_pembimbing,
                    'cv_path'               => $cv,
                    'surat_permohonan_path' => $surat,
                    // Format sama dengan data import: "04/05/2026 - 04/11/2026"
                    'periode_magang'        => $lamaran->tgl_mulai->format('d/m/Y') . ' - ' . $lamaran->tgl_selesai->format('d/m/Y'),
                    'status_kehadiran_awal' => 'hadir',
                    'tgl_mulai'             => $lamaran->tgl_mulai->toDateString(),
                    'tgl_selesai'           => $lamaran->tgl_selesai->toDateString(),
                    'status_magang'         => 'ACTIVE',
                ]);

                $lamaran->update([
                    'status'          => 'diterima',
                    'jenis_kelamin'   => $data['jenis_kelamin'],
                    'nis_nim'         => $data['nis_nim'],
                    'diputuskan_oleh' => $this->adminId(),
                    'diputuskan_pada' => now(),
                    'data_magang_id'  => $dm->id,
                ]);

                // Auto-decline lamaran lain milik pelamar yang sama (hanya bisa diterima di 1 posisi).
                $otomatis = Lamaran::tolakLainnyaOtomatis($lamaran, $this->adminId());

                return $user;
            });
        } catch (\Throwable $e) {
            $publik->delete(array_filter([$foto, $cv, $surat]));   // jangan tinggalkan berkas yatim
            throw $e;
        }

        // Lanjut ke langkah berikutnya: HRD diminta membuat Surat Balasan Ijin Prakerin (form terbuka otomatis).
        return redirect()
            ->route('admin.lamaran.index', ['status' => 'diterima', 'buka' => $lamaran->id, 'surat' => 1])
            ->with('sukses', "{$lamaran->nama} diterima. Akun absensi dibuat: login dengan NIS/NIM {$data['nis_nim']} dan kata sandi yang sama dengan akun portal pelamar. QR kartu bisa dicetak di menu Peserta Aktif. " . ($otomatis ? "{$otomatis} lamaran lain milik pelamar ini otomatis ditolak (hanya bisa diterima di satu posisi). " : '') . 'Langkah berikutnya: isi form Surat Balasan Ijin Prakerin di bawah ini.');
    }

    /** Salin berkas pelamar (disk privat) ke disk public; mengembalikan path barunya atau null. */
    private function salinKePublik(?string $path, string $folder): ?string
    {
        $privat = Storage::disk('local');

        if (! $path || ! $privat->exists($path)) {
            return null;
        }

        $baru = $folder . '/' . Str::uuid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
        Storage::disk('public')->writeStream($baru, $privat->readStream($path));

        return $baru;
    }

    public function tolak(Request $request, Lamaran $lamaran)
    {
        $data = $request->validate(['catatan_admin' => ['nullable', 'string', 'max:500']]);

        if ($lamaran->status !== 'menunggu') {
            return back()->withErrors(['status' => 'Lamaran ini sudah diputuskan sebelumnya.']);
        }

        $lamaran->update([
            'status'          => 'ditolak',
            'catatan_admin'   => $data['catatan_admin'] ?? null,
            'diputuskan_oleh' => $this->adminId(),
            'diputuskan_pada' => now(),
        ]);

        return back()->with('sukses', "Lamaran {$lamaran->nama} ditolak.");
    }

    private function untukModal(Lamaran $l): array
    {
        $smk = $l->tipe_magang === 'smk';

        return [
            'id'            => $l->id,
            'nama'          => $l->nama,
            'smk'           => $smk,
            'posisi'        => $l->nama_posisi,
            'internal'         => $l->adalahInternal(),
            'jk'            => match ($l->jenis_kelamin) { 'L' => 'Laki-laki', 'P' => 'Perempuan', default => '-' },
            'jkKode'        => $l->jenis_kelamin,
            'email'         => $l->email,
            'wa'            => $l->no_wa,
            'alamat'        => $l->alamat,
            'asal'          => $l->asal_sekolah,
            'asalLabel'     => $smk ? 'Asal Sekolah' : 'Asal Kampus',
            'jurusan'       => $l->jurusan,
            'periode'       => $l->tgl_mulai->translatedFormat('j M Y') . ' s/d ' . $l->tgl_selesai->translatedFormat('j M Y')
                               . ' (' . (int) $l->tgl_mulai->diffInDays($l->tgl_selesai) . ' hari)',
            'pembimbingLabel' => $smk ? 'Pembimbing Sekolah' : 'Dosen Pembimbing',
            'pembimbing'    => $l->pembimbing ? $l->pembimbing . ($l->no_wa_pembimbing ? ' (' . $l->no_wa_pembimbing . ')' : '') : '-',
            'tglDaftar'     => $l->created_at->translatedFormat('d M Y, H:i'),
            'status'        => $l->status,
            'catatan'       => $l->catatan_admin,
            'diputuskan'    => $l->diputuskan_pada?->translatedFormat('d M Y, H:i'),
            'departemenId'  => $l->lowongan?->departemen_id,
            'departemenNama' => $l->lowongan?->departemen_id ? \App\Models\Departemen::find($l->lowongan->departemen_id)?->nama : null,
            'nisNim'        => $l->nis_nim,
            'suratUrl'      => route('admin.lamaran.surat.simpan', $l),
            'suratPdf'      => $l->suratBalasan ? route('admin.lamaran.surat.pdf', $l) : null,
            // Isi form surat: data yang sudah tersimpan, atau usulan awal dari data lamaran.
            'surat'         => [
                'nomor_surat'      => $l->suratBalasan?->nomor_surat ?? '',
                'tanggal_surat'    => ($l->suratBalasan?->tanggal_surat ?? today())->toDateString(),
                // Bapak/Ibu <dosen/guru pembimbing yang diinput pelamar>
                'ditujukan_kepada' => $l->suratBalasan?->ditujukan_kepada ?? (string) $l->pembimbing,
                'tgl_pengajuan'    => ($l->suratBalasan?->tgl_pengajuan ?? $l->created_at)->toDateString(),
                'media_pengajuan'  => $l->suratBalasan?->media_pengajuan ?? ($l->adalahInternal() ? 'Pengajuan Sekolah' : 'Website Prakerin'),
                'tgl_mulai'        => ($l->suratBalasan?->tgl_mulai ?? $l->tgl_mulai)->toDateString(),
                'tgl_selesai'      => ($l->suratBalasan?->tgl_selesai ?? $l->tgl_selesai)->toDateString(),
                'tgl_induction'    => ($l->suratBalasan?->tgl_induction ?? $l->tgl_mulai)->toDateString(),
            ],
            'adaSurat'      => (bool) $l->suratBalasan,
            'berkas'        => [
                'foto'  => $l->foto_pas ? route('admin.lamaran.berkas', [$l, 'foto']) : null,
                'cv'    => $l->cv_path ? route('admin.lamaran.berkas', [$l, 'cv']) : null,
                'surat' => $l->surat_permohonan_path ? route('admin.lamaran.berkas', [$l, 'surat']) : null,
            ],
        ];
    }

    /** ID admin dari tabel `admins` (kosong kalau login lewat guard lain). */
    private function adminId(): ?int
    {
        return config('auth.guards.admin') ? Auth::guard('admin')->id() : null;
    }
}