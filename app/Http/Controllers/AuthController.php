<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\DTO\LoginResourceDTO;
use App\DTO\RegisterResourceDTO;
use App\DTO\UserResourceDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $user = Auth::user();
        if ($user->two_factor_enabled) {
            return response()->json(['message' => 'Two-factor authentication required'], 403);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(new LoginResourceDTO($token), 200);
    }
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'birthday' => $data['birthday'],
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json((new RegisterResourceDTO(
            $user->username,
            $user->email,
            $user->birthday
        ))->toArray() + ['token' => $token], 201);
    }
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json(new UserResourceDTO(
            $user->id,
            $user->username,
            $user->email,
            $user->birthday
        ));
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
    public function tokens(Request $request)
    {
        $tokens = $request->user()->tokens()->pluck('name');
        return response()->json(['tokens' => $tokens]);
    }
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'All tokens revoked'], 200);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        return $fail('Current password is incorrect.');
                    }
                },
            ],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',      
                'regex:/[A-Z]/',      
                'regex:/[0-9]/',      
                'regex:/[@$!%*?&#]/',
            ],
        ]);
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);
        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function requestTwoFactorCode(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $email = $request->input('email');
        if ($user->email !== $email) {
            return response()->json(['message' => 'Invalid user credentials'], 403);
        }
        if ($user->two_factor_attempts > 3) {
            $lastAttempt = $user->two_factor_last_attempt_at;
            if ($lastAttempt && now()->diffInSeconds($lastAttempt) < 30) {
                return response()->json(['message' => 'Too many attempts. Please wait 30 seconds.'], 429);
            }
        }
        $code = $user->generateTwoFactorCode();
        \Log::info("Generated 2FA code for user {$user->id}: {$code}");
        return response()->json(['message' => 'New 2FA code generated']);
    }
    public function confirmTwoFactorCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);
        $user = User::where('email', $request->input('email'))->first();
        if (!$user->isTwoFactorCodeValid($request->input('code'))) {
            return response()->json(['message' => 'Invalid or expired 2FA code'], 400);
        }
        $user->invalidateTwoFactorCode();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => '2FA code confirmed',
            'token' => $token,
        ]);
    }
    public function toggleTwoFactorAuth(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'enable' => 'required|boolean',
        ]);
        $user = $request->user();
        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json(['message' => 'Invalid password'], 403);
        }
        if ($request->input('enable')) {
            $user->enableTwoFactorAuth();
        } else {
            $user->disableTwoFactorAuth();
        }
        return response()->json(['message' => '2FA status updated']);
    }
}