<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ============================================================
     * FLOWLIST MK2 - SUBTASKS TABLE
     * ============================================================
     *
     * Satu task dapat memiliki banyak subtask/checklist.
     *
     * Contoh:
     *
     * Task:
     * "Finish Flowlist MK2"
     *
     * Subtasks:
     * 1. Create migration
     * 2. Create model
     * 3. Create controller
     * 4. Testing
     */
    public function up(): void
    {
        Schema::create('subtasks', function (Blueprint $table) {

            /*
             * Primary Key.
             */
            $table->id();

            /*
             * Foreign key menuju tabel tasks.
             *
             * Jika task utama dihapus,
             * seluruh subtask miliknya otomatis ikut terhapus.
             */
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            /*
             * Isi checklist.
             */
            $table->string('title', 180);

            /*
             * Status checkbox:
             *
             * false = belum selesai
             * true  = selesai
             */
            $table->boolean('is_completed')
                ->default(false);

            /*
             * Posisi subtask.
             *
             * Digunakan untuk fitur Move Up / Move Down.
             */
            $table->unsignedInteger('position')
                ->default(1);

            /*
             * Menyimpan kapan subtask diselesaikan.
             *
             * NULL berarti belum selesai.
             */
            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamps();

            /*
             * Index untuk mempercepat pengambilan
             * subtask berdasarkan task dan posisi.
             */
            $table->index(
                ['task_id', 'position'],
                'subtasks_task_position_index'
            );

            $table->index(
                ['task_id', 'is_completed'],
                'subtasks_task_completed_index'
            );
        });
    }


    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtasks');
    }
};