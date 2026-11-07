<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.string' => 'Alamat email harus berupa teks.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
        ];
    }

    public function authenticate(): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Provider Eloquent Laravel memeriksa hash; status akun bagian dari kredensial.
        if (! Auth::attempt([
            'email' => $this->string('email')->toString(),
            'password' => $this->string('password')->toString(),
            'status_akun' => User::STATUS_AKTIF,
        ])) {
            RateLimiter::hit($this->throttleKey(), 60);

            // Pesan sama agar tidak mengungkap keberadaan atau status suatu akun.
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai, atau akun tidak aktif.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function throttleKey(): string
    {
        return 'login:'.hash('sha256', Str::lower($this->string('email')->toString()).'|'.$this->ip());
    }
}
