<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Kolom role dipakai untuk membedakan user biasa dan admin.
         * Default-nya user agar akun register publik tidak otomatis menjadi admin.
         */
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)
                    ->default('user')
                    ->after('date_format')
                    ->index();
            });
        }

        /*
         * Pengaman untuk data lama.
         * Semua akun lama yang belum punya role akan dianggap user biasa.
         */
        DB::table('users')
            ->whereNull('role')
            ->update(['role' => 'user']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};