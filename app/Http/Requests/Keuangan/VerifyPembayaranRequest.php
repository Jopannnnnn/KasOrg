<?php

namespace App\Http\Requests\Keuangan;

class VerifyPembayaranRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('verify', $this->route('pembayaran'));
    }

    public function rules(): array
    {
        return [
            'keputusan' => ['required', 'in:disetujui,ditolak'],
            'konfirmasi' => ['accepted_if:keputusan,disetujui'],
            'alasan_penolakan' => ['required_if:keputusan,ditolak', 'nullable', 'string', 'max:255'],
        ];
    }
}
