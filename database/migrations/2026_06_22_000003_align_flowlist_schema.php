<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->alignUsersTable();
        $this->alignTasksTable();
        $this->alignTaskTagsTable();
    }

    public function down(): void
    {
        // Sengaja tidak menghapus tabel/kolom agar data hasil impor manual tetap aman.
    }

    private function alignUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('username', 100)->unique();
                $table->string('full_name', 150)->nullable();
                $table->string('email', 150)->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('avatar_path')->nullable();
                $table->string('password');
                $table->boolean('email_notifications')->default(true);
                $table->boolean('task_reminders')->default(true);
                $table->boolean('weekly_report')->default(false);
                $table->string('theme', 20)->default('Light');
                $table->string('language', 30)->default('English');
                $table->string('date_format', 30)->default('MM/DD/YYYY');
                $table->rememberToken();
                $table->timestamps();
            });

            return;
        }

        $columns = [
            'name' => fn (Blueprint $table) => $table->string('name')->nullable()->after('id'),
            'username' => fn (Blueprint $table) => $table->string('username', 100)->nullable()->after('id'),
            'full_name' => fn (Blueprint $table) => $table->string('full_name', 150)->nullable()->after('username'),
            'email_verified_at' => fn (Blueprint $table) => $table->timestamp('email_verified_at')->nullable()->after('email'),
            'avatar_path' => fn (Blueprint $table) => $table->string('avatar_path')->nullable()->after('email'),
            'remember_token' => fn (Blueprint $table) => $table->rememberToken(),
            'email_notifications' => fn (Blueprint $table) => $table->boolean('email_notifications')->default(true),
            'task_reminders' => fn (Blueprint $table) => $table->boolean('task_reminders')->default(true),
            'weekly_report' => fn (Blueprint $table) => $table->boolean('weekly_report')->default(false),
            'theme' => fn (Blueprint $table) => $table->string('theme', 20)->default('Light'),
            'language' => fn (Blueprint $table) => $table->string('language', 30)->default('English'),
            'date_format' => fn (Blueprint $table) => $table->string('date_format', 30)->default('MM/DD/YYYY'),
        ];

        foreach ($columns as $column => $callback) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', $callback);
            }
        }
    }

    private function alignTasksTable(): void
    {
        if (! Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('title', 150);
                $table->text('description')->nullable();
                $table->enum('status', ['pending', 'in-progress', 'completed'])->default('pending');
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->string('category', 100)->nullable();
                $table->date('deadline')->nullable();
                $table->integer('progress')->default(0);
                $table->string('assignee', 100)->nullable();
                $table->timestamps();
            });

            return;
        }

        $columns = [
            'user_id' => fn (Blueprint $table) => $table->unsignedBigInteger('user_id')->index(),
            'title' => fn (Blueprint $table) => $table->string('title', 150),
            'description' => fn (Blueprint $table) => $table->text('description')->nullable(),
            'status' => fn (Blueprint $table) => $table->enum('status', ['pending', 'in-progress', 'completed'])->default('pending'),
            'priority' => fn (Blueprint $table) => $table->enum('priority', ['low', 'medium', 'high'])->default('medium'),
            'category' => fn (Blueprint $table) => $table->string('category', 100)->nullable(),
            'deadline' => fn (Blueprint $table) => $table->date('deadline')->nullable(),
            'progress' => fn (Blueprint $table) => $table->integer('progress')->default(0),
            'assignee' => fn (Blueprint $table) => $table->string('assignee', 100)->nullable(),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ];

        foreach ($columns as $column => $callback) {
            if (! Schema::hasColumn('tasks', $column)) {
                Schema::table('tasks', $callback);
            }
        }
    }

    private function alignTaskTagsTable(): void
    {
        if (! Schema::hasTable('task_tags')) {
            Schema::create('task_tags', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('tag_name', 100);
                $table->timestamp('created_at')->useCurrent();
            });

            return;
        }

        $columns = [
            'task_id' => fn (Blueprint $table) => $table->unsignedBigInteger('task_id')->index(),
            'tag_name' => fn (Blueprint $table) => $table->string('tag_name', 100),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->useCurrent(),
        ];

        foreach ($columns as $column => $callback) {
            if (! Schema::hasColumn('task_tags', $column)) {
                Schema::table('task_tags', $callback);
            }
        }
    }
};
