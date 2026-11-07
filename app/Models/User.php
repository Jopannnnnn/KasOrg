<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_NONAKTIF = 'nonaktif';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nim',
        'email',
        'password',
        'role_id',
        'status_akun',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function tagihanIuran(): HasMany
    {
        return $this->hasMany(TagihanIuran::class);
    }

    public function pembayaranDiajukan(): HasMany
    {
        return $this->hasMany(PembayaranIuran::class, 'diajukan_oleh');
    }

    public function pembayaranDiverifikasi(): HasMany
    {
        return $this->hasMany(PembayaranIuran::class, 'diverifikasi_oleh');
    }

    public function transaksiDibuat(): HasMany
    {
        return $this->hasMany(TransaksiKas::class, 'dibuat_oleh');
    }

    public function periodeDitutup(): HasMany
    {
        return $this->hasMany(Periode::class, 'ditutup_oleh');
    }

    public function isAdmin(): bool
    {
        return $this->role?->kode === Role::ADMIN;
    }

    public function isAktif(): bool
    {
        return $this->status_akun === self::STATUS_AKTIF;
    }
}
