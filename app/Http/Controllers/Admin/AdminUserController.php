<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        /*
         * Ambil input pencarian dari URL.
         * Contoh: /admin/users?search=marcell&role=admin
         */
        $search = trim((string) $request->query('search', ''));
        $role = trim((string) $request->query('role', ''));

        /*
         * Query utama untuk daftar user.
         * withCount('tasks') menghitung jumlah task milik setiap user.
         */
        $users = User::query()
            ->withCount('tasks')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, [User::ROLE_ADMIN, User::ROLE_USER], true), function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderByRaw("role = 'admin' DESC")
            ->latest('created_at')
            ->paginate(8)
            ->withQueryString();

        /*
         * Statistik kecil untuk bagian atas halaman.
         */
        $totalUsers = User::count();
        $totalAdmins = User::where('role', User::ROLE_ADMIN)->count();
        $totalRegularUsers = User::where('role', User::ROLE_USER)->count();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'selectedRole' => $role,
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalRegularUsers' => $totalRegularUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /*
         * Admin boleh membuat user baru.
         * Password wajib diisi karena akun dibuat langsung dari panel admin.
         */
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'password' => ['required', 'string', 'min:4', 'max:255', 'confirmed'],
        ]);

        $createdUser = User::create([
            'name' => $validated['full_name'],
            'full_name' => $validated['full_name'],
            'username' => $validated['username'],
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ]);

        /*
        * Catat aktivitas create user.
        */
        ActivityLogService::log(
            module: 'admin_users',
            action: 'create',
            description: 'Admin created user account: '.$createdUser->email,
            properties: [
                'created_user_id' => $createdUser->id,
                'email' => $createdUser->email,
                'username' => $createdUser->username,
                'role' => $createdUser->role,
            ],
            targetUserId: $createdUser->id
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User has been created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        /*
         * Validasi update user.
         * Rule unique memakai ignore agar user tetap boleh menyimpan email/username miliknya sendiri.
         */
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
        ]);

        /*
         * Admin tidak boleh menurunkan role dirinya sendiri.
         * Ini mencegah kondisi admin terkunci dari halaman admin.
         */
        if ($request->user()->id === $user->id && $validated['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'You cannot remove your own admin role.');
        }

        /*
         * Sistem harus selalu punya minimal satu admin.
         */
        if (
            $user->isAdmin()
            && $validated['role'] === User::ROLE_USER
            && User::where('role', User::ROLE_ADMIN)->count() <= 1
        ) {
            return back()->with('error', 'The system must have at least one admin.');
        }

        $before = [
            'full_name' => $user->full_name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $user->update([
            'name' => $validated['full_name'],
            'full_name' => $validated['full_name'],
            'username' => $validated['username'],
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
        ]);

        /*
        * Catat aktivitas update user.
        */
        ActivityLogService::log(
            module: 'admin_users',
            action: 'update',
            description: 'Admin updated user account: '.$user->email,
            properties: [
                'updated_user_id' => $user->id,
                'before' => $before,
                'after' => [
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
            targetUserId: $user->id
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User has been updated successfully.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        /*
         * Password user bisa di-reset oleh admin.
         * confirmed berarti field password_confirmation harus sama.
         */
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:4', 'max:255', 'confirmed'],
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
            'remember_token' => null,
        ])->save();

        /*
        * Catat aktivitas reset password.
        */
        ActivityLogService::log(
            module: 'admin_users',
            action: 'reset_password',
            description: 'Admin reset password for user: '.$user->email,
            properties: [
                'target_user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'password_changed_at' => $user->password_changed_at?->toDateTimeString(),
            ],
            targetUserId: $user->id
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User password has been reset successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        /*
        * Admin tidak boleh menghapus akun sendiri.
        */
        if ($request->user()->id === $user->id) {
            return back()->with(
                'error',
                'You cannot delete your own account.'
            );
        }

        /*
        * Sistem harus memiliki minimal satu admin.
        */
        if (
            $user->isAdmin()
            && User::where('role', User::ROLE_ADMIN)->count() <= 1
        ) {
            return back()->with(
                'error',
                'The system must have at least one admin.'
            );
        }

        /*
        * Simpan informasi sebelum user dan task-nya dihapus.
        */
        $deletedUserData = [
            'deleted_user_id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'role' => $user->role,
            'tasks_count' => $user->tasks()->count(),
        ];

        $deletedEmail = $user->email;

        /*
        * Hapus user setelah data audit disimpan ke variabel.
        */
        $user->delete();

        /*
        * targetUserId harus null karena user target sudah dihapus.
        * ID lama tetap tersimpan pada properties.
        */
        ActivityLogService::log(
            module: 'admin_users',
            action: 'delete',
            description: 'Admin deleted user account: '.$deletedEmail,
            properties: $deletedUserData,
            targetUserId: null
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User has been deleted successfully.');
    }
}