<?php

namespace App\Http\Requests\Keuangan;

use App\Models\TransaksiKas;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SaveTransaksiKasRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaksi');

        return $transaction ? Gate::allows('update', $transaction) : Gate::allows('create', TransaksiKas::class);
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in([TransaksiKas::PEMASUKAN, TransaksiKas::PENGELUARAN])],
            'kategori_transaksi_id' => ['required', 'integer', Rule::exists('kategori_transaksi', 'id')->where('jenis', is_string($this->input('jenis')) ? $this->input('jenis') : '')],
            'periode_id' => ['required', 'integer', 'exists:periode,id'],
            'tanggal' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            // Batasi bentuk dan panjang SEBELUM BCMath; tidak mengubah uang menjadi float.
            'nominal' => ['bail', 'required', 'string', 'max:16', 'regex:/\A[0-9]{1,13}(?:\.[0-9]{1,2})?\z/', function ($attribute, $value, $fail) {
                if (bccomp($value, '0', 2) <= 0) {
                    $fail('Nominal harus lebih dari nol.');
                }
            }],
            'keterangan' => ['required', 'string', 'max:255'],
            'sumber' => ['prohibited'], 'tagihan_iuran_id' => ['prohibited'], 'pembayaran_iuran_id' => ['prohibited'],
            'dibuat_oleh' => ['prohibited'], 'diubah_oleh' => ['prohibited'], 'deleted_at' => ['prohibited'],
            'created_at' => ['prohibited'], 'updated_at' => ['prohibited'], 'id' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['jenis' => 'Jenis transaksi', 'tanggal' => 'Tanggal transaksi', 'kategori_transaksi_id' => 'Kategori transaksi', 'keterangan' => 'Keterangan'];
    }

    protected function prepareForValidation(): void
    {
        // Form browser mengirim string. JSON integer diperbolehkan tanpa konversi float.
        if (is_int($this->input('nominal'))) {
            $this->merge(['nominal' => (string) $this->input('nominal')]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.transaksi', Arr::only($this->query(), ['periode_id', 'jenis', 'q', 'page']));
    }
}
