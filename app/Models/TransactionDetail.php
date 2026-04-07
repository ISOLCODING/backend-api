<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'buy_price',
        'sell_price',
        'quantity',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'buy_price'  => 'decimal:2',
            'sell_price' => 'decimal:2',
            'quantity'   => 'integer',
            'subtotal'   => 'decimal:2',
        ];
    }

    // =====================================================================
    // RELATIONS
    // =====================================================================

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
