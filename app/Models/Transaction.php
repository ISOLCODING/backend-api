<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voided_by',
        'invoice_number',
        'payment_method',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid',
        'change',
        'payment_status',
        'notes',
        'void_reason',
        'voided_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total'           => 'decimal:2',
            'paid'            => 'decimal:2',
            'change'          => 'decimal:2',
            'voided_at'       => 'datetime',
        ];
    }

    // =====================================================================
    // STATIC HELPERS
    // =====================================================================

    /**
     * Generate nomor invoice unik.
     * Format: INV/YYYYMMDD/000001
     */
    public static function generateInvoiceNumber(): string
    {
        $setting = StoreSetting::firstOrNew([]);
        $prefix = $setting->invoice_prefix ?? 'INV';
        $digits = $setting->invoice_digits ?? 6;

        $today = Carbon::now()->format('Ymd');
        $lastTransaction = static::whereDate('created_at', Carbon::today())
            ->orderByDesc('id')
            ->first();

        $sequence = $lastTransaction ? (int) $lastTransaction->id + 1 : 1;

        return sprintf('%s/%s/%0' . $digits . 'd', $prefix, $today, $sequence);
    }

    // =====================================================================
    // SCOPES
    // =====================================================================

    public function scopeToday($query): void
    {
        $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisMonth($query): void
    {
        $query->whereMonth('created_at', Carbon::now()->month)
              ->whereYear('created_at', Carbon::now()->year);
    }

    public function scopeNotVoided($query): void
    {
        $query->whereNull('voided_at');
    }

    public function scopeDateRange($query, ?string $startDate, ?string $endDate): void
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    public function isVoided(): bool
    {
        return ! is_null($this->voided_at);
    }

    // =====================================================================
    // RELATIONS
    // =====================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
