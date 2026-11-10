<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\User;
use App\Models\ZonaAbsen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ZonaController extends Controller
{
    public function index(Request $r)
    {
        $q = User::with('departemen')->where('peran', 'peserta')->orderBy('name');

        if ($r->filled('q')) {
            $kata = '%' . $r->query('q') . '%';
            $q->where(fn ($w) => $w->where('name', 'like', $kata)->orWhere('nis_nim', 'like', $kata));
        }
        if ($r->filled('departemen_id')) {
            $q->where('departemen_id', $r->query('departemen_id'));
        }
        if ($r->query('zona') === 'kosong') {
            $q->whereNull('pos_kode');
        } elseif ($r->filled('zona')) {
            $q->where('pos_kode', $r->query('zona'));
        }

        return view('admin.zona.index', [
            'peserta' => $q->paginate(100)->withQueryString(),
            'pos' => ZonaAbsen::daftar(),
            'departemen' => Departemen::orderBy('nama')->get(),
            'ringkasan' => User::where('peran', 'peserta')
                ->selectRaw('pos_kode, COUNT(*) as n')->groupBy('pos_kode')->pluck('n', 'pos_kode')->all(),
        ]);
    }

    /** Atur zona untuk peserta yang dicentang (fitur lama). */
    public function update(Request $r)
    {
        $kodeValid = collect(ZonaAbsen::daftar())->pluck('kode')->filter()->values()->all();

        $d = $r->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer',
            'pos_kode' => ['nullable', Rule::in($kodeValid)],
        ], [
            'user_ids.required' => 'Centang minimal satu peserta dulu.',
            'pos_kode.in' => 'Zona yang dipilih tidak dikenal.',
        ]);

        $kode = $d['pos_kode'] ?: null;

        $n = User::where('peran', 'peserta')->whereIn('id', $d['user_ids'])->update(['pos_kode' => $kode]);

        $nama = $kode
            ? (collect(ZonaAbsen::daftar())->firstWhere('kode', $kode)['nama'] ?? $kode)
            : null;

        return redirect()->back()->with('success', $kode
            ? "$n peserta diatur ke zona $nama."
            : "Zona $n peserta dikosongkan (belum bisa absen sampai diatur lagi).");
    }

    // ============ EDIT ZONA (peta satelit) ============
    // Hanya dibuka lewat tombol "Edit Zona" di halaman Zona Absen.

    public function edit()
    {
        $pakai = User::where('peran', 'peserta')->whereNotNull('pos_kode')
            ->selectRaw('pos_kode, COUNT(*) as n')->groupBy('pos_kode')->pluck('n', 'pos_kode')->all();

        return view('admin.zona.edit', [
            'zona' => ZonaAbsen::orderBy('id')->get()->map(fn ($z) => [
                'id' => $z->id,
                'kode' => $z->kode,
                'nama' => $z->nama,
                'lat' => (float) $z->lat,
                'lng' => (float) $z->lng,
                'radius' => (int) $z->radius,
                'peserta' => (int) ($pakai[$z->kode] ?? 0),
            ])->values(),
        ]);
    }

    public function simpanTitik(Request $r)
    {
        $d = $this->validasiTitik($r);

        ZonaAbsen::create($d + ['kode' => ZonaAbsen::kodeBaru()]);

        return redirect()->route('admin.zona.edit')
            ->with('success', "Titik lokasi \"{$d['nama']}\" ditambahkan. Atur peserta ke zona ini di halaman Zona Absen.");
    }

    public function perbaruiTitik(Request $r, ZonaAbsen $zona)
    {
        $d = $this->validasiTitik($r, $zona);

        // 'kode' sengaja tidak diubah: peserta yang sudah diatur ke zona ini tetap terhubung.
        $zona->update($d);

        return redirect()->route('admin.zona.edit')->with('success', "Titik lokasi \"{$zona->nama}\" diperbarui.");
    }

    public function hapusTitik(ZonaAbsen $zona)
    {
        $dipakai = User::where('pos_kode', $zona->kode)->count();

        if ($dipakai > 0) {
            return redirect()->route('admin.zona.edit')->with(
                'error',
                "Zona \"{$zona->nama}\" masih dipakai $dipakai peserta. Pindahkan atau kosongkan zona mereka dulu di halaman Zona Absen."
            );
        }

        $nama = $zona->nama;
        $zona->delete();

        return redirect()->route('admin.zona.edit')->with('success', "Titik lokasi \"$nama\" dihapus.");
    }

    private function validasiTitik(Request $r, ?ZonaAbsen $zona = null): array
    {
        return $r->validate([
            'nama' => ['required', 'string', 'max:60', Rule::unique('zona_absen', 'nama')->ignore($zona?->id)],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'between:1,5000'],
        ], [
            'nama.required' => 'Nama titik lokasi wajib diisi.',
            'nama.unique' => 'Nama titik lokasi itu sudah dipakai.',
            'lat.required' => 'Latitude wajib diisi.',
            'lat.numeric' => 'Latitude harus berupa angka.',
            'lat.between' => 'Latitude harus di antara -90 dan 90.',
            'lng.required' => 'Longitude wajib diisi.',
            'lng.numeric' => 'Longitude harus berupa angka.',
            'lng.between' => 'Longitude harus di antara -180 dan 180.',
            'radius.required' => 'Ukuran area zona (meter) wajib diisi.',
            'radius.integer' => 'Ukuran area zona harus berupa bilangan bulat (meter).',
            'radius.between' => 'Ukuran area zona harus 1 sampai 5000 meter.',
        ]);
    }
}