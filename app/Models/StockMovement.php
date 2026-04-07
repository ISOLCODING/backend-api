<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'transaction_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reason',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'integer',
            'stock_before' => 'integer',
            'stock_after'  => 'integer',
        ];
    }

    // =====================================================================
    // RELATIONS
    // =====================================================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
