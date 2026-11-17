<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormPenilaian;
use App\Models\Penilaian;
use App\Models\SuratKeterangan;
use App\Services\SinkronSuratKeterangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Admin membuat form penilaian (seperti Google Form), membagikan linknya, dan melihat hasil isiannya. */
class FormPenilaianController extends Controller
{
    public function index()
    {
        $forms = FormPenilaian::query()
            ->with('departemen')
            ->withCount(['kriteria', 'penilaian'])
            ->latest('id')
            ->get();

        return view('admin.penilaian.index', ['forms' => $forms]);
    }

    public function create()
    {
        return view('admin.penilaian.form', [
            'form'       => new FormPenilaian(['status' => 'dibuka']),
            'kriteria'   => old('kriteria', [
                ['id' => null, 'nama' => 'Kedisiplinan', 'keterangan' => '', 'bobot' => 1],
                ['id' => null, 'nama' => 'Tanggung Jawab', 'keterangan' => '', 'bobot' => 1],
                ['id' => null, 'nama' => 'Kemampuan Teknis', 'keterangan' => '', 'bobot' => 1],
            ]),
            'departemen' => DB::table('departemen')->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $form = DB::transaction(function () use ($data) {
            $form = FormPenilaian::create([
                'judul'         => $data['judul'],
                'deskripsi'     => $data['deskripsi'] ?? null,
                'departemen_id' => $data['departemen_id'] ?? null,
                'status'        => $data['status'],
                'dibuat_oleh'   => $this->adminId(),
            ]);

            $this->simpanKriteria($form, $data['kriteria']);

            return $form;
        });

        return redirect()->route('admin.penilaian.index')
            ->with('sukses', "Form \"{$form->judul}\" dibuat. Salin link-nya dari daftar di bawah lalu kirim ke penilai.");
    }

    public function edit(FormPenilaian $form)
    {
        $form->load('kriteria');

        return view('admin.penilaian.form', [
            'form'       => $form,
            'kriteria'   => old('kriteria', $form->kriteria->map(fn ($k) => [
                'id' => $k->id, 'nama' => $k->nama, 'keterangan' => $k->keterangan, 'bobot' => $k->bobot,
            ])->all()),
            'departemen' => DB::table('departemen')->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function update(Request $request, FormPenilaian $form)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($form, $data) {
            $form->update([
                'judul'         => $data['judul'],
                'deskripsi'     => $data['deskripsi'] ?? null,
                'departemen_id' => $data['departemen_id'] ?? null,
                'status'        => $data['status'],
            ]);

            $this->simpanKriteria($form, $data['kriteria']);
        });

        return redirect()->route('admin.penilaian.index')
            ->with('sukses', "Form \"{$form->judul}\" diperbarui. Nilai yang sudah masuk tidak berubah.");
    }

    public function status(FormPenilaian $form)
    {
        $form->update(['status' => $form->dibuka() ? 'ditutup' : 'dibuka']);

        return back()->with('sukses', 'Form "' . $form->judul . '" sekarang ' . ($form->dibuka() ? 'DIBUKA' : 'DITUTUP') . '.');
    }

    public function show(FormPenilaian $form)
    {
        $form->load('departemen');

        $hasil = $form->penilaian()
            ->with(['dataMagang', 'nilai'])
            ->latest('id')
            ->get();

        return view('admin.penilaian.show', [
            'form'  => $form,
            'hasil' => $hasil,
            'sisa'  => $form->pesertaBelumDinilai()->count(),
        ]);
    }

    public function destroy(FormPenilaian $form)
    {
        $judul   = $form->judul;
        $pesertaIds = $form->penilaian()->pluck('data_magang_id')->unique();

        $form->delete();   // kriteria, penilaian, dan nilai ikut terhapus (cascade)

        $this->sinkronUlang($pesertaIds);

        return redirect()->route('admin.penilaian.index')->with('sukses', "Form \"{$judul}\" dan seluruh isiannya dihapus.");
    }

    public function hapusHasil(Penilaian $penilaian)
    {
        $formId = $penilaian->form_penilaian_id;
        $nama   = $penilaian->dataMagang?->nama ?? 'peserta';

        $penilaian->delete();
        $this->sinkronUlang([$penilaian->data_magang_id]);

        return redirect()->route('admin.penilaian.show', $formId)->with('sukses', "Penilaian untuk {$nama} dihapus.");
    }

    /** Hitung ulang surat keterangan peserta setelah penilaiannya berkurang. */
    private function sinkronUlang(iterable $dataMagangIds): void
    {
        $sinkron = app(SinkronSuratKeterangan::class);

        foreach (\App\Models\DataMagang::whereIn('id', collect($dataMagangIds)->all())->get() as $dm) {
            if ($dm->penilaian()->exists()) {
                $sinkron->jalankan($dm);
            } else {
                // Tidak ada penilaian tersisa: surat draft dibuang; surat yang sudah terbit dibiarkan.
                SuratKeterangan::where('data_magang_id', $dm->id)->where('status', 'draft')->delete();
            }
        }
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'judul'                  => ['required', 'string', 'max:150'],
            'deskripsi'              => ['nullable', 'string', 'max:1000'],
            'departemen_id'          => ['nullable', 'integer', 'exists:departemen,id'],
            'status'                 => ['required', 'in:dibuka,ditutup'],
            'kriteria'               => ['required', 'array', 'min:1', 'max:30'],
            'kriteria.*.id'          => ['nullable', 'integer'],
            'kriteria.*.nama'        => ['required', 'string', 'max:150'],
            'kriteria.*.keterangan'  => ['nullable', 'string', 'max:255'],
            'kriteria.*.bobot'       => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'judul.required'               => 'Judul form wajib diisi.',
            'kriteria.required'            => 'Tambahkan minimal satu butir penilaian.',
            'kriteria.min'                 => 'Tambahkan minimal satu butir penilaian.',
            'kriteria.*.nama.required'     => 'Nama butir penilaian tidak boleh kosong.',
            'kriteria.*.bobot.required'    => 'Bobot setiap butir wajib diisi (1 - 100).',
            'kriteria.*.bobot.min'         => 'Bobot minimal 1.',
            'kriteria.*.bobot.max'         => 'Bobot maksimal 100.',
        ]);
    }

    /** Samakan butir di database dengan isian form: perbarui yang ada, tambah yang baru, hapus yang dibuang. */
    private function simpanKriteria(FormPenilaian $form, array $baris): void
    {
        $idSah   = $form->kriteria()->pluck('id')->all();
        $dipakai = [];

        foreach (array_values($baris) as $urutan => $b) {
            $isi = [
                'nama'       => $b['nama'],
                'keterangan' => $b['keterangan'] ?? null,
                'bobot'      => (int) $b['bobot'],
                'urutan'     => $urutan,
            ];

            if (! empty($b['id']) && in_array((int) $b['id'], $idSah, true)) {
                $form->kriteria()->whereKey($b['id'])->update($isi);
                $dipakai[] = (int) $b['id'];
            } else {
                $dipakai[] = $form->kriteria()->create($isi)->id;
            }
        }

        // Nilai lama aman: penilaian_nilai menyimpan salinan nama & bobot butir.
        $form->kriteria()->whereNotIn('id', $dipakai)->delete();
    }

    private function adminId(): ?int
    {
        return config('auth.guards.admin') ? Auth::guard('admin')->id() : null;
    }
}
