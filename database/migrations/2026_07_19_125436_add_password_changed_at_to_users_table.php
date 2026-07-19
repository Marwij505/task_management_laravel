<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan informasi waktu terakhir password diubah.
     *
     * Kolom ini tidak menyimpan password asli.
     * Kolom hanya menyimpan tanggal dan waktu perubahan password.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_changed_at')
                ->nullable()
                ->after('password');
        });

        /*
         * Data lama belum memiliki password_changed_at.
         * Gunakan updated_at sebagai nilai awal jika tersedia.
         */
        DB::table('users')
            ->whereNull('password_changed_at')
            ->update([
                'password_changed_at' => DB::raw('COALESCE(updated_at, created_at)'),
            ]);
    }

    /**
     * Menghapus kolom ketika migration di-rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_changed_at');
        });
    }
};