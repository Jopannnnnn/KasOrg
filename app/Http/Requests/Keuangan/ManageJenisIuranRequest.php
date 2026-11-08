<?php

namespace App\Http\Requests\Keuangan;

use App\Models\JenisIuran;

class ManageJenisIuranRequest extends StoreJenisIuranRequest
{
    public function authorize(): bool
    {
        if ($this->isMethod('GET')) {
            return $this->user()->can('viewAny', JenisIuran::class);
        }

        return $this->user()->can($this->isMethod('DELETE') ? 'delete' : 'update', $this->route('jenisIuran'));
    }

    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [
                'q' => ['nullable', 'string', 'max:100'],
                'status' => ['nullable', 'in:aktif,nonaktif'],
                'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            ];
        }

        return $this->isMethod('DELETE') ? ['konfirmasi' => ['required', 'accepted']] : ['aktif' => ['required', 'boolean']];
    }

    public function messages(): array
    {
        return parent::messages() + ['accepted' => 'Konfirmasi penghapusan wajib dicentang.'];
    }

    protected function getRedirectUrl(): string
    {
        return $this->isMethod('GET') ? route('admin.iuran.jenis.index') : parent::getRedirectUrl();
    }
}
