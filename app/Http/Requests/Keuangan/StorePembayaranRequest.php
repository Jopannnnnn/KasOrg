<?php

namespace App\Http\Requests\Keuangan;

class StorePembayaranRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pay', $this->route('tagihan'));
    }

    public function rules(): array
    {
        return [
            'metode' => ['required', 'in:tunai,transfer'],
            'tanggal_bayar' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'bukti' => ['exclude_unless:metode,transfer', 'required_if:metode,transfer', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'dimensions:min_width=1,min_height=1,max_width=10000,max_height=10000', 'max:5120'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'nominal' => ['prohibited'], 'status' => ['prohibited'], 'bukti_path' => ['prohibited'],
            'diajukan_oleh' => ['prohibited'], 'diverifikasi_oleh' => ['prohibited'],
        ];
    }
}
