<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Permissions & Roles (harus pertama sebelum User)
            PermissionSeeder::class,

            // 2. Pengaturan toko & data master
            StoreSettingSeeder::class,
            TaxSettingSeeder::class,

            // 3. Users (dengan role assignment)
            UserSeeder::class,

            // 4. Katalog produk
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
