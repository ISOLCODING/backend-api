<?php

namespace Database\Seeders;

use App\Models\TaxSetting;
use Illuminate\Database\Seeder;

class TaxSettingSeeder extends Seeder
{
    public function run(): void
    {
        // PPN 11% — default aktif
        TaxSetting::firstOrCreate(
            ['tax_name' => 'PPN 11%'],
            [
                'tax_rate'   => 11.00,
                'is_active'  => true,
            ]
        );

        // Tidak ada pajak (untuk kebutuhan khusus)
        TaxSetting::firstOrCreate(
            ['tax_name' => 'Tanpa Pajak'],
            [
                'tax_rate'   => 0.00,
                'is_active'  => true,
            ]
        );
    }
}
