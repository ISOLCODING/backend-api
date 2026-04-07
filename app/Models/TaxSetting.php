<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $fillable = [
        'tax_name',
        'tax_rate',
        'tax_type',
        'rounding',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate'  => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // =====================================================================
    // SINGLETON PATTERN — ambil setting aktif atau buat default
    // =====================================================================

    /**
     * Ambil pengaturan pajak aktif. Jika tidak ada, kembalikan instance default.
     */
    public static function getActive(): static
    {
        return static::where('is_active', true)->first()
            ?? new static([
                'tax_name' => 'PPN',
                'tax_rate' => 11.00,
                'tax_type' => 'exclusive',
                'rounding' => 'normal',
                'is_active' => false,
            ]);
    }

    /**
     * Hitung pajak dari subtotal setelah diskon.
     */
    public function calculateTax(float $amount): float
    {
        if (! $this->is_active || ! $this->exists) {
            return 0;
        }

        return $this->tax_type === 'inclusive'
            ? $amount - ($amount / (1 + ($this->tax_rate / 100)))
            : $amount * ($this->tax_rate / 100);
    }
}
