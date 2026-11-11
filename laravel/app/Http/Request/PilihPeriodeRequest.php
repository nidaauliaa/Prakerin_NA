<?php

namespace App\Http\Request;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Langkah 1 (modal Detail & Lamar): pelamar hanya memilih periode magang. */
class PilihPeriodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('lowongan')->bisaDilamar();
    }

    protected function getRedirectUrl(): string
    {
        return route('beranda') . '#lowongan';
    }

    public function rules(): array
    {
        return [
            'tgl_mulai'   => ['required', 'date', 'after_or_equal:today'],
            'tgl_selesai' => ['required', 'date', 'after:tgl_mulai'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $v) {
                if ($v->errors()->hasAny(['tgl_mulai', 'tgl_selesai'])) {
                    return;
                }

                $hari = Carbon::parse($this->tgl_mulai)->startOfDay()
                    ->diffInDays(Carbon::parse($this->tgl_selesai)->startOfDay());

                if ($hari < 90) {
                    $v->errors()->add('tgl_selesai', 'Durasi magang minimal 3 bulan (90 hari).');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return ['tgl_mulai' => 'tanggal mulai', 'tgl_selesai' => 'tanggal selesai'];
    }

    public function messages(): array
    {
        return [
            'tgl_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tgl_selesai.after'        => 'Tanggal selesai harus lebih besar dari tanggal mulai.',
        ];
    }
}