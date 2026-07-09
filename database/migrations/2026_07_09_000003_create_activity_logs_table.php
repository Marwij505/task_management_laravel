<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel activity_logs.
     * Tabel ini menyimpan jejak aktivitas user dan admin.
     */
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();

                /*
                 * user_id adalah pelaku aktivitas.
                 * Contoh: admin yang mengedit user, user yang login, admin yang menghapus task.
                 */
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                /*
                 * target_user_id adalah user yang terdampak.
                 * Contoh: admin mereset password user A.
                 */
                $table->foreignId('target_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                /*
                 * module menjelaskan area fitur.
                 * Contoh: auth, admin_users, admin_tasks.
                 */
                $table->string('module', 60)->index();

                /*
                 * action menjelaskan jenis aksi.
                 * Contoh: login, logout, create, update, delete, reset_password.
                 */
                $table->string('action', 60)->index();

                /*
                 * description adalah kalimat singkat yang mudah dibaca admin.
                 */
                $table->text('description');

                /*
                 * properties menyimpan detail tambahan dalam bentuk JSON.
                 * Contoh: email user, role lama, role baru, task title, owner task.
                 */
                $table->json('properties')->nullable();

                /*
                 * Data teknis untuk kebutuhan audit sederhana.
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
     * Menghapus tabel jika migration di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};