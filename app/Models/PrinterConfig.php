<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterConfig extends Model
{
    protected $fillable = [
        'name',
        'printer_type',
        'device_address',
        'paper_size',
        'header_text',
        'footer_text',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // =====================================================================
    // BOOT — pastikan hanya satu printer yang is_default=true
    // =====================================================================

    protected static function booted(): void
    {
        static::saving(function (PrinterConfig $config): void {
            if ($config->is_default) {
                // Reset semua printer lain agar tidak ada dua default
                static::where('id', '!=', $config->id ?? 0)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }
}
