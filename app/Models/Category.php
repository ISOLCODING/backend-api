<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
    ];

    // =====================================================================
    // BOOT — Auto-generate slug dari nama
    // =====================================================================

    protected static function booted(): void
    {
        static::creating(function (Category $category): void {
            $category->slug = Str::slug($category->name);
        });

        static::updating(function (Category $category): void {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // =====================================================================
    // RELATIONS
    // =====================================================================

    /**
     * Parent category (untuk hierarki).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Sub-categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Semua produk dalam kategori ini.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
