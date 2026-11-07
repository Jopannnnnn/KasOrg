<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TagihanIuran extends Model
{
    use HasFactory;

    public const BELUM_LUNAS = 'belum_lunas';

    public const LUNAS = 'lunas';

    protected $table = 'tagihan_iuran';

    protected $fillable = [
        'user_id',
        'jenis_iuran_id',
        'periode_id',
        'nominal',
        'jatuh_tempo',
        'status',
        'lunas_at',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'jatuh_tempo' => 'date',
            'lunas_at' => 'datetime',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function jenisIuran(): BelongsTo
    {
        return $this->belongsTo(JenisIuran::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function pembayaranIuran(): HasMany
    {
        return $this->hasMany(PembayaranIuran::class);
    }

    public function transaksiKas(): HasOne
    {
        return $this->hasOne(TransaksiKas::class);
    }
}
