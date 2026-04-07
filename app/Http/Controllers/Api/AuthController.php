<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Login dengan email & password.
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                email    : $request->email,
                password : $request->password,
                ip       : $request->ip(),
            );

            return $this->successResponse($result, 'Login berhasil.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Email atau password salah.', 401);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return $this->errorResponse('Terjadi kesalahan server.', 500);
        }
    }

    /**
     * Login cepat dengan PIN code.
     * POST /api/v1/auth/login-pin
     */
    public function loginWithPin(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['nullable', 'email'],
            'pin_code' => ['required', 'string', 'size:6'],
        ]);

        try {
            $result = $this->authService->loginWithPin(
                email   : $request->email,
                pinCode : $request->pin_code,
            );

            return $this->successResponse($result, 'Login dengan PIN berhasil.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Email atau PIN salah.', 401);
        } catch (\Exception $e) {
            Log::error('PIN login error: ' . $e->getMessage());
            return $this->errorResponse('Terjadi kesalahan server.', 500);
        }
    }

    /**
     * Logout — hapus current token.
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'Berhasil logout.');
    }

    /**
     * Ambil data user yang sedang login.
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            new UserResource($request->user()),
            'Data user berhasil diambil.'
        );
    }
}
