<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('nim', 20)->after('name')->unique();
            $table->foreignId('role_id')
                ->after('password')
                ->constrained('roles')
                ->restrictOnDelete();
            $table->enum('status_akun', ['aktif', 'nonaktif'])
                ->default('aktif')
                ->after('role_id')
                ->index();
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'nim',
                'role_id',
                'status_akun',
                'last_login_at',
                'deleted_at',
            ]);
        });
    }
};
