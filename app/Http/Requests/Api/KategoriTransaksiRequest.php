<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Keuangan\KeuanganRequest;

class KategoriTransaksiRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return true; // Auth, status aktif dan role wajib pada route.
    }

    public function rules(): array
    {
        return ['jenis' => ['nullable', 'in:pemasukan,pengeluaran']];
    }

    public function attributes(): array
    {
        return ['jenis' => 'Jenis kategori'];
    }
}
