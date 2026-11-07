<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PembayaranIuran extends Model
{
    use HasFactory;

    public const METODE_TUNAI = 'tunai';

    public const METODE_TRANSFER = 'transfer';

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'pembayaran_iuran';

    protected $fillable = [
        'tagihan_iuran_id',
        'diajukan_oleh',
        'metode',
        'nominal',
        'tanggal_bayar',
        'bukti_path',
        'status',
        'diverifikasi_oleh',
        'diverifikasi_at',
        'alasan_penolakan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal_bayar' => 'date',
            'diverifikasi_at' => 'datetime',
        ];
    }

    public function tagihanIuran(): BelongsTo
    {
        return $this->belongsTo(TagihanIuran::class);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh')->withTrashed();
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh')->withTrashed();
    }

    public function transaksiKas(): HasOne
    {
        return $this->hasOne(TransaksiKas::class);
    }
}
