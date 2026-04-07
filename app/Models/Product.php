<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'barcode',
        'name',
        'description',
        'buy_price',
        'sell_price',
        'stock',
        'min_stock',
        'unit',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'buy_price'  => 'decimal:2',
            'sell_price' => 'decimal:2',
            'stock'      => 'integer',
            'min_stock'  => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    // =====================================================================
    // SCOPES
    // =====================================================================

    /**
     * Filter produk yang aktif.
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Filter produk dengan stok di bawah minimum (stok menipis).
     */
    public function scopeLowStock($query): void
    {
        $query->whereRaw('stock <= min_stock')->where('is_active', true);
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    /**
     * Cek apakah stok sudah di bawah minimum.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Ambil URL gambar produk (gunakan placeholder jika belum ada gambar).
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/no-image.png');
    }

    // =====================================================================
    // RELATIONS
    // =====================================================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
