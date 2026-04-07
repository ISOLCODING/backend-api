<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'      => 'Admin Toko',
                'email'     => 'admin@kasirin.test',
                'password'  => 'password',
                'role'      => 'admin',
                'pin_code'  => '111111',
                'phone'     => '081234567890',
                'is_active' => true,
            ],
            [
                'name'      => 'Kasir 1',
                'email'     => 'kasir@kasirin.test',
                'password'  => 'password',
                'role'      => 'kasir',
                'pin_code'  => '222222',
                'phone'     => '081234567891',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            
            $user = User::create($userData);
            $user->assignRole($role);
        }
    }
}
