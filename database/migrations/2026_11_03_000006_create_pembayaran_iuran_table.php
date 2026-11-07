<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_iuran', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tagihan_iuran_id')->constrained('tagihan_iuran')->restrictOnDelete();
            $table->foreignId('diajukan_oleh')->constrained('users')->restrictOnDelete();
            $table->enum('metode', ['tunai', 'transfer']);
            $table->decimal('nominal', 15, 2)->unsigned();
            $table->date('tanggal_bayar');
            $table->string('bukti_path', 255)->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])
                ->default('menunggu')
                ->index();
            $table->foreignId('diverifikasi_oleh')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->string('alasan_penolakan', 255)->nullable();
            $table->string('catatan', 255)->nullable();
            $table->timestamps();

            $table->index(['tagihan_iuran_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_iuran');
    }
};
