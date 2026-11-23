<?php

namespace App\Http\Request;

use App\Models\Lowongan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/** Langkah 3: data diri + berkas. Posisi & periode diambil dari session (hasil langkah 1). */
class StoreLamaranRequest extends FormRequest
{
    // No. HP: 6-15 digit angka (boleh diawali +). Spasi, strip, titik, dan kurung dibuang otomatis.
    private const HP = '/^\+?\d{6,15}$/';

    private ?Lowongan $lowonganCache = null;

    public function draft(): ?array
    {
        return $this->session()->get('lamaran_draft');
    }

    public function lowongan(): ?Lowongan
    {
        $draft = $this->draft();

        return $this->lowonganCache ??= $draft ? Lowongan::find($draft['lowongan_id']) : null;
    }

    public function authorize(): bool
    {
        return $this->lowongan()?->bisaDilamar() === true;
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            redirect(route('beranda') . '#lowongan')
                ->with('info', 'Pilihan periode Anda sudah berakhir. Silakan pilih posisi dan periode lagi.')
        );
    }

    protected function getRedirectUrl(): string
    {
        return route('lamaran.form');
    }

    protected function prepareForValidation(): void
    {
        $bersih = fn ($v) => $v ? preg_replace('/[\s\-\.\(\)]/', '', $v) : $v;

        $this->merge([
            'no_wa'            => $bersih($this->no_wa),
            'no_wa_pembimbing' => $bersih($this->no_wa_pembimbing),
            'nis_nim'          => $this->nis_nim ? preg_replace('/\s+/', '', $this->nis_nim) : $this->nis_nim,
        ]);
    }

    public function rules(): array
    {
        $isSmk = $this->lowongan()->tipe_magang === 'smk';

        return [
            'nama'             => ['required', 'string', 'max:150'],
            'jenis_kelamin'    => ['required', Rule::in(['L', 'P'])],
            'setuju_persyaratan' => ['accepted'],
            // NIS/NIM = username login presensi, jadi harus unik dan tanpa spasi.
            'nis_nim'          => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._\/-]+$/', 'unique:users,nis_nim', 'unique:data_magang,nis_nim', Rule::unique('pelamar', 'nis_nim')->ignore(\Illuminate\Support\Facades\Auth::guard('pelamar')->id())],
            'no_wa'            => ['required', 'regex:' . self::HP],
            'alamat'           => ['required', 'string', 'max:1000'],
            'foto_pas'         => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'asal_sekolah'     => ['required', 'string', 'max:150'],
            'jurusan'          => ['required', 'string', 'max:150'],

            // SMK: pembimbing wajib. Kuliah: opsional.
            'pembimbing'       => [$isSmk ? 'required' : 'nullable', 'string', 'max:150'],
            'no_wa_pembimbing' => [$isSmk ? 'required' : 'nullable', 'regex:' . self::HP],

            // CV / portofolio: WAJIB untuk Diploma/Sarjana, opsional untuk SMK
            'cv'               => [$isSmk ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:5120'],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama'             => 'nama lengkap',
            'jenis_kelamin'    => 'jenis kelamin',
            'nis_nim'          => 'NIS/NIM',
            'no_wa'            => 'no. WhatsApp',
            'foto_pas'         => 'foto pas',
            'asal_sekolah'     => 'asal sekolah / kampus',
            'pembimbing'       => 'pembimbing',
            'no_wa_pembimbing' => 'no. HP pembimbing',
            'cv'               => 'CV / portofolio',
            'surat_permohonan' => 'surat pengajuan',
        ];
    }

    public function messages(): array
    {
        return [
            'cv.required'            => 'CV / portofolio wajib diunggah (PDF) untuk pelamar Diploma/Sarjana.',
            'setuju_persyaratan.accepted' => 'Anda harus menyetujui persyaratan umum untuk mengirim lamaran.',
            'nis_nim.required'       => 'NIS/NIM wajib diisi (dipakai untuk login presensi).',
            'nis_nim.regex'          => 'NIS/NIM hanya boleh berisi huruf, angka, titik, garis miring, atau strip (tanpa spasi).',
            'nis_nim.unique'         => 'NIS/NIM ini sudah terdaftar sebagai peserta. Periksa kembali penulisannya.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in'       => 'Jenis kelamin tidak valid.',
            'no_wa.regex'            => 'No. WhatsApp harus 6-15 digit angka (contoh: 081234567890).',
            'no_wa_pembimbing.regex' => 'No. HP pembimbing harus 6-15 digit angka (contoh: 081234567890).',
        ];
    }
}