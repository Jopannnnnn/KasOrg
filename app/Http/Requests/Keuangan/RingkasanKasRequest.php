<?php

namespace App\Http\Requests\Keuangan;

use App\Models\Role;
use Illuminate\Validation\Rule;

class RingkasanKasRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAktif() && in_array($this->user()->role?->kode, [Role::ADMIN, Role::ANGGOTA], true);
    }

    public function rules(): array
    {
        return [
            'periode_id' => ['nullable', Rule::when($this->input('periode_id') !== 'semua', ['integer', 'min:1', 'exists:periode,id'])],
            'jenis' => ['nullable', 'in:pemasukan,pengeluaran'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['jenis' => 'Jenis transaksi'];
    }

    protected function getRedirectUrl(): string
    {
        return route($this->route()->getName());
    }
}
