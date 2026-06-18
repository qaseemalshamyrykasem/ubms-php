<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['locale'] = $data['locale'] ?? 'ar';

        $user = User::create($data);
        $user->assignRole('student');

        // Attach student record if batch provided
        if (!empty($data['batch_id'])) {
            $batch = Batch::findOrFail($data['batch_id']);
            $studentId = $data['student_id'] ?? ($batch->code . '-' . str_pad((string) $batch->students()->count() + 1, 3, '0', STR_PAD_LEFT));
            Student::create([
                'user_id' => $user->id,
                'batch_id' => $batch->id,
                'student_id' => $studentId,
                'enrolled_at' => now(),
            ]);
        }

        AuditLog::record('register', $user);

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => __('auth.registered'),
            'user' => $this->userResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => [__('auth.inactive')],
            ]);
        }

        $tokenName = 'auth_token';
        if ($request->boolean('remember')) {
            $tokenName = 'auth_token_remember';
        }

        $expiresAt = $request->boolean('remember') ? now()->addDays(90) : now()->addDays(1);
        $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        AuditLog::record('login', $user);

        return response()->json([
            'message' => __('auth.login_success'),
            'user' => $this->userResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        AuditLog::record('logout', $user);
        return response()->json(['message' => __('auth.logged_out')]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userResource($request->user()),
        ]);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;
        return response()->json(['token' => $token]);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink($request->only('email'));
        return response()->json([
            'message' => __($status === Password::RESET_LINK_SENT ? 'auth.reset_link_sent' : 'auth.reset_link_failed'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
            AuditLog::record('password_reset', $user);
        });

        return response()->json([
            'message' => __($status === Password::PASSWORD_RESET ? 'auth.reset_success' : 'auth.reset_failed'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|string|max:255|nullable',
            'phone' => 'sometimes|string|max:30|nullable',
            'national_id' => 'sometimes|string|max:30|nullable',
            'avatar' => 'sometimes|image|max:2048|nullable',
            'locale' => 'sometimes|in:ar,en',
            'dark_mode' => 'sometimes|boolean',
            'timezone' => 'sometimes|string|max:60',
        ]);

        $user = $request->user();
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $old = $user->toArray();
        $user->update($data);
        AuditLog::record('profile_update', $user, $old, $user->toArray());

        return response()->json([
            'message' => __('auth.profile_updated'),
            'user' => $this->userResource($user->fresh()),
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth.current_password_incorrect')],
            ]);
        }

        $user->update(['password' => Hash::make($data['password'])]);
        AuditLog::record('password_change', $user);

        return response()->json(['message' => __('auth.password_changed')]);
    }

    private function userResource(User $user): array
    {
        $role = $user->primaryRole();
        $extras = [];

        if ($role === 'student' && $user->studentProfile) {
            $extras['student'] = [
                'id' => $user->studentProfile->id,
                'student_id' => $user->studentProfile->student_id,
                'batch' => $user->studentProfile->batch ? [
                    'id' => $user->studentProfile->batch->id,
                    'code' => $user->studentProfile->batch->code,
                    'name_ar' => $user->studentProfile->batch->name_ar,
                    'chain' => $user->studentProfile->batch->chainPath(),
                ] : null,
            ];
        } elseif ($role === 'representative' && $user->representativeProfile) {
            $extras['representative'] = [
                'id' => $user->representativeProfile->id,
                'batch' => $user->representativeProfile->batch ? [
                    'id' => $user->representativeProfile->batch->id,
                    'code' => $user->representativeProfile->batch->code,
                    'name_ar' => $user->representativeProfile->batch->name_ar,
                    'chain' => $user->representativeProfile->batch->chainPath(),
                ] : null,
            ];
        } elseif ($role === 'college_admin' && $user->collegeAdminProfile) {
            $extras['college_admin'] = [
                'college' => $user->collegeAdminProfile->college ? [
                    'id' => $user->collegeAdminProfile->college->id,
                    'name_ar' => $user->collegeAdminProfile->college->name_ar,
                ] : null,
            ];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'name_ar' => $user->name_ar,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'role' => $role,
            'status' => $user->status,
            'locale' => $user->locale,
            'dark_mode' => $user->dark_mode,
            'timezone' => $user->timezone,
            'telegram_connected' => $user->telegramConnected(),
            'email_verified' => !is_null($user->email_verified_at),
            'created_at' => $user->created_at,
            ...$extras,
        ];
    }
}
