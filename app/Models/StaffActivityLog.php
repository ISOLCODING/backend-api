<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    // =====================================================================
    // STATIC HELPER — catat aktivitas
    // =====================================================================

    /**
     * Catat aktivitas staff ke log.
     */
    public static function log(
        int $userId,
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        array $properties = []
    ): void {
        static::create([
            'user_id'    => $userId,
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // =====================================================================
    // RELATIONS
    // =====================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
