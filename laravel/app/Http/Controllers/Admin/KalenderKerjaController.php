<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\HariPengganti;
use App\Models\SesiPresensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Kalender Kerja: atur hari libur (1 hari atau rentang) dan hari kerja pengganti. */
class KalenderKerjaController extends Controller
{
    private const MAKS_HARI = 60;

    public function index(Request $r)
    {
        if (! Schema::hasTable('hari_libur') || ! Schema::hasTable('hari_pengganti')) {
            return view('admin.kalender.index', ['belumSiap' => true, 'daftar' => collect(), 'tahun' => today()->year,
                'tahunTersedia' => collect([today()->year]), 'payload' => [], 'ringkas' => []]);
        }

        $tahun = (int) $r->query('tahun', today()->year);

        $daftar = HariLibur::with('pengganti')
            ->where(fn ($q) => $q->whereYear('tanggal_mulai', $tahun)->orWhereYear('tanggal_selesai', $tahun))
            ->orderBy('tanggal_mulai')
            ->get();

        $tahunTersedia = HariLibur::selectRaw('YEAR(tanggal_mulai) as t')->distinct()->pluck('t')
            ->push(today()->year)->push($tahun)->unique()->sortDesc()->values();

        $payload = $daftar->mapWithKeys(fn (HariLibur $h) => [$h->id => [
            'id' => $h->id,
            'jenis' => $h->jenis,
            'jenis_label' => $h->jenis_label,
            'nama' => $h->nama,
            'mulai' => $h->tanggal_mulai->toDateString(),
            'selesai' => $h->tanggal_selesai->toDateString(),
            'keterangan' => $h->keterangan,
            'pengganti' => $h->pengganti->map(fn ($p) => $p->tanggal->toDateString())->values()->all(),
        ]])->all();

        $hariIni = today();
        $ringkas = [
            'jumlah' => $daftar->count(),
            'total_hari' => $daftar->sum(fn ($h) => $h->lamaHari()),
            'akan_datang' => $daftar->filter(fn ($h) => $h->tanggal_mulai->gt($hariIni))->count(),
            'pengganti' => $daftar->sum(fn ($h) => $h->pengganti->count()),
        ];

        return view('admin.kalender.index', [
            'belumSiap' => false,
            'daftar' => $daftar,
            'tahun' => $tahun,
            'tahunTersedia' => $tahunTersedia,
            'payload' => $payload,
            'ringkas' => $ringkas,
        ]);
    }

    public function store(Request $r)
    {
        return $this->simpan($r, null);
    }

    public function update(Request $r, HariLibur $libur)
    {
        return $this->simpan($r, $libur);
    }

    public function destroy(HariLibur $libur)
    {
        $nama = $libur->nama;
        $tahun = $libur->tanggal_mulai->year;
        $libur->delete(); // hari_pengganti ikut terhapus (cascade)

        return redirect()->route('admin.kalender.index', ['tahun' => $tahun])
            ->with('success', "Hari libur \"$nama\" dihapus. Tanggalnya kembali dihitung normal.");
    }

