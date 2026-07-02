<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        return match ((string) $request->input('action')) {
            'update_profile' => $this->updateProfile($request),
            'update_notifications' => $this->updateNotifications($request),
            'update_preferences' => $this->updatePreferences($request),
            'update_password' => $this->updatePassword($request),
            default => response()->json([
                'success' => false,
                'message' => 'Invalid action.',
            ], 400),
        };
    }

    private function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $avatarPath = $user->avatar_path;

        if ($request->hasFile('avatar')) {
            if (is_string($avatarPath) && str_starts_with($avatarPath, '/storage/')) {
                Storage::disk('public')->delete(substr($avatarPath, strlen('/storage/')));
            }

            $storedPath = $request->file('avatar')->store('avatars', 'public');
            $avatarPath = Storage::url($storedPath);
        }

        $fullName = trim((string) $request->input('full_name'));

        $user->update([
            'name' => $fullName,
            'full_name' => $fullName,
            'email' => strtolower(trim((string) $request->input('email'))),
            'avatar_path' => $avatarPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => (int) $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name ?: $user->username,
                'email' => $user->email,
                'avatar_path' => $user->avatar_path ?? '',
            ],
        ]);
    }

    private function updateNotifications(Request $request): JsonResponse
    {
        $request->user()->update([
            'email_notifications' => $request->boolean('email_notifications'),
            'task_reminders' => $request->boolean('task_reminders'),
            'weekly_report' => $request->boolean('weekly_report'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully.',
        ]);
    }

    private function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'theme' => ['required', Rule::in(['Light', 'Dark', 'System'])],
            'language' => ['required', Rule::in(['English', 'Indonesian'])],
            'date_format' => ['required', Rule::in(['MM/DD/YYYY', 'DD/MM/YYYY', 'YYYY-MM-DD'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $request->user()->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully.',
        ]);
    }

    private function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:4', 'max:255'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();

        if (! Hash::check((string) $request->input('current_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make((string) $request->input('new_password')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name ?: $user->username,
            'email' => $user->email,
            'avatar_path' => $user->avatar_path ?? '',
            'email_notifications' => (int) $user->email_notifications,
            'task_reminders' => (int) $user->task_reminders,
            'weekly_report' => (int) $user->weekly_report,
            'theme' => $user->theme ?: 'Light',
            'language' => $user->language ?: 'English',
            'date_format' => $user->date_format ?: 'MM/DD/YYYY',
            'created_at' => $user->created_at?->format('Y-m-d H:i:s') ?? '',
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
