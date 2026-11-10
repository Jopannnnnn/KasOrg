<?php

namespace App\Http\Requests\Anggota;

use App\Models\User;
use App\Support\DemoAccountProtection;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

abstract class SaveAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $anggota = $this->route('anggota');

        return $anggota instanceof User
            ? Gate::allows('update', $anggota)
            : Gate::allows('create', User::class);
    }

    protected function identityRules(): array
    {
        $anggota = $this->route('anggota');
        $nim = Rule::unique('users', 'nim');
        $email = Rule::unique('users', 'email');

        if ($anggota instanceof User) {
            // ID berasal dari route model binding, tidak pernah dari input form.
            $nim->ignore($anggota);
            $email->ignore($anggota);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/', $nim, Rule::notIn(DemoAccountProtection::NIMS)],
            'email' => ['required', 'string', 'email', 'max:255', $email, Rule::notIn(DemoAccountProtection::EMAILS)],
            'status_akun' => ['required', Rule::in([User::STATUS_AKTIF, User::STATUS_NONAKTIF])],
            'role_id' => ['prohibited'],
            'role' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama anggota wajib diisi.',
            'name.string' => 'Nama anggota harus berupa teks.',
            'name.max' => 'Nama anggota maksimal 255 karakter.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.string' => 'NIM harus berupa teks angka.',
            'nim.max' => 'NIM maksimal 20 karakter.',
            'nim.regex' => 'NIM hanya boleh berisi angka.',
            'nim.unique' => 'NIM sudah digunakan oleh akun lain, termasuk akun yang diarsipkan.',
            'nim.not_in' => 'NIM tersebut dilindungi untuk akun demo utama.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan oleh akun lain, termasuk akun yang diarsipkan.',
            'email.not_in' => 'Email tersebut dilindungi untuk akun demo utama.',
            'status_akun.required' => 'Status akun wajib dipilih.',
            'status_akun.in' => 'Status akun harus aktif atau nonaktif.',
            'role_id.prohibited' => 'Role ditentukan sistem dan tidak boleh diubah melalui form anggota.',
            'role.prohibited' => 'Role ditentukan sistem dan tidak boleh diubah melalui form anggota.',
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal 12 karakter.',
            'password.max' => 'Kata sandi maksimal 72 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.password.mixed' => 'Kata sandi harus mengandung huruf besar dan huruf kecil.',
            'password.password.numbers' => 'Kata sandi harus mengandung angka.',
            'password.password.symbols' => 'Kata sandi harus mengandung simbol.',
            'password.prohibited' => 'Kata sandi tidak dapat diganti melalui edit profil anggota.',
            'password_confirmation.prohibited' => 'Kata sandi tidak dapat diganti melalui edit profil anggota.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    public function rememberFormContext(): void
    {
        $this->session()->flash('anggota_form', [
            'mode' => $this->route('anggota') instanceof User ? 'edit' : 'create',
            'id' => $this->route('anggota')?->getKey(),
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        $this->rememberFormContext();
        parent::failedValidation($validator);
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.anggota');
    }
}
