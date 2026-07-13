<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel activity_logs.
     *
     * Tabel menyimpan:
     * - aktivitas autentikasi
     * - aktivitas task user biasa
     * - perubahan profile
     * - aktivitas pengelolaan user oleh admin
     * - aktivitas pengelolaan task oleh admin
     */
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();

                /*
                 * Actor atau pelaku aktivitas.
                 * Nullable karena register dan forgot password
                 * dapat dilakukan tanpa login.
                 */
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                /*
                 * User yang menjadi target aktivitas.
                 * Contoh: admin reset password user lain.
                 */
                $table->foreignId('target_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                /*
                 * Area fitur.
                 * Contoh: auth, user_tasks, profile,
                 * admin_users, admin_tasks.
                 */
                $table->string('module', 80)->index();

                /*
                 * Jenis aktivitas.
                 * Contoh: login, create, update, delete.
                 */
                $table->string('action', 80)->index();

                /*
                 * Kalimat yang mudah dibaca pada halaman admin.
                 */
                $table->text('description');

                /*
                 * Detail tambahan dalam format JSON.
                 * Password dan token tidak boleh dimasukkan.
                 */
                $table->json('properties')->nullable();

                /*
                 * Informasi teknis untuk kebutuhan audit.
                 */
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();

                $table->timestamps();

                $table->index(['module', 'action']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Menghapus tabel ketika rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};