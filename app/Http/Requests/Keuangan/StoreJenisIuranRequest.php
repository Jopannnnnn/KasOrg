<?php

namespace App\Http\Requests\Keuangan;

use App\Models\JenisIuran;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreJenisIuranRequest extends KeuanganRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', JenisIuran::class);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100', Rule::unique('jenis_iuran', 'nama')],
            'nominal' => ['required', 'numeric', 'regex:/^\d{1,13}(\.\d{1,2})?$/', 'gt:0', 'max:9999999999999.99'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
            'aktif' => ['sometimes', 'required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + ['boolean' => ':attribute harus berupa pilihan aktif atau nonaktif.'];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['aktif' => 'Status jenis iuran'];
    }

    public function listingParameters(): array
    {
        // Hanya konteks daftar lokal yang valid; bukan URL redirect dari klien.
        return Validator::make(Arr::only($this->query(), ['q', 'status', 'page']), [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:aktif,nonaktif'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ])->valid();
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.iuran.jenis.index', $this->listingParameters());
    }

    protected function prepareForValidation(): void
    {
        if ($this->isMethod('GET')) {
            return;
        }
        $type = $this->route('jenisIuran');
        $modal = $type instanceof JenisIuran
            ? ($this->isMethod('DELETE') ? 'hapus-jenis-' : 'edit-jenis-').$type->id
            : 'modal-jenis';
        // ID modal berasal dari route binding, bukan penanda yang mungkin hilang.
        $this->merge(['_modal' => $modal]);
        // Handler validasi mem-flash input dari HTTP request asli, bukan salinan Form Request.
        request()->merge(['_modal' => $modal]);
    }
}
