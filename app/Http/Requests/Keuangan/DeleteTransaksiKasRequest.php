<?php

namespace App\Http\Requests\Keuangan;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class DeleteTransaksiKasRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return Gate::allows('delete', $this->route('transaksi'));
    }

    public function rules(): array
    {
        return ['konfirmasi' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return parent::messages() + ['konfirmasi.accepted' => 'Konfirmasi penghapusan transaksi wajib dicentang.', 'konfirmasi.required' => 'Konfirmasi penghapusan transaksi wajib dicentang.'];
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.transaksi', Arr::only($this->query(), ['periode_id', 'jenis', 'q', 'page']));
    }
}
