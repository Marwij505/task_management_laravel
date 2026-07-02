<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

FLOWLIST - LARAVEL 13
========================================

Paket ini mengubah aplikasi task_management manual menjadi struktur Laravel:
- Blade views untuk Login, Register, Forgot Password, Dashboard, Task List,
  Create/Update Task, Task Detail, Calendar, Statistics, dan Profile.
- Controller Laravel untuk autentikasi, task CRUD, dashboard, statistik, dan profil.
- Model Eloquent User, Task, dan TaskTag beserta relasinya.
- Route web + endpoint JSON berbasis session Laravel.
- CSRF untuk seluruh request POST.
- Upload avatar melalui disk public Laravel.
- Migration penyelaras skema yang aman terhadap tabel yang sudah ada.
- Smoke test untuk halaman dan CRUD task.

PERSIAPAN
---------
1. Pastikan Apache dan MySQL XAMPP aktif.
2. Database tujuan harus bernama task_management_laravel.
3. Data dari task_management_databases harus sudah diimpor seperti proses sebelumnya.
4. Jangan menjalankan php artisan migrate:fresh karena akan menghapus data.
5. Pastikan hasilnya bukan folder bertingkat. Contoh benar:
   task_management_laravel/app/Http/Controllers/AuthController.php
6. Periksa file .env:

   APP_NAME=Flowlist
   APP_URL=http://127.0.0.1:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=task_management_laravel
   DB_USERNAME=root
   DB_PASSWORD=

7. Dari terminal root project jalankan:

   php artisan optimize:clear
   php artisan migrate
   php artisan storage:link
   php artisan route:list

8. Pengujian otomatis bersifat opsional. JANGAN langsung menjalankan test dengan
   database utama karena test memakai RefreshDatabase. Untuk test yang aman:
   - Salin .env.testing.flowlist.example menjadi .env.testing
   - Jalankan: php artisan key:generate --env=testing
   - Setelah itu: php artisan test --filter=FlowlistSmokeTest

9. Jalankan website:

   php artisan serve

10. Buka:

   http://127.0.0.1:8000

URL UTAMA
---------
/login
/register
/forgot-password
/dashboard
/tasks
/tasks/create
/task-detail?id=ID_TASK
/calendar
/statistics
/profile

PENTING
-------
- Forgot Password masih mempertahankan perilaku aplikasi manual: email dan password
  baru dimasukkan langsung. Ini untuk demonstrasi lokal, bukan production.
- Upload avatar membutuhkan php artisan storage:link.
- Jika CSS tidak tampil, jalankan php artisan optimize:clear lalu Ctrl+F5.
- Jika muncul 419 Page Expired, pastikan views dan laravel-fetch.js dari paket ini
  sudah disalin dan APP_URL sesuai.
- Jika login gagal dan password akun lama tidak diketahui, gunakan Forgot Password
  untuk menetapkan password baru.
