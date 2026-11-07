<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_iuran', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('jenis_iuran_id')->constrained('jenis_iuran')->restrictOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->restrictOnDelete();
            $table->decimal('nominal', 15, 2)->unsigned();
            $table->date('jatuh_tempo')->nullable();
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas')->index();
            $table->timestamp('lunas_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'jenis_iuran_id', 'periode_id'],
                'tagihan_user_jenis_periode_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_iuran');
    }
};
