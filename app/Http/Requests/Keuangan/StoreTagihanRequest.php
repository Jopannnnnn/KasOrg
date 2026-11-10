<?php

namespace App\Http\Requests\Keuangan;

use App\Models\TagihanIuran;

class StoreTagihanRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', TagihanIuran::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'jenis_iuran_id' => ['required', 'integer', 'exists:jenis_iuran,id'],
            'periode_id' => ['required', 'integer', 'exists:periode,id'],
            'jatuh_tempo' => ['nullable', 'date_format:Y-m-d'],
            'nominal' => ['prohibited'], 'status' => ['prohibited'],
        ];
    }
}
