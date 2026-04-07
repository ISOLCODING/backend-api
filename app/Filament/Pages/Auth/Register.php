<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Lengkap')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(User::class, 'email')
                ->maxLength(255),

            TextInput::make('phone')
                ->label('No. Telepon')
                ->tel()
                ->maxLength(15)
                ->nullable(),

            Select::make('role')
                ->label('Role')
                ->options([
                    'admin'   => 'Admin Toko',
                    'cashier' => 'Kasir',
                ])
                ->default('admin')
                ->required(),

            TextInput::make('pin_code')
                ->label('PIN Code')
                ->helperText('6 digit PIN untuk login cepat (opsional).')
                ->maxLength(6)
                ->minLength(6)
                ->password()
                ->nullable(),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->required()
                ->rule(Password::default()),

            TextInput::make('password_confirmation')
                ->label('Konfirmasi Password')
                ->password()
                ->required()
                ->same('password'),
        ]);
    }

    /**
     * Override to handle extra fields like pin_code and role.
     */
    protected function handleRegistration(array $data): User
    {
        return User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'], // Password cast 'hashed' in User model
            'phone'     => $data['phone'] ?? null,
            'role'      => $data['role'] ?? 'admin',
            'pin_code'  => $data['pin_code'] ?? null, // Pin code cast 'hashed' in User model
            'is_active' => true,
        ]);
    }
}
