<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Divisi;
use App\Models\Lowongan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LowonganAdminController extends Controller
{
    public function index()
    {
        $lowongan = Lowongan::query()
            ->leftJoin('departemen', 'departemen.id', '=', 'lowongan.departemen_id')
            ->select('lowongan.*', 'departemen.nama as departemen_nama')
            ->withCount([
                'lamaran as menunggu_count' => fn ($q) => $q->where('status', 'menunggu'),
                'lamaran as diterima_count' => fn ($q) => $q->where('status', 'diterima'),
                'lamaran as total_count',
            ])
            ->orderBy('lowongan.id')
            ->get();

        return view('admin.lowongan.index', [
            'lowongan'   => $lowongan,
            'rows'       => $lowongan->map(fn ($l) => [
                'id'               => $l->id,
                'judul'            => $l->judul,
                'tipe_magang'      => $l->tipe_magang,
                'divisi_id'        => $l->divisi_id,
                'kuota'            => $l->kuota,
                'jurusan_diterima' => $l->jurusan_diterima,
                'deskripsi'        => $l->deskripsi,
                'status'           => $l->status,
                'batas_pendaftaran' => $l->batas_pendaftaran?->toDateString(),
            ])->values(),
            'departemen' => Departemen::orderBy('nama')->get(['id', 'nama']),
            // Sumber dropdown "Nama Posisi": section/divisi, dikelompokkan per departemen.
            'divisi'     => Divisi::with('departemen:id,nama')
                ->get(['id', 'departemen_id', 'nama', 'jurusan'])
                ->sortBy(fn ($d) => ($d->departemen?->nama ?? '') . '|' . $d->nama)
                ->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        Lowongan::create($data + [
            'slug'        => $this->slugUnik($data['judul']),
            'dibuat_oleh' => $this->adminId(),
        ]);

        return redirect()->route('admin.lowongan.index')->with('sukses', "Posisi {$data['judul']} ditambahkan.");
    }

    public function update(Request $request, Lowongan $lowongan)
    {
        $lowongan->update($this->validasi($request));   // slug tidak diubah supaya URL tetap stabil

        return redirect()->route('admin.lowongan.index')->with('sukses', "Posisi {$lowongan->judul} diperbarui.");
    }

    /** Buka / tutup pendaftaran dengan satu klik. */
    public function status(Lowongan $lowongan)
    {
        $baru = $lowongan->status === 'dibuka' ? 'ditutup' : 'dibuka';
        $lowongan->update(['status' => $baru]);

        return back()->with('sukses', "Posisi {$lowongan->judul} sekarang " . ($baru === 'dibuka' ? 'DIBUKA' : 'DITUTUP') . '.');
    }

    public function destroy(Lowongan $lowongan)
    {
        if ($lowongan->lamaran()->exists()) {
            return back()->withErrors([
                'hapus' => "Posisi {$lowongan->judul} sudah punya lamaran dan tidak bisa dihapus. Gunakan tombol Tutup.",
            ]);
        }

        $lowongan->delete();

        return back()->with('sukses', "Posisi {$lowongan->judul} dihapus.");
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate(Divisi::aturanPilihan() + [
            'tipe_magang'       => ['required', Rule::in(['smk', 'kuliah'])],
            'kuota'             => ['required', 'integer', 'min:1', 'max:100'],
            'jurusan_diterima'  => ['nullable', 'string', 'max:2000'],
            'deskripsi'         => ['nullable', 'string', 'max:2000'],
            'status'            => ['required', Rule::in(['draft', 'dibuka', 'ditutup'])],
            'batas_pendaftaran' => ['nullable', 'date'],
        ], Divisi::pesanPilihan() + [
            'kuota.min' => 'Kuota minimal 1 orang.',
        ]);

        // Nama posisi & departemen mengikuti section yang dipilih (bukan diketik manual).
        $divisi = Divisi::dariPilihan($data, $data['jurusan_diterima'] ?? null);
        $data['divisi_id']     = $divisi->id;
        $data['judul']         = $divisi->nama;
        $data['departemen_id'] = $divisi->departemen_id;

        unset($data['divisi_baru_nama'], $data['divisi_baru_departemen_id']);

        return $data;
    }

    private function slugUnik(string $judul): string
    {
        $dasar = Str::slug($judul) ?: 'posisi';
        $slug  = $dasar;
        $i     = 2;

        while (Lowongan::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $dasar . '-' . $i++;
        }

        return $slug;
    }

    private function adminId(): ?int
    {
        return config('auth.guards.admin') ? Auth::guard('admin')->id() : null;
    }
}