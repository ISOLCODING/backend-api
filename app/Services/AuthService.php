<?php

namespace App\Services;

use App\Models\StaffActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Login dengan email & password.
     * Kembalikan token Sanctum.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, string $ip): array
    {
        $user = User::where('email', $email)
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Hapus semua token lama sebelum buat token baru
        $user->tokens()->delete();
        $token = $user->createToken('kasirin-api', ['*'], now()->addDays(30))->plainTextToken;

        // Catat aktivitas login
        StaffActivityLog::log($user->id, 'login', properties: ['ip' => $ip]);

        return [
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->getRoleNames()->first() ?? $user->role,

                'phone' => $user->phone,
            ],
        ];
    }

    /**
     * Login cepat dengan PIN code (untuk kasir di tablet).
     *
     * @throws ValidationException
     */
    public function loginWithPin(?string $email, string $pinCode): array
    {
        $user = null;

        if ($email) {
            $user = User::where('email', $email)
                ->where('is_active', true)
                ->first();
        } else {
            // Find user by scanning all active users (since PIN is hashed)
            // Note: This is efficient enough for small user bases (typical for single POS)
            $activeUsers = User::where('is_active', true)->get();
            foreach ($activeUsers as $potentialUser) {
                if (Hash::check($pinCode, $potentialUser->pin_code)) {
                    $user = $potentialUser;
                    break;
                }
            }
        }

        if (! $user || ($email && ! Hash::check($pinCode, $user->pin_code))) {
            throw ValidationException::withMessages([
                'pin_code' => ['Email atau PIN salah.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('kasirin-pin', ['*'], now()->addHours(12))->plainTextToken;

        return [
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->getRoleNames()->first() ?? $user->role,
            ],
        ];
    }

    /**
     * Logout — hapus current token.
     */
    public function logout(User $user): void
    {
        StaffActivityLog::log($user->id, 'logout');
        $user->currentAccessToken()->delete();
    }
}
