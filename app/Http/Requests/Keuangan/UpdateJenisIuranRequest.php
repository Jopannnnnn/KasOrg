<?php

namespace App\Http\Requests\Keuangan;

use Illuminate\Validation\Rule;

class UpdateJenisIuranRequest extends StoreJenisIuranRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('jenisIuran'));
    }

    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'nama' => ['required', 'string', 'max:100', Rule::unique('jenis_iuran', 'nama')->ignore($this->route('jenisIuran'))],
            'aktif' => ['required', 'boolean'],
        ]);
    }
}
