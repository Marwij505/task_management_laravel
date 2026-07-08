<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /*
     * Role resmi yang dipakai sistem.
     * Pakai constant agar tidak salah tulis string role di controller atau middleware.
     */
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'username',
        'full_name',
        'email',
        'avatar_path',
        'password',
        'role',
        'email_notifications',
        'task_reminders',
        'weekly_report',
        'theme',
        'language',
        'date_format',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_notifications' => 'boolean',
            'task_reminders' => 'boolean',
            'weekly_report' => 'boolean',
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /*
     * Cek apakah user aktif adalah admin.
     * Method ini akan dipakai oleh middleware dan redirect login.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /*
     * Cek apakah user aktif adalah user biasa.
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}