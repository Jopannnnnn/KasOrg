<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periode extends Model
{
    use HasFactory;

    public const TERBUKA = 'terbuka';

    public const DITUTUP = 'ditutup';

    protected $table = 'periode';

    protected $fillable = [
        'tahun',
        'bulan',
        'tanggal_mulai',
        'tanggal_selesai',
        'saldo_awal',
        'saldo_akhir_snapshot',
        'status',
        'ditutup_oleh',
        'ditutup_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'saldo_awal' => 'decimal:2',
            'saldo_akhir_snapshot' => 'decimal:2',
            'ditutup_at' => 'datetime',
        ];
    }

    public function penutup(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditutup_oleh');
    }

    public function tagihanIuran(): HasMany
    {
        return $this->hasMany(TagihanIuran::class);
    }
}
