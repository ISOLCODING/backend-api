<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Override authenticate to add logging for debugging.
     */
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        Log::info('Login Attempt Start:', ['email' => $data['email']]);

        try {
            // Panggil base authenticate (ini yang melakukan Auth::attempt)
            $response = parent::authenticate();
            
            Log::info('Login Attempt Success (Response Returned):', ['email' => $data['email']]);
            
            return $response;
            
        } catch (ValidationException $e) {
            Log::error('Login Validation Failed:', [
                'email'  => $data['email'],
                'errors' => $e->errors(),
            ]);
            
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login Unexpected Exception:', [
                'email'   => $data['email'],
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}
