<?php

namespace App\Http\Requests\Anggota;

use Illuminate\Validation\Rules\Password;

class StoreAnggotaRequest extends SaveAnggotaRequest
{
    public function rules(): array
    {
        return [
            ...$this->identityRules(),
            'password' => [
                'required', 'string', 'max:72', 'confirmed',
                function ($attribute, $value, $fail): void {
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Kata sandi maksimal 72 byte, termasuk karakter multibyte.');
                    }
                },
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }
}
