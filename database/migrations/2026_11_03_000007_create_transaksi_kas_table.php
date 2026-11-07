<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_kas', function (Blueprint $table): void {
            $table->id();
            $table->date('tanggal')->index();
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->foreignId('kategori_transaksi_id');
            $table->foreignId('tagihan_iuran_id')
                ->nullable()
                ->unique()
                ->constrained('tagihan_iuran')
                ->restrictOnDelete();
            $table->foreignId('pembayaran_iuran_id')
                ->nullable()
                ->unique()
                ->constrained('pembayaran_iuran')
                ->restrictOnDelete();
            $table->string('keterangan', 255);
            $table->decimal('nominal', 15, 2)->unsigned();
            $table->enum('sumber', ['manual', 'pembayaran_iuran'])->default('manual');
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['kategori_transaksi_id', 'jenis'])
                ->references(['id', 'jenis'])
                ->on('kategori_transaksi')
                ->restrictOnDelete();
            $table->index(['jenis', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_kas');
    }
};
