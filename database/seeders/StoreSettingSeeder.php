<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        StoreSetting::create([
            'store_name'      => 'KASIRIN AJA',
            'store_address'   => 'Jl. Contoh No. 123, Jakarta',
            'store_phone'     => '021-12345678',
            'store_email'     => 'kasirin@example.com',
            'currency'        => 'IDR',
            'currency_symbol' => 'Rp',
            'timezone'        => 'Asia/Jakarta',
            'invoice_prefix'  => 'INV',
            'invoice_digits'  => 6,
        ]);

    }
}