    private function simpan(Request $r, ?HariLibur $ubah)
    {
        // Rapikan daftar tanggal pengganti: buang yang kosong & duplikat, kosongkan bila "tidak ada pengganti".
        $pengganti = $r->boolean('ada_pengganti')
            ? array_values(array_unique(array_filter((array) $r->input('pengganti', []), fn ($x) => filled($x))))
            : [];
        $r->merge(['pengganti' => $pengganti]);

        $d = $r->validate([
            'jenis' => ['required', Rule::in(array_keys(HariLibur::JENIS))],
            'jenis_label' => ['nullable', 'required_if:jenis,lainnya', 'string', 'max:60'],
            'nama' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'pengganti' => ['array', 'max:10'],
            'pengganti.*' => ['date_format:Y-m-d'],
        ], [
            'jenis.required' => 'Pilih jenis libur.',
            'jenis_label.required_if' => 'Tulis jenis liburnya (mis. Libur Perusahaan).',
            'nama.required' => 'Nama/keterangan libur wajib diisi (mis. Hari Raya Idul Fitri).',
            'tanggal_mulai.required' => 'Tanggal mulai libur wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai libur wajib diisi (sama dengan tanggal mulai bila 1 hari).',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'pengganti.max' => 'Maksimal 10 hari pengganti.',
            'pengganti.*.date_format' => 'Tanggal hari pengganti tidak valid.',
        ]);

        $mulai = Carbon::createFromFormat('!Y-m-d', $d['tanggal_mulai']);
        $selesai = Carbon::createFromFormat('!Y-m-d', $d['tanggal_selesai']);

        if ($mulai->diffInDays($selesai, true) + 1 > self::MAKS_HARI) {
            throw ValidationException::withMessages(['tanggal_selesai' => 'Lama libur maksimal ' . self::MAKS_HARI . ' hari. Pecah jadi beberapa entri bila lebih.']);
        }

        // Hari pengganti harus tanggal yang memang bukan hari kerja & tidak bentrok dengan libur/pengganti lain.
        $hariKerjaBiasa = (array) config('prakerin.hari_kerja');
        $galat = [];
        foreach ($d['pengganti'] as $p) {
            $tgl = Carbon::createFromFormat('!Y-m-d', $p);
            $label = $tgl->copy()->locale('id')->translatedFormat('l, j F Y');

            if ($tgl->between($mulai, $selesai)) {
                $galat[] = "$label ada di dalam rentang libur itu sendiri.";
            } elseif (in_array($tgl->dayOfWeekIso, $hariKerjaBiasa, true)) {
                $galat[] = "$label sudah hari kerja biasa, tidak perlu hari pengganti. Pilih tanggal yang biasanya libur (mis. Sabtu).";
            } elseif (HariLibur::when($ubah, fn ($q) => $q->where('id', '!=', $ubah->id))
                ->whereDate('tanggal_mulai', '<=', $p)->whereDate('tanggal_selesai', '>=', $p)->exists()) {
                $galat[] = "$label sedang termasuk hari libur lain.";
            } elseif (HariPengganti::when($ubah, fn ($q) => $q->where('hari_libur_id', '!=', $ubah->id))
                ->whereDate('tanggal', $p)->exists()) {
                $galat[] = "$label sudah dipakai sebagai hari pengganti libur lain.";
            }
        }

        // Rentang libur baru tidak boleh menimpa hari pengganti milik libur lain.
        $menimpa = HariPengganti::when($ubah, fn ($q) => $q->where('hari_libur_id', '!=', $ubah->id))
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])->first();
        if ($menimpa) {
            $galat[] = 'Rentang libur ini menimpa hari pengganti ' . $menimpa->tanggal->copy()->locale('id')->translatedFormat('l, j F Y')
                . ' milik libur lain. Ubah salah satunya dulu.';
        }

        if ($galat) {
            throw ValidationException::withMessages(['pengganti' => $galat]);
        }

        $data = [
            'jenis' => $d['jenis'],
            'jenis_label' => $d['jenis'] === 'lainnya' ? trim($d['jenis_label']) : null,
            'nama' => trim($d['nama']),
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'keterangan' => filled($d['keterangan'] ?? null) ? trim($d['keterangan']) : null,
        ];

        $libur = DB::transaction(function () use ($ubah, $data, $d) {
            $libur = $ubah ?? new HariLibur();
            $libur->fill($data)->save();

            $libur->pengganti()->delete();
            foreach ($d['pengganti'] as $p) {
                $libur->pengganti()->create(['tanggal' => $p]);
            }

            return $libur;
        });

        $sudahAda = SesiPresensi::whereBetween('tanggal', [$data['tanggal_mulai'], $data['tanggal_selesai']])->count();
        $pesan = ($ubah ? 'Hari libur diperbarui' : 'Hari libur ditambahkan') . ": {$libur->nama} ({$libur->lamaHari()} hari)."
            . ($sudahAda > 0 ? " Catatan: sudah ada $sudahAda sesi presensi di rentang ini. Datanya tetap tersimpan, tetapi tanggal ini tidak dihitung sebagai hari kerja." : '');

        return redirect()->route('admin.kalender.index', ['tahun' => $mulai->year])->with('success', $pesan);
    }
}
