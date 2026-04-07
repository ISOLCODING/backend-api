<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'store_address',
        'store_phone',
        'store_email',
        'store_logo',
        'currency',
        'currency_symbol',
        'timezone',
        'invoice_prefix',
        'invoice_digits',
    ];

    protected function casts(): array
    {
        return [
            'invoice_digits' => 'integer',
        ];
    }

    // =====================================================================
    // SINGLETON — ambil/buat pengaturan toko default
    // =====================================================================

    /**
     * Ambil pengaturan toko. Jika belum ada, buat record default.
     */
    public static function getSettings(): static
    {
        return static::firstOrCreate([], [
            'store_name'      => 'KasirinAja Store',
            'currency'        => 'IDR',
            'currency_symbol' => 'Rp',
            'timezone'        => 'Asia/Jakarta',
            'invoice_prefix'  => 'INV',
            'invoice_digits'  => 6,
        ]);
    }

    /**
     * Ambil URL logo toko.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->store_logo ? asset('storage/' . $this->store_logo) : null;
    }
}
