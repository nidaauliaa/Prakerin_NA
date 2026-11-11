<?php

namespace App\Http\Request;

use App\Models\Lowongan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/** Langkah 3: data diri + berkas. Posisi & periode diambil dari session (hasil langkah 1). */
class StoreLamaranRequest extends FormRequest
{
    private const HP = '/^(\+?62|0)8\d{8,12}$/';

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
        ]);
    }

    public function rules(): array
    {
        $isSmk = $this->lowongan()->tipe_magang === 'smk';

        return [
            'nama'             => ['required', 'string', 'max:150'],
            'no_wa'            => ['required', 'regex:' . self::HP],
            'alamat'           => ['required', 'string', 'max:1000'],
            'foto_pas'         => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'asal_sekolah'     => ['required', 'string', 'max:150'],
            'jurusan'          => ['required', 'string', 'max:150'],

            // SMK: pembimbing wajib. Kuliah: opsional.
            'pembimbing'       => [$isSmk ? 'required' : 'nullable', 'string', 'max:150'],
            'no_wa_pembimbing' => [$isSmk ? 'required' : 'nullable', 'regex:' . self::HP],

            // Opsional untuk keduanya
            'cv'               => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama'             => 'nama lengkap',
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
            'no_wa.regex'            => 'Format no. WhatsApp tidak valid (contoh: 081234567890).',
            'no_wa_pembimbing.regex' => 'Format no. HP pembimbing tidak valid (contoh: 081234567890).',
        ];
    }
}